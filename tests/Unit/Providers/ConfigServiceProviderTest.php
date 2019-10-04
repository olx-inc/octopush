<?php

use Providers\ConfigServiceProvider;

class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigService()
    {
        $file =
        'database:
          driver: un_driver
          dbname: esquema
          host: host.olx.com.ar
          user: db_user
          password: ******
          port: 3306
environment: production
jenkins:
          host: http://jenkins.olx.com';

        $app = new Silex\Application();

        $configProvider = new ConfigServiceProvider($file);
        $configProvider->boot($app);
        $configProvider->register($app);

        $app->register($configProvider);

        $this->assertArrayHasKey("driver", $app['config']['database']);
        $this->assertEquals("production", $app['config']['environment']);
        $this->assertArrayHasKey("host", $app['config']['jenkins']);
    }

    public function testGetFile()
    {
        $file = 'database:
          driver: un_driver
          dbname: esquema
          host: host.olx.com.ar
          user: db_user
          password: ******
          port: 3306
          environments: [\'qa1\',\'testing\',\'production\']
          modules: [\'billing\',\'adserving\',\'pannello\']';

        $configProvider = new ConfigServiceProvider($file);
        $getFile = $configProvider->getConfigFile();

        $this->assertEquals($file, $getFile);
    }
}
