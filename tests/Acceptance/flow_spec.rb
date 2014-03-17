require 'json'

describe "End to End flow" do

  it "should enqueu, getStatus and report success tests" do
    # enqueue    
    #url = "http://octopush.com/environments/qa1/modules/ok-project/versions/1.1.1/push"
    #json_response = Octopush.get(url)

    url = "http://octopush.com/jobs/create"
    data = { :body => {:module => 'ok-project', :version => '1.1.1', :requestor => 'zzzzz'} }
    json_response = Octopush.post(url, data)
    puts json_response
    response = JSON.parse(json_response)
    job_id = response['job_id']
    puts "job enqueued: #{job_id}"

    # getStatus   
    sleep 5
    url = "http://octopush.com/jobs/#{job_id}/status"
    json_response = Octopush.get(url)
    json_response.body.should include 'QUEUED'
    puts "job status => QUEUED"

    Octopush.get("http://octopush.com/run");
    
    # getStatus   
    sleep 50
    url = "http://octopush.com/status/#{job_id}"
    json_response = Octopush.get(url)
    json_response.body.should include 'DEPLOYING'
    puts "job status => DEPLOYING"

    Octopush.get("http://octopush.com/run");

    # getStatus   
    sleep 50
    url = "http://octopush.com/status/#{job_id}"
    json_response = Octopush.get(url)
    json_response.body.should include 'PENDING_TESTS'
    puts "job status => PENDING_TESTS"

    Octopush.get("http://octopush.com/run");

    # report TEST OK    
    sleep 5
    url = "http://octopush.com/jobs/#{job_id}/register_test_job_result"
    data = { :body => {:success => true} }
    json_response = Octopush.post(url, data)
    json_response.body.should include 'success'

    Octopush.get("http://octopush.com/run");

    # getStatus
    sleep 5   
    url = "http://octopush.com/status/#{job_id}"
    json_response = Octopush.get(url)
    json_response.body.should include 'TESTS_PASSED'

  end
  
  it "enqueu should return error when the job does not exist in configuration" do  
    url = "http://octopush.com/jobs/create"
    data = { :body => {:module => 'yerba', :version => '1.1.1', :requestor => 'zzzzz'} }
    json_response = Octopush.post(url, data)
    response = JSON.parse(json_response)
  
    json_response.body.should include 'error'
  end

end
