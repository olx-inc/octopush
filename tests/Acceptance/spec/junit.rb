=begin

Copyright (c) 2012, Nathaniel Ritmeyer
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice,
this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.

3. Neither the name Nathaniel Ritmeyer nor the names of contributors to
this software may be used to endorse or promote products derived from this
software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS
IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

=end

require 'time'
require 'builder'
require 'rspec/core/formatters/base_formatter'

class JUnit < RSpec::Core::Formatters::BaseFormatter
  def initialize output
    super output
    @test_results = []
  end

  def example_passed example
    @test_results << example
  end

  def example_failed example
    @test_results << example
  end

  def example_pending example
    @test_results << example
  end

  def failure_details_for example
    exception = example.metadata[:execution_result][:exception]
    exception.nil? ? "" : "#{exception.message}n#{format_backtrace(exception.backtrace, example).join("n")}"
  end

  def full_name_for example
    test_name = ""
    current_example_group = example.metadata[:example_group]
    until current_example_group.nil? do
      test_name = "#{current_example_group[:description]}." + test_name
      current_example_group = current_example_group[:example_group]
    end
    test_name << example.metadata[:description]
  end

  def dump_summary duration, example_count, failure_count, pending_count
    builder = Builder::XmlMarkup.new :indent => 2
    builder.instruct! :xml, :version => "1.0", :encoding => "UTF-8"
    builder.testsuite :errors => 0, :failures => failure_count, :skipped => pending_count, :tests => example_count, :time => duration, :timestamp => Time.now.iso8601 do
      builder.properties
      @test_results.each do |test|
        builder.testcase :classname => full_name_for(test), :name => test.metadata[:full_description], :time => test.metadata[:execution_result][:run_time] do
          case test.metadata[:execution_result][:status]
          when "failed"
            builder.failure :message => "failed #{test.metadata[:full_description]}", :type => "failed" do
              builder.cdata! failure_details_for test
            end
          when "pending" then builder.skipped
          end
        end
      end
    end
    output.puts builder.target!
  end
end

