-- MySQL dump 10.11
--
-- Host: localhost    Database: oerdev
-- ------------------------------------------------------
-- Server version	5.0.45-Debian_1ubuntu3.1-log

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
-- Table structure for table `ocw_acl`
--

DROP TABLE IF EXISTS `ocw_acl`;
CREATE TABLE `ocw_acl` (
  `user_id` bigint(20) NOT NULL default '0',
  `course_id` int(11) NOT NULL default '0',
  `role` enum('instructor','dscribe1','dscribe2') collate utf8_unicode_ci NOT NULL default 'dscribe1',
  PRIMARY KEY  (`user_id`,`course_id`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_ci_sessions`
--

DROP TABLE IF EXISTS `ocw_ci_sessions`;
CREATE TABLE `ocw_ci_sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `session_data` text,
  PRIMARY KEY  (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_copyright_contactinfo`
--

DROP TABLE IF EXISTS `ocw_copyright_contactinfo`;
CREATE TABLE `ocw_copyright_contactinfo` (
  `id` bigint(20) NOT NULL,
  `copyright_holder_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_copyright_holders`
--

DROP TABLE IF EXISTS `ocw_copyright_holders`;
CREATE TABLE `ocw_copyright_holders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `friend` enum('1','0') collate utf8_unicode_ci NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_corecomp`
--

DROP TABLE IF EXISTS `ocw_corecomp`;
CREATE TABLE `ocw_corecomp` (
  `id` bigint(20) NOT NULL auto_increment,
  `corecomp` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_course_files`
--

DROP TABLE IF EXISTS `ocw_course_files`;
CREATE TABLE `ocw_course_files` (
  `id` bigint(20) NOT NULL auto_increment,
  `course_id` bigint(20) NOT NULL,
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `modified_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_courses`
--

DROP TABLE IF EXISTS `ocw_courses`;
CREATE TABLE `ocw_courses` (
  `id` bigint(20) NOT NULL auto_increment,
  `number` int(10) unsigned default NULL,
  `title` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `curriculum_id` bigint(20) default NULL,
  `director` varchar(70) character set utf8 collate utf8_unicode_ci NOT NULL,
  `creator` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `instructor_id` int(10) unsigned default NULL,
  `collaborators` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `level` enum('Undegraduate','Masters','PhD','M1','M2','M3','M4') character set utf8 collate utf8_unicode_ci NOT NULL,
  `length` enum('1 week','2 weeks','3 weeks','4 weeks','5 weeks','6 weeks','7 weeks','8 weeks','9 weeks','10 weeks','11 weeks','12 weeks','13 weeks','14 weeks') character set utf8 collate utf8_unicode_ci NOT NULL,
  `term` enum('Fall','Winter','Spring','Summer') character set utf8 collate utf8_unicode_ci NOT NULL,
  `year` year(4) NOT NULL,
  `copyright_holder_id` int(11) default NULL,
  `language` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default 'English',
  `school_id` int(10) unsigned default NULL,
  `subject_id` int(10) unsigned default NULL,
  `curricular_info` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `lifecycle_version` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `imagefile` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `highlights` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `description` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `keywords` text character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `curriculum_id` (`curriculum_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `copyright_holder_id` (`copyright_holder_id`),
  KEY `school_id` (`school_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `ocw_courses_ibfk_1` FOREIGN KEY (`curriculum_id`) REFERENCES `ocw_curriculums` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ocw_courses_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `ocw_instructors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ocw_courses_ibfk_3` FOREIGN KEY (`school_id`) REFERENCES `ocw_schools` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ocw_courses_ibfk_4` FOREIGN KEY (`subject_id`) REFERENCES `ocw_subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ocw_courses_ibfk_5` FOREIGN KEY (`copyright_holder_id`) REFERENCES `ocw_copyright_holders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_curriculums`
--

DROP TABLE IF EXISTS `ocw_curriculums`;
CREATE TABLE `ocw_curriculums` (
  `id` bigint(20) NOT NULL auto_increment,
  `school_id` bigint(20) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_instructors`
--

DROP TABLE IF EXISTS `ocw_instructors`;
CREATE TABLE `ocw_instructors` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `title` text collate utf8_unicode_ci NOT NULL,
  `info` text collate utf8_unicode_ci NOT NULL,
  `uri` varchar(255) collate utf8_unicode_ci default NULL,
  `imagefile` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_material_categories`
--

DROP TABLE IF EXISTS `ocw_material_categories`;
CREATE TABLE `ocw_material_categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_material_comments`
--

DROP TABLE IF EXISTS `ocw_material_comments`;
CREATE TABLE `ocw_material_comments` (
  `id` bigint(20) NOT NULL auto_increment,
  `material_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) NOT NULL,
  `comments` longtext collate utf8_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_material_files`
--

DROP TABLE IF EXISTS `ocw_material_files`;
CREATE TABLE `ocw_material_files` (
  `id` bigint(20) NOT NULL auto_increment,
  `material_id` bigint(20) NOT NULL,
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `modified_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_materials`
--

DROP TABLE IF EXISTS `ocw_materials`;
CREATE TABLE `ocw_materials` (
  `id` bigint(20) NOT NULL auto_increment,
  `course_id` bigint(20) NOT NULL,
  `category` varchar(255) collate utf8_unicode_ci NOT NULL default 'Resource Items',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ctools_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `author` varchar(255) collate utf8_unicode_ci default NULL,
  `collaborators` text collate utf8_unicode_ci NOT NULL,
  `tag_id` int(11) NOT NULL default '0',
  `mimetype_id` int(11) NOT NULL default '0',
  `in_ocw` enum('1','0') character set latin1 NOT NULL default '0',
  `embedded_co` enum('0','1') character set latin1 NOT NULL default '0',
  `nodetype` enum('child','parent') character set latin1 NOT NULL default 'child',
  `parent` bigint(20) NOT NULL default '0',
  `order` int(11) NOT NULL default '0',
  `modified` enum('1','0') character set latin1 NOT NULL default '0',
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10275 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_materials_corecomp`
--

DROP TABLE IF EXISTS `ocw_materials_corecomp`;
CREATE TABLE `ocw_materials_corecomp` (
  `id` bigint(20) NOT NULL auto_increment,
  `material_id` bigint(20) NOT NULL,
  `corecomp_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_mimetypes`
--

DROP TABLE IF EXISTS `ocw_mimetypes`;
CREATE TABLE `ocw_mimetypes` (
  `id` tinyint(4) NOT NULL auto_increment,
  `name` varchar(20) character set utf8 collate utf8_unicode_ci NOT NULL,
  `mimetype` varchar(70) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_object_comments`
--

DROP TABLE IF EXISTS `ocw_object_comments`;
CREATE TABLE `ocw_object_comments` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) NOT NULL,
  `comments` longtext collate utf8_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_copyright`
--

DROP TABLE IF EXISTS `ocw_object_copyright`;
CREATE TABLE `ocw_object_copyright` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL,
  `status` enum('unknown','copyrighted','public domain') collate utf8_unicode_ci NOT NULL,
  `holder` varchar(255) collate utf8_unicode_ci NOT NULL,
  `notice` text collate utf8_unicode_ci NOT NULL,
  `url` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_files`
--

DROP TABLE IF EXISTS `ocw_object_files`;
CREATE TABLE `ocw_object_files` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL,
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `modified_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fname` (`object_id`,`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=1090 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_log`
--

DROP TABLE IF EXISTS `ocw_object_log`;
CREATE TABLE `ocw_object_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) NOT NULL,
  `log` longtext collate utf8_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_questions`
--

DROP TABLE IF EXISTS `ocw_object_questions`;
CREATE TABLE `ocw_object_questions` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL,
  `question` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `answer` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_object_replacement_comments`
--

DROP TABLE IF EXISTS `ocw_object_replacement_comments`;
CREATE TABLE `ocw_object_replacement_comments` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) NOT NULL,
  `comments` longtext collate utf8_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_replacement_copyright`
--

DROP TABLE IF EXISTS `ocw_object_replacement_copyright`;
CREATE TABLE `ocw_object_replacement_copyright` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL,
  `status` enum('unknown','copyrighted','public domain') collate utf8_unicode_ci NOT NULL,
  `holder` varchar(255) collate utf8_unicode_ci NOT NULL,
  `notice` text collate utf8_unicode_ci NOT NULL,
  `url` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_replacement_log`
--

DROP TABLE IF EXISTS `ocw_object_replacement_log`;
CREATE TABLE `ocw_object_replacement_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) NOT NULL,
  `log` longtext collate utf8_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_replacement_questions`
--

DROP TABLE IF EXISTS `ocw_object_replacement_questions`;
CREATE TABLE `ocw_object_replacement_questions` (
  `id` bigint(20) NOT NULL auto_increment,
  `object_id` bigint(20) NOT NULL,
  `question` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `answer` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_object_replacements`
--

DROP TABLE IF EXISTS `ocw_object_replacements`;
CREATE TABLE `ocw_object_replacements` (
  `id` bigint(20) NOT NULL auto_increment,
  `material_id` bigint(20) NOT NULL,
  `object_id` bigint(20) NOT NULL default '0',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `location` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `description` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `author` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `contributor` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `citation` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `tags` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `ask` enum('yes','no') character set utf8 collate utf8_unicode_ci NOT NULL,
  `ask_status` enum('new','in progress','done') character set utf8 collate utf8_unicode_ci NOT NULL default 'new',
  `suitable` enum('yes','no','pending') character set utf8 collate utf8_unicode_ci NOT NULL default 'pending',
  `unsuitable_reason` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_object_subtypes`
--

DROP TABLE IF EXISTS `ocw_object_subtypes`;
CREATE TABLE `ocw_object_subtypes` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `type_id` bigint(20) NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_object_types`
--

DROP TABLE IF EXISTS `ocw_object_types`;
CREATE TABLE `ocw_object_types` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `description` text character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_objects`
--

DROP TABLE IF EXISTS `ocw_objects`;
CREATE TABLE `ocw_objects` (
  `id` bigint(20) NOT NULL auto_increment,
  `material_id` bigint(20) NOT NULL default '0',
  `subtype_id` int(11) NOT NULL,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `location` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `description` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `author` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `contributor` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `instructor_owns` enum('yes','no','pending') character set utf8 collate utf8_unicode_ci NOT NULL default 'pending',
  `other_copyholder` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `is_unique` enum('yes','no','pending') character set utf8 collate utf8_unicode_ci NOT NULL default 'pending',
  `citation` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `tags` longtext character set utf8 collate utf8_unicode_ci NOT NULL,
  `ask` enum('yes','no') character set utf8 collate utf8_unicode_ci NOT NULL,
  `ask_status` enum('new','in progress','done') character set utf8 collate utf8_unicode_ci NOT NULL default 'new',
  `action_type` enum('Fair Use','Search','Commission','Permission','Retain','Remove') character set utf8 collate utf8_unicode_ci NOT NULL,
  `action_taken` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `status` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `done` enum('1','0') character set utf8 collate utf8_unicode_ci NOT NULL default '0',
  `time` bigint(20) NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_on` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=700 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_objects_copyright`
--

DROP TABLE IF EXISTS `ocw_objects_copyright`;
CREATE TABLE `ocw_objects_copyright` (
  `id` bigint(20) NOT NULL,
  `object_id` bigint(20) NOT NULL,
  `copyright_holder_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_schools`
--

DROP TABLE IF EXISTS `ocw_schools`;
CREATE TABLE `ocw_schools` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `description` text character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_subjects`
--

DROP TABLE IF EXISTS `ocw_subjects`;
CREATE TABLE `ocw_subjects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `subj_code` varchar(15) collate utf8_unicode_ci NOT NULL,
  `subj_desc` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ocw_tags`
--

DROP TABLE IF EXISTS `ocw_tags`;
CREATE TABLE `ocw_tags` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `Description` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ocw_users`
--

DROP TABLE IF EXISTS `ocw_users`;
CREATE TABLE `ocw_users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `user_name` varchar(45) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `role` varchar(50) NOT NULL default 'user',
  `banned` tinyint(1) NOT NULL default '0',
  `forgotten_password_code` varchar(50) default NULL,
  `last_visit` datetime default NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-04-24 14:31:19