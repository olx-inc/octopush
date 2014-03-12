#! /bin/bash

clear  # borra la pantalla

# run if user hits control-c 
control_c() {
  echo -en "\n*** Ouch! Exiting ***\n"
  cleanup
  exit $?
}
 
# trap keyboard interrupt (control-c)
trap control_c SIGINT

echo "Execute $file...."  

export TestType='WithoutBrowser'

rspec *.rb -r ./spec/junit.rb -f JUnit -o ../../build/test-reports.xml

