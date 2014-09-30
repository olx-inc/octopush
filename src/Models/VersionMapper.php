<?php

namespace Models;

use Doctrine\DBAL\Connection;

class VersionMapper
{
    private $_db;

    const FIND_ALL_STATEMENT = "SELECT * FROM versions ORDER BY module";
    const INSERT_STATEMENT = "INSERT INTO versions (module, version, environment, ticket, updated_at) VALUES (?, ?, ?, ?, ?, ?);";
    const UPDATE_STATEMENT = "UPDATE versions SET version = ?, updated_at = ?, ticket = ? WHERE module = ? AND environment = ?";

    public function __construct(Connection $db)
    {
        $this->_db = $db;
    }


    public function findAll()
    {
        $sql = VersionMapper::FIND_ALL_STATEMENT;
        $params = array();

        $data = $this->_db->fetchAll($sql, $params);

        return $data;
    }

    public function save($job)
    {
        if ($job->getId() == 0) {
            $this->_executeInsert($job);
        } else {
            $this->_executeUpdate($job);
        }
    }

    /****** REVISAR *****/

    private function _executeInsert($job)
    {
        $sql = VersionMapper::INSERT_STATEMENT;
        $insertedDate = date('Y-m-d H:i:s');
        $this->_db->executeUpdate(
            $sql,
            array(
                $job->getTargetModule(),
                $job->getTargetVersion(),
                $job->getTargetEnvironment(),
                $job->getRequestorJenkins(),
                $job->getStatus(),
                $job->getTestJobUrl(),
                $job->getDeploymentJobId(),
                $job->getUser(),
                $job->getTicket(),
                $job->getRollbackedFrom(),                
                $insertedDate,
            )
        );
        $job->setId($this->_db->lastInsertId());
    }

    private function _executeUpdate($job)
    {
        $sql = VersionMapper::UPDATE_STATEMENT;
        $updatedDate = date('Y-m-d H:i:s');
        $this->_db->executeUpdate(
            $sql,
            array(
                $job->getStatus(),
                $updatedDate,
                $job->getTestJobUrl(),
                $job->getDeploymentJobId(),
                $job->getLiveJobId(),
                $job->getUser(),
                $job->getTicket(),
                $job->getRollbackedFrom(),
                $job->getId(),
          )
        );
        $job->setUpdateDate($updatedDate);
    }
}
