CREATE DATABASE `OCTOPUSH_DEV` /*!40100 DEFAULT CHARACTER SET utf8 */

USE OCTOPUSH_DEV;

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(20) NOT NULL DEFAULT '',
  `version` varchar(15) NOT NULL DEFAULT '',
  `environment` varchar(10) NOT NULL DEFAULT '',
  `status` varchar(15) NOT NULL DEFAULT 'queued',
  `queue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp,
  `jenkins` varchar(50) DEFAULT '',
  PRIMARY KEY (`job_id`),
  KEY `job` (`job_id`),
  KEY `module` (`module`),
  KEY `environment` (`environment`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='utf8_unicode_ci';
