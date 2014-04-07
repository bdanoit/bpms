-- MySQL dump 10.13  Distrib 5.5.35, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bpms
-- ------------------------------------------------------
-- Server version	5.5.35-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(64) DEFAULT NULL,
  `icon` char(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission`
--

LOCK TABLES `permission` WRITE;
/*!40000 ALTER TABLE `permission` DISABLE KEYS */;
INSERT INTO `permission` VALUES (1,'Member','user'),(2,'Edit','pencil'),(3,'Remove','trash'),(4,'Grant','gift');
/*!40000 ALTER TABLE `permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phase`
--

DROP TABLE IF EXISTS `phase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` char(64) NOT NULL,
  `description` text,
  `end` datetime NOT NULL,
  `creator_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `end` (`end`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phase`
--

LOCK TABLES `phase` WRITE;
/*!40000 ALTER TABLE `phase` DISABLE KEYS */;
INSERT INTO `phase` VALUES (1,2,'Phase 1','The first phase of the project!','2014-04-03 23:59:00',2),(2,15,'Phase1 due','','2014-02-07 10:30:00',13),(3,15,'Phase 2 due','','2014-03-14 23:30:00',13),(4,15,'Showcase project','Tell the world about how awesome BPMS is!','2014-04-15 02:30:00',13),(8,17,'Show baleze head','kill him and chop his head & skin him','2014-06-06 23:59:00',19),(9,18,'milestone','','2014-04-04 23:59:00',20),(10,19,'Milestone demonstration','Test.','2014-04-06 23:59:00',21),(11,22,'sadsdasd','','2014-04-06 23:59:00',13);
/*!40000 ALTER TABLE `phase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(64) NOT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (15,'BPMS Project',2,'2014-03-30 10:16:19'),(16,'Documentation Walk Through',12,'2014-04-03 13:59:08'),(17,'man-deep',19,'2014-04-04 11:12:05'),(18,'testprojectpleaseignore',20,'2014-04-04 11:18:14'),(19,'Demonstration',21,'2014-04-04 11:34:12'),(20,'asdf',14,'2014-04-04 11:43:33'),(21,'asdf',14,'2014-04-04 11:43:33'),(22,'test',13,'2014-04-06 13:35:41');
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `project_creator_permissions_insert`
    AFTER INSERT ON `project`
FOR EACH ROW
    BEGIN
        INSERT INTO project_permission (project_id, user_id, permission_id) SELECT NEW.id AS project_id, NEW.creator_id AS user_id, id AS permission_id FROM permission;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `project_permission`
--

DROP TABLE IF EXISTS `project_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_permission` (
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL,
  UNIQUE KEY `project_id` (`project_id`,`user_id`,`permission_id`),
  KEY `project_id_2` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_permission`
--

LOCK TABLES `project_permission` WRITE;
/*!40000 ALTER TABLE `project_permission` DISABLE KEYS */;
INSERT INTO `project_permission` VALUES (15,2,1),(15,2,2),(15,2,3),(15,2,4),(15,12,1),(15,12,2),(15,12,3),(15,13,1),(15,13,2),(15,14,1),(15,14,2),(16,12,1),(16,12,2),(16,12,3),(16,12,4),(17,13,1),(17,13,2),(17,13,3),(17,13,4),(17,19,1),(17,19,2),(17,19,3),(17,19,4),(18,20,1),(18,20,2),(18,20,3),(18,20,4),(19,13,1),(19,13,2),(19,14,1),(19,14,2),(19,14,4),(19,21,1),(19,21,2),(19,21,3),(19,21,4),(20,14,1),(20,14,2),(20,14,3),(20,14,4),(21,14,1),(21,14,2),(21,14,3),(21,14,4),(22,2,1),(22,2,2),(22,2,3),(22,2,4),(22,13,1),(22,13,2),(22,13,3),(22,13,4);
/*!40000 ALTER TABLE `project_permission` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER createrCantBeRemovedFromProject
   BEFORE DELETE ON project_permission
   FOR EACH ROW
BEGIN
   IF EXISTS (SELECT id FROM project WHERE id = OLD.project_id AND creator_id = OLD.user_id) THEN
      CALL raise_application_error(1236, 'cant remove create from project', 'project_permission', OLD.user_id);
      CALL get_last_custom_error(); 
   END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `hash` varchar(32) NOT NULL,
  `user_id` varchar(64) DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hash`),
  KEY `user_id` (`user_id`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('001535b2f4249af39f7df5eb83631ba5','2','2014-03-28 18:31:59'),('037af704f0cc0b63c6b0ed40a7a7b469','14','2014-03-25 05:06:30'),('08a3ae856c6ade7d10756d3f3352c9b5','14','2014-03-29 00:44:15'),('0b07821129a0f6dd0f18da1bf4d200c4','2','2014-04-03 07:17:07'),('0b084ff568046982a982668a5bd986d2','13','2014-04-06 20:41:24'),('0bbe72208a7845212623197cdfe4ee96','2','2014-03-22 21:35:12'),('0ecde35a05d773b94eb7c998f3cc83e1','2','2014-03-28 20:27:14'),('0ee6333c25611bfbc64d5df448bbbdc8','2','2014-03-22 21:31:13'),('10ca9305db0351251585106aff35460a','2','2014-03-26 20:12:52'),('15fd711704bee8562b5908a6213003b7','2','2014-03-27 20:04:39'),('198aa187a2422d2f3cf70514cd1659e0','2','2014-03-30 06:52:54'),('1f7faa9a51a7a0fa57bfc600e7c51e66','2','2014-03-29 07:51:43'),('219ce15b58bb7f770f14cfe411a32e17','2','2014-03-23 19:44:50'),('21eeebb34c2c838feacbe6277852d93a','2','2014-03-29 21:13:05'),('22a05ff28a2215595b8d7dae28328e29','14','2014-04-03 18:34:40'),('2410dcf294fa12274314a8fad57faed4','1','2014-03-21 20:19:21'),('28183deb7083fd8d0ea192e679368499','2','2014-04-03 22:04:55'),('2961c2e70de438c953010c1d8b884b21','2','2014-03-28 05:04:19'),('2b1ef59837f00922c7cb28d0cc453ec9','13','2014-03-24 21:39:40'),('2d1dc759f66d5414ad9c1a7db1ea2de5','12','2014-04-01 14:39:51'),('2d303a7d505baa8149927058a4af99a4','13','2014-03-24 18:28:04'),('2fedbdefbc810d79e476e4d72f26ca0a','14','2014-04-04 18:43:07'),('310882dcb7b180053e3e359481055bb5','14','2014-04-01 23:24:42'),('310e5aef6084e1266364612bd132a2da','2','2014-03-23 19:09:20'),('326889861ff4ca7f3c4e8a9c01bdfcb4','7','2014-03-28 17:13:06'),('35349b700088030cdca94d178cf4a617','2','2014-03-30 03:14:41'),('368888033a9d0ff81aaca254b20b94f6','2','2014-03-23 19:02:19'),('38dd5d94066d91bdaaa8d77aa532c1b5','13','2014-03-30 17:15:43'),('3a7853897ee1b2fe6c801c0db35bf197','2','2014-03-28 03:36:28'),('400d52086f67bba7eeb9c6c1ff80ba55','15','2014-03-26 16:52:41'),('406aee015799aaeb6657d99c979e180d','2','2014-03-23 21:19:52'),('423284347bbe71a3925aacd0d68832c2','1','2014-03-21 20:37:22'),('4cbc25d695548887a43b0a916b66a695','14','2014-04-01 23:17:56'),('51435dc281fd1bc9b71517115ce36ed8','2','2014-03-29 07:51:25'),('52211bb8cb66c2e05d79d33d4215b0db','2','2014-03-22 21:35:42'),('533f283d548140c9e3d6597e09428f61','3','2014-03-22 01:06:02'),('538ea7ef6341bfd48ab89e17135a9c55','14','2014-04-01 23:22:54'),('5610129d7ab0532a9f0d7e18335d51fe','12','2014-04-03 21:51:02'),('591317dd554b2298b93512cd0603d737','10','2014-03-23 19:50:19'),('59bff509b0d88993428bfcfccc42cec9','2','2014-03-22 02:31:26'),('5b0aab8eb0213578af12e7657d291b64','14','2014-04-01 23:23:05'),('5c2cea7704060db3504714928e9effe4','12','2014-04-03 20:58:33'),('5cf4ecb920bd94b9ae76ff32c846bc8a','2','2014-03-23 21:18:27'),('5fb68277e49acc3063b014bd8a529a47','20','2014-04-04 18:18:05'),('6274282b246987e5d452f431936980fc','2','2014-03-22 04:04:42'),('630d989e8ddea372badf8524d673e5ab','14','2014-04-01 23:22:16'),('6675ba37feed027fbcfc656254711d85','13','2014-03-24 23:33:27'),('6726664c8e66064051cd372ee500da9d','2','2014-03-29 00:00:24'),('68cec5514d0cc3e875693d5562e1f753','2','2014-03-23 20:38:31'),('6a292eb949c731ac5a4103a17a5630a0','2','2014-04-03 07:11:55'),('6c01dee80e5350dc1e7c07b1546b5951','2','2014-03-29 07:33:11'),('707f55f03e1d67ee191568b6407e80d8','6','2014-03-22 02:32:09'),('72c28ec943fe0c2f3b19be3368fa9921','14','2014-03-25 05:04:52'),('737d7b5ac82705b4561558d20f929555','2','2014-03-22 21:36:12'),('76eb4e21e1aec9aa5ab01c9b9eb6b8a4','2','2014-03-28 17:30:15'),('791b4b6e1b4c7fc2b444648b6d44ebe5','2','2014-03-22 01:10:26'),('7afb9fb2a9b870ff7951a82c7dfc1920','2','2014-03-29 23:19:28'),('7c0d2296191d7be9525f7323a9afc8ae','8','2014-03-23 19:02:05'),('815823ddad640222c2b2a73ccaa8690e','4','2014-03-22 01:08:10'),('8212fc0adb2ff902c6a73f08ed0d092f','13','2014-03-26 20:12:06'),('87574304821d99a3d1b87a8b41597380','14','2014-04-04 19:03:48'),('8927372357ff3c782d5823d8bac68fd8','14','2014-04-03 20:18:14'),('90df5990238e3113b39fd1ffc07445c4','5','2014-03-22 01:56:51'),('94c01a3c53c732e623a107b7f34a9c72','13','2014-03-31 23:19:37'),('95898971ae05e7a6a0757bdd8a3433a8','12','2014-03-31 17:01:13'),('9655d1a3301d0a20d2311ffe97635704','14','2014-04-04 18:30:00'),('968ef4d8f82d8b70e9f458dd38475ff2','2','2014-03-26 19:54:31'),('9713117ef4b6920eeb4cb41b6cc6bd76','2','2014-03-23 19:09:14'),('97cf0fe1851e84580202e3de6e63c1d2','1','2014-03-21 20:19:08'),('997df45aaf6eece3c71d4961b136ef40','21','2014-04-04 18:33:59'),('99937d8feb2d43939e7b366915d530fb','11','2014-03-24 00:12:03'),('9d7724d1b03bd9abc9d70aa56764a4f1','12','2014-03-28 19:10:10'),('a064960dfe016899fdf10e9b73d9f3f7','1','2014-03-21 21:20:21'),('a12e00807c9c4beb8dba1fb43e9370a0','10','2014-03-23 19:05:39'),('a49496b0db91079e7aa2bc929dcc0815','9','2014-03-23 19:05:12'),('a70efdd4a1995fe2cf2e8e51b4f6e23b','12','2014-03-24 18:26:39'),('a76cbbbb4797b39e1bd2a28d7ac3f89f','14','2014-03-29 00:44:33'),('ad6751f5b8492694310a07ef4380d70a','2','2014-03-22 21:35:33'),('adc0cd95494288a1a0e29e2f96ae62f3','2','2014-03-23 21:17:46'),('af9917c45967ac65d09c32bda307e234','1','2014-03-21 21:02:56'),('b6a7aee4a67918ad0308c7d0f40d6378','2','2014-03-23 21:23:51'),('bb0ed08741a21dfb4bbec47465de919c','2','2014-03-23 19:36:42'),('bb6ac84b29eb6579eab99bcbef61ecac','16','2014-03-27 20:04:43'),('c2d1d82822e2a69d4a9fb30377bb20ca','13','2014-03-25 20:05:49'),('c65dfb0e1450a2a42b232c272cb89bd4','13','2014-03-24 19:44:59'),('c871e949e34f2cf0075b0d3dbe357def','2','2014-03-29 05:21:38'),('cf9e1fd225299e29c4d9287f308935cb','2','2014-03-22 21:35:58'),('d077ea7fc9b7adf21395cb4fa6a6bd35','14','2014-04-03 18:35:24'),('d2b7eb2748ae651f0e1f7656a40ed1e3','13','2014-03-26 19:54:03'),('d6dfcf5cfec2489198c673374ed3fa68','2','2014-03-25 18:15:34'),('d7b99da7fc2e7c3575c757692f2b8012','13','2014-03-27 20:05:16'),('d82a3579ee6e8cf99117776daddc9fb1','10','2014-03-23 21:18:14'),('da757b6f5f8e4d3f9e8ed5e03508ad66','18','2014-03-28 17:29:12'),('e0e113928ade309d47d829fab5efb5fc','10','2014-03-23 19:37:16'),('e30e693b2f99e80c9777def48294ce01','13','2014-04-06 20:34:28'),('e880cb2d70f6ffb3490535e41822aab6','2','2014-04-03 22:05:08'),('e9f57c8e6afc88a50c7e148cfe03d9bf','2','2014-03-30 08:18:55'),('ed4bcf6683c4de70e6d980e6f39072e3','7','2014-03-29 05:09:03'),('eda1b0b731f8188d9db2723448c102ec','2','2014-03-21 23:15:28'),('efbd4e6c43248aa5734ad82b2ed38b33','10','2014-03-23 21:16:21'),('f090e4c2fae9b8bdc5e27c8218fadc54','2','2014-03-26 20:21:25'),('f417ce97a8b935370efb7008424f572d','2','2014-04-04 18:41:01'),('f44e62d9474fa7d3853404b6bcadc71d','2','2014-03-21 23:34:17'),('f4f25537c5817048d2bd1886495c2841','19','2014-04-04 18:11:56'),('f5dbc0dd223f22e2a4f62b0d83dc4b4e','10','2014-03-23 21:20:10'),('f6f32b848555730245a9685382ad9cd8','16','2014-03-27 20:04:20'),('f7cb980f900794a7cd9c02b857929db9','2','2014-03-28 17:16:00'),('f8f544275fcd7489b820d5a4f2032e6b','2','2014-03-28 00:38:50'),('fc8f4d36d1ed6ab46d8f4bbe89d8e8fd','14','2014-04-04 18:37:30');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` char(64) NOT NULL,
  `description` text,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `start` (`start`),
  KEY `end` (`end`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task`
--

LOCK TABLES `task` WRITE;
/*!40000 ALTER TABLE `task` DISABLE KEYS */;
INSERT INTO `task` VALUES (1,7,'GET READY','rocksteady','1969-12-31 16:00:00','2014-03-29 12:00:00',NULL,0),(2,7,'wgegwg','jolly rancher','1969-12-31 16:00:00','2014-03-04 00:00:00',NULL,0),(8,15,'Project: overview','Design the overview page for the project management system','2014-03-27 12:00:00','2014-04-03 23:59:00',NULL,0),(15,15,'Tasks: follow','Allow users to select and follow tasks','2014-03-29 12:00:00','2014-03-31 23:59:00',NULL,0),(22,14,'First task','Lmao','2014-03-29 12:00:00','2014-03-30 23:59:00',NULL,0),(23,15,'Phase 1 Documentation','Write phase 1 documentation','2014-01-10 14:30:00','2014-02-07 10:30:00',NULL,1),(24,15,'Phase 1 Presentation','Make Phase 1 Presentation','2014-01-10 14:30:00','2014-02-07 10:30:00',NULL,1),(25,15,'State diagrams','Create state diagrams for phase 2 documents','2014-02-07 12:00:00','2014-03-10 10:30:00',NULL,1),(26,15,'Activity diagrams and sequence diagrams and use cases','create activity diagrams and sequence diagrams for phase 2 documents','2014-02-07 12:00:00','2014-03-10 10:30:00',NULL,1),(27,15,'Class diagram','Create class diagrams for phase 2 documents','2014-02-07 12:00:00','2014-03-10 10:30:00',NULL,1),(28,15,'Prototype design','Create paper prototype design for the project','2014-02-14 12:00:00','2014-03-10 10:30:00',NULL,1),(29,15,'Origanize phase 2 documentation','Put all the documents together and do the basic write up','2014-03-10 12:00:00','2014-03-14 10:30:00',NULL,1),(30,15,'Phase 2 presentation','Make phase2 presentation','2014-03-10 12:00:00','2014-03-14 10:30:00',NULL,1),(35,15,'create database table','','2014-03-15 12:00:00','2014-03-17 23:59:00',NULL,1),(37,15,'Get the chat system up','','2014-03-15 12:00:00','2014-03-28 23:59:00',NULL,0),(38,15,'get login and basic website framework up','','2014-03-15 12:00:00','2014-03-24 23:59:00',NULL,1),(39,15,'Get create project up','Make the create project option in website','2014-03-24 00:00:00','2014-03-26 23:59:00',NULL,1),(40,15,'Combine chat system into website','','2014-03-29 00:00:00','2014-04-14 23:59:00',NULL,0),(41,15,'Create task option in the website','','2014-03-26 00:00:00','2014-03-27 23:59:00',NULL,1),(42,15,'Test site functionality','','2014-03-15 12:00:00','2014-04-13 23:59:00',NULL,0),(44,15,'Triggers','Get the triggers done you fool...','2014-03-27 12:00:00','2014-03-29 23:59:00',NULL,1),(46,15,'Tasks: add time','Create a page to add time to tasks','2014-03-28 12:00:00','2014-03-29 23:59:00',NULL,1),(47,15,'Make a project for our project!','Create a detailed project to manage the creation of our project management system. Thanks bruh...','2014-03-28 12:00:00','2014-03-30 23:59:00',NULL,1),(48,15,'Tasks: email alerts','Make sure tasks that are due soon or overdue send email alerts to asignees','2014-03-28 12:00:00','2014-04-03 22:05:00',NULL,1),(49,15,'Milestones','Create, edit, view milestones','2014-03-28 12:00:00','2014-03-30 23:59:00',NULL,1),(50,15,'Register: email verification','Add email verification to registration process','2014-03-28 12:00:00','2014-04-07 23:59:00',NULL,0),(51,15,'Members: view','create a page to view individual members, and their logs','2014-03-29 12:00:00','2014-04-05 23:59:00',NULL,0),(53,15,'Tasks: view','Create task view with progress bar','2014-03-28 12:00:00','2014-03-29 23:59:00',NULL,1),(54,15,'Members: add (autocomplete)','Member selection in add members should autocomplete','2014-03-28 12:00:00','2014-03-29 23:59:00',NULL,1),(55,15,'Redesign project view','Redesign the project view and default view','2014-03-28 12:00:00','2014-03-29 23:59:00',NULL,1),(58,11,'blA','','2014-03-31 12:00:00','2014-04-01 23:59:00',NULL,0),(59,2,'TESTING TRIGGERS','start after end','2014-04-02 12:00:00','2014-04-03 23:59:00',NULL,0),(60,15,'Task view: complete task button','Add \'complete task\' button to task view','2014-04-03 12:00:00','2014-04-03 23:59:00',NULL,1),(61,15,'Final Documentation','Finish the Final Documentation as described at http://csci.viu.ca/~krazanmp/CSCI310/csci_310_TermProjectPhase3.html','2014-04-03 12:00:00','2014-04-09 23:59:00',NULL,0),(62,15,'Usurp Mandip','','2014-04-03 18:00:00','2014-04-04 18:08:00',2,1),(63,15,'Contact us page',':)','2014-04-04 12:00:00','2014-04-07 00:00:00',2,0),(65,17,'sleep all the way','','2014-04-04 12:00:00','2014-04-05 23:59:00',19,1),(66,18,'test task please ignore','ignore','2014-04-04 12:00:00','2014-04-05 23:59:00',20,1),(67,19,'Add some members','Added some members in a live demonstration.','2014-04-04 12:00:00','2014-04-05 23:59:00',21,0),(72,15,'test','','2014-04-06 12:00:00','2014-04-10 23:59:00',12,0),(73,22,'tretrrt','','2014-04-06 12:00:00','2014-04-07 23:59:00',13,1),(74,15,'Finsh sql query','','2014-04-05 12:00:00','2014-04-07 23:59:00',13,0);
/*!40000 ALTER TABLE `task` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER taskEndDateBeforeStart

   BEFORE INSERT ON task

   FOR EACH ROW
BEGIN

   IF NEW.start > NEW.end THEN

      CALL raise_application_error(1235, 'Cant start after end date', 'task', NEW.start);

      CALL get_last_custom_error();       

   END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_create_email_alert_insert
   AFTER INSERT ON task
   FOR EACH ROW
BEGIN
    CALL proc_create_email_alert(NEW.project_id, NEW.id, NEW.end, 0);
    CALL proc_create_email_alert(NEW.project_id, NEW.id, (NEW.end - INTERVAL 3 DAY), 1);
    IF NEW.complete = 1 THEN
        CALL proc_create_email_alert(NEW.project_id, NEW.id, NEW.end, 2);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER taskEndDateBeforeStartUpdate

   BEFORE UPDATE ON task

   FOR EACH ROW
BEGIN

   IF NEW.start > NEW.end THEN

      CALL raise_application_error(1235, 'Cant start after end date', 'task', NEW.start);

      CALL get_last_custom_error();       

   END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_create_email_alert_update
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `task_assigned_to`
--

DROP TABLE IF EXISTS `task_assigned_to`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_assigned_to` (
  `project_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  UNIQUE KEY `project_id` (`project_id`,`task_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_assigned_to`
--

LOCK TABLES `task_assigned_to` WRITE;
/*!40000 ALTER TABLE `task_assigned_to` DISABLE KEYS */;
INSERT INTO `task_assigned_to` VALUES (7,1,2),(7,2,2),(14,22,2),(14,22,7),(15,3,2),(15,4,7),(15,5,2),(15,6,13),(15,7,13),(15,8,2),(15,9,2),(15,10,13),(15,11,2),(15,12,2),(15,13,2),(15,14,2),(15,15,2),(15,16,2),(15,17,2),(15,18,2),(15,21,12),(15,23,12),(15,23,14),(15,24,2),(15,24,14),(15,25,12),(15,26,13),(15,27,2),(15,28,13),(15,28,14),(15,29,12),(15,30,14),(15,31,2),(15,32,12),(15,32,13),(15,33,2),(15,34,13),(15,35,12),(15,35,13),(15,36,13),(15,37,14),(15,38,2),(15,39,2),(15,40,2),(15,40,14),(15,41,2),(15,42,13),(15,44,13),(15,46,2),(15,47,13),(15,48,2),(15,49,2),(15,50,2),(15,51,2),(15,53,2),(15,54,2),(15,55,2),(15,60,2),(15,61,12),(15,62,2),(15,63,2),(15,63,12),(15,70,2),(15,70,14),(15,71,2),(15,72,12),(15,74,12),(15,74,13),(17,64,19),(17,65,13),(17,65,19),(18,66,20),(19,67,14),(19,67,21),(22,73,13);
/*!40000 ALTER TABLE `task_assigned_to` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_email_alerts`
--

DROP TABLE IF EXISTS `task_email_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_email_alerts` (
  `project_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `when` datetime NOT NULL,
  `type` int(11) NOT NULL,
  KEY `when` (`when`),
  KEY `project_id` (`project_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_email_alerts`
--

LOCK TABLES `task_email_alerts` WRITE;
/*!40000 ALTER TABLE `task_email_alerts` DISABLE KEYS */;
INSERT INTO `task_email_alerts` VALUES (15,74,'2014-04-07 23:59:00',0);
/*!40000 ALTER TABLE `task_email_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_log`
--

DROP TABLE IF EXISTS `task_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_log` (
  `project_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `note` varchar(240) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `start` (`start`),
  KEY `end` (`end`),
  KEY `project_id` (`project_id`,`task_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_log`
--

LOCK TABLES `task_log` WRITE;
/*!40000 ALTER TABLE `task_log` DISABLE KEYS */;
INSERT INTO `task_log` VALUES (15,61,12,7,'2014-04-03 14:35:00','2014-04-03 23:34:00','Writing the final documentation'),(15,42,13,8,'2014-04-04 11:06:00','2014-04-04 09:06:00','test site'),(15,48,2,9,'2014-04-03 16:10:00','2014-04-03 20:10:00','Finished implementing email alerts'),(15,60,2,10,'2014-04-02 13:13:00','2014-04-02 14:13:00','Feature has been incorporated'),(15,62,2,11,'2014-04-03 13:14:00','2014-04-03 13:19:00','It only took me 5 minutes to usurp mandip...'),(18,66,20,12,'2014-04-04 11:22:00','2014-04-04 11:22:00','looked for the log time button'),(15,40,14,13,'2014-04-04 11:39:00','2014-04-04 13:39:00','Everything.'),(15,8,2,14,'2014-04-05 13:00:00','2014-04-05 23:00:00','Allowed for filtering of tasks in project overview'),(15,8,2,15,'2014-04-04 17:30:00','2014-04-05 04:15:00','Converted overview from HTML to Canvas'),(15,72,12,16,'2014-04-06 13:35:00','2014-04-06 13:35:00',''),(15,72,12,17,'2014-04-06 13:35:00','2014-04-06 13:35:00',''),(15,72,12,18,'2014-04-06 13:35:00','2014-04-06 13:35:00',''),(15,38,2,19,'2014-03-15 10:30:00','2014-03-15 11:30:00','Framework and login functionality has been implemented'),(15,46,2,20,'2014-03-28 17:30:00','2014-03-28 21:30:00','Log time feature has been implemented'),(15,53,2,21,'2014-03-29 12:30:00','2014-04-29 17:00:00','Task view has been implemented'),(15,42,13,22,'2014-04-06 11:52:00','2014-04-06 13:52:00','check project overview'),(15,42,13,23,'2014-04-07 13:53:00','2014-04-06 13:53:00','test'),(15,74,13,24,'2014-04-06 13:55:00','2014-04-06 15:55:00','create sql script');
/*!40000 ALTER TABLE `task_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(32) NOT NULL,
  `password` char(32) NOT NULL,
  `email` char(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (2,'Baleze','76f5647fa35af64b5dc73240f2cf961f','bdanoit@gmail.com'),(7,'Test','e10adc3949ba59abbe56e057f20f883e','test@test.com'),(10,'Jean','76f5647fa35af64b5dc73240f2cf961f','jean@danoit.com'),(11,'Tester','76f5647fa35af64b5dc73240f2cf961f','tester@test.com'),(12,'John','5f4dcc3b5aa765d61d8327deb882cf99','jtribe77@gmail.com'),(13,'Mandip','5d41402abc4b2a76b9719d911017c592','Mandeeip@hotmail.com'),(14,'Daniel','82e5831d0264808fc65b2054ad256d19','danieldeyaegher@hotmail.com'),(15,'Hello','827ccb0eea8a706c4c34a16891f84e7b','helloworld@hotmail.com'),(16,'Bob','43c5a403872b0a19c51ec9ac2a1f475a','bob@bob.ca'),(18,'Mandip2','e10adc3949ba59abbe56e057f20f883e','mandip@sangha.com'),(19,'lembang','25f9e794323b453885f5181f1b624d0b','test@mandeep.com'),(20,'Canthon','827ccb0eea8a706c4c34a16891f84e7b','robert@robert.com'),(21,'NewUser','81dc9bdb52d04dc20036dbd8313ed055','test@test.ca');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `user_password_hash_insert`
    BEFORE INSERT ON `user`
FOR EACH ROW
    BEGIN
        SET NEW.password = md5(NEW.password);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `user_password_hash_update`
    BEFORE UPDATE ON `user`
FOR EACH ROW
    BEGIN
        SET NEW.password = IF(NEW.password = OLD.password, NEW.password, md5(NEW.password));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary table structure for view `userProject`
--

DROP TABLE IF EXISTS `userProject`;
/*!50001 DROP VIEW IF EXISTS `userProject`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `userProject` (
  `name` tinyint NOT NULL,
  `project_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `creator_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `userProject`
--

/*!50001 DROP TABLE IF EXISTS `userProject`*/;
/*!50001 DROP VIEW IF EXISTS `userProject`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `userProject` AS select `project`.`name` AS `name`,`project_permission`.`project_id` AS `project_id`,`project_permission`.`user_id` AS `user_id`,`project`.`creator_id` AS `creator_id` from (`project_permission` join `project`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-06 14:43:48
