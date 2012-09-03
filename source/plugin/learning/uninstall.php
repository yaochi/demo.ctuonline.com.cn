<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$learning_excitation = DB :: table('learning_excitation');

$sql1 = <<<EOF

DROP TABLE IF EXISTS $learning_excitation;

EOF;

runquery($sql1);


$integral =DB :: table('learn_credit');
$sql2 = <<<EOF
DROP TABLE IF EXISTS $integral;
EOF;
runquery($sql2);

$opinion_reply =DB :: table('opinion_reply');
$sql3 = <<<EOF
DROP TABLE IF EXISTS $opinion_reply;
EOF;

runquery($sql3);

$record =DB :: table('learncredit_record');
$sql4 = <<<EOF
DROP TABLE IF EXISTS $record;
EOF;
runquery($sql4);


$harvestoption =DB :: table('larnsouce_harvestoption');
$sql5 = <<<EOF
DROP TABLE IF EXISTS $harvestoption;
EOF;

$learn_attachment =DB :: table('learn_attachment');
$sql6 = <<<EOF
DROP TABLE IF EXISTS $learn_attachment;
EOF;
runquery($sql6);

$finish = TRUE;