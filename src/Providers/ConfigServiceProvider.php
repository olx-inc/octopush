<?php

namespace Providers;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigServiceProvider implements ServiceProviderInterface
{
    protected $file;

    public function __construct($file) {
        $this->file = $file;
    }


    public function register(Application $app) {
        $config = Yaml::parse($this->file);

    	if (isset($app['config']) && is_array($app['config'])) {
			$app['config'] = array_merge($app['config'], $config);
		} else {
			$app['config'] = $config;
		}
    }

    public function boot(Application $app) {
    }

    public function getConfigFile() {
        return $this->file;
    }
}