DROP TABLE IF EXISTS passes;
CREATE TABLE passes (
  id INTEGER NOT NULL AUTO_INCREMENT,
  subscriber_id INTEGER NOT NULL,
  `type` VARCHAR(32) NOT NULL,
  auth_token VARCHAR(64) NOT NULL,
  data TEXT NOT NULL,
  created TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY type (`type`),
  KEY auth_token (auth_token),
  KEY user_id` (subscriber_id)
)
ENGINE=InnoDB;


DROP TABLE IF EXISTS subscribers;
CREATE TABLE subscribers (
  id INTEGER NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL,
  email VARCHAR(100) NOT NULL,
  picture VARCHAR(255) NOT NULL,
  `function` VARCHAR(32) DEFAULT NULL,
  created TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY name (name)
)
ENGINE=InnoDB;

DROP TABLE IF EXISTS devices;
CREATE TABLE devices (
  id VARCHAR(32) NOT NULL,
  push_token VARCHAR(64) NOT NULL,
  created TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY push_token (push_token)
)
ENGINE=InnoDB;

DROP TABLE IF EXISTS devices_passes;
CREATE TABLE devices_passes (
  id INTEGER NOT NULL AUTO_INCREMENT,
  device_id VARCHAR(32) NOT NULL,
  pass_id INTEGER NOT NULL,
  pass_type VARCHAR(32) NOT NULL,
  created TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY device_id (device_id),
  KEY pass_id (pass_id),
  KEY pass_type (pass_type)
)
ENGINE=InnoDB;
