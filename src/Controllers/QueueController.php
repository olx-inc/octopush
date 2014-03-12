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
    private $_app;
    private $_log;
    private $_controlFile;
    
    public function __construct(Application $app, JobMapper $jobMapper, $jenkins, $log) 
    {
        $this->_jobMapper = $jobMapper;
        $this->_config = $app['config'];
        $this->_app = $app;
        $this->_jenkins = $jenkins;
        $this->_log = $log;
        $this->_controlFile = __DIR__.'/../control/control.txt';
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
            return json_encode($error);
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
        
        return json_encode($result);
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

    public function health()
    {
        $this->_jenkins->ping();
        $this->_jobMapper->findAllByStatus(JobStatus::DEPLOYING, 1);
        return "ok. Version: " . Version::getFull();
    }

    public function processJob() 
    {
        if ($this->_isPaused()) {
            $this->_log->addInfo("The service is paused");
            return $this->_jsonResult(true, "The service is paused");
        }

        $modules = $this->_config['modules'];

        $this->_processJobs();
        $this->_processQueue($modules);

        $this->_processLiveJobs();
        $this->_processLiveQueue($modules);
        return $this->_jsonResult(true, "Successful operation");
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
            switch ($buildStatus)
            {
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
            switch ($buildStatus)
            {
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
        $jobsToProcess = $this->_jobMapper->findAllByStatus(JobStatus::QUEUED_FOR_LIVE);
        $this->_log->addInfo("About processing the live queue");
        if (count($jobsToProcess) == 0) {
            $this->_log->addInfo("The Live queue is empty");
        }
        foreach ($jobsToProcess as $job) {
            $jobsInProgress = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GOING_LIVE));
            if ($job->canRun($jobsInProgress, $modules)) {                
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
            $processedJobs =  $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::TESTS_PASSED, JobStatus::TESTS_FAILED, JobStatus::DEPLOY_FAILED), $processedLenght);

            $liveQueue = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED_FOR_LIVE));
            $liveInProgress = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GOING_LIVE));
            $liveProcessed = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GO_LIVE_DONE, JobStatus::GO_LIVE_FAILED), $processedLenght);
        } catch (\Exception $exc) {
            $this->_app->abort(503, $exc->getMessage());
        }
        return $app['twig']->render('index.twig', array(
            'queued_jobs' => $queuedJobs,
            'in_progress_jobs' => $inProgressJobs,
            'processed_jobs' => $processedJobs,
            
            'liveQueue' => $liveQueue,
            'liveInProgress' => $liveInProgress,
            'liveProcessed' => $liveProcessed,

            'version' => Version::getShort(),

            'jenkins' => $this->_jenkins,
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
        return json_encode($data);
    }

}
