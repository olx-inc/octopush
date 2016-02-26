<?php

namespace Services;

use Library\HttpRequest;

class ThirdParty {

    private $_preDeployUrl;
    private $_postDeployUrl;
    private $_permissionsUrl;
    private $_adminTeamId;
    private $_pocsTeamId;
    private $_log;
    const DEPLOY_SUCCESS = "success";
    const DEPLOY_FAILED = "failure";
    const NOT_AVAILABLE = "none";

    public function __construct($config,
                                $log) {
        if (isset($config['thirdparty']['pre-deploy']))
            $this->_preDeployUrl = $config['thirdparty']['pre-deploy'];
        if (isset($config['thirdparty']['post-deploy']))
            $this->_postDeployUrl = $config['thirdparty']['post-deploy'];
        $this->_permissionsUrl = $config['thirdparty']['member-permissions'];
        $this->_adminTeamId = $config['teams']['admin'];
        $this->_pocsTeamId = $config['teams']['pocs'];
        $this->_log = $log;
        $this->_log->addInfo("ThirdParty instance created");
    }

    public function preDeploy($job, $action = 'deploy')
    {
        if (isset($this->_preDeployUrl))
            return $this->_externalCall($job, $this->_preDeployUrl, $action);
        return NOT_AVAILABLE;
    }

    public function postDeploy($job, $action = 'success')
    {
        if (isset($this->_postDeployUrl))
            return $this->_externalCall($job, $this->_postDeployUrl, $action);
        return NOT_AVAILABLE;
    }

    public function getMemberPermissions($username)
    {
        if (isset($this->_permissionsUrl)){
            $url = $this->_permissionsUrl . $username;
            $perms = json_decode(@file_get_contents($url), true);
        }
        else
            $perms = array('teams' => "*", 'repositories' => "*");

        return $perms;
    }

    public function canMemberGoLive($permissions, $repository)
    {
        if (isset($permissions["teams"]) &&
                isset($permissions["repositories"])) {
            if (in_array($this->_adminTeamId, $permissions["teams"]) ||
                    (in_array($repository, $permissions["repositories"]) &&
                    in_array($this->_pocsTeamId, $permissions["teams"]))) {
                return true;
            }
        }
        return false;
    }

    private function _externalCall($job, $external_url, $action) {
        $params = array(
            'repo' => $job->getTargetModule(),
            'version' => $job->getTargetVersion(),
            'ticket' => urlencode($job->getTicket()),
            'user' => $job->getUser(),
            'action' => $action
        );

        $url = $external_url . '?' . http_build_query($params);
        $response = json_decode(file_get_contents($url));
        $response->ticket = urldecode($response->ticket);
        return $response;
    }

}
