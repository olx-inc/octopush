<?php

use Controllers\QueueController,
    Models\JobMapper,
    Models\Job,
    Library\OctopushApplication;

//require_once  __DIR__ .'/../src/OctopushApplication.php';

class QueueControllerTest extends \PHPUnit_Framework_TestCase
{
    private $_jenkinsMock;
    private $_versionMapperMock;
    private $_logMock;

    public function setUp()
    {
        $this->_jenkinsMock = $this->getMockBuilder('Services\Jenkins')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_versionMapperMock = $this->getMockBuilder('Models\VersionMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_logMock = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testQueueJob()
    {
        $_GET['jenkins'] = "jenkins.olx.com";

        $appMock = new ApplicationMock();

        $jobMapperMock = $this->getMockBuilder('Models\JobMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $jobMapperMock->expects($this->once())
            ->method('save');

        $queueController = new QueueController($appMock, $jobMapperMock, $this->_versionMapperMock, $this->_jenkinsMock, $this->_logMock);

        $result = $queueController->queueJob("qa1", "billing", "3.3.3");
        $this->assertEquals('{"status":"success","message":"Job inserted in queue","job_id":0}', $result->getContent());
    }

    public function testInvalidModule()
    {
        $appMock = new ApplicationMock();

        $jobsMapperMock = $this->getMockBuilder('Models\JobMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $queueController = new QueueController($appMock, $jobsMapperMock, $this->_versionMapperMock, $this->_jenkinsMock, $this->_logMock);
        $result = $queueController->queueJob("qa1", "xxx", "3.3.3");
        $this->assertEquals('{"status":"error","message":"xxx is not a valid module to push."}', $result->getContent());
    }

    public function testQueueJobWithError()
    {
        $appMock = new ApplicationMock();

        $jobsMapperMock = $this->getMockBuilder('Models\JobMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $jobsMapperMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new Exception("Error")));

        $queueController = new QueueController($appMock, $jobsMapperMock,  $this->_versionMapperMock, $this->_jenkinsMock, $this->_logMock);
        $result = $queueController->queueJob("qa1", "billing", "3.3.3");
        $this->assertEquals('{"status":"error","message":"Job not inserted in queue","detail":"Error"}', $result->getContent());
    }

/*
    public function testShowJob()
    {
        $jobArray1 =  array(
                'job_id' => 1,
                'module' => 'billing',
                'version' => '3.43.2',
                'environment' => 'qa1',
                'jenkins' => '',
                'status' => "queued",
                'updated_at' => null,
                'queue_date' => "2013-08-30 15:57:21",
            );
        $job1 = Job::createFromArray($jobArray1);

        $jobArray2 = array(
                'job_id' => 2,
                'module' => 'billing',
                'version' => '3.43.3',
                'environment' => 'qa1',
                'jenkins' => '',
                'status' => "queued",
                'updated_at' => null,
                'queue_date' => "2013-08-30 15:59:21",
            );
        $job2 = Job::createFromArray($jobArray2);

        $appMock = new ApplicationMock();

        $urlGeneratorMock = $this->getMockBuilder('Silex\Provider\UrlGeneratorServiceProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();

        $urlGeneratorMock->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('some_url'));

        $appMock['url_generator'] = $urlGeneratorMock;

        $formProviderMock = $this->getMockBuilder('Silex\Provider\FormServiceProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('generateCsrfToken'))
            ->getMock();

        $appMock['form.csrf_provider'] = $formProviderMock;

        $twigMock = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $twigMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue(array("Octopush")));

        $appMock->setTwigMock($twigMock);

        $jobsMapperMock = $this->getMockBuilder('Models\JobMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $jobsMapperMock->expects($this->exactly(6))
            ->method('findAllByMultipleStatus')
            ->will($this->returnValue(array($job1, $job2)));

        $queueController = new QueueController($appMock, $jobsMapperMock, $this->_jenkinsMock, null, $this->_logMock);
        $result = $queueController->showJobs();
        $this->assertContains("Octopush", $result);
    }
*/

}

class ApplicationMock extends OctopushApplication
{
    protected $values;

    public function __construct()
    {
        $this->values['config'] = array(
            'environments' => array('qa1'),
            'modules' => array(
                "billing" => 1),
            'control_file' => __DIR__.'/../../../src/control/control.txt',
            'jobs' => array(
                'queue.lenght' => 5,
                'processed.lenght' => 10,
            )
        );
        $this->values['services.ThirdParty'] = "mock";
    }

    public function setTwigMock($twigMock)
    {
        $this->values['twig'] = $twigMock;
    }

    public function abort($statusCode, $message, $headers)
    {
        throw new HttpExceptionMock();
    }
}

class HttpExceptionMock extends Exception
{
    public function __construct() {}
}
