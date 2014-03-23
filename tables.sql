DROP TABLE IF EXISTS task_assigned_to;
DROP TABLE IF EXISTS task_permission;
DROP TABLE IF EXISTS project_permission;
DROP TABLE IF EXISTS permission;
DROP TABLE IF EXISTS task;
DROP TABLE IF EXISTS phase;
DROP TABLE IF EXISTS project;
DROP TABLE IF EXISTS user;

CREATE TABLE user(
    id int primary key auto_increment,
    password char(32) not null,
    email char(128) not null
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE project(
    id int primary key auto_increment,
    name char(64) not null,
    creator_id int references user(id),
    created date
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE phase(
    id int primary key auto_increment,
    project_id int references project(id),
    name char(64) not null,
    description text,
    start date not null,
    end date not null,
    creator_id int references user(id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task(
    id int primary key auto_increment,
    project_id int references project(id),
    phase_id int references phase(id),
    name char(64) not null,
    description text,
    start date not null,
    end date not null,
    creator_id int references user(id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE permission(
    id int primary key auto_increment,
    name char(64)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE project_permission(
    project_id int references project(id),
    user_id int references user(id),
    permission_id int references permission(id),
    UNIQUE(project_id,user_id,permission_id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task_permission(
    project_id int references project(id),
    task_id int references project(id),
    user_id int references user(id),
    permission_id int references permission(id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task_assigned_to(
    project_id int references project(id),
    task_id int references project(id),
    user_id int references user(id)
)ENGINE=InnoDB CHARACTER SET=utf8;




DELIMITER $$
DROP TRIGGER IF EXISTS `user_password_hash_insert`$$
CREATE TRIGGER `user_password_hash_insert`
    BEFORE INSERT ON `user`
FOR EACH ROW
    BEGIN
        SET NEW.password = md5(NEW.password);
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `user_password_hash_update`$$
CREATE TRIGGER `user_password_hash_update`
    BEFORE UPDATE ON `user`
FOR EACH ROW
    BEGIN
        SET NEW.password = IF(NEW.password = OLD.password, NEW.password, md5(NEW.password));
END$$
DELIMITER ;