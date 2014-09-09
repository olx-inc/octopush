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

    public function getMyComponentsValue()
    {
        return self::$_app['session']->get('myComponents');     
    }

    public function setMyComponents($state)
    {
        return self::$_app['session']->set('myComponents', $state);     
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

    public static function buildBackendSession($app, $token)
    {
        $user = $app['services.GitHub']->getUser($token);
        $permissions = $app['services.ThirdParty']->
                getMemberPermissions($user->getUserName());

        $userData = array(
            'user' => $user,
            'permissions' => $permissions,
            'my_components' => 'btn-on',
            'is_admin_user' => in_array(
                $app['config']['teams']['admin'], 
                $permissions['teams']
            ),
        );
        
        $app['session']->set('userData', $userData);

    }

    public static function buildSession($app, $token)
    {
        $username = $app['services.GitHub']->getUserName($token);
        $permissions = $app['services.ThirdParty']->
                getMemberPermissions($username);
        
        $userData = array(
            'user' => $token->getUser(),
            'permissions' => $permissions,
            'my_components' => 'btn-on',
            'is_admin_user' => in_array(
                $app['config']['teams']['admin'], 
                $permissions['teams']
            ),
        );
        
        $app['session']->set('userData', $userData);

    }

}
