require 'json'

describe "Octopush end to end pipeline flow" do

  octopush_url = ENV['octopush_url'] || "http://octopush.com"

  it "should enqueu, getStatus and report success tests" do

    # first ensure is not paused
    Octopush.get(octopush_url + "/resume");

    url = octopush_url + "/jobs/create"
    data = { :body => {:module => 'ok-project', :version => '1.1.1', :requestor => 'zzzzz'} }
    json_response = Octopush.post(url, data)
    response = JSON.parse(json_response)
    job_id = response['job_id']

    # getStatus   
    sleep 5
    url = octopush_url + "/jobs/#{job_id}/status"
    json_response = Octopush.get(url)
    json_response.body.should include 'QUEUED'

    Octopush.get(octopush_url + "/run");
    
    # getStatus   
    sleep 50
    url = octopush_url + "/status/#{job_id}"
    json_response = Octopush.get(url)
    json_response.body.should include 'DEPLOYING'

    Octopush.get(octopush_url + "/run");

    # getStatus   
    sleep 50
    url = octopush_url + "/status/#{job_id}"
    json_response = Octopush.get(url)
    json_response.body.should include 'PENDING_TESTS'

    Octopush.get(octopush_url + "/run");

    # report TEST OK    
    sleep 5
    url = octopush_url + "/jobs/#{job_id}/register_test_job_result"
    data = { :body => {:success => true} }
    json_response = Octopush.post(url, data)
    json_response.body.should include 'success'

    Octopush.get(octopush_url + "/run");

    # getStatus
    sleep 5   
    url = octopush_url + "/status/#{job_id}"
    json_response = Octopush.get(url)
    json_response.body.should include 'TESTS_PASSED'

  end
  
  it "enqueu should return error when the job does not exist in configuration" do  
    url = octopush_url + "/jobs/create"
    data = { :body => {:module => 'yerba', :version => '1.1.1', :requestor => 'zzzzz'} }
    json_response = Octopush.post(url, data)
    response = JSON.parse(json_response)
  
    json_response.body.should include 'error'
  end

end
