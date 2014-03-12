<?php

namespace Controllers;

use Models\JobStatus,
    Models\JobMapper,
    Models\Job,
    Controllers\JenkinsController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;


/* Handle requests related to specific jobs, all request expects a job_id parameter */
class JobsController
{
    private $_config;
    private $_jobMapper;
    private $_log;

    public function __construct($config, 
                                JobMapper $jobMapper,
                                $log) 
    {
        $this->_config = $config;
        $this->_jobMapper = $jobMapper;
        $this->_log = $log;
    }

    public function createJob(Request $request) 
    {
        $config = $this->_config;
        $jenkins = '';

        $env = 'testing';
        $jenkins = $request->get('requestor'); 
        $module = $request->get('module');
        $version = $request->get('version');

        if (!in_array($env, $config['environments'])) {
            $error = array(
                'status' => "error",
                'message' => "$env is not a valid environment."
            );
            $this->_log->addError($error['message']);
            return json_encode($error);
        }

        if (!array_key_exists($module, $config['modules'])) {
            $error = array(
                'status' => "error",
                'message' => $module . " is not a valid module to push."
            );
            $this->_log->addError($error['message']);
            return json_encode($error);
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

    public function getJobStatus($jobId)
    {
        try {
            $job = $this->_jobMapper->get($jobId);
            $result = array(
                'job_status' => $job->getStatus(),
                'job_id' => $jobId
            );
            return json_encode($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to get job status",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);
            return json_encode($error);
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
            return json_encode($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to cancel job",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);
            return json_encode($error);
        }
    }

    public function goLive($jobId)
    {
        try {
            $job = $this->_jobMapper->get($jobId);

            if ($job->canGoLive()) {
                $job->moveStatusTo(JobStatus::QUEUED_FOR_LIVE);
                $this->_jobMapper->save($job);

                $result = array(
                'job_status' => $job->getStatus(),
                'job_id' => $jobId
                );
                return json_encode($result);
            } else {
                $result = array(
                'job_status' => $job->getStatus(),
                'job_id' => $jobId,
                'status' => "Error",
                'message' => "The job is not in a valid status to go live"
                );
                return json_encode($result);
            }
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to go live with Job",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);
            return json_encode($error);
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
            return json_encode($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Test job url",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);
            return json_encode($error);
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
                'message' => "Test result registerd"
            );
            return json_encode($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Test result",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);
            return json_encode($error);
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
                'message' => "Test result registerd"
            );
            return json_encode($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Test result",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);
            return json_encode($error);
        }
    }


}
