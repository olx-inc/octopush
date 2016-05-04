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
    private $_url_prefix;
    private $_regex_version;
    private $_uri_version;


    public function __construct(OctopushApplication $app,
                                VersionMapper $versionMapper,
                                $url_prefix,
                                $uri_version,
                                $regex_version,
                                $log)
    {
        $this->_versionMapper = $versionMapper;
        $this->_log = $log;
        $this->_app = $app;
        $this->_url_prefix = $url_prefix;
        $this->_uri_version = $uri_version;
        $this->_regex_version = $regex_version;

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
        $session = $this->_app['helpers.session'];

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
            $version_array['_' . $version['environment'] . '_time'] = $version['updated'];
            $version_array['_module_link'] = $this->_url_prefix . $version['module'];
            preg_match($this->_regex_version, $version['version'], $match);
            if (! empty ( $match ))
              $version_array['_' . $version['environment'] . '_link'] = $this->_url_prefix . $version['module']
                    . $this->_uri_version . $match[0];
            else
              $version_array['_' . $version['environment'] . '_link'] = $this->_url_prefix . $version['module'];
            $version_array['_canGoLive'] = $session->isAdminUser();

        }
        array_push($result, $version_array);

        return $this->_app->json($result);
    }

    public function getComponentList()
    {
         $result = array();

         $record = array();
         $record["Value"] = 'None';
         $record["URI"] = '&#47';
         array_push($result, $record);

         $components = $this->_versionMapper->findAllModules();

         foreach ($components as $component) {
             $record = array();
             $record["Value"] = $component['module'];
             $record["URI"] = '?repo=' . $component['module'];
             array_push($result, $record);
         }

         return $this->_app->json($result);
    }

}
