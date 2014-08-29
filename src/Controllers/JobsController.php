<?php

namespace Controllers;

use Models\JobStatus,
    Models\JobMapper,
    Models\Job,
    Silex\Application,
    Controllers\JenkinsController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/* Handle requests related to specific jobs, all request expects a job_id parameter */
class JobsController
{
    private $_config;
    private $_jobMapper;
    private $_log;
    private $_app;
    private $_thirdParty;

    public function __construct(Application $app, 
                                $config,
                                JobMapper $jobMapper,
                                $jenkins, 
                                $log)
    {
        $this->_config = $config;
        $this->_jobMapper = $jobMapper;
        $this->_log = $log;
        $this->_app = $app;
        $this->_jenkins = $jenkins;
        $this->_thirdParty = $app['services.ThirdParty'];
    }

    public function createJob(Request $request)
    {
        $config = $this->_config;
        $jenkins = '';

        $env = 'testing';
        $jenkins = $request->get('requestor');
        $module = $request->get('module');
        $version = $request->get('version');

        if (!array_key_exists($module, $config['modules'])) {
            $error = array(
                'status' => "error",
                'message' => $module . " is not a valid module to push."
            );
            $this->_log->addError($error['message']);

            return $this->_app->json($error);
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

    public function getJobStatus($jobId)
    {
        try {
            $job = $this->_jobMapper->get($jobId);
            $result = array(
                'job_status' => $job->getStatus(),
                'job_id' => $jobId
            );

            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to get job status",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }
    }

    public function cancel($jobId)
    {
        try {
            $job = $this->_jobMapper->get($jobId);
            $job->moveStatusTo(JobStatus::DEPLOY_FAILED);
            $this->_jobMapper->save($job);

            $result = array(
                'job_status' => $job->getStatus(),
                'job_id' => $jobId
            );

            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to cancel job",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }
    }

    public function goLive($jobId)
    {
        try {
            $job = $this->_jobMapper->get($jobId);

            $helperSession = $this->_app['helpers.session'];
            $permissions = $helperSession->getPermissions();
            
            if (isset($permissions) && 
                    $this->canBePushedLive($job, $permissions) && 
                    $job->canGoLive()) {
                
                $email = $helperSession->getUser()->getEmail();
                if (!empty($email))
                    $job->setUser($email);
                else
                    $job->setUser($helperSession->getUser()->getUserName());

                $response = $this->_thirdParty->preDeploy($job);
                $ticket = isset($response->ticket) ? $response->ticket : false;
                
                if ($ticket) {
                    $job->setTicket($ticket);
                    $job->moveStatusTo(JobStatus::QUEUED_FOR_LIVE);
                } else {
                    $job->movestatusTo(JobStatus::GO_LIVE_FAILED);
                }
                
                $this->_jobMapper->save($job);

                $result = array(
                    'job_status' => $job->getStatus(),
                    'job_id' => $jobId
                );
                return $this->_app->json($result);
            } else {
                $result = array(
                'job_status' => $job->getStatus(),
                'job_id' => $jobId,
                'status' => "Error",
                'message' => "The job is not in a valid status to go live or "
                    . "you don't have permissions to do this action"
                );
                return $this->_app->json($result);
            }
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to go live with Job",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }
    }

    public function rollback($jobId)
    {   
        try {
            $oldJob = $this->_jobMapper->get($jobId);
            $helperSession = $this->_app['helpers.session'];
            $permissions = $helperSession->getPermissions();
            
            if (! isset($permissions) || 
                ! $this->canBePushedLive($oldJob, $permissions) || 
                ! $oldJob->wentLive()) {
                
                $result = array(
                    'job_status' => $oldJob->getStatus(),
                    'job_id' => $jobId,
                    'status' => "Error",
                    'message' => "The job is not in a valid status to rollback "
                        . "or you don't have permissions to do this action"
                );

                return $this->_app->json($result);
            }
            
            $job = Job::createWith($oldJob->getTargetModule(), $oldJob->getTargetVersion(), 
                    $oldJob->getTargetEnvironment(), $oldJob->getRequestorJenkins());
            $job->movestatusTo(JobStatus::QUEUED_FOR_LIVE);
            $job->setRollbackedFrom($oldJob->getId());
            $job->setTicket($oldJob->getTicket());

            $response = $this->_thirdParty->preDeploy($job, "rollback");
            $job->setTicket($response->ticket);
            $job->setUser($helperSession->getUser()->getEmail());
            
            $this->_jobMapper->save($job);

            $result = array(
                'status' => "success",
                'message' => "Job inserted in queue",
                'job_id' => (int) $job->getId(),
            );
            $this->_log->addInfo($result['message'] . " with id: " . $result['job_id']);
            
            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $result = array(
                'status' => "error",
                'message' => "Job not inserted in queue",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($result['message'] . " :: " . $result['detail']);
            return $this->_app->json($result);
        }

    }
    
    public function registerTestJobUrl(Request $request)
    {
        try {
            $jobId = $request->get('jobId');
            $url = $request->get('test_job_url');
            $job = $this->_jobMapper->get($jobId);
            $job->setTestJobUrl($url);

            $this->_jobMapper->save($job);

            $result = array(
                'status' => "success",
                'message' => "Test job url registerd"
            );

            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Test job url",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }
    }

    public function registerTestJobResult(Request $request)
    {
        try {
            $jobId = $request->get('jobId');
            $success = $request->get('success');
            $status = $success == 'true' ? JobStatus::TESTS_PASSED : JobStatus::TESTS_FAILED;

            $job = $this->_jobMapper->get($jobId);
            $job->moveStatusTo($status);

            $this->_jobMapper->save($job);

            $result = array(
                'status' => "success",
                'message' => "Test result registered"
            );

            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Test result",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }
    }

    public function registerTestResult($jobId, $success)
    {
        try {
            $status = $success == 'true' ? JobStatus::TESTS_PASSED : JobStatus::TESTS_FAILED;

            $job = $this->_jobMapper->get($jobId);
            $job->moveStatusTo($status);

            $this->_jobMapper->save($job);

            $result = array(
                'status' => "success",
                'message' => "Test result registered"
            );

            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Test result",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }
    }
    
    public function canBePushedLive($job) 
    {
        $permissions = $this->_app['helpers.session']->getPermissions();
        
        if (isset($permissions)) {
            if ($this->_thirdParty->canMemberGoLive(
                $permissions, 
                $job->getTargetModule()
            )) {
                return true;
            }
        }               
            
        return false;
    } 



    /*************************/

    public function deploying() //DEPRECATE and use inprogress(prod)
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
        $sessionHelper = $this->_app['helpers.session'];

        $sessionHelper->setMyComponents($state);

        return $sessionHelper->isMyComponentsOn();
    }

    public function all(){
        $all = array('preprodQueue' => $this->_queued('staging'));
        $all['preprodInprogress'] = $this->_inprogress('staging');
        $all['preprodDeployed'] = $this->_deployed('staging');
        $all['prodQueue'] = $this->_queued('prod');
        $all['prodInprogress'] = $this->_inprogress('prod');
        $all['prodDeployed'] = $this->_deployed('prod');

        return $this->_app->json($all);
    }

    private function _deployed($env)
    {
        $queueLenght = $this->_config['jobs']['queue.lenght'] ? 
                            $this->_config['jobs']['queue.lenght'] : null;

        $sessionHelper = $this->_app['helpers.session'];
        $statuses = array('staging' => array(JobStatus::TESTS_PASSED, JobStatus::TESTS_FAILED, JobStatus::DEPLOY_FAILED), 
            'prod' => array(JobStatus::GO_LIVE_DONE, JobStatus::GO_LIVE_FAILED));
        if ( $sessionHelper->isMyComponentsOn() ){
            $perm = $sessionHelper->getPermissions();
            $result =  $this->_jobMapper->findAllByMultipleStatusAndModules($statuses[$env], $perm["repositories"], $queueLenght);
        }else{
            $this->_log->addInfo("Components OFF: ");
            $result =  $this->_jobMapper->findAllByMultipleStatus($statuses[$env], $queueLenght);
        }        
        $result = $this->fillResults($result, 'json', $this->_jenkins);
        return $result;
    }

    private function fillResults($data, $type, $jenkins){
        $result = array();
        foreach ($data as $record) {
            $job = Job::createFromArray($record);
            $job_array = $job->serialize();
            
            $job_array['_buildJobUrl'] = $jenkins->getRequestorJobConsoleUrl($job);
            $job_array['_deployJobUrl'] = $jenkins->getPreProdJobDeployUrl($job);
            $job_array['_deployLiveJobUrl'] = $jenkins->getLiveJobDeployUrl($job);

            $job_array['_canGoLive'] = ($job->canGoLive() && $this->canBePushedLive($job));

            $job_array['_canRollback'] = ($job->wentLive() && $this->canBePushedLive($job));

            array_push($result, $job_array);
        }

        return $result;
    }

    
    public function deployed($env)
    {
        $deployed = $this->_deployed($env);
        return $this->_app->json($deployed);
    }

   public function queued($env)
    {
        $queued = $this->_queued($env);
        return $this->_app->json($queued);
    }

   private function _queued($env)
    {
        if ($env == 'staging')
            $queuedJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED), null);
        elseif ($env == 'prod')
            $queuedJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::QUEUED_FOR_LIVE), null);

        $result = $this->fillResults($queuedJobs, 'json', $this->_jenkins);
        return $result;
    }

   public function inprogress($env)
    {
        $inprogress = $this->_inprogress($env);
        return $this->_app->json($inprogress);
    }

   private function _inprogress($env)
    {
        if ($env == 'staging')
            $inProgressJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::DEPLOYING, JobStatus::PENDING_TESTS), null);
        elseif ($env == 'prod')
            $inProgressJobs = $this->_jobMapper->findAllByMultipleStatus(array(JobStatus::GOING_LIVE), null);

        $result = $this->fillResults($inProgressJobs, 'json', $this->_jenkins);
        return $result;
    }


}
