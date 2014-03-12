USE OCTOPUSH_DEV;

ALTER TABLE jobs CHANGE jenkins jenkins varchar(200);
ALTER TABLE jobs CHANGE test_job_url test_job_url varchar(200);
ALTER TABLE jobs CHANGE module module varchar(100);


