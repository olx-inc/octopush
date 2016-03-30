<?php

namespace Controllers;

use Models\JobMapper,
    Models\VersionMapper,
    Models\Job,
    Models\Version,
    Models\JobStatus,
    Models\OctopushVersion,
    Services\ThirdParty,
    Services\Jenkins,
    Library\OctopushApplication;

class QueueController
{
    private $_jobMapper;
    private $_versionMapper;
    private $_config;
    private $_jenkins;
    private $_app;
    private $_log;
    private $_thirdParty;


    public function __construct(OctopushApplication $app,
                                JobMapper $jobMapper,
                                VersionMapper $versionMapper,
                                Jenkins $jenkins,
                                $log) {
        $this->_jobMapper = $jobMapper;
        $this->_versionMapper = $versionMapper;
        $this->_config = $app['config'];
        $this->_app = $app;
        $this->_jenkins = $jenkins;
        $this->_log = $log;
        $this->_thirdParty = $app['services.ThirdParty'];
    }

    // Refactorizar!
    private function getKeyAndSession(){
        if (isset($_REQUEST['access_token'])){
            Session::buildBackendSession($this->_app, $_REQUEST['access_token']);
        }
    }

    /**********************   API METHODS ***********************/
    public function queueJob($env, $module, $version)
    {
        $config = $this->_config;
        $jenkins = '';
        $helperSession = $this->_app['helpers.session'];
        $this->getKeyAndSession();

        $this->_log->addInfo('checking jenkins');
        if (array_key_exists('HTTP_JENKINS', $_SERVER)) {
            $jenkins = $_SERVER['HTTP_JENKINS'];
        }

        try {
            $job = Job::createWith($module, $version, $env, $jenkins);
            $job->setStatusId(JobStatus::getStatusId(
                          JobStatus::getQueuedStatus($env)));
            $email = $helperSession->getUser()->getEmail();
            if (!empty($email))
                $job->setUser($email);

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


    public function pause()
    {
        return $this->_pause("1");
    }
    public function _pause($paused)
    {
        $success = true;
        try {
          $version = Version::createFromArray(array('environment' => 'paused',
                              'module' => 'octopush', 'version' => $paused ) );

          $this->_versionMapper->save($version);

          return $this->_jsonResult(true);

        } catch (\Exception $exception) {
          return $this->_jsonResult(false, $exception->getMessage());
        }
    }

    private function _isPaused()
    {
        $paused = $this->_versionMapper->find("paused", "octopush");
        return (!empty($paused) and ($paused=="1"));
    }

    public function resume()
    {
        return $this->_pause("0");
    }

    public function status()
    {
        $status = 'ON';
        if ($this->_isPaused()) $status = 'OFF';
//        $status = $this->_app['paused'];
        return $status;
    }

    public function health()
    {
        $this->_jenkins->ping();
        $this->_jobMapper->findAllByStatus(JobStatus::DEPLOYING, 1);

        $status = 'Ok';
        if ($this->_isPaused()) $status = 'Paused';
        return "Status: " . $status . "\nVersion: " . OctopushVersion::getFull();
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
            $this->_processLiveQueue();
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
            $jobsInProgress = $this->_jobMapper->findAllByMultipleStatusAndModules(
                array(JobStatus::DEPLOYING, JobStatus::PENDING_TESTS));
            $jobsInProgress = $this->fillResults($jobsInProgress);

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


    private function fillResults($data){
        $result = array();
        foreach ($data as $record) {
            $job = Job::createFromArray($record);
            array_push($result, $job);
        }
        return $result;
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
                    $version = Version::createFromJob($runningJob);
                    $this->_versionMapper->save($version);
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
                    $version = Version::createFromJob($runningJob);
                    $this->_versionMapper->save($version);
                    $this->_thirdParty->postDeploy($runningJob, ThirdParty::DEPLOY_SUCCESS);
                    $message = "Job successfully processed.JobId:" . $runningJob->getId();
                    $this->_log->addInfo($message);
                    break;
                case "ABORTED":
                case "FAILURE":
                    $runningJob->moveStatusTo(JobStatus::GO_LIVE_FAILED);
                    $this->_jobMapper->save($runningJob);
                    $this->_thirdParty->postDeploy($runningJob, ThirdParty::DEPLOY_FAILED);
                    $message = "Job failed. JobId:" . $runningJob->getId();
                    $this->_log->addError($message);
                    break;
                default:
                    $message = "Jenkins still working";
                    $this->_log->addInfo($message);
            }
        }
    }

    private function setJobTicket($job)
    {
        $ticket=$job->getTicket();
        if (!isset($ticket)) {
          $ticket = $this->_thirdParty->preDeploy($job);

          if (isset($ticket)) {
            $job->setTicket($ticket);
            return true;
          } else
            return false;
        }
        return true;

    }

    private function _processLiveQueue()
    {
        $jobsGoingLive = $this->_jobMapper->findAllByStatus(JobStatus::GOING_LIVE);
        if (count($jobsGoingLive) > 0) {
            $this->_log->addInfo("There are jobs going LIVE, exit!");
            return;
        }
        $jobsToProcess = $this->_jobMapper->findAllByStatus(JobStatus::QUEUED_FOR_LIVE, 1);

        $this->_log->addInfo("About processing the live queue");
        if (count($jobsToProcess) == 0) {
            $this->_log->addInfo("The Live queue is empty");
        }
        foreach ($jobsToProcess as $job) {
            $hasTicket=setJobTicket($job);
            $job->moveStatusTo(JobStatus::GOING_LIVE);
            $job->setTargetEnvironment(Version::PRODUCTION);
            $this->_jobMapper->save($job);
            if ($hasTicket && $this->_jenkins->pushLive($job)) {
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
    private function _index($page)
    {
        $app = $this->_app;
        $config = $this->_config;

        $sessionHelper = $app['helpers.session'];

        return $app['twig']->render($page . '.html', array(
            'contact' => $config['contact_to'],
            'my_components' => $sessionHelper->getMyComponentsValue(),
            'version' => OctopushVersion::getShort(),
            'userdata' => $app['helpers.session']->getUserData(),
            'logoutUrl' =>  $app['url_generator']->generate('logout', array(
                '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')))
        ));
    }

    public function index()
    {
        return $this->_index("index");
    }

    public function versions()
    {
        return $this->_index("versions");
    }

    /**********************   PRIVATE METHODS ***********************/

    private function _jsonResult($success, $message="")
    {
        $result = $success? 'SUCCESS':'ERROR';
        $data = array('result' => $result, 'message' => $message);

        return $this->_app->json($data);
    }

}
