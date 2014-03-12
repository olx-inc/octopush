CREATE DATABASE OCTOPUSH;

USE OCTOPUSH;

CREATE TABLE jobs (
  job_id int(11) NOT NULL AUTO_INCREMENT,
  module varchar(100) NOT NULL DEFAULT '',
  version varchar(15) NOT NULL DEFAULT '',
  status varchar(15) NOT NULL DEFAULT 'queued',
  queue_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp,
  jenkins varchar(200),
  deployment_job_id int DEFAULT 0,
  live_job_id int DEFAULT 0,
  test_job_url varchar(200),
  PRIMARY KEY (job_id)
);
