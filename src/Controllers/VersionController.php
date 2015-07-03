<?php

namespace Controllers;

use Models\VersionMapper,
    Models\Version,
    Library\OctopushApplication;

/* Handle requests related to specific jobs, all request expects a job_id parameter */
class VersionController
{
    private $_versionMapper;
    private $_log;
    private $_app;

    public function __construct(OctopushApplication $app,
                                VersionMapper $versionMapper,
                                $log)
    {
        $this->_versionMapper = $versionMapper;
        $this->_log = $log;
        $this->_app = $app;
    }


    public function get($environment, $module)
    {
      try{
        $version = $this->_versionMapper->find($environment, $module);
      } catch (\Exception $exc) {
          $error = array(
              'status' => "error",
              'message' => "Problems trying to register Version",
              'detail' => $exc->getMessage(),
          );
          $this->_log->addError($error['message'] . " :: " . $error['detail']);

          return $this->_app->json($error);
      }
      return $version;

    }

    public function update($environment, $module, $version)
    {
        try {
            $version = Version::createFromArray(array('environment' => $environment, 'module' => $module, 'version' => $version ) );
            if (isset($_REQUEST['ticket']))
                $version['ticket'] = $_REQUEST['ticket'];

            $this->_versionMapper->save($version);
            $result = array(
                'status' => "success",
                'message' => "Version registered succesfully",
            );

            return $this->_app->json($result);
        } catch (\Exception $exc) {
            $error = array(
                'status' => "error",
                'message' => "Problems trying to register Version",
                'detail' => $exc->getMessage(),
            );
            $this->_log->addError($error['message'] . " :: " . $error['detail']);

            return $this->_app->json($error);
        }


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
            if ( isset($version['ticket']) )
                $version_array['_ticket'] = $version['ticket'];
            $version_array['_module'] = $version['module'] ;
            $version_array['_' . $version['environment']] = $version['version'];

        }
        array_push($result, $version_array);

        return $this->_app->json($result);
    }

}
