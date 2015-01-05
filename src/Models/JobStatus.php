<?php

namespace Models;

class JobStatus
{
    const QUEUED ="QUEUED";
    const DEPLOYING = 'DEPLOYING';
    const DEPLOY_FAILED = "DEPLOY_FAILED";
    const PENDING_TESTS = "PENDING_TESTS";
    const TESTS_PASSED = "TESTS_PASSED";
    const TESTS_FAILED = "TESTS_FAILED";
    const QUEUED_FOR_LIVE = "QUEUED_FOR_LIVE";
    const GOING_LIVE = "GOING_LIVE";
    const GO_LIVE_DONE = "GO_LIVE_DONE";
    const GO_LIVE_FAILED = "GO_LIVE_FAILED";
    const DEPLOYED = "DEPLOYED";

    private static $status_array = array(
            0 => JobStatus::QUEUED,
            1 => JobStatus::DEPLOYING,
            2 => JobStatus::DEPLOY_FAILED,
            3 => JobStatus::PENDING_TESTS,
            4 => JobStatus::TESTS_PASSED,
            5 => JobStatus::TESTS_FAILED,
            6 => JobStatus::QUEUED_FOR_LIVE,
            7 => JobStatus::GOING_LIVE,
            8 => JobStatus::GO_LIVE_DONE,
            9 => JobStatus::GO_LIVE_FAILED,
           10 => JobStatus::DEPLOYED
        );

    public static function getJobStatus($statusId)
    {
         return self::$status_array[$statusId];
    }

    public static function getStatusId($status)
    {
        return array_search($status, self::$status_array);
    }    

}
