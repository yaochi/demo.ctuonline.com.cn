<?php


$_config = array();

// ------------------  CONFIG DB  ------------------- //
$_config['db']['1']['dbhost'] = '10.127.1.36';
$_config['db']['1']['dbuser'] = 'esntest';
$_config['db']['1']['dbpw'] = '!@#$%^2010';
$_config['db']['1']['dbcharset'] = 'utf8';
$_config['db']['1']['pconnect'] = '0';
$_config['db']['1']['dbname'] = 'forum1114';
$_config['db']['1']['tablepre'] = 'pre_';

//$_config['db2']['1']['dbhost'] = '10.127.1.13';
//$_config['db2']['1']['dbuser'] = 'forum';
//$_config['db2']['1']['dbpw'] = 'forum';
//$_config['db2']['1']['dbcharset'] = 'utf8';
//$_config['db2']['1']['pconnect'] = '0';
//$_config['db2']['1']['dbname'] = 'forum';
//$_config['db2']['1']['tablepre'] = 'pre_';

$_config['db2']['1']['dbhost'] = '10.127.1.36';
$_config['db2']['1']['dbuser'] = 'esntest';
$_config['db2']['1']['dbpw'] = '!@#$%^2010';
$_config['db2']['1']['dbcharset'] = 'utf8';
$_config['db2']['1']['pconnect'] = '0';
$_config['db2']['1']['dbname'] = 'forum1114';
$_config['db2']['1']['tablepre'] = 'pre_';
// ----------------  CONFIG MEMORY  ----------------- //
$_config['memory']['prefix'] = 'UezBhH_';
$_config['memory']['eaccelerator'] = 0;
$_config['memory']['xcache'] = 0;
//$_config['memory']['memcache']['server'] = '10.127.1.34';
//$_config['memory']['memcache']['port'] = 6379;
$_config['memory']['memcache']['pconnect'] = 1;
$_config['memory']['memcache']['timeout'] = 1;

$_config['memory']['redis']['on'] =true;
$_config['memory']['redis']['server'] = '10.127.1.34';
$_config['memory']['redis']['port'] = 6379;
$_config['memory']['redis']['pconnect'] = 1;

$_config['memory']['redis']['group'] =true;
// -----------------  CONFIG CACHE  ----------------- //
$_config['cache']['main']['type'] = '';
$_config['cache']['main']['file']['path'] = 'data/ultraxcache';
$_config['cache']['type'] = 'sql';

// ----------------  CONFIG OUTPUT  ----------------- //
$_config['output']['charset'] = 'utf-8';
$_config['output']['forceheader'] = 1;
$_config['output']['gzip'] = '0';
$_config['output']['tplrefresh'] = 1;
$_config['output']['language'] = 'zh_cn';
$_config['output']['staticurl'] = 'static/';

// ----------------  CONFIG COOKIE  ----------------- //
$_config['cookie']['cookiepre'] = 'HOCa_';
$_config['cookie']['cookiedomain'] = '';
$_config['cookie']['cookiepath'] = '/';

// ------------------  CONFIG APP  ------------------ //
// Ĭ�ϵ�¼��ַ
$_config['app']['default'] = 'portal';
// ר��������
$_config['app']['domain']['forum'] = '';
// ר�����ҳ������
$_config['app']['domain']['group'] = '';
// �û����İ�����
$_config['app']['domain']['home'] = '';
// �Ż�������
$_config['app']['domain']['portal'] = '';
// ֱ�����İ�����
$_config['app']['domain']['live'] = '';
// ���ݹ��������
$_config['app']['domain']['manage'] = '';
$_config['app']['domain']['admin'] = '';
$_config['app']['domain']['sh'] = 'forum.php?mod=group&fid=536';
// ��ȥ���������֮��ĵ�ַ�󶨵�����
$_config['app']['domain']['default'] = '';
// -----------------  CONFIG FORWARD URL  ------------------ //
$_config['forward']['home'] = 'home.myctu.cn';
$_config['forward']['www'] ='www.myctu.cn';
$_config['forward']['demo'] ='demo.ctuonline.com.cn';
// -----------------  CONFIG SSO  ------------------ //
// SSO����Э��
$_config['sso']['server_version'] = '2.0'; 
// �Ƿ����ô���ģʽ��Ŀǰ�汾��֧�֣��ò�����Ч
$_config['sso']['proxy'] = false;
// SSO��������ַ
$_config['sso']['hostname'] = 'demosso.ctuonline.com.cn';
// SSO����˿�
$_config['sso']['server_port'] = 8443;
// SSO Server��Ŀ¼��
$_config['sso']['server_uri'] = 'cas';


// ---------------  CONFIG SECURITY  ---------------- //
$_config['security']['authkey'] = '342db0hxTHYZw59b';
$_config['security']['urlxssdefend'] = 1;
$_config['security']['attackevasive'] = '0';

// ----------------  CONFIG ADMINCP  ---------------- //
$_config['admincp']['founder'] = 1;
$_config['admincp']['forcesecques'] = '0';
$_config['admincp']['checkip'] = 1;
$_config['admincp']['runquery'] = 1;
$_config['admincp']['dbimport'] = 1;

// -----------------  CONFIG HOME  ------------------ //
$_config['home']['allowdomain'] = '0';
$_config['home']['domainroot'] = '';
$_config['home']['holddomain'] = 'www,space,home,forum,portal';

// -----------------  CONFIG RESOURCE URL  ------------------ //
$_config['misc']['resourcehost'] = 'know.myctu.cn';
$_config['misc']['liveurlhost'] = 'live.myctu.cn';
$_config['misc']['liveclienthost'] = '116.236.175.196';
// -------------------  THE END  -------------------- //
$_config[misc][taskhost] = "http://10.127.1.34:3000";
$_config[misc][convercode] = false;
$_config["debug"] = 1;
$_config[sql_debug]  = false;
// �ⲿר������
//$_config[expert][activeurl] = 'demo.ctuonline.com.cn:8080';
$_config[expert][activeurl] = 'admin.myctu.cn';
// ���ֿ���
$_config[credit][switcher] = flase;
$_config[expert][liveurl]="http://10.127.1.108:8080"; //��ֱ����ַ
$_config['ctuonline']['url'] = "http://www.ctuonline.com.cn";


//��Ƶ��ַ
$_config['media']['url']='http://video.ctuonline.com.cn:8011';

//ͼƬ��ַ
$_config['image']['url']='http://demo.ctuonline.com.cn:8011';


$_config['videouploadurl']="http://demo.ctuonline.com.cn:8011/upload";

// -----------------  CONFIG SPHINX  ------------------ //
$_config['sphinx']['used'] = true;
$_config['sphinx']['hostname'] = '10.127.1.36';
$_config['sphinx']['server_port'] = 3312;
//---------------- THE END      --------------//
$_config['esn']['ip']="10.127.1.5";
$_config['esn']['dbuser']="sa";
$_config['esn']['dbpasswd']="sa";

$_config['suggest']['number'] = "50";

//appurl
$_config['app']['url']='http://demo.ctuonline.com.cn:8011';
//apiurl
$_config['api']['url']='http://demo.ctuonline.com.cn:8011';
?>
