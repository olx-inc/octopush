<?php

namespace Services;

use Library\HttpRequest;

class ThirdParty
{

    private $_preDeployUrl;
    private $_security;
    private $_httpRequest;
    private $_log;

    public function __construct($config, HttpRequest $httpRequest, $log)
    {
        $this->_preDeployUrl = $config['thirdparty']['pre-deploy'];
        $this->_security = $config['thirdparty']['security'];
        $this->_log = $log;
        $this->_httpRequest = $httpRequest;
        $this->_log->addInfo("ThirdParty instance created");
    }

    public function preDeploy($repo, $version, $action = 'deploy')
    {
        $urlParams = array(
            'repo' => $repo,
            'version' => $version,
            'action' => $action
        );
        
        return $this->_callToPreDeploy($urlParams);
    }
    
    
    private function _callToPreDeploy($params = array()) {
        $url = $this->_preDeployUrl . '?' . http_build_query($params);
        return json_decode(file_get_contents($url));
    }

}
