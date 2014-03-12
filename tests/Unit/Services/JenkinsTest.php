<?php

use Services\Jenkins,
    Models\JobStatus,    
    Models\Job;

class JenkinsTest extends \PHPUnit_Framework_TestCase
{
    private $_httpRequest;
    private $_config;
    private $_job;
    private $_mockLog;
    
    public function setUp()
    {   
        $this->_httpRequest = $this->getMockBuilder('Library\HttpRequest')
            ->setMethods(array('send', 'setUrl', 'setOptions','getUrl', 'getResponseCode'))
            ->getMock();

        $this->_mockLog = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();         

        $this->_config = array(
            'environments' => array('qa1'),
            'modules' => array("billing"),
            'jobs' => array(
                'queue.lenght' => 5,
                'priority' => 'queue_date',
            ),
            'jenkins' => array(
                'host' => "host",
                'user' => "user",
                'pass' => "pass",
                'jobs' => array(
                    'prefix' => "Push_Artifactory_",
                    'notifications' => "Octopush_Notifications_",
                ),
            ),
        );

        $job_array = array(
            'job_id' => 1,
            'module' => 'billing',
            'version' => '3.43.2',
            'environment' => 'qa1',
            'status' => JobStatus::QUEUED,
            'queue_date' => "2013-08-30 15:57:21",
            'jenkins' => "jenkins.olx.com.ar",
            'updated_at' => null
        );

        $this->_job = Job::CreateFromArray($job_array);
    }    
       
    public function testPushShouldReturnFalseWhenHttpError()
    {   
        $this->_httpRequest->expects($this->once())
            ->method('setUrl');
        
        $this->_httpRequest->expects($this->once())
            ->method('setOptions');
        
        $this->_httpRequest->expects($this->once())
            ->method('send')
            ->will($this->throwException(new Exception));
        
        $jenkinsTest = new Jenkins($this->_config, $this->_httpRequest, $this->_mockLog);
        $response = $jenkinsTest->push($this->_job);
        
        $this->assertEquals(false, $response);
    }
    
    public function testNotifyResult() 
    {
        $this->_httpRequest->expects($this->once())
            ->method('setUrl');
        
        $this->_httpRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue("Respuesta"));
        
        $jenkinsTest = new Jenkins($this->_config, $this->_httpRequest,$this->_mockLog);
        $jenkinsTest->notifyResult($this->_job, "SUCESS");
    }
    
    /**
     * @expectedException Exception
     */
    public function testNotifyResultWithException() 
    {
        $this->_httpRequest->expects($this->once())
            ->method('setUrl');
        
        $this->_httpRequest->expects($this->once())
            ->method('send')
            ->will($this->throwException(new Exception));
        
        $jenkinsTest = new Jenkins($this->_config, $this->_httpRequest,$this->_mockLog);
        $jenkinsTest->notifyResult($this->_job, "SUCESS");
    }
}
