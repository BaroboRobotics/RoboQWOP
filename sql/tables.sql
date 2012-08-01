USE barobo;

/* Contains users waiting to control the Mobot */
CREATE TABLE IF NOT EXISTS queue (
	id INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(id),
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	last_active TIMESTAMP NULL DEFAULT NULL,
	user_id INT NOT NULL,
    robot_number INT NOT NULL DEFAULT 0,
    UNIQUE(user_id)
) ENGINE = InnoDB;

/* Contains all the users that have ever signed in, to control the Mobot. */
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    email varchar(255),
    first_name varchar(200),
    last_name varchar(200),
    country varchar(100),
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP NULL DEFAULT NULL,
    control_time INT DEFAULT 60, /* Time to control the mobot when added to the queue */
    UNIQUE (email)
) ENGINE = InnoDB;

/* Contains the available Mobots that can be controlled */
CREATE TABLE IF NOT EXISTS robots (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    address CHAR(17),
    name VARCHAR(100),
	color1_hex VARCHAR(6),
	color2_hex VARCHAR(6),
	color1_name VARCHAR(10),
	color2_name VARCHAR(10),
    number INT NOT NULL,
    UNIQUE(number)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS controllers (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    control_time INT DEFAULT 60,
    user_id INT NOT NULL,
    robot_number INT NOT NULL DEFAULT 0,
    unique(user_id)
) ENGINE = InnoDB;
