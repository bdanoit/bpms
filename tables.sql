DROP TABLE IF EXISTS task_assigned_to;
DROP TABLE IF EXISTS project_permission;
DROP TABLE IF EXISTS permission;
DROP TABLE IF EXISTS task;
DROP TABLE IF EXISTS phase;
DROP TABLE IF EXISTS project;
DROP TABLE IF EXISTS user_verify;
DROP TABLE IF EXISTS user;

CREATE TABLE user(
    id int primary key auto_increment,
    name char(32) unique not null,
    password char(32) not null,
    email char(128) unique not null
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE `sessions` (
  `hash` varchar(32) NOT NULL,
  `user_id` varchar(64) DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hash`),
  INDEX (`user_id`),
  INDEX (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_verify` (
  `hash` varchar(32) NOT NULL,
  `user_id` int not null references user(id) on delete cascade,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hash`),
  INDEX (`user_id`),
  INDEX (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_forgot` (
  `hash` varchar(32) NOT NULL,
  `user_id` int not null references user(id) on delete cascade,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hash`),
  INDEX (`user_id`),
  INDEX (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
    end datetime not null,
    creator_id int references user(id),
    index(end)
)ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE task(
    id int primary key auto_increment,
    project_id int not null references project(id) ON DELETE CASCADE,
    name char(64) not null,
    description text,
    start datetime not null,
    end datetime not null,
    creator_id int references user(id),
    complete tinyint(1) not null default 0,
    index(start),
    index(end)
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

CREATE TABLE task_email_alerts(
    project_id int not null references project(id) ON DELETE CASCADE,
    task_id int not null references user(id) ON DELETE CASCADE,
    `when` datetime not null,
    `type` int not null,
    index(`when`),
    index(project_id, task_id)
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


/* APPLICATION ERROR */
DROP PROCEDURE IF EXISTS raise_application_error;
DROP PROCEDURE IF EXISTS get_last_custom_error;
DROP FUNCTION IF EXISTS get_last_custom_error;
DROP PROCEDURE IF EXISTS fix_last_custom_error;
DROP TABLE IF EXISTS RAISE_ERROR;
DELIMITER $$

CREATE PROCEDURE raise_application_error( IN CODE INTEGER, IN MESSAGE VARCHAR(255), IN TABLENAME VARCHAR(30), IN KEYVALUE VARCHAR(50)) SQL SECURITY INVOKER DETERMINISTIC
BEGIN
   CREATE TEMPORARY TABLE IF NOT EXISTS RAISE_ERROR(F1 INT NOT NULL);
   SELECT CODE, MESSAGE, TABLENAME, KEYVALUE INTO @error_code, @error_message, @error_table, @error_key ;
   INSERT INTO RAISE_ERROR VALUES(NULL);
END;
$$

CREATE PROCEDURE fix_last_custom_error()
   IF @error_code = 1234 THEN 
      DELETE FROM Classes WHERE class = @error_key;
   END IF;
$$

CREATE FUNCTION get_last_custom_error()
   RETURNS VARCHAR(200) DETERMINISTIC
   RETURN(@error_message);
$$
DELIMITER ;
/* END APPLICATION ERROR */



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


/*Check to make sure phase end date happen after start date*/
DROP TRIGGER IF EXISTS phaseEndDateBeforeStart;
DELIMITER $$
CREATE TRIGGER phaseEndDateBeforeStart
   BEFORE INSERT ON phase
   FOR EACH ROW
BEGIN
   IF NEW.start > NEW.end THEN
      CALL raise_application_error(1234, 'Cant start after end date', 'phase', NEW.start);
      CALL get_last_custom_error();       
   END IF;
END$$ 




/*Check to make sure taskend date happen after start date*/
DROP TRIGGER IF EXISTS taskEndDateBeforeStart;
DELIMITER $$
CREATE TRIGGER taskEndDateBeforeStart
   BEFORE INSERT ON task
   FOR EACH ROW
BEGIN
   IF NEW.start > NEW.end THEN
      CALL raise_application_error(1235, 'Cant start after end date', 'task', NEW.start);
      CALL get_last_custom_error();       
   END IF;
END$$ 


/*Check to make sure taskend date happen after start date*/
DROP TRIGGER IF EXISTS taskEndDateBeforeStartUpdate;
DELIMITER $$
CREATE TRIGGER taskEndDateBeforeStartUpdate
   BEFORE UPDATE ON task
   FOR EACH ROW
BEGIN
   IF NEW.start > NEW.end THEN
      CALL raise_application_error(1235, 'Cant start after end date', 'task', NEW.start);
      CALL get_last_custom_error();       
   END IF;
END$$

/*Check to make sure project creators can't be removed from a project*/
DROP TRIGGER IF EXISTS createrCantBeRemovedFromProject;
DELIMITER $$
CREATE TRIGGER createrCantBeRemovedFromProject
   BEFORE DELETE ON project_permission
   FOR EACH ROW
BEGIN
   IF EXISTS (SELECT id FROM project WHERE id = OLD.project_id AND creator_id = OLD.user_id) THEN
      CALL raise_application_error(1236, 'cant remove create from project', 'project_permission', OLD.user_id);
      CALL get_last_custom_error(); 
   END IF;
END$$

/* Once a task is inserted or updated, create the email alert for it */
DROP TRIGGER IF EXISTS trg_create_email_alert_insert;
DELIMITER $$
CREATE TRIGGER trg_create_email_alert_insert
   AFTER INSERT ON task
   FOR EACH ROW
BEGIN
    CALL proc_create_email_alert(NEW.project_id, NEW.id, NEW.end, 0);
    CALL proc_create_email_alert(NEW.project_id, NEW.id, (NEW.end - INTERVAL 3 DAY), 1);
    IF NEW.complete = 1 THEN
        CALL proc_create_email_alert(NEW.project_id, NEW.id, NEW.end, 2);
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_create_email_alert_update;
DELIMITER $$
CREATE TRIGGER trg_create_email_alert_update
   AFTER UPDATE ON task
   FOR EACH ROW
BEGIN
    IF NEW.end <> OLD.end THEN
        CALL proc_create_email_alert(NEW.project_id, NEW.id, NEW.end, 0);
        CALL proc_create_email_alert(NEW.project_id, NEW.id, (NEW.end - INTERVAL 3 DAY), 1);
    END IF;
    IF OLD.complete != NEW.complete AND NEW.complete = 1 THEN
        CALL proc_create_email_alert(NEW.project_id, NEW.id, (NOW() + INTERVAL 30 SECOND), 2);
    END IF;
END$$

DROP PROCEDURE IF EXISTS proc_create_email_alert;
DELIMITER $$
CREATE PROCEDURE proc_create_email_alert(IN project_id INT, IN task_id INT, IN alert_time DATETIME, IN alert_type INT)
BEGIN
    delete from task_email_alerts where project_id = project_id AND task_id = task_id AND `type` = alert_type;
    IF(alert_time >= NOW()) THEN
        insert into task_email_alerts (`project_id`, `task_id`, `when`, `type`) values (project_id, task_id, alert_time, alert_type);
    END IF;
END;
$$



/* VIEWS */
DROP VIEW IF EXISTS userProject;
CREATE OR REPLACE VIEW userProject AS
SELECT name, project_id, user_id,creator_id
FROM project_permission NATURAL JOIN project;

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
