<?php

namespace Models;

class Version
{
    private $_id;
    private $_module;
    private $_version;
    private $_environment;
    private $_updated_at;
    private $_ticket;

    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    public function getId()
    {
        return $this->_id;
    }


    public function setModule($module)
    {
        $this->_module = $module;
    }

    public function getModule()
    {
        return $this->_module;
    }

    public function setVersion($version)
    {
        $this->_version = $version;
    }

    public function getVersion()
    {
        return $this->_version;
    }


    public function setEnvironment($environment)
    {
        $this->_environment = $environment;
    }

    public function getEnvironment()
    {
        return $this->_environment;
    }
    

   public function setTicket($ticket)
    {
        $this->_ticket = $ticket;
    }

    public function getTicket()
    {
        return $this->_ticket;
    }

    public function getUpdateDate()
    {
        return $this->_updated_at;
    }

    public function setUpdateDate($date)
    {
        $this->_updated_at = $date;
    }

    public static function createFromArray($data)
    {
        $version = new Version();
        $version->_id = (int) $data['id'];
        $version->_module = $data['module'];
        $version->_version = $data['version'];
        $version->_environment = $data['environment'];
        $version->_updated_at = $data['updated_at'];
        $version->_ticket = isset($data['ticket']) ? $data['ticket'] : "";

        return $version;
    }

    public function serialize()
    {
        return get_object_vars($this);

    }    

}
