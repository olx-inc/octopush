<?php

namespace Models;

class Job
{
    private $_id;
    private $_targetModule;
    private $_targetVersion;
    private $_targetEnvironment;
    private $_requestorJenkins;
    private $_queued_at;
    private $_updated_at;
    private $_statusId;
    private $_status;
    private $_deploymentJobId;
    private $_testJobUrl;
    private $_liveJobId;
    private $_user;
    private $_ticket;
    private $_rollbackedFrom;

    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getTargetModule()
    {
        return $this->_targetModule;
    }

    public function getTargetVersion()
    {
        return $this->_targetVersion;
    }

    public function getTargetEnvironment()
    {
        return $this->_targetEnvironment;
    }

    public function setTargetEnvironment($env)
    {
        $this->_targetEnvironment = $env;
    }

    public function getRequestorJenkins()
    {
        return $this->_requestorJenkins;
    }

    public function getTestJobUrl()
    {
        return $this->_testJobUrl;
    }

    public function setTestJobUrl($url)
    {
        $this->_testJobUrl = $url;
    }

    public function getQueuedDate()
    {
        return $this->_queued_at;
    }

    public function getUpdateDate()
    {
        return $this->_updated_at;
    }

    public function setUpdateDate($date)
    {
        $this->_updated_at = $date;
    }

    public function getStatus()
    {
        return JobStatus::getJobStatus($this->_statusId);
    }

    public function setStatusId($statusId)
    {
        $this->_statusId = $statusId;
        $this->_status = JobStatus::getJobStatus($statusId);
    }

    public function moveStatusTo($newStatus)
    {
        $newStatusId = JobStatus::getStatusId($newStatus);
        if ($newStatusId < $this->_statusId) {
            throw new InvalidOperationException();
        }
        $this->_statusId = $newStatusId;
    }

    public function setDeploymentJobId($id)
    {
        $this->_deploymentJobId = $id;
    }

    public function getDeploymentJobId()
    {
        return $this->_deploymentJobId;
    }

    public function setLiveJobId($id)
    {
        $this->_liveJobId = $id;
    }

    public function getLiveJobId()
    {
        return $this->_liveJobId;
    }

   public function setUser($user)
    {
        $this->_user = $user;
    }

    public function getUser()
    {
        return $this->_user;
    }

   public function setTicket($ticket)
    {
        $this->_ticket = $ticket;
    }

    public function getTicket()
    {
        return $this->_ticket;
    }

   public function getRollbackedFrom()
    {
        return $this->_rollbackedFrom;
    }

    public function setRollbackedFrom($id)
    {
        $this->_rollbackedFrom = $id;
    }


    public function __construct()
    {
        $this->_id = 0;
        $this->_statusId = 0;
        $this->_status = JobStatus::QUEUED;
        $this->_deploymentJobId = 0;
    }

    public function canRun($jobsInProgress)
    {
        foreach ($jobsInProgress as $job) {
            // check is the module is already running
            if ($job->getTargetModule() == $this->getTargetModule()) {
                return false;
            }
        }

        return true;
    }

    public function canGoLive()
    {
        return ($this->getStatus() == JobStatus::TESTS_PASSED);
    }

    public function wentLive()
    {
        return ($this->_statusId > 6);
    }

    public function isARollback()
    {
        return (!empty($this->_rollbackedFrom));
    }

    public static function createWith($module, $version, $env, $jenkins)
    {
        $job = new Job();
        $job->_targetModule = $module;
        $job->_targetVersion = $version;
        $job->_targetEnvironment = $env;
        $job->_requestorJenkins = $jenkins;
        $job->_testJobUrl = "";
        $job->_user = "";
        $job->_ticket = "";

        return $job;
    }

    public static function createFromArray($data)
    {
        $job = new Job();
        $job->_id = (int) $data['job_id'];
        $job->_statusId = JobStatus::getStatusId($data['status']);
        $job->_status = $data['status'];
        $job->_targetModule = $data['module'];
        $job->_targetVersion = $data['version'];
        $job->_targetEnvironment = $data['environment'];
        $job->_requestorJenkins = $data['jenkins'];
        $testJobKey = 'test_job_url';
        $job->_testJobUrl = isset($data[$testJobKey]) ? $data[$testJobKey] : "";
        $job->_queued_at = $data['queue_date'];
        $job->_updated_at = $data['updated_at'];
        $key = 'deployment_job_id';
        $job->_deploymentJobId = isset($data[$key]) ? $data[$key] : 0;
        $key = 'live_job_id';
        $job->_liveJobId = isset($data[$key]) ? $data[$key] : 0;
        $job->_user = isset($data['user']) ? $data['user'] : "";
        $job->_ticket = isset($data['ticket']) ? $data['ticket'] : "";
        $job->_rollbackedFrom = isset($data['rollback_id']) ? $data['rollback_id'] : null;

        return $job;
    }

    public function serialize()
    {
        return get_object_vars($this);

    }

}
