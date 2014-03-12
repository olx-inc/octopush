require 'rubygems'
require 'rspec'
require 'watir-webdriver'
#require 'watir-webdriver-performance'
require 'junit'
require 'httparty'

class Octopush
  include HTTParty
end

test_name = ""
test_time = 0
description = ""

if ENV['TestType'] == 'remote' 
  browser = initializeWithRemote
elsif ENV['TestType'] == 'WithoutBrowser'
  browser = nil
else
  browser = Watir::Browser.new
end

RSpec.configure do |config| 
  config.before(:each) {    
    if ENV['ENVIRONMENT'] != nil
      require "config." + ENV['ENVIRONMENT']
    else
      require 'config.dev'
    end
    @conf = ConfigEnv.new
    path = example.metadata[:example_group][:file_path]
    description = example.metadata[:example_group][:description]
    curr_path = config.instance_variable_get(:@curr_file_path)
    if (curr_path.nil? || path != curr_path)
      config.instance_variable_set(:@curr_file_path, path)            
    end

    test_name = path.gsub("./", "").gsub(".rb", "") + "." + description.gsub(' ', '')
    test_time = Time.now

  }
  
  config.after(:suite) {  
    browser.close unless browser.nil? 
  }

end

def initializeWithRemote()
  caps = Selenium::WebDriver::Remote::Capabilities.firefox
  #caps.version = "21"
  caps.platform = :LINUX
  caps[:name] = "testing"
  browser = Watir::Browser.new(
    :remote,
    :url => "http://localhost:4444/wd/hub",
    :desired_capabilities => caps)

  return browser
end

def translate_ip_env(ip)    
    ip_env = Hash.new
    ip_env["10.0.0.203"] = 'LOCAL_SITE'
    ip_env["168.78.73.42"] = 'DEV_SITE'
    ip_env["10.0.0.201"] = 'QA_SITE'
    ip_env["10.0.0.202"] = 'QA_SITE'
    ip_env["10.0.0.200"] = 'PROD_SITE' 
    return ip_env[ip]        
end

