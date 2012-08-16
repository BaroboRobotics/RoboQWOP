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
    is_admin BOOLEAN NOT NULL DEFAULT 0,
    show_tutorial BOOLEAN NOT NULL DEFAULT 1,
    UNIQUE (email)
) ENGINE = InnoDB;

/* Contains the available Mobots that can be controlled */
CREATE TABLE IF NOT EXISTS robots (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    address CHAR(17),
    name VARCHAR(100),
    color1_hex VARCHAR(6) DEFAULT '000000',
    color2_hex VARCHAR(6) DEFAULT 'ffffff',
    color1_name VARCHAR(10) DEFAULT 'Black',
    color2_name VARCHAR(10) Default 'White',
    number INT NOT NULL,
    status INT NOT NULL DEFAULT 1,
    UNIQUE(number)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS controllers (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_active TIMESTAMP NULL DEFAULT NULL,
    control_time INT DEFAULT 60,
    user_id INT NOT NULL,
    robot_number INT NOT NULL DEFAULT 0,
    unique(user_id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS courses (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    title varchar(100),
    unique(title)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS assignments (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    course_id INT NOT NULL,    
    number INT NOT NULL,
    objective TEXT,
    youtube_url VARCHAR(200),
    instructions TEXT
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS users_last_completed_assignment_for_course (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id),
    user_id INT NOT NULL,    
    course_id INT NOT NULL,
    last_completed_assignment_number INT NOT NULL   
) ENGINE = InnoDB;

INSERT INTO courses (title) values ('Mobot Tutorial');
INSERT INTO assignments (course_id, number, objective, instructions, youtube_url) values (1, 1, 'Move the robot to the wall.', 'Hitting W&O at the same time on your keyboard will move the robot one direction. Hitting O&P will move it in the other direction.', "http://www.youtube.com/embed/uUzY5vpPMdU");
INSERT INTO assignments (course_id, number, objective, instructions) values (1, 2, 'Kick the ball to the wall', 'First get the robot next to the ball. Second spin the robot by pressing P&W (clockwise) or Q&O (counter-clockwise).', "http://www.youtube.com/embed/_6sePrtHDeU");
INSERT INTO assignments (course_id, number, objective, instructions, youtube_url) values (1, 3, 'Stand the robot up straight and knock it back down with legs straight out', "First get the robot rotated so that the joints move up and now not side to side. Second push both joints down rapidly. Depending on the robot's orientation, you'll need to press U&R down or I&E down.", "http://www.youtube.com/embed/_6H_AzCQ3do");