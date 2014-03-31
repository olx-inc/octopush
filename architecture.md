---
layout: default
title: Application Architecture
---
 
## High level design

From a conceptual point of view Octopush consists of 2 components:

* A frontend, that is a website that exposes the Octopush API and a dashboard that provides visibility of request status
* A backend, that takes care of processing the requests and updating the their status


The whole business flow can be described as:

* The development team has a Jenkins instance that takes care of running continuous integration.
When the development team wants to deploy a new version to pre-production environment, they fire a deployment request from the development Jenkins.
* Development Jenkins sends the deployment request to Octopush API specifying module and version to be deployed. 
* After sending the request, Development Jenkins starts looping, querying the status of the deployment request.
* When Octopush API receives the deployment request, it creates a new Job and adds it to the deployment queue.
* Octopush backend queries the queue, runs the jobs, checks the concurrency rules, invokes RM Jenkins and updates status of the jobs.
* When the Development Jenkins detects the deployment has been completed, it fires the execution of acceptance tests
* When the acceptance tests are completed, Development Jenkins reports the result to Octopush
* Once test results are reported to Octopush, if the result is successful, then a Go live deployment can be manually trigger using Octopush website (this requires the user to be authorized for that operation)
* Octopush backend takes care of interacting with RM Jenkins to orchestrate the Go Live deployment.


From a functional standview, the application provides an API to manipulate/query jobs and a dashboard page that shows each job status and that allows authorized users to perform some additional actions like moving a job to live environment.

## Implementation details

The whole system is implemented as one web application in Php using Silex Framework and MySql database.

The backend is implemented inside the web application under the /run endpoint. The idea is to have a cron doing a get request to /run every 1 minute. 

At the module level the application is structured according to the MVC patterns.

Models includes the _Job_ class, its mapper class to encapsulate database access and a _JobStatus_ class that defines the constants that represents the valid status of a Job.

There are 2 controllers, the _QueueController_ that allows to manage the application itself (pause/resume) and the _JobController_ that provides the operations to manipulates jobs (enqueue/getStatus/reportTestResults).

The interaction with the RM Jenkins in encapsulated in a service class called _Jenkins_.

GitHub interaction (required for authorization) is encapsulated within the _GitHub_ service class.
