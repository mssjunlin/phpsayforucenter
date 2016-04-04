<?php

//添加帐号到phpsay
function phpsay_add_user($uc_user)
{
    
}

//同步登录phpsay
function phpsay_synclogin($user)
{
    
}

//同步退出phpsay
function phpsay_synclogout()
{
    
}

function _setcookie($var, $value, $life = 0, $prefix = 1)
{
    global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
    setcookie(($prefix ? $cookiepre : '').$var, $value,
        $life ? $timestamp + $life : 0, $cookiepath,
        $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;

    $key = md5($key ? $key : UC_KEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

function _stripslashes($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = _stripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

class uc_note
{

    public $dbconfig = '';
    public $db = '';
    public $tablepre = '';
    public $appdir = '';

    public function _serialize($arr, $htmlon = 0)
    {
        if (!function_exists('xml_serialize')) {
            include_once DISCUZ_ROOT.'./uc_client/lib/xml.class.php';
        }
        return xml_serialize($arr, $htmlon);
    }

    public function uc_note()
    {
        $this->appdir = substr(dirname(__FILE__), 0, -3);
        $this->dbconfig = $this->appdir.'./uc_client/uc_config.inc.php';
        $this->db = $GLOBALS['db'];
        $this->tablepre = $GLOBALS['tablepre'];
    }

    //UC通讯测试
    public function test($get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    //UC同步更新头像到TS - 尚未同步
    public function face($get)
    {
       
    }

    //同步登录
    public function synlogin($get, $post)
    {
//      if (!API_SYNLOGIN) {
//          return API_RETURN_FORBIDDEN;
//      }
//      header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
//
//      $uc_uid         = $get['uid'];
//      $uc_uname     = uc_auto_charset($get['username']);
//      $uc_password = $get['password'];
//      $uc_user_ref = ts_get_ucenter_user_ref('', $uc_uid);
//      $user = ts_get_user($uc_user_ref['uid']);
//      if ($user) {
//          //检查是否激活，未激活用户不自动登录
//          if ($user['is_active'] == 0) {
//              exit;
//          }
//          if ($uc_uname != $uc_user_ref['uc_username']) {
//              ts_update_ucenter_user_ref($uc_user_ref['uid'], $uc_uid, $uc_uname);
//          }
//          //登录到TS系统
//          $user['login_from_dz'] = true;
//          $result = ts_synclogin($user);
//      }
    }

   
}
