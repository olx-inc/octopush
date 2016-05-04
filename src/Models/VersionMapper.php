<?php

namespace Models;

use Doctrine\DBAL\Connection;

class VersionMapper
{
    private $_db;

    const FIND_ALL_STATEMENT = "SELECT * FROM versions ORDER BY module";
    const FIND_VERSION_STATEMENT = "SELECT * FROM versions WHERE module = ? AND environment = ?";
    const INSERT_STATEMENT = "INSERT INTO versions (module, version, environment, ticket, updated) VALUES (?, ?, ?, ?, ?);";
    const UPDATE_STATEMENT = "UPDATE versions SET version = ?, ticket = ?, updated = ? WHERE module = ? AND environment = ?";
    const FIND_ALL_MODULES_STATEMENT = "SELECT distinct module FROM versions order by module";

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

    public function find($environment, $module)
    {
      $params = array($module, $environment);
      try
      {
          $data = $this->_db->fetchAll(VersionMapper::FIND_VERSION_STATEMENT, $params);

          if (empty($data)) {
              return "";
          } else {
              return $data[0]['version'];
          }

      } catch (\Exception $exc) {
          error_log($exc);
      }

    }


    public function save($version)
    {
        $params = array($version->getModule(), $version->getEnvironment());
        try
        {
            $data = $this->_db->fetchAll(VersionMapper::FIND_VERSION_STATEMENT, $params);

            if (empty($data)) {
                $this->_executeInsert($version);
            } else {
                $this->_executeUpdate($version);
            }

        } catch (\Exception $exc) {
            error_log($exc);
        }

    }

    /****** REVISAR *****/

    private function _executeInsert($version)
    {
        $sql = VersionMapper::INSERT_STATEMENT;
        $insertedDate = date('Y-m-d H:i:s');
        $this->_db->executeUpdate(
            $sql,
            array(
                $version->getModule(),
                $version->getVersion(),
                $version->getEnvironment(),
                $version->getTicket(),
                $insertedDate,
            )
        );
        $version->setId($this->_db->lastInsertId());
    }

    private function _executeUpdate($version)
    {
        $sql = VersionMapper::UPDATE_STATEMENT;
        $updatedDate = date('Y-m-d H:i:s');
        $this->_db->executeUpdate(
            $sql,
            array(
                $version->getVersion(),
                $version->getTicket(),
                $updatedDate,
                $version->getModule(),
                $version->getEnvironment(),
          )
        );
        $version->setUpdateDate($updatedDate);
    }

    public function findAllModules()
    {
        $sql = JobMapper::FIND_ALL_MODULES_STATEMENT;
        $params = array();

        $data = $this->_db->fetchAll($sql, $params);

        return $data;
    }

}
