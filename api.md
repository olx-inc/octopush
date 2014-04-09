---
layout: default
title: API specification
---
 
Octopush is basically API based, you can pretty much do everything from it, here are the commands:

These 3 control the queue. 
<code>
/run
/pause
/resume
</code>

This is the health check
<pre><code>
/health
</code></pre>

These are useful for manually removing a blocking job on PENDING_TESTS (first) or cancelling a QUEUED job (2nd):
<pre><code>
/jobs/{jobId}/tests/{success}
/jobs/{jobId}/cancel
</code></pre>

These are the usual ones, you can check their uses at jenkins:
<pre><code>
/environments/{env}/modules/{module}/versions/{version}/push
/jobs/create
/jobs/{jobId}/register_test_job_result
/jobs/{jobId}/register_test_job_url
/jobs/{jobId}/status
/jobs/{jobId}/golive
</code></pre>

