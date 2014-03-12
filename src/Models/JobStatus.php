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
}
