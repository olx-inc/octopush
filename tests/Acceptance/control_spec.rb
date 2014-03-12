describe "Octopush control functions" do

  describe 'pause' do
    it "should return result SUCCESS" do
      url = "http://octopush.com/pause"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'
    end
  end

  describe 'run' do

    it "should return result SUCCESS when is Paused" do
      url = "http://octopush.com/pause"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'

      url = "http://octopush.com/run"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'
      response.body.should include 'The service is paused'

    end

  end

  describe 'resume' do

    it "should return result SUCCESS" do
      url = "http://octopush.com/resume"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'
    end

  end

end
