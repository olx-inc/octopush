<?php

namespace Controllers;

use Models\JobMapper,
    Models\Job,
    Models\JobStatus,
    Models\Version,
    Silex\Application;

class QueueController
{
    private $_jobMapper;
    private $_config;
    private $_jenkins;
    private $_jobsController;
    private $_app;
    private $_log;
    private $_controlFile;

    public function __construct(Application $app, 
                                JobMapper $jobMapper, 
                                $jenkins, 
                                $jobsController, 
                                $log) {
        $this->_jobMapper = $jobMapper;
        $this->_config = $app['config'];
        $this->_app = $app;
        $this->_jenkins = $jenkins;
        $this->_jobsController = $jobsController;
        $this->_log = $log;
        $this->_controlFile = $this->_config['control_file'];
    }

    /**********************   API METHODS ***********************/
    public function queueJob($env, $module, $version)
    {
        $config = $this->_config;
        $jenkins = '';

        if (!array_key_exists($module, $config['modules'])) {
            $error = array(
                'status' => "error",
                'message' => "$module is not a valid module to push."
            );
            $this->_log->addError($error['message']);

            return $this->_app->json($error);
        }

        $this->_log->addInfo('checking jenkins');
        if (array_key_exists('HTTP_JENKINS', $_SERVER)) {
            $jenkins = $_SERVER['HTTP_JENKINS'];
        }

        try {
            $job = Job::createWith($module, $version, $env, $jenkins);
            $this->_jobMapper->save($job);

            $result = array(
                'status' => "success",
                'message' => "Job inserted in queue",
                'job_id' => (int) $job->getId(),
            );
            $this->_log->addInfo($result['message'] . " with id: " . $result['job_id']);
        } catch (\Exception $exc) {
            $result = array(
                'status' => "error",
                'message' => "Job not inserted in queue",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($result['message'] . " :: " . $result['detail']);
        }

        return $this->_app->json($result);
    }

    public function deploying()
    {
        $jobsGoingLive = $this->_jobMapper->findAllByStatus(JobStatus::GOING_LIVE);
        if (count($jobsGoingLive) == 0) {
            $result = array(
                'status' => "Idle",
                'module' => "-",
                'version' => "-",
            );
            return $this->_app->json($result);
        }        
        foreach ($jobsGoingLive as $job) {
            $result = array(
                'status' => "Deploying",
                'module' => $job->getTargetModule(),
                'version' => $job->getTargetVersion(),                
            );
            return $this->_app->json($result);
            
        }
    }

    public function my_components($state)
    {
        return "";
    }
    
    public function deployed($env)
    {
        $queueLenght = $config['jobs']['queue.lenght'] ? $config['jobs']['queue.lenght'] : null;

        if ($env == 'staging')
            $result =  $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::TESTS_PASSED, JobStatus::TESTS_FAILED, JobStatus::DEPLOY_FAILED), $queueLenght, 'json');
        elseif ($env == 'prod')
            $result = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GO_LIVE_DONE, JobStatus::GO_LIVE_FAILED), $queueLenght, 'json');

        return $this->_app->json($result);
    }

   public function queued($env)
    {
        if ($env == 'staging')
            $queuedJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED));
        elseif ($env == 'prod')
            $queuedJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED_FOR_LIVE));

        return $this->_app->json($queuedJobs);
    }

   public function inprogress($env)
    {
        if ($env == 'staging')
            $inProgressJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::DEPLOYING, JobStatus::PENDING_TESTS));
        elseif ($env == 'prod')
            $inProgressJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GOING_LIVE));

        return $this->_app->json($inProgressJobs);
    }

    public function pause()
    {
        $success = true;
        $success = file_put_contents($this->_controlFile, 'pause');

        return $this->_jsonResult($success);
    }


    public function resume()
    {
        $success = true;
        if ($this->_isPaused()) {
            $success = unlink($this->_controlFile);
        }

        return $this->_jsonResult($success);
    }

    public function status()
    {
        $status = 'ON';
        if ($this->_isPaused()) $status = 'OFF';
        return $status;
    }

    public function health()
    {
        $this->_jenkins->ping();
        $this->_jobMapper->findAllByStatus(JobStatus::DEPLOYING, 1);

        $status = 'Ok';
        if ($this->_isPaused()) $status = 'Paused';
        return "Status: " . $status . "\nVersion: " . Version::getFull();
    }

    public function processJob()
    {
        $resultOk = true;
        try {
            if ($this->_isPaused()) {
                $this->_log->addInfo("The service is paused");

                return $this->_jsonResult(true, "The service is paused");
            }

            $modules = $this->_config['modules'];

            $this->_processJobs();
            $this->_processQueue($modules);

            $this->_processLiveJobs();
            $this->_processLiveQueue($modules);
        } catch (\Exception $exception) {
            $resultOk = false;
            $this->_log->addError("Error when processing Jobs queue:" . $exception->getMessage());
        }

        return $this->_jsonResult($resultOk);
    }

    private function _processQueue($modules)
    {
        $jobsToProcess = $this->_jobMapper->findAllByStatus(JobStatus::QUEUED);
        $this->_log->addInfo("About processing the testing queue");
        if (count($jobsToProcess) == 0) {
            $this->_log->addInfo("The testing queue is empty");
        }

        foreach ($jobsToProcess as $job) {
            $jobsInProgress = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::DEPLOYING, JobStatus::PENDING_TESTS));
            if ($job->canRun($jobsInProgress, $modules)) {
                $job->moveStatusTo(JobStatus::DEPLOYING);
                $this->_jobMapper->save($job);
                if ($this->_jenkins->push($job)) {
                    $this->_jobMapper->save($job);
                    $result = array(
                        'status' => "success",
                        'message' => "Job {$job->getId()} in progress.",
                    );
                    $this->_log->addInfo($result['message']);
                } else {
                    $job->moveStatusTo(JobStatus::DEPLOY_FAILED);
                    $this->_jobMapper->save($job);
                    $result = array(
                        'status' => "error",
                        'message' => "Job {$job->getId()} failed.",
                    );
                    $this->_log->addError($result['message']);
                }
            }
        }
    }

    private function _processJobs()
    {
        $jobs = $this->_jobMapper->findAllByStatus(JobStatus::DEPLOYING);
        $this->_log->addInfo("Checking progress of jobs that are already running");
        foreach ($jobs as $runningJob) {
            $buildStatus = $this->_jenkins->getLastBuildStatus($runningJob);
            switch ($buildStatus) {
                case "SUCCESS":
                    $runningJob->moveStatusTo(JobStatus::PENDING_TESTS);
                    $this->_jobMapper->save($runningJob);
                    $message = "Job successfully processed.JobId:" . $runningJob->getId();
                    $this->_log->addInfo($message);
                    break;
                case "ABORTED":
                case "FAILURE":
                    $runningJob->moveStatusTo(JobStatus::DEPLOY_FAILED);
                    $this->_jobMapper->save($runningJob);
                    $message = "Job failed. JobId:" . $runningJob->getId();
                    $this->_log->addError($message);
                    break;
                default:
                    $message = "Jenkins still working";
                    $this->_log->addInfo($message);
            }
        }
    }

    private function _processLiveJobs()
    {
        $jobs = $this->_jobMapper->findAllByStatus(JobStatus::GOING_LIVE);
        $this->_log->addInfo("Checking progress of jobs that are already running");
        foreach ($jobs as $runningJob) {
            $buildStatus = $this->_jenkins->getLastBuildStatus($runningJob);
            switch ($buildStatus) {
                case "SUCCESS":
                    $runningJob->moveStatusTo(JobStatus::GO_LIVE_DONE);
                    $this->_jobMapper->save($runningJob);
                    $message = "Job successfully processed.JobId:" . $runningJob->getId();
                    $this->_log->addInfo($message);
                    break;
                case "ABORTED":
                case "FAILURE":
                    $runningJob->moveStatusTo(JobStatus::GO_LIVE_FAILED);
                    $this->_jobMapper->save($runningJob);
                    $message = "Job failed. JobId:" . $runningJob->getId();
                    $this->_log->addError($message);
                    break;
                default:
                    $message = "Jenkins still working";
                    $this->_log->addInfo($message);
            }
        }
    }

    private function _processLiveQueue($modules)
    {
        $jobsGoingLive = $this->_jobMapper->findAllByStatus(JobStatus::GOING_LIVE);
        if (count($jobsGoingLive) > 0) {
            $this->_log->addInfo("There are jobs going LIVE, exit!"); 
            exit;
        }        
        $jobsToProcess = $this->_jobMapper->findAllByStatus(JobStatus::QUEUED_FOR_LIVE, 1);

        $this->_log->addInfo("About processing the live queue");
        if (count($jobsToProcess) == 0) {
            $this->_log->addInfo("The Live queue is empty");
        }
        foreach ($jobsToProcess as $job) {
            $job->moveStatusTo(JobStatus::GOING_LIVE);
            $this->_jobMapper->save($job);
            if ($this->_jenkins->pushLive($job)) {
                $this->_jobMapper->save($job);
                $result = array(
                    'status' => "success",
                    'message' => "Job {$job->getId()} going live in progress.",
                );
                $this->_log->addInfo($result['message']);
            } else {
                $job->moveStatusTo(JobStatus::GO_LIVE_FAILED);
                $this->_jobMapper->save($job);
                $result = array(
                    'status' => "error",
                    'message' => "Job {$job->getId()} go live failed.",
                );
                $this->_log->addError($result['message']);
            }
        }
    }

    /**********************   UI HANDLER METHODS ***********************/
    public function showJobs()
    {
        $app = $this->_app;
        $config = $this->_config;

        $queueLenght = $config['jobs']['queue.lenght'] ?
                $config['jobs']['queue.lenght'] : null;

        $processedLenght = $config['jobs']['processed.lenght'] ?
                $config['jobs']['processed.lenght'] : null;

        try {
            $queuedJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED));
            $inProgressJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::DEPLOYING, JobStatus::PENDING_TESTS));
            $sessionHelper = $app['helpers.session'];
            if ( $sessionHelper->isMyComponentsOn() ){
                $processedJobs =  $this->_jobMapper->findAllByMultipleStatusAndModules(
                    array(JobStatus::TESTS_PASSED, JobStatus::TESTS_FAILED, JobStatus::DEPLOY_FAILED), 
                    $sessionHelper->getPermissions(), $processedLenght);
                    
                $liveProcessed = $this->_jobMapper->findAllByMultipleStatusAndModules(
                    array(JobStatus::GO_LIVE_DONE, JobStatus::GO_LIVE_FAILED), 
                    $sessionHelper->getPermissions(), $processedLenght);

            }else{
                $processedJobs =  $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::TESTS_PASSED, JobStatus::TESTS_FAILED, JobStatus::DEPLOY_FAILED), $processedLenght);

                $liveProcessed = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GO_LIVE_DONE, JobStatus::GO_LIVE_FAILED), $processedLenght);
            }

            $liveQueue = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED_FOR_LIVE));
            $liveInProgress = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GOING_LIVE));
        } catch (\Exception $exc) {
            $this->_app->abort(503, $exc->getMessage());
        }

        return $app['twig']->render('index.twig', array(
            'isPaused' => $this->_isPaused(),
            'contact' => $config['contact_to'],
            
            'queued_jobs' => $queuedJobs,
            'in_progress_jobs' => $inProgressJobs,
            'processed_jobs' => $processedJobs,

            'liveQueue' => $liveQueue,
            'liveInProgress' => $liveInProgress,
            'liveProcessed' => $liveProcessed,

            'version' => Version::getShort(),
            
            'userdata' => $app['helpers.session']->getUserData(),

            'jenkins' => $this->_jenkins,
            'jobsController' => $this->_jobsController,
            'logoutUrl' =>  $app['url_generator']->generate('logout', array(
                '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')))
        ));
    }

    /**********************   PRIVATE METHODS ***********************/
    private function _isPaused()
    {
        return file_exists($this->_controlFile);
    }

    private function _jsonResult($success, $message="")
    {
        $result = $success? 'SUCCESS':'ERROR';
        $data = array('result' => $result, 'message' => $message);

        return $this->_app->json($data);
    }

}
