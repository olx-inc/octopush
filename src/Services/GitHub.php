<?php

namespace Services;

use Library\HttpRequest;

class GitHub {

    private $_managementKey;
    private $_adminTeamId;
    private $_log;

    public function __construct($config, $log)
    {
        $this->_managementKey = $config['github_management_key'];
        $this->_adminTeamId = $config['teams']['admin'];
        $this->_log = $log;
    }

    public function IsUserAdmin($userToken) {

        $login = $this->getUserName($userToken);
        $url = 'https://api.github.com/teams/' . $this->_adminTeamId
                . '/members/' . $login . '?access_token='
                . $this->_managementKey;

        $req = new \Library\HttpRequest();
        $req->setUrl($url);
        $rawResponse = $req->send();

        return $req->getResponseCode() == 204;
    }

    public function getUserName($userToken) {

        $token = $userToken->getAccessToken()->getAccessToken();
        $url = "https://api.github.com/user?access_token=" . $token;
        $req = new \Library\HttpRequest($url);
        $rawResponse = $req->send();
        $jsonResponse = json_decode($rawResponse['body'], true);
        return $jsonResponse['login'];
    }

    public function getUser($token) {

        $url = "https://api.github.com/user?access_token=" . $token;
        $req = new \Library\HttpRequest($url);
        $rawResponse = $req->send();
        $jsonResponse = json_decode($rawResponse['body'], true);
        return new User($jsonResponse['login'], $jsonResponse['email']);
    }

    public function IsUserInAdminTeam($username) {
        $result = false;
        $url = "https://api.github.com/user/teams?client_id=" . $this->_key ."&client_secret=" .$this->_secret;
        $req = new \Library\HttpRequest($url);
        $rawResponse = $this->_httpRequest->send();
        $jsonResponse = json_decode($rawResponse['body'], true);
        if (strpos($rawResponse['body'], $username) > 1) {
            $result = true;
        }

        return $result;
    }

    public function getUsersRepos($username){

    }

}

class User
{
    private $name;
    private $mail;

    public function __construct($_name, $_mail)
    {
        $this->name = $_name;
        $this->mail = $_mail;
    }

    public function getUserName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->mail;
    }

}
