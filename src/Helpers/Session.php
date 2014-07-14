<?php

namespace Helpers;

class Session
{
    private static $_instance;
    private static $_app;

    public static function getInstance($app)
    {
      if (is_null(self::$_instance)) {
        self::$_instance = new self();
        self::$_app = $app;
      }
      return self::$_instance;
    }

    public function isMyComponentsOn()
    {
        return self::$_app['session']->get('myComponents')=='btn-on';     
    }

    public function getUserData()
    {
        return self::$_app['session']->get('userData');
    }
    
    public function getUser() 
    {
        $userdata = $this->getUserData();
        return $userdata['user'];
    }
    
    public function getPermissions() 
    {
        $userdata = $this->getUserData();
        return $userdata['permissions'];
    }
    
    public function isAdminUser() 
    {
        $userdata = $this->getUserData();
        return $userdata['is_admin_user'];
    }
}
