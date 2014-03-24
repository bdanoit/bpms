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




DELIMITER $$
DROP TRIGGER IF EXISTS `project_creator_permissions_insert`$$
CREATE TRIGGER `project_creator_permissions_insert`
    AFTER INSERT ON `project`
FOR EACH ROW
    BEGIN
        INSERT INTO project_permission (project_id, user_id, permission_id) SELECT NEW.id AS project_id, NEW.creator_id AS user_id, id AS permission_id FROM permission;
END$$
DELIMITER ;

/*
DELIMITER $$
DROP TRIGGER IF EXISTS `project_creator_permissions_delete`$$
CREATE TRIGGER `project_creator_permissions_delete`
    BEFORE DELETE ON `project_permission`
FOR EACH ROW
    BEGIN
        DECLARE msg VARCHAR(255);
        DECLARE creator_id CURSOR FOR SELECT creator_id FROM project WHERE id = OLD.project_id;
        
        IF(creator_id = OLD.user_id) THEN
            SET msg = 'DELETE canceled, project owner permissions cannot be revoked.';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = msg; 
        END IF;
END$$
DELIMITER ;*/