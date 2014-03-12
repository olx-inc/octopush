# This script shows how to call Octopush to enqueue a deploy request
# It supposed this script will be run by a Jenkins job.
# It calls Octopush and wait in a loop till the deployment is completed.
# The following environment variables are used:
#   * PROJECT: the identifier of the project to be deployed
#   * VERSION: the version of the project to be deployed
#   * BUILD_URL: optional, it is provided by jenkins and refers to the URL of the build performing the invocation

echo "deploying ${PROJECT}-${VERSION}.zip"
GREP_RETURN_CODE=0

# Start the build
JOB_URL="http://octopush.com/jobs/create"
JSON_RESPONSE=`curl --data "module=${PROJECT}&version=${VERSION}&requestor=${BUILD_URL}" -s "$JOB_URL"`


JOB_ID=`echo ${JSON_RESPONSE} | cut -d: -f4 | sed 's/}//'`
JOB_STATUS_URL="http://octopush.com/jobs/${JOB_ID}/status"

# Poll every ten seconds until the build is finished
while [ ${GREP_RETURN_CODE} -eq 0 ]
do
    sleep 10
    JSON_REPONSE=`curl "$JOB_STATUS_URL"`
    GREP_RETURN_CODE=$?
    if echo ${JSON_REPONSE} | fgrep -q 'QUEUED'
    then
      echo "remote jenkins still working .."
    elif echo ${JSON_REPONSE} | fgrep -q 'DEPLOYING'
    then
      echo "remote jenkins still working .."      
    else
      GREP_RETURN_CODE=1
    fi
done

if echo $JSON_REPONSE | fgrep -q 'PENDING_TESTS'
then
  echo "SUCCESS!!"
  RESPOSE=0
else
  echo "FAILED!!"
  RESPONSE=1
fi
echo "Build finished"
echo "JOB_ID=$JOB_ID" > param.properties
exit $RESPONSE