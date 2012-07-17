CREATE DATABASE barobo;
GRANT ALL PRIVILEGES ON barobo.* to 'user'@'localhost' IDENTIFIED BY 'changeme01';
USE barobo;
CREATE TABLE sessions (
	id INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(id),
	session_id VARCHAR(30),
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	last_active TIMESTAMP NULL DEFAULT NULL,
	started TIMESTAMP NULL DEFAULT NULL,
	active INT
) ENGINE = InnoDB;
CREATE INDEX idx_sessions_active ON sessions (active);
CREATE INDEX idx_sessions_created ON sessions (created);
