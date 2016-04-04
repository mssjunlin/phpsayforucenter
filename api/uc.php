<?php
// 
//  uc.php
//  <api for phpsay>
//  
//  Created by Ms. on 2016-04-04.
//  Copyright 2016 Ms. All rights reserved.
// 

error_reporting(0);

// phpsay系统根目录路径
define('SITE_PATH', dirname(dirname(__FILE__)));



define('UC_CLIENT_VERSION', '1.6.0');
define('UC_CLIENT_RELEASE', '20110501');

define('API_DELETEUSER', 1);
define('API_RENAMEUSER', 1);
define('API_GETTAG', 1);
define('API_SYNLOGIN', 1);
define('API_SYNLOGOUT', 1);
define('API_UPDATEPW', 1);
define('API_UPDATEBADWORDS', 1);
define('API_UPDATEHOSTS', 1);
define('API_UPDATEAPPS', 1);
define('API_UPDATECLIENT', 1);
define('API_UPDATECREDIT', 1);
define('API_GETCREDIT', 1);
define('API_GETCREDITSETTINGS', 1);
define('API_UPDATECREDITSETTINGS', 1);
define('API_ADDFEED', 1);
define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '1');
define('DISCUZ_ROOT', SITE_PATH.'/api/');
define('IN_API', true);
define('CURSCRIPT', 'api');


require_once SITE_PATH.'/api/uc_client/uc_config.inc.php';
require_once SITE_PATH.'/api/uc_client/common.php';

$get = $post = array();

$code = @$_GET['code'];

parse_str(_authcode($code, 'DECODE', UC_KEY), $get);

//时间戳验证
$timestamp = time();

if (empty($get)) {
    exit('Invalid Request');
}

$action = $get['action'];
require_once DISCUZ_ROOT.'./uc_client/lib/xml.class.php';
$post = xml_unserialize(file_get_contents('php://input'));


if (in_array($get['action'], array('test', 'face', 'deleteuser', 'renameuser', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
    require_once DISCUZ_ROOT.'./uc_client/lib/db.class.php';

    //UC的数据库连接
    $GLOBALS['db'] = new ucclient_db;
    $GLOBALS['db']->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCONNECT, true, UC_DBCHARSET);
    $GLOBALS['tablepre'] = UC_DBTABLEPRE;
    //TS的数据库连接
    $GLOBALS['tsdb'] = new ucclient_db;
    $GLOBALS['tsdb']->connect($tsconfig['DB_HOST'], $tsconfig['DB_USER'], $tsconfig['DB_PWD'], $tsconfig['DB_NAME'], UC_DBCONNECT, true, $tsconfig['DB_CHARSET']);
    define('TS_DBTABLEPRE', $tsconfig['DB_PREFIX']);
    $GLOBALS['tstablepre'] = TS_DBTABLEPRE;
    //执行UC动作
    $uc_note = new uc_note();
    exit($uc_note->$get['action']($get, $post));
} else {
    exit(API_RETURN_FAILED);
}


	