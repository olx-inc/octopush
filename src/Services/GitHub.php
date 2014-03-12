<?php

namespace Services;
use Library\HttpRequest;

class GitHub
{
    private $_managementKey;
    private $_adminTeamId;
    private $_httpRequest;
    private $_log;

    public function __construct($config, HttpRequest $httpRequest, $log)
    {
        $this->_managementKey = $config['github_management_key'];
        $this->_adminTeamId = $config['admin_team_id'];
        $this->_log = $log;
        $this->_httpRequest = $httpRequest;
    }

    public function IsUserAdmin($userToken)
    {
        $req = new \Library\HttpRequest();
        $token = $userToken->getAccessToken()->getAccessToken();
        $url = "https://api.github.com/user?access_token=" . $token;
        $req->setUrl($url);
        $rawResponse = $req->send();
        $jsonResponse = json_decode($rawResponse['body'], true);
        $login = $jsonResponse['login'];
      
        $url = 'https://api.github.com/teams/' . $this->_adminTeamId 
                . '/members/' . $login . '?access_token=' 
                . $this->_managementKey;

        $req->setUrl($url);
        $rawResponse = $req->send();
        return $req->getResponseCode() == 204;
    }

    public function IsUserInAdminTeam($username)
    {
        $result = false;
        $url = "https://api.github.com/user/teams?client_id=" . $this->_key ."&client_secret=" .$this->_secret;
        $this->_httpRequest->setUrl($url);
        $rawResponse = $this->_httpRequest->send();
        $jsonResponse = json_decode($rawResponse['body'], true);
        if (strpos($rawResponse['body'], $username) > 1) {
            $result = true;
        }
        return $result;
    }
}
