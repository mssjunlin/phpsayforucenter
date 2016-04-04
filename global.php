<?php
error_reporting(E_ALL);

define('IN_API', true);
define('UC_CONNECT', 'mysql');
define('UC_DBHOST', 'localhost');
define('UC_DBUSER', 'root');
define('UC_DBPW', '');
define('UC_DBNAME', 'ultrax');
define('UC_DBCHARSET', 'utf8');
define('UC_DBTABLEPRE', '`ultrax`.pre_ucenter_');
define('UC_DBCONNECT', '0');
define('UC_KEY', '123123');
define('UC_API', 'http://127.0.0.1/dz/uc_server');
define('UC_CHARSET', 'utf-8');
define('UC_IP', '');
define('UC_APPID', '2');
define('UC_PPP', '20');


define('PHPSAY_SITE', dirname(__FILE__));

date_default_timezone_set('PRC');

require(dirname(__FILE__)."/config/config_PHPSay.php");

require(dirname(__FILE__)."/controller/class_Xxtea.php");

require(dirname(__FILE__)."/controller/class_PHPSay.php");

require(dirname(__FILE__)."/controller/function.php");

$loginInfo = isLogin($PHPSayConfig['ppsecure'],$_COOKIE);

$isMobileRequest = isMobileRequest();

$currentPage = ( isset($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
?>