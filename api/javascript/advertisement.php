<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: advertisement.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60).' GMT');

if(!defined('IN_API')) {
	exit('document.write(\'Access Denied\')');
}

loadcore();

$adtitle = $_G['gp_adtitle'];
$adtitles = array(
    "unionad"=>"NP3",
    "partymember"=>"NP4",
    "wirelessad"=>"NP5",
    "challengead"=>"NP6",
    "cdmaad"=>"NP7",
    "helpad"=>"NP1",
    "oldversion"=>"NP2",
    "livead1"=>"NL1",
    "livead2"=>"NL2",
    "livead3"=>"NL3",
    "livead4"=>"NL4",
    "indexleft1"=>"NB1",
    "indexleft2"=>"NB2",
    "indexleft3"=>"NB3",
    "indexleft4"=>"NB4",
    "indexleft5"=>"NB5",
    "indexleft6"=>"NB6",
);
$adtitle = $adtitles[$adtitle]?$adtitles[$adtitle]:$adtitle;

$data = adshowbytitle($adtitle);
echo 'document.write(\''.preg_replace("/\r\n|\n|\r/", '\n', addcslashes($data, "'\\")).'\');';
?>