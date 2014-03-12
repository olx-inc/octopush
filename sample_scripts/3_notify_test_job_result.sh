# This script notifies Octopush the result of the acceptance tests.
# It dependens on the JOB_ID provided by Octopush so it can be linked to the corresponding Octopush pipeline
# The the expected values for TEST_PASSED variable are "true" or "false"

curl --data "success=true" "http://octopush.com/jobs/${JOB_ID}/register_test_job_result"

