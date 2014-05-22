<?php

namespace Models;

use Doctrine\DBAL\Connection;

class JobMapper
{
    private $_db;

    const GET_JOB_STATEMENT = "SELECT * FROM jobs WHERE job_id = ?";
    const FIND_ALL_BY_STATUS_STATEMENT = "SELECT * FROM jobs WHERE STATUS = ? ORDER BY queue_date";
    const FIND_ALL_BY_STATUS_LIMIT_STATEMENT = "SELECT * FROM jobs WHERE STATUS = :status ORDER BY queue_date limit :limit";
    const FIND_ALL_STATEMENT = "SELECT * FROM jobs ORDER BY queue_date DESC";
    const FIND_ALL_WITH_LIMIT_STATEMENT = "SELECT * FROM jobs ORDER BY queue_date DESC limit :limit";
    const INSERT_STATEMENT = "INSERT INTO jobs (module, version, environment, jenkins, status, test_job_url, deployment_job_id) VALUES (?, ?, ?, ?, ?, ?, ?);";
    const UPDATE_STATEMENT = "UPDATE jobs SET status = ?, updated_at = ?, test_job_url = ?, deployment_job_id = ?, live_job_id = ?, user = ?, ticket = ? WHERE job_id = ?";

    public function __construct(Connection $db)
    {
        $this->_db = $db;
    }

    public function get($jobId)
    {
        $data = $this->_db->fetchAssoc(JobMapper::GET_JOB_STATEMENT, array($jobId));
        if (empty($data)) {
            throw new \Exception();
        }
        $job = Job::createFromArray($data);

        return $job;
    }

    public function findAllByStatus($status, $limit=null)
    {
        $sql = JobMapper::FIND_ALL_BY_STATUS_STATEMENT;
        $params = array($status);
        if (!is_null($limit)) {
            $sql = JobMapper::FIND_ALL_BY_STATUS_LIMIT_STATEMENT;
            $params['limit'] = $limit;
        }

        $data = $this->_db->fetchAll($sql, $params);

        $result = array();
        foreach ($data as $record) {
            array_push($result, Job::createFromArray($record));
        }

        return $result;
    }

    public function findAllByMultipleStatus($statusArray, $limit=null)
    {
        $targetStatus = implode("','", $statusArray);
        $sql = "SELECT * FROM jobs WHERE STATUS in ('" . $targetStatus ."') ORDER BY queue_date DESC";

        if (!is_null($limit)) {
            $sql .= " limit " . $limit;
        }

        $data = $this->_db->fetchAll($sql);
        $result = array();
        foreach ($data as $record) {
            array_push($result, Job::createFromArray($record));
        }

        return $result;
    }

    public function findAllExceptStatus($statusArray, $limit=null)
    {
        $targetStatus = implode("','", $statusArray);
        $sql = "SELECT * FROM jobs WHERE STATUS not in ('" . $targetStatus ."') ORDER BY queue_date DESC";

        if (!is_null($limit)) {
            $sql .= " limit " . $limit;
        }

        $data = $this->_db->fetchAll($sql);
        $result = array();
        foreach ($data as $record) {
            array_push($result, Job::createFromArray($record));
        }

        return $result;
    }

    public function findAll($limit=null)
    {
        $sql = JobMapper::FIND_ALL_STATEMENT;
        $params = array();
        if (!is_null($limit)) {
            $params['limit'] = $limit;
            $sql = JobMapper::FIND_ALL_WITH_LIMIT_STATEMENT. $limit;
        }

        $data = $this->_db->fetchAll($sql, $params);

        $result = array();
        foreach ($data as $record) {
            array_push($result, Job::createFromArray($record));
        }

        return $result;
    }

    public function save($job)
    {
        if ($job->getId() == 0) {
            $this->_executeInsert($job);
        } else {
            $this->_executeUpdate($job);
        }
    }

    private function _executeInsert($job)
    {
        $sql = JobMapper::INSERT_STATEMENT;
        $this->_db->executeUpdate(
            $sql,
            array(
                $job->getTargetModule(),
                $job->getTargetVersion(),
                $job->getTargetEnvironment(),
                $job->getRequestorJenkins(),
                $job->getStatus(),
                $job->getTestJobUrl(),
                $job->getDeploymentJobId()
            )
        );
        $job->setId($this->_db->lastInsertId());
    }

    private function _executeUpdate($job)
    {
        $sql = JobMapper::UPDATE_STATEMENT;
        $updatedDate = date('Y-m-d H:i:s');
        $this->_db->executeUpdate(
            $sql,
            array(
                $job->getStatus(),
                $updatedDate,
                $job->getTestJobUrl(),
                $job->getDeploymentJobId(),
                $job->getLiveJobId(),
                $job->getId()
                $job->getUser()
                $job->getTicket()
          )
        );
        $job->setUpdateDate($updatedDate);
    }
}
