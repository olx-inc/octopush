describe "Push: Try job push" do

  it "Checks that a job can be pushed" do 
    url = "http://octopush.com/jobs/create"
    data = { :body => {:module => 'ok-project', :version => '1.1.1', :requestor => 'zzzzz'} }
    response = Octopush.post(url, data)
    response.body.should include 'Job inserted in queue'
  end
  
  it "Checks that a job can't be pushed with an invalid module" do
    url = "http://octopush.com/jobs/create"
    data = { :body => {:module => 'xxx', :version => '1.1.1', :requestor => 'zzzzz'} }
    response = Octopush.post(url, data)
    response.body.should include 'xxx is not a valid module to push.'
  end

end
