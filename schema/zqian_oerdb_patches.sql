ALTER TABLE `ocw_objects` CHANGE `action_type` `action_type` ENUM( 'Permission','Search','Fair Use', 'Re-Create','Retain: Instructor Created', 'Retain: Public Domain', 'Retain: No Copyright', 'Commission', 'Remove & Annotate' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Search'; 


ALTER TABLE `ocw_object_replacement_questions`

ALTER TABLE `ocw_object_replacement_questions`