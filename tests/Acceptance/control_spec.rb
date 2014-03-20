describe "Octopush control functions" do

  octopush_url = ENV['octopush_url'] || "http://octopush.com"

  describe 'pause' do
    it "should return result SUCCESS" do
      url = octopush_url + "/pause"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'
    end
  end

  describe 'run' do

    it "should return result SUCCESS when is Paused" do
      # first pause it
      url = octopush_url + "/pause"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'

      # then make it run and check it is still paused
      url = octopush_url + "/run"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'
      response.body.should include 'The service is paused'

    end

  end

  describe 'resume' do

    it "should return result SUCCESS" do
      url =  octopush_url + "/resume"
      response = Octopush.get(url)
      response.body.should include 'SUCCESS'
    end

  end

end
