---
layout: default
title: API specification
---
 
Octopush is basically API based, you can pretty much do everything from it, here are the commands:

These three control the queue:
````
/run
/pause
/resume

````

This is the health check:
````
/health

````

These are useful for manually removing a blocking job on PENDING_TESTS (first) or cancelling a QUEUED job (2nd):

````
/jobs/{jobId}/tests/{success}
/jobs/{jobId}/cancel

````

These are the usual ones, you can check their uses at jenkins:

````
/environments/{env}/modules/{module}/versions/{version}/push
/jobs/create
/jobs/{jobId}/register_test_job_result
/jobs/{jobId}/register_test_job_url
/jobs/{jobId}/status
/jobs/{jobId}/golive

````