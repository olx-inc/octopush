describe "Octopush screen" do
  it "Checks that a screen is render" do
   
    url = "http://octopush.com/"
    response = Octopush.get(url)
    response.body.should include 'Octopush'
    response.body.should include 'Queue'
    response.body.should include 'Deployed'

  end
end
