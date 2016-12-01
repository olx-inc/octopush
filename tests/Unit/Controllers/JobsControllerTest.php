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
            'environments' => array('staging'),
            'modules' => array("ok-project"),
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
            'module' => 'ok-project',
            'version' => '3.43.2',
            'environment' => 'staging',
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
        $appMock = new ApplicationMock();
        $appMock['services.ThirdParty'] = "";

        $jobsController = new JobsController($appMock, $this->_config, $this->_jobMapperMock, null, "", "", "", $this->_logMock);

        $result = $jobsController->getJobStatus($jobId);

        $this->assertEquals('{"job_status":"PENDING_TESTS","job_id":1}', $result->getContent());
    }
}
