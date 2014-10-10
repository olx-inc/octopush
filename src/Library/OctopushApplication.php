<?php

namespace Library;

class OctopushApplication extends \Silex\Application
{

    public function isPaused()
    {
        return file_exists($this['config']['control_file']);
    }
	
    public function pause()
    {
        $success = true;
        $success = file_put_contents($this['config']['control_file'], 'pause');
        
        return $success;
    }


    public function resume()
    {
        $success = true;
        if ($this->isPaused()) {
            $success = unlink($this['config']['control_file']);
        }

        return $success;
    }

}