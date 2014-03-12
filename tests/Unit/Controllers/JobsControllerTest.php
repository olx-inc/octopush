<?php

use Controllers\JobsController,
    Models\Job,
    Models\JobStatus;

class JobsControllerTest extends \PHPUnit_Framework_TestCase
{
    private $_config;
    private $_jobsMapperMock;
    private $_jenkinsControllerMock;
    private $_logMock;
    
    public function setUp()
    {
        $this->_config = array(
            'environments' => array('qa1'),
            'modules' => array("billing"),
            'jobs' => array(
                'queue.lenght' => 5,
                'priority' => 'queue_date',
            )
        );
        
        $this->_jobMapperMock = $this->getMockBuilder('Models\JobMapper')
            ->disableOriginalConstructor()
            ->getMock();    
        
        $this->_logMock = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

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
       
    public function testGetJobStatusShouldCallMapperAndReturnJson()
    {
        $this->_job->moveStatusTo(JobStatus::PENDING_TESTS);
        $jobId = 1;

        $this->_jobMapperMock->expects($this->once())
            ->method('get')
            ->with("$jobId")
            ->will($this->returnValue($this->_job));

        $jobsController = new JobsController($this->_config, $this->_jobMapperMock, $this->_logMock);

        $result = $jobsController->getJobStatus($jobId);

        $this->assertEquals('{"job_status":"PENDING_TESTS","job_id":1}', $result);
    }
}
