<?php
/* Function: 删除3个表
 * 1    station 岗位表
 * 2    station_course 岗位课程表
 * 3	user_courses 用户课程表。
 * Com.:
 * Author: qiaoyz
 * Date: 2011-4-8
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$station = DB :: table('station');
$courses = DB :: table('courses');
$station_course = DB :: table('station_course');
$user_station =DB :: table('user_station');

$sql = <<<EOF

DROP TABLE IF EXISTS $station;
DROP TABLE IF EXISTS $courses;
DROP TABLE IF EXISTS $station_course;
DROP TABLE IF EXISTS $user_station;

EOF;

runquery($sql);

$finish = TRUE;
?>
