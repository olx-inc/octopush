<?php

namespace Services;

use Library\HttpRequest;

class ThirdParty {

    private $_preDeployUrl;
    private $_security;
    private $_permissionsUrl;
    private $_adminTeamId;
    private $_pocsTeamId;
    private $_httpRequest;
    private $_log;

    public function __construct($config, 
                                HttpRequest $httpRequest, 
                                $log) {
        $this->_preDeployUrl = $config['thirdparty']['pre-deploy'];
        $this->_security = $config['thirdparty']['security'];
        $this->_permissionsUrl = $config['thirdparty']['member-permissions'];
        $this->_adminTeamId = $config['teams']['admin'];
        $this->_pocsTeamId = $config['teams']['pocs'];
        $this->_log = $log;
        $this->_httpRequest = $httpRequest;
        $this->_log->addInfo("ThirdParty instance created");
    }

    public function preDeploy($job, $action = 'deploy')
    {
        $urlParams = array(
            'repo' => $job->getTargetModule(),
            'version' => $job->getTargetVersion(),
            'ticket' => $job->getTicket(),
            'action' => $action
        );

        return $this->_callToPreDeploy($urlParams);
    }

    public function getMemberPermissions($username) 
    {        
        $url = $this->_permissionsUrl . $username;
        return json_decode(file_get_contents($url), true);
    }
    
    public function canMemberGoLive($permissions, $repository) 
    {
        if (in_array($this->_adminTeamId, $permissions["teams"]) ||
                (in_array($repository, $permissions["repositories"]) &&
                in_array($this->_pocsTeamId, $permissions["teams"]))) {
            return true;
        }

        return false;
    }

    private function _callToPreDeploy($params = array()) {
        $url = $this->_preDeployUrl . '?' . http_build_query($params);
        return json_decode(file_get_contents($url));
    }

}
