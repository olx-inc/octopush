<?php

namespace Services;

use Library\HttpRequest;

class GitHub {

    private $_managementKey;
    private $_adminTeamId;
    private $_pocTeamId;
    private $_log;
    const USER_AGENT = "Octopush";

    public function __construct($config, $log)
    {
        $this->_managementKey = $config['github_management_key'];
        $this->_adminTeamId = $config['teams']['admin'];
        $this->_pocTeamId = $config['teams']['pocs'];
        $this->_log = $log;
    }

    public function IsUserAdmin($userToken) {

        $login = $this->getUserName($userToken);
        $url = 'https://api.github.com/teams/' . $this->_adminTeamId
                . '/members/' . $login . '?access_token='
                . $this->_managementKey;

        $req = new HttpRequest($url, "GET");
        $req->setUserAgent(GitHub::USER_AGENT);
        $rawResponse = $req->send();

        return $req->getResponseCode() == 204;
    }

    public function getUserName($userToken) {

        $token = $userToken->getAccessToken()->getAccessToken();
        $url = "https://api.github.com/user?access_token=" . $token;
        $req = new HttpRequest($url, "GET");
        $req->setUserAgent(GitHub::USER_AGENT);
        $rawResponse = $req->send();
        $jsonResponse = json_decode($rawResponse, true);
        return $jsonResponse['login'];
    }

    public function getUser($token) {

        $url = "https://api.github.com/user?access_token=" . $token;
        $req = new HttpRequest($url, "GET");
        $req->setUserAgent(GitHub::USER_AGENT);
        $rawResponse = $req->send();
        $jsonResponse = json_decode($rawResponse, true);
        return new User($jsonResponse['login'], $jsonResponse['email']);
    }

    public function getMemberPermissions($user, $token) {

        $url = "https://api.github.com/teams/" . $this->_pocTeamId . "/memberships/"
              . $user . "?access_token=" . $this->_managementKey;
        $req = new HttpRequest($url, "GET");
        $req->setUserAgent(GitHub::USER_AGENT);
        $rawResponse = $req->send();
        if ($req->getResponseCode() == 200){
          $jsonResponse = json_decode($rawResponse, true);
          return "active";
        }
        return "";
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
