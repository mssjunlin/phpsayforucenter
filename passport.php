<?php
require(dirname(__FILE__)."/global.php");
require(PHPSAY_SITE."/controller/class_Ucenter.php");
if ( isset($_GET['do']) )
{
	if ( $_GET['do'] == "Login" )
	{
		if( isset($_POST['account'],$_POST['password']) )
		{
			if ( $loginInfo['uid'] > 0 )
			{
				die('{"result":"login","message":""}');
			}

			$loginAccount	= strtolower(stripslashes(trim($_POST['account'])));

			$loginPassword	= stripslashes($_POST['password']);
          
			if( strlen($loginAccount) < 2 )
			{
				die('{"result":"error","message":"账号无效","position":1}');
			}
				
			if( strlen($loginPassword) < 6 || strlen($loginPassword) > 26 || substr_count($loginPassword," ") > 0 )
			{
				die('{"result":"error","message":"密码无效","position":2}');
			}

			$loginType = "nickname";
			
			$uctype = 0;
 
			if( emailCheck($loginAccount) )
			{
				$loginType = "email";
				$uctype = 2;
			}
			else
			{
				if( checkNickname($loginAccount) != "" )
				{
					die('{"result":"error","message":"账号不合法","position":1}');
				}
			}
			
		    
		
            //Uc登陆
            $UCuserInfo  = Ucenter::ucLogin($loginAccount,$loginPassword,$uctype);
			

			
			list($uid, $username, $email) = $UCuserInfo;
			
			if($uid>0){
				
				$DB = database();
				
				//查询本地信息
				$userInfo = PHPSay::getMemberInfo($DB,$loginType,$loginAccount);
				
				
				if($userInfo['uid']){
					
				    loginCookie($PHPSayConfig['ppsecure'],$userInfo['uid'],$username,$userInfo['groupid']);	
					
					echo '{"result":"success","message":"登录成功","position":0}';
									
				}else{
					
					//UC存在，本地不存在，那就在本地创建一个
					
					$userID = PHPSay::memberJoin($DB,$username,$email,md5($loginPassword),"");
					
					newAvatar($userID,"");
					
					loginCookie($PHPSayConfig['ppsecure'],$userID,$username,1);
					
					echo '{"result":"success","message":"登录成功","position":0}';
                    
				}			
			}else{
				
				$DB = database();
				
				//uc不存在不代表本地不存在，进行本地搜索，如果本地存在，则在UC上创建一个。
				$userInfo = PHPSay::getMemberInfo($DB,$loginType,$loginAccount);
				if( md5($loginPassword) != $userInfo['password'] ){
					echo '{"result":"error","message":"账号与密码不匹配","position":2}';
					exit();
				}
							
				if($userInfo['uid']){
					
					if(!$userInfo['email']){
						$userInfo['email'] = $loginAccount."@admin.com";
					}
					
					$UCreg  = Ucenter::ucReg($loginAccount,$loginPassword,$userInfo['email']);
					
				    if($UCreg > 0) {
				    	
					    loginCookie($PHPSayConfig['ppsecure'],$userInfo['uid'],$loginAccount,$userInfo['groupid']);
					
					    echo '{"result":"success","message":"登录成功","position":0}';
						
						exit();
				    }
					
				}else{
            	   echo '{"result":"error","message":"账号不存在","position":1}';
				
				   exit();
				
				}
            }
//			print_r($UCuserInfo);exit();
            
//			if( empty($userInfo['uid']) )
//			{
//				echo '{"result":"error","message":"账号不存在","position":1}';
//			}
//			else
//			{
//				if( md5($loginPassword) == $userInfo['password'] )
//				{
//					loginCookie($PHPSayConfig['ppsecure'],$userInfo['uid'],$userInfo['nickname'],$userInfo['groupid']);
//
//					echo '{"result":"success","message":"登录成功","position":0}';
//				}
//				else
//				{
//					if( $userInfo['password'] == "" )
//					{
//						echo '{"result":"error","message":"该账号不支持密码登录","position":2}';
//					}
//					else
//					{
//						echo '{"result":"error","message":"账号与密码不匹配","position":2}';
//					}
//				}
//			}

			$DB->close();
		}
	}
	else if ( $_GET['do'] == "logout" )
	{
		singOut();

		header("location:".$_SERVER['PHP_SELF']);
	}
	else if($_GET['do'] == "loginTime")
	{
		if ( $loginInfo['uid'] > 0 )
		{
			if( isset($_COOKIE['phpsay_logintime']) )
			{
				die('{"result":"success","balance":0}');
			}

			setcookie("phpsay_logintime",time(),mktime(23,59,59,date('n'),date('j'),date('Y'))+1,"/");

			$DB = database();

			$updateRes = PHPSay::updateMemberLogin( $DB, $loginInfo['uid'], $isMobileRequest ? rand(30,60) : rand(10,50) );

			$DB->close();

			loginCookie($PHPSayConfig['ppsecure'],$loginInfo['uid'],$loginInfo['nickname'],$updateRes['group'],time());

			echo '{"result":"success","balance":'.$updateRes['coin'].'}';
		}
	}
	else if ( $_GET['do'] == "SignUp" )
	{
		if( isset($_POST['email'],$_POST['nickname'],$_POST['password']) )
		{
			if ( $loginInfo['uid'] > 0 )
			{
				die('{"result":"login","message":""}');
			}

			if ( !$PHPSayConfig['emailjoin'] )
			{
				die('{"result":"error","message":"您暂时不能使用邮箱注册","position":0}');
			}			

			$email	= strtolower(stripslashes(trim($_POST['email'])));

			$nickname = filterCode($_POST['nickname'],true);

			$password	= stripslashes($_POST['password']);

			if( !emailCheck($email) )
			{
				die('{"result":"error","message":"邮件地址不正确","position":1}');
			}

			$nicknameError = checkNickname($nickname);

			if( $nicknameError != "" )
			{
				die('{"result":"error","message":"'.$nicknameError.'","position":2}');
			}

			if( substr_count($password," ") > 0 )
			{
				die('{"result":"error","message":"密码不能使用空格","position":3}');
			}

			if( strlen($password) < 6 || strlen($password) > 26 )
			{
				die('{"result":"error","message":"密码长度不合法","position":3}');
			}

			$DB = database();

			if( PHPSay::getMemberCount($DB,"email",$email) != 0 )
			{
				echo '{"result":"error","message":"邮件地址已被占用","position":1}';
			}
			else
			{
				if( PHPSay::getMemberCount($DB,"nickname",$nickname) != 0 )
				{
					echo '{"result":"error","message":"昵称已被占用","position":2}';
				}
				else
				{
//					$userID = PHPSay::memberJoin($DB,$nickname,$email,md5($password),"");
//
//					

                    //uc注册
                    $userID  = PHPSay::memberJoin($DB,$nickname,$email,md5($password),""); 
                    
                    if ($userID > 0)
					{
						
						$ucid  = Ucenter::ucReg($nickname,$password,$email);
						
						newAvatar($userID,"");

						loginCookie($PHPSayConfig['ppsecure'],$userID,$nickname,1);

						echo '{"result":"success","message":"注册成功"}';
					}
					else
					{
						echo '{"result":"error","message":"注册失败","position":0}';
					}
   
				}
			}

			$DB->close();
		}
	}
	else if ( $_GET['do'] == "sendPassword" )
	{
		if( isset($_POST['email'],$_POST['verify_code']) )
		{
			session_start();

			$sessionCode = isset($_SESSION['identifying_code']) ? $_SESSION['identifying_code'] : "";

			session_destroy();

			if( $sessionCode == "" || $sessionCode != md5(strtoupper($_POST['verify_code']).$PHPSayConfig['ppsecure']) )
			{
				die('{"result":"error","message":"验证码不正确","position":2}');
			}

			if( !emailCheck($_POST['email']) )
			{
				die('{"result":"error","message":"邮件地址不合法","position":1}');
			}

			$DB = database();

			$userInfo = PHPSay::getMemberInfo($DB,"email",$_POST['email']);

			if( $userInfo['email'] == "" )
			{
				echo '{"result":"error","message":"该邮件地址尚未登记","position":1}';
			}
			else
			{
				$codeArr = PHPSay::getResetPasswordCode($DB,"uid",$userInfo['uid']);

				$resetCode = $codeArr['code'];

				if( time() - $codeArr['dateline'] >= 1800 )
				{
					$resetCode = $userInfo['uid'].createSecureKey(1,false).createSecureKey(9);

					$DB->query("REPLACE INTO `phpsay_resetpassword` VALUE(".$userInfo['uid'].",'".$resetCode."',".time().")");
				}

				if( time() - $codeArr['dateline'] > 120 )
				{
					if( $codeArr['dateline'] > 0 )
					{
						$DB->query("UPDATE `phpsay_resetpassword` SET `dateline`=".time()." WHERE `uid`=".$userInfo['uid']);
					}

					sendPasswordEmail($PHPSayConfig['sitename'],$PHPSayConfig['sitemail'],$userInfo['email'],$userInfo['nickname'],$resetCode);
				}

				echo '{"result":"success","message":""}';
			}

			$DB->close();
		}
	}
	else if ( $_GET['do'] == "resetPassword" )
	{
		if( isset($_GET['code'],$_POST['password']) )
		{
			$newPassword = stripslashes($_POST['password']);

			if( substr_count($newPassword," ") > 0 )
			{
				die('{"result":"error","message":"密码不能使用空格"}');
			}

			if( strlen($newPassword) < 6 || strlen($newPassword) > 26 )
			{
				die('{"result":"error","message":"密码长度不合法"}');
			}

			$DB = database();

			$codeArr = PHPSay::getResetPasswordCode($DB,"code",strAddslashes($_GET['code']));

			if( empty($codeArr['uid']) || time() - $codeArr['dateline'] > 2000 )
			{
				echo '{"result":"error","message":"链接已失效，请刷新页面"}';
			}
			else
			{
				$DB->query("UPDATE `phpsay_member` SET password='".md5($newPassword)."' WHERE `uid`=".$codeArr['uid']);

				$DB->query("DELETE FROM `phpsay_resetpassword` WHERE `uid`=".$codeArr['uid']);

				$userInfo = PHPSay::getMemberInfo($DB,"uid",$codeArr['uid']);

				loginCookie($PHPSayConfig['ppsecure'],$userInfo['uid'],$userInfo['nickname'],$userInfo['groupid']);

				echo '{"result":"success","message":""}';
			}

			$DB->close();
		}
	}
	else if ( $_GET['do'] == "password" )
	{
		if( $loginInfo['uid'] > 0 )
		{
			header("location:./");

			exit;
		}

		$resetCode = "";

		if( isset($_GET['code']) )
		{
			if( $_GET['code'] != "" )
			{
				$DB = database();

				$codeArray = PHPSay::getResetPasswordCode($DB,"code",strAddslashes($_GET['code']));

				$DB->close();

				if( time() - $codeArray['dateline'] < 1800 )
				{
					$resetCode = $codeArray['code'];
				}
			}

			if( $resetCode == "" )
			{
				header("location:./passport.php?do=password");

				exit;
			}
		}

		$template = template( $isMobileRequest ? "mobile_reset_password.html" : "reset_password.html" );

		$template->assign( 'PHPSayConfig', $PHPSayConfig );

		$template->assign( 'resetCode', $resetCode );

		$template->output();
	}
	else
	{
		header("location:".$_SERVER['PHP_SELF']);
	}
}
else
{
	if ( $loginInfo['uid'] > 0 )
	{
		$locationURL = "./";
		
		if( isset($_COOKIE['returnURL']) )
		{
			if( substr($_COOKIE['returnURL'], 0 ,1) == "/" )
			{
				$locationURL = $_COOKIE['returnURL'];
			}

			setcookie( 'returnURL', '', 0, "/" );
		}
		
		header("location:".$locationURL);
	}
	else
	{
		if( isset($_GET['return']) )
		{
			if( substr($_GET['return'], 0 ,1) == "/" )
			{
				setcookie( 'returnURL', $_GET['return'], time()+3600, "/" );
			}
		}

		$template = template( $isMobileRequest ? "mobile_login.html" : "login.html" );

		$template->assign( 'PHPSayConfig', $PHPSayConfig );

		$template->assign( 'connectArray', isQQConnect($PHPSayConfig['ppsecure']) );

		$template->output();
	}
}
?>