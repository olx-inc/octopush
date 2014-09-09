<?php

namespace Services;
use Models\JobStatus,
    Library\HttpRequest;

class Jenkins
{
    private $_host;
    private $_user;
    private $_pass;
    private $_jobs;
    private $_httpRequest;
    private $_log;

    public function __construct($config, HttpRequest $httpRequest, $log)
    {
        $this->_host = $config['jenkins']['host'];
        $this->_user = $config['jenkins']['user'];
        $this->_pass = $config['jenkins']['pass'];
        $this->_jobs = $config['jenkins']['jobs'];
        $this->_log = $log;
        $this->_httpRequest = $httpRequest;
        $this->_log->addInfo("New Jenkins instance created");
    }

    public function push($job)
    {
        $url = $this->_getUrlForJob($job);
        $url .= '/buildWithParameters';
        $data = array('env' => $job->getTargetEnvironment(), 
            'repo' => $job->getTargetModule(), 
            'revision' =>  $job->getTargetVersion());
        $toLive = false;
        return $this->_doPush($job, $url, $data, $toLive);
    }

    public function pushLive($job)
    {
        $url = $this->_getLiveUrlForJob($job);
        $url .= '/buildWithParameters';
        $data = array('tag' => $job->getTargetVersion());
        if ($job->isARollback())
            $data['wait'] = "0";
        $toLive = true;
        return $this->_doPush($job, $url, $data, $toLive);
    }

    public function getLastBuildStatus($job)
    {
        if ( ($job->getStatus() == JobStatus::QUEUED) || 
                ($job->getStatus() == JobStatus::DEPLOYING)) {
            $url = $this->_getUrlForJob($job);
            $url .= "/" . $job->getDeploymentJobId();
        } else {
            $url = $this->_getLiveUrlForJob($job);
            $url .= "/" . $job->getLiveJobId();
        }
        $url .= "/api/json";
        $this->_httpRequest->setUrl($url);
        $rawResponse = $this->_send();
        $jsonResponse = json_decode($rawResponse['body'], true);

        return $jsonResponse['result'];
    }

    public function getLastBuildId($job)
    {
        $url = "";
        if ( ($job->getStatus() == JobStatus::QUEUED) || 
                ($job->getStatus() == JobStatus::DEPLOYING)) {
            $url = $this->_getUrlForJob($job);
        } else {
            $url = $this->_getLiveUrlForJob($job);
        }
        $url .= '/lastBuild/api/json';
        $this->_httpRequest->setUrl($url);
        $this->_log->addInfo("GettingLastBuild:" . $url);
        $rawResponse = $this->_send();
        $jsonResponse = json_decode($rawResponse['body'], true);

        return $jsonResponse['number'];
    }

    public function notifyResult($job, $status)
    {
        $url = 'http://' . $job->getRequestorJenkins() . "/job/";
        $url .= "/{$this->_jobs['notifications']}/";
        $url .= 'buildWithParameters?';
        $url .= 'env=' . $job->getTargetEnvironment();
        $url .= '&repo=' . $job->getTargetModule();
        $url .= '&revision=' . $job->getTargetVersion();
        $url .= '&status=' . $status;
        $url .= '&jobId=' . $job->getId();
        $this->_httpRequest->setUrl($url);

        return $this->_send();
    }

    public function ping()
    {
        $url = $this->_host;
        $this->_httpRequest->setUrl($url);
        $rawResponse = $this->_send();
        if ($this->_httpRequest->getResponseCode() != 200) {
            throw new \Exception();
        }
    }

    private function _send()
    {
        try {
            $this->_log->addInfo("Calling Jenkins:" . $this->_httpRequest->getUrl());
            $httpAuth = $this->_user . ':' . $this->_pass;
            $this->_httpRequest->setOptions(array('httpauth' => $httpAuth));
            $response = $this->_httpRequest->send();
            $this->_log->addInfo("Response:" . $this->_httpRequest->getResponseCode());
            if ($this->_httpRequest->getResponseCode() > 400) {
                $this->_log->addError("Error while calling jenkins: " . $this->_httpRequest->getUrl());
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $this->_log->addError($e->getMessage());
            throw $e;
        }

        return $response;
    }

    public function getPreProdJobDeployUrl($job)
    {
        $url = $job->getDeploymentJobId() > 0 ? $this->_getUrlForJob($job) . "/" . $job->getDeploymentJobId(): "";

        return $url;
    }

    public function getRequestorJobConsoleUrl($job)
    {
        $url = $job->getRequestorJenkins();

        return empty($url) ? "" : $url . "/console";
    }

    public function getTestJobConsoleUrl($job)
    {
        $url = $job->getTestJobUrl();

        return empty($url) ? "" : $url . "/console";
    }

    public function getLiveJobDeployUrl($job)
    {
        $url = $job->getLiveJobId() > 0 ? $this->_getLiveUrlForJob($job) . "/" . $job->getLiveJobId(): "";

        return $url;
    }

    private function _getUrlForJob($job)
    {
        $url = $this->_host . "/job/" .
            $this->_jobs['prefix'] . $job->getTargetModule();

        return $url;
    }

    private function _getLiveUrlForJob($job)
    {
        $url = $this->_host . "/job/" .
            $this->_jobs['live.prefix'] . $job->getTargetModule();

        return $url;
    }

    private function _doPush($job, $pushUrl, $data, $toLive)
    {
        try {
            $currentBuildId = 0;
            $lastBuildId = $this->getLastBuildId($job);
            $this->_log->addInfo("lastBuildId: " . $lastBuildId);
            $req = new \HttpRequest($pushUrl, HttpRequest::METH_POST);
            $httpAuth = $this->_user . ':' . $this->_pass;
            $this->_log->addInfo("auth" . $httpAuth);
            $req->setOptions(array('httpauthtype'=>HTTP_AUTH_BASIC, 'httpauth' => $httpAuth));
            $req->addPostFields($data);
            $this->_log->addInfo("About to call JenkinsRM to queue job: " . $pushUrl);
            $req->send();
            $this->_log->addInfo("Response:" . $req->getResponseCode());
            $this->_log->addInfo("Response:" . $req->getResponseBody());
            if ($req->getResponseCode() > 400) {
                $this->_log->addError("Error while calling jenkins: " . $req->getUrl());
                throw new \Exception();
            }
            while ($lastBuildId>=$currentBuildId) {
                $this->_log->addInfo("lastBuildId: " . $lastBuildId);
                $this->_log->addInfo("currentBuildId: " . $currentBuildId);
                sleep(2);
                if ($toLive) {
                    $job->setLiveJobId($this->getLastBuildId($job));
                    if (is_numeric($job->getLiveJobId()))
                        $currentBuildId = $job->getLiveJobId();
                } else {
                    $job->setDeploymentJobId($this->getLastBuildId($job));
                    if (is_numeric($job->getDeploymentJobId()))
                        $currentBuildId = $job->getDeploymentJobId();
                }
                
            }
            $this->_log->addInfo("currentBuildIdAssigned: " . $job->getDeploymentJobId());

            return true;
        } catch (\Exception $ex) {
            $this->_log->addError("Error while pushing Job to Jenkins RM:" . $ex->getMessage());
            error_log($ex->getMessage());
            return false;
        }

    }
}
