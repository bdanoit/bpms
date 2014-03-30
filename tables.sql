DROP TABLE IF EXISTS task_assigned_to;
DROP TABLE IF EXISTS project_permission;
DROP TABLE IF EXISTS permission;
DROP TABLE IF EXISTS task;
DROP TABLE IF EXISTS phase;
DROP TABLE IF EXISTS project;
DROP TABLE IF EXISTS user;

CREATE TABLE user(
    id int primary key auto_increment,
    name char(32) unique not null,
    password char(32) not null,
    email char(128) unique not null
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE project(
    id int primary key auto_increment,
    name char(64) not null,
    creator_id int references user(id),
    created datetime
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE phase(
    id int primary key auto_increment,
    project_id int not null references project(id) ON DELETE CASCADE,
    name char(64) not null,
    description text,
    start datetime not null,
    end datetime not null,
    creator_id int references user(id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task(
    id int primary key auto_increment,
    project_id int not null references project(id) ON DELETE CASCADE,
    name char(64) not null,
    description text,
    start datetime not null,
    end datetime not null,
    creator_id int references user(id),
    complete tinyint(1) not null default 0
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task_log(
    project_id int not null references project(id) ON DELETE CASCADE,
    task_id int not null references user(id) ON DELETE CASCADE,
    user_id int not null references user(id) ON DELETE CASCADE,
    id int not null primary key auto_increment,
    start datetime not null,
    end datetime not null,
    note varchar(240),
    index(start),
    index(end),
    index(project_id, task_id, user_id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE permission(
    id int primary key auto_increment,
    name char(64),
    icon char(16)
)ENGINE=InnoDB CHARACTER SET=utf8;
INSERT INTO permission (name, icon) VALUES
    ('Member', 'user'),
    ('Edit', 'pencil'),
    ('Remove', 'trash'),
    ('Grant', 'gift');

CREATE TABLE project_permission(
    project_id int references project(id) ON DELETE CASCADE,
    user_id int references user(id) ON DELETE CASCADE,
    permission_id int references permission(id),
    UNIQUE(project_id,user_id,permission_id)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task_assigned_to(
    project_id int references project(id),
    task_id int references task(id) ON DELETE CASCADE,
    user_id int references user(id) ON DELETE CASCADE,
    unique key(project_id, task_id, user_id)
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
