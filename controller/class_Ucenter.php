<?php

class Ucenter
{
	public static function ucLogin($loginname,$password,$type)
	{
		include_once PHPSAY_SITE.'/api/uc_client/client.php';
		$uc_user = uc_user_login($loginname, $password, $type);
		return $uc_user;
	}	
	
	public static function ucReg($loginname,$password,$email)
	{
		include_once PHPSAY_SITE.'/api/uc_client/client.php';
		$uid = uc_user_register(addslashes($loginname), $password, $email);
		return $uid;
	}	
	
	
}
?>