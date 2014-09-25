<?php

namespace Controllers;

use Models\VersionMapper,
    Models\Version,
    Silex\Application;

/* Handle requests related to specific jobs, all request expects a job_id parameter */
class VersionController
{
    private $_versionMapper;
    private $_log;
    private $_app;

    public function __construct(\OctopushApplication $app, 
                                VersionMapper $versionMapper,
                                $log)
    {
        $this->_versionMapper = $versionMapper;
        $this->_log = $log;
        $this->_app = $app;
    }


    public function getAllVersions()
    {
        $result = array();
        $version_array = array();
        $versions = $this->_versionMapper->findAll();
        $module = "";

        foreach ($versions as $version) {
            if ( $version['module']!=$module ){
                if (!empty($version_array)){
                    array_push($result, $version_array);
                }

                $version_array = array();
                $module = $version['module'];
            }
            $version_array['_module'] = $version['module'] ;
            $version_array['_ticket'] = isset($version['ticket']) ? $version['ticket'] : "";
            $version_array['_' . $version['environment']] = $version['version'];

        }
        array_push($result, $version_array);

        return $this->_app->json($result);
    }
}
