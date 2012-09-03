<?php
/* Function: 删除3个表
 * 1    exam 有奖问答表
 * 2    exam_question 试题表
 * 3	exam_answer 学员答题表
 * Com.:
 * Author: qiaoyz
 * Date: 2012-2-28
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$exam = DB :: table('exam');
$exam_question = DB :: table('exam_question');
$exam_answer = DB :: table('exam_answer');

$sql = <<<EOF

DROP TABLE IF EXISTS $exam;
DROP TABLE IF EXISTS $exam_question;
DROP TABLE IF EXISTS $exam_answer;

EOF;

runquery($sql);
$finish = TRUE;