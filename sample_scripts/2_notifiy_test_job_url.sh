# This script notifies Octopush the url of the job that is executing the acceptance tests.
# It dependens on the JOB_ID provided by Octopush so it can be linked to the corresponding Octopush pipeline

curl --data "test_job_url=${BUILD_URL}" "http://octopush.com/jobs/${JOB_ID}/register_test_job_url"

