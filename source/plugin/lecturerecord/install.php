<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_lecture_record;
CREATE TABLE `pre_lecture_record` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `class_name` VARCHAR(45) NOT NULL,
  `class_level` INTEGER UNSIGNED NOT NULL,
  `org_id` INTEGER UNSIGNED NOT NULL,
  `org_name` VARCHAR(500) NOT NULL,
  `org_person` VARCHAR(500) NOT NULL,
  `par_level` INTEGER UNSIGNED NOT NULL,
  `class_outcome` TEXT NOT NULL,
  `attachment` VARCHAR(500) NOT NULL,
  `join_num` INTEGER UNSIGNED NOT NULL,
  `tu_num` INTEGER UNSIGNED NOT NULL,
  `starttime` LONG NOT NULL,
  `endtime` LONG NOT NULL,
  `address_id` INTEGER UNSIGNED NOT NULL,
  `address` VARCHAR(500) NOT NULL,
  `class_stud_num` INTEGER UNSIGNED NOT NULL,
  `class_time` INTEGER UNSIGNED NOT NULL,
  `class_result` VARCHAR(500) NOT NULL,
  `type` INTEGER,
  `teacher_id` INTEGER,
  `teacher_name` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>