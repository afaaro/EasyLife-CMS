<?php

class registerController {
	function index() {
		global $db, $User, $config;

		if ($config['user_signup']) {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$displayname = Io::GetVar('POST','displayname');
				$username = Io::GetVar('POST','username');
				$password = Io::GetVar('POST','password');
				$cpassword = Io::GetVar('POST','cpassword');
				$email = Io::GetVar('POST','email');

				if ($config['user_signup_invite']) {
					$invitecode = Io::GetVar('POST','invitecode');
				}
				$errors = array();
				if (empty($username))	$errors[] = "The field username is required";
				if (empty($password))	$errors[] = "The field password is required";
				if (empty($cpassword))	$errors[] = "The field confirm password is required";
				if (empty($email))		$errors[] = "The field email is required";


				if (!sizeof($errors)) {
					if (!preg_match("#^[a-zA-Z0-9]{4,}$#is",$username)) $errors[] = "The username is not valid";
					if (!Utils::ValidEmail($email)) $errors[] = "The email is not valid";
					if ($password!=$cpassword) $errors[] = "Your passwords don\'t match";
					if ($row = $db->query("SELECT id FROM ".PREFIX."user WHERE (username='".$db->escape($username)."' OR email='".$db->escape($email)."')")->row) {
						$errors[] = "The username or the email already exist in our database";
					}
				}

				if (!sizeof($errors)) {
					$status = ($config['user_signup_moderate']) ? "moderate" : "active" ;
					$status = ($config['user_signup_confirm']) ? "waiting" : $status ;
					$code = ($config['user_signup_confirm']) ? random_str(10) : "" ;
					$db->query("INSERT INTO ".PREFIX."user (id,username,real_name,password,email,regdate,lastip,code,status)
								VALUES (null,'".$db->escape($username)."','".$db->escape($displayname)."','".Hash::make($password)."','".$db->escape($email)."',NOW(),'".$db->escape(Utils::Ip2num(Utils::GetIp()))."','".$db->escape($code)."','$status')");

					$uid = $db->getLastId();

					// if ($config['user_signup_confirm']) {
					// 	$actlink = RewriteUrl($config['site_url']._DS."index.php?"._NODE."="._PLUGIN."&op=activate&uid=$uid&code=$code");
					// 	$message = _t("EMAIL_ACTIVATION_TEXT",$displayname,$config['site_name'],$actlink,$config['site_name'],$config['site_name']);

					// 	$Email = new Email();
					// 	$Email->AddEmail($email,$displayname);
					// 	$Email->SetFrom($config['site_email'],$config['site_name']);
					// 	$Email->SetSubject(_t("ACTIVATE_ACCOUNT_AT_X",$config['site_name']));
					// 	$Email->SetContent($message);
					// 	$result = $Email->Send();

					// 	if ($result) {
					// 		Error::Trigger("INFO",_t('YOU_RECEIVE_ACT_LINK_ACCOUNT_EXPIRE_IN_X',48));
					// 	} else {
					// 		Error::StoreLog("error_sys","Message: Activation email not sent<br />User: [$uid] $username ($email)<br />File: ".__FILE__."<br />Line: ".__LINE__."<br />Details: ".implode(",",$Email->GetErrors()));
					// 		Error::Trigger("INFO",_t("REG_SUC_TECH_PROB_CONTACT_ADMIN_ACC_ACTIVATED"),implode("<br />",$Email->GetErrors()));
					// 	}
					// } else if ($config['user_signup_moderate']) {
					// 	Error::Trigger("INFO",_t("ACCOUNT_ACTIVED_SOON_BYADMIN"));
					// } else {
					// 	Error::Trigger("INFO",_t("ACCOUNT_ACTIVED_NOW_UCAN_LOGIN"));
					// }
					
					// if ($config['user_signup_invite']) {
					// 	$db->query("UPDATE #__user_invites SET registrations=registrations-1 WHERE code='".$Db->_e($invitecode)."'");
					// }
					Error::Trigger("INFO", "Account Created");
				} else {
					Error::Trigger("USERERROR",implode("<br />",$errors));
				}
			} else {
			opentable("Register Form");
				echo Form::Open();
					//Username
					echo Form::AddElement(array('element'=>'text','label'=>'Username','name'=>'username'));
					//Password
					echo Form::AddElement(array("element"=>"text","label"=>'Password',"name"=>"password","password"=>true,"info"=>'Letters and Numbers only'));
					//Password confirm
					echo Form::AddElement(array("element"=>"text","label"=>'Confirm Password',"name"=>"cpassword","password"	=>true));
					//Email
					echo Form::AddElement(array("element"=>"text","label"=>'Email Address',"name"=>"email"));
					//Submit
					echo Form::AddElement(array('element'=>'submit', 'name'=>'register', 'value'=>'Register'));
				echo Form::Close();
				closetable();
			}
		}
	}
}