<?php

use Models\Job,
    Models\JobStatus,
    Models\InvalidOperationException;

class JobTest extends \PHPUnit_Framework_TestCase
{
    private $_job;
    private $_otherJob;
    private $_modules;

    public function setUp()
    {
        $this->_module = "my_module";
        $this->_version = "1";
        $this->_environment = "qa";
        $this->_jenkins = "jdev";
        $this->_job = Job::createWith(
            $this->_module, 
            $this->_version, 
            $this->_environment, 
            $this->_jenkins
        );

        $this->_otherJob = Job::createWith(
            'otherModule', 
            'otherVersion', 
            'qa', 
            'jdev2'
        );

        $_modules = array(
                $this->_job->getTargetModule() => 1,
                $this->_otherJob->getTargetModule() =>2
                );
    }
   
    public function testContructorShouldInitializeFields()
    {
        $this->assertEquals($this->_module, $this->_job->getTargetModule());
        $this->assertEquals($this->_version, $this->_job->getTargetVersion());
        $this->assertEquals($this->_environment, $this->_job->getTargetEnvironment());
        $this->assertEquals($this->_jenkins, $this->_job->getRequestorJenkins());
        $this->assertEquals("QUEUED", $this->_job->getStatus());
    }

    public function testMoveStatusToShouldChangeStatusWhenValidStatus()
    {
        $this->_job->moveStatusTo(JobStatus::DEPLOYING);
        $this->assertEquals(JobStatus::DEPLOYING, $this->_job->getStatus());
    }

    /**
     * @expectedException Models\InvalidOperationException
     */
    public function testMoveStatusToShouldRaiseExceptionWhenInvalidStatus()
    {
        $this->_job->moveStatusTo(JobStatus::DEPLOYING);
        $this->_job->moveStatusTo(JobStatus::QUEUED);
    }

    public function testCanRunShouldReturnTrueIfNoJobsInProgress()
    {   
        $jobsInProgress = array();
        $result = $this->_job->canRun($jobsInProgress, $this->_modules);
        $this->assertTrue($result);
    }

    public function testCanRunShouldReturnFalseIfSameModuleIsInProgress()
    {   
        $jobsInProgress = array($this->_job);
        $result = $this->_job->canRun($jobsInProgress, $this->_modules);
        $this->assertFalse($result);
    }

    public function testCanRunShouldReturnTrueIfOtherModuleWithSameTagIsInProgress()
    {  
 
        $jobsInProgress = array($this->_otherJob);
        $modules = array(
                $this->_job->getTargetModule() => 1,
                $this->_otherJob->getTargetModule() =>1
                );
        $result = $this->_job->canRun($jobsInProgress, $modules);
        $this->assertTrue($result);
    }

    public function testCanRunShouldReturnFalseIfOtheModuleWithOtherTagIsInProgress()
    {  
        $jobsInProgress = array($this->_otherJob);
        $modules = array(
                $this->_job->getTargetModule() => 1,
                $this->_otherJob->getTargetModule() =>2
                );
        $result = $this->_job->canRun($jobsInProgress, $modules);
        $this->assertFalse($result);
    }

    public function testCanGoLiveShouldReturnTrueIsStatusIsTestPassed()
    {  
        $this->_job->moveStatusTo(JobStatus::TESTS_PASSED);
        $this->assertTrue($this->_job->canGoLive());
    }

    public function testCanGoLiveShouldReturnFalseIsStatusIsNotTestPassed()
    {  
        $this->_job->moveStatusTo(JobStatus::TESTS_FAILED);
        $this->assertFalse($this->_job->canGoLive());
    }

    public function testWentLiveShouldReturnTrueIsStatusIsGoingLive()
    {  
        $this->_job->moveStatusTo(JobStatus::GOING_LIVE);
        $this->assertTrue($this->_job->wentLive());
    }

    public function testWentLiveShouldReturnFalseIsStatusIsTestPasses()
    {  
        $this->_job->moveStatusTo(JobStatus::TESTS_PASSED);
        $this->assertFalse($this->_job->wentLive());
    }
}
