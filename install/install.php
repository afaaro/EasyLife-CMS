<?php
session_start();

@error_reporting ( E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );

define("_LOAD", TRUE);
define('BASEDIR', dirname(dirname (__FILE__)).'/');
$basedir = dirname(dirname (__FILE__)).'/';

function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
	// error was suppressed with the @-operator
	if (0 === error_reporting()) {
		return false;
	}
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('handleError');


$skin_header = <<<HTML
	<!doctype html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>EasyLife Installer</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
		<style type="text/css">
			body {
			    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI","Roboto","Oxygen","Ubuntu","Cantarell","Fira Sans","Droid Sans","Helvetica Neue",sans-serif;
			    font-size: 13px;
			    line-height: 1.4285715;
			    color: #000000;
			    background-color: #ededed;
			    position: relative;
			    min-height: 100%;
			}
			.navbar {
			    border-radius: 0;
			}
			.bg-primary-700 {
			    background-color: #1976d2;
			    border-color: #1976d2;
			    color: #fff;
			}
			@media (min-width: 769px) {
				.page-container {
				    width: 100%;
				    display: table;
				    table-layout: fixed;
				}
			}
			.panel-footer {
			    padding: 8px 20px;
			    background-color: #f5f5f5;
			    border-top: 1px solid #ddd;
			    border-bottom-right-radius: 2px;
			    border-bottom-left-radius: 2px;
			}
			.bg-teal {
			    background-color: #009688;
			    border-color: #009688;
			    color: #fff;
			}
		</style>
	</head>
	<body class="no-theme">
	<div class="navbar navbar-inverse bg-primary-700">
		<div class="navbar-header">
			<a class="navbar-brand" href="#">EasyLife Engine Installation Wizard</a>
		</div>
	</div>
	<div class="page-container"><div class="page-content"><div class="col-md-8 col-md-offset-2 mt-20">
HTML;

$skin_footer = <<<HTML
    </div></div></div>
	</body></html>
HTML;

echo $skin_header;
include BASEDIR."/includes/hash.php";
include BASEDIR."/includes/mb.php";
include BASEDIR."/includes/io.php";

$action = Io::GetVar("REQUEST", "action");

if ($action == "function_check") {
	echo "<form method='get' action=''>";
	echo "<input type=hidden name='action' value='config'>";
		opentable("Check of the installed PHP components");
			echo "<div class='table-responsive'>";
				echo "<table class='table table-striped table-xs'>";
					echo "<thead>";
						echo "<tr>";
							echo "<th width='250'>Minimal script requirements</th>";
							echo "<th colspan='2'>Current Value</th>";
						echo "</tr>";
					echo "</thead>";

					echo "<tbody>";

						echo "<tr>";
							if (version_compare(phpversion(), '5.4', '<')) {
								$status = '<span class="text-danger"><b>No</b></span>';
							} else {
								$status = '<span class="text-success"><b>Yes</b></span>';
						    }
						    echo "<td>PHP ver. 5.4 or higher</td>";
						    echo "<td colspan='2'>$status</td>";
						echo "</tr>";

						echo "<tr>";
							$status = function_exists('mysqli_connect') ? '<span class="text-success"><b>Yes</b></span>' : '<span class="text-danger"><b>No</b></span>';
						    echo "<td>MySQLi support</td>";
						    echo "<td colspan='2'>$status</td>";
						echo "</tr>";

						echo "<tr>";
							$status = extension_loaded('zlib') ? '<span class="text-success"><b>Yes</b></span>' : '<span class="text-danger"><b>No</b></span>';
						    echo "<td>ZLib compression support</td>";
						    echo "<td colspan='2'>$status</td>";
						echo "</tr>";

						echo "<tr>";
							$status = extension_loaded('xml') ? '<span class="text-success"><b>Yes</b></span>' : '<span class="text-danger"><b>No</b></span>';
						    echo "<td>XML support</td>";
						    echo "<td colspan='2'>$status</td>";
						echo "</tr>";

						echo "<tr>";
							$status = function_exists('mb_convert_encoding') ? '<span class="text-success"><b>Yes</b></span>' : '<span class="text-danger"><b>No</b></span>';
						    echo "<td>Multibyte strings support</td>";
						    echo "<td colspan='2'>$status</td>";
						echo "</tr>";
					echo "</tbody>";

				echo "</table>";
			echo "</div>";
		closetable(true);
	echo "</form>";
} elseif($action == "config") {
	$url = explode( "install/install.php", strtolower ( $_SERVER['PHP_SELF'] ) );
	$url = reset($url);

	if( isSSL() ) $url  = "https://".$_SERVER['HTTP_HOST'].$url;
	else $url  = "http://".$_SERVER['HTTP_HOST'].$url;

	echo "<form method='POST' action=''>";
		echo "<input type='hidden' name='action' value='doinstall'>";
		opentable("System configuration settings");
			echo "<table width='100%' class='table table-striped table-striped-xs'>";

				echo "<tr>";
					echo "<td>Website URL:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='url' value='$url'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td colspan='3'>&nbsp;<b>Data for access to MySQL server</b></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>MySQL Server Host:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='dbhost' value='localhost'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>Database Name:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='dbname'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>MySQL User Name:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='dbuser'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>MySQL Password:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='dbpasswd'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>Prefix:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='dbprefix' value='fusion_'>";
					echo "<br /> <span class='text-size-small text-muted'>Do not change the parameter unless you know what it is for</span>";
					echo "</td>";
				echo "</tr>";


				echo "<tr>";
					echo "<td colspan='3'>&nbsp;<b>Data for the access to the control panel</b></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>Login for Administrator:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='reg_username'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>Password:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='reg_password1'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>Confirm Your Password:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='reg_password2'></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>E-mail:<td style='padding: 5px;'><input type='text' class='form-control' style='width:220px;' name='reg_email'></td>";
				echo "</tr>";


				echo "<tr>";
					echo "<td colspan='3'>&nbsp;<b>Extra Settings</b></td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td>Enable User-Friendly URL Support:";
					echo "<td style='padding: 5px;'>";
					echo "<select class='uniform' name='seo_url'>";
						echo "<option value='1'>Yes</option>";
						echo "<option value='0'>No</option>";
					echo "</select>";
					echo "<br /><span class='text-size-small text-muted'>If you disable User-Friendly URL support, be sure to remove .htaccess file from the root folder</span>";
				echo "</tr>";

			echo "</table>";

		closetable(true);
	echo "</form>";
} elseif($action == "doinstall") {
	$errors = array();

	if(empty($_POST['dbhost'])) {
		$errors[] = "Database Host field is empty";
	}

	if(empty($_POST['dbname'])) {
		$errors[] = "Database Name field is empty";
	}

	if(empty($_POST['dbuser'])) {
		$errors[] = "Database User field is empty";
	}

	if(empty($_POST['reg_username'])) {
		$errors[] = "Username field is empty";
	}

	if(empty($_POST['reg_password1'])) {
		$errors[] = "Password field is empty";
	}

	if(empty($_POST['reg_password2'])) {
		$errors[] = "Confirm password field is empty";
	}

	if($_POST['reg_password1'] != $_POST['reg_password2']) {
		$errors[] = "Password do not match";
	}

	if(empty($_POST['reg_email'])) {
		$errors[] = "E-mail field is empty";
	}

	if(sizeof($errors)) {
		echo implode("<br />", $errors);
	} else {
		$dbhost 	= Io::GetVar("POST", "dbhost");
		$dbuser 	= Io::GetVar("POST", "dbuser");
		$dbpasswd 	= Io::GetVar("POST", "dbpasswd");
		$dbname 	= Io::GetVar("POST", "dbname");
		$dbprefix 	= Io::GetVar("POST", "dbprefix");
		define("PREFIX", $dbprefix);

		include BASEDIR."/includes/database.php";
		include BASEDIR."/includes/braces.php";
		include BASEDIR."/includes/installer.php";
		$continue = false;
		if ($db = new Database($dbhost, $dbuser, $dbpasswd, $dbname)) {
			$success = true;

			//Installer::schema($db, $dbprefix);
			$file = BASEDIR . 'includes/storage/database.distro';
			if (!file_exists($file)) {
				exit('Could not load sql file: ' . $file);
			}

			$lines = file($file);
			if ($lines) {
				$sql = '';

				foreach ($lines as $line) {
					if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
						$sql .= $line;

						if (preg_match('/;\s*$/', $line)) {
							$sql = str_replace("DROP TABLE IF EXISTS `#__", "DROP TABLE IF EXISTS `" . $dbprefix, $sql);
							$sql = str_replace("CREATE TABLE `#__", "CREATE TABLE `" . $dbprefix, $sql);
							$sql = str_replace("INSERT INTO `#__", "INSERT INTO `" . $dbprefix, $sql);

							$db->query($sql);

							$sql = '';
						}
					}					
				}
				$db->query("SET CHARACTER SET utf8");

				//$db->query("SET @@session.sql_mode = 'MYSQL40'");

			}
			if ($success) {
				writeConfigFile($dbhost, $dbuser, $dbpasswd, $dbname, $dbprefix);

				$url = explode( "install/install.php", strtolower ( $_SERVER['PHP_SELF'] ) );
				$url = reset($url);

				if( isSSL() ) $url  = "https://".$_SERVER['HTTP_HOST'].$url;
				else $url  = "http://".$_SERVER['HTTP_HOST'].$url;

				//HTACCESS i
				$dir = pathinfo($_SERVER['SCRIPT_NAME']); 
				$dir = $dir['dirname'];
				$dir = str_replace("install/install.php", "", $dir);
				if ($dir=="/") { $dir = ""; }
				
				Installer::rewrite($dir);
				//HTACCESS e	

				$reg_username 	= Io::GetVar("POST", "reg_username");
				$reg_password 	= Io::GetVar("POST", "reg_password1");
				$reg_email 		= Io::GetVar("POST", "reg_email");

				if (!sizeof($errors)) {
					if (!preg_match("#^[a-zA-Z0-9]{4,}$#is",$reg_username)) $errors[] = "The username is not valid";
					if (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) $errors[] = "The email is not valid";
				}

				$continue = true;

				if($continue) {
					// Insert Super Administrator
					$db->query("INSERT INTO ".$dbprefix."user SET real_name='Administrator', username='".$db->escape($reg_username)."', password='".Hash::make($reg_password)."', email='".$db->escape($reg_email)."', status='active', regdate=NOW(), user_level='103'");

					echo "<form method='get' action=''>";
					echo "<input type='hidden' name='action' value='complete'>";
						opentable("Installation is complete.");
							echo "Installation is complete, Now Click on Next";
						closetable(true);
					echo "</form>";
				}
			}
		}
	}
} elseif($action == "complete") {
	$url = explode( "install/install.php", strtolower ( $_SERVER['PHP_SELF'] ) );
	$url = reset($url);

	if( isSSL() ) $url  = "https://".$_SERVER['HTTP_HOST'].$url;
	else $url  = "http://".$_SERVER['HTTP_HOST'].$url;

	opentable("Install complete.");
		echo "SUCCESS! CMS successfully installed on your server<br><br>";
		echo "Public link: " . $url . "<br>";
		echo "Admin link: " . $url . "admin/<br><br>";
	closetable();
} else {
	if (@file_exists(BASEDIR.'system/storage/config/dbconfig.php')) {
		echo "<p>Installed copy of the script is detected on the server. If you want to re-install the script, you must manually delete <b>/storage/config/config.php</b> using FTP protocol.</p>";
	}
	echo "<form method='get' action=''>";
		echo "<input type='hidden' name='action' value='function_check'>";
		opentable("Installation Wizard");
			echo "Welcome to the EasyLife Engine installation wizard. This wizard will help you install the script in just a few minutes. However, we strongly recommend that you to read the documentation on the work of the script, and documentation on its installation, which comes with a script.<br><br>
			Before you begin the installation, please, make sure that all the distribution files was uploaded to the server. Don't forget to set the necessary permissions for folders and files.<br><br>
			Please note that EasyLife Engine supports User-Friendly URL, and it requires <b>mod_rewrite</b> module to be installed and allowed for use. If you want to disable this feature, then delete <b>.htaccess</b> from the root directory and disable support for this feature during the script installation process.<br><br>
			<span class='text-danger'>Attention: when you install the engine, the database structure and administrator's account are created, and basic system settings are performed, so you need to delete <b>install.php</b> after the successful installation in order to avoid re-installation of the engine!</span><br><br>
			Enjoy your work with the engine,<br><br>
			Afaaro Media Group";
		closetable(true);
	echo "</form>";
	
}
	
// function usage() {
// 	echo "Usage:\n";
// 	echo "======\n";
// 	echo "\n";
// 	$options = implode(" ", array(
// 		'--db_hostname', 'localhost',
// 		'--db_username', 'root',
// 		'--db_password', 'pass',
// 		'--db_database', 'nuke',
// 		'--db_port', '3306',
// 		'--username', 'admin',
// 		'--password', 'admin',
// 		'--email', 'youremail@example.com',
// 		'--http_server', 'http://localhost/nuke/'
// 	));
// 	echo 'php install.php install ' . $options . "\n\n";
// }

// function get_options($argv) {
// 	$defaults = array(
// 		'db_hostname' => 'localhost',
// 		'db_database' => 'nuke',
// 		'db_prefix' => 'easylife_',
// 		'db_port' => '3306',
// 		'username' => 'admin',
// 	);

// 	$options = array();
// 	$total = count($argv);
// 	for ($i=0; $i < $total; $i=$i+2) {
// 		$is_flag = preg_match('/^--(.*)$/', $argv[$i], $match);
// 		if (!$is_flag) {
// 			throw new Exception($argv[$i] . ' found in command line args instead of a valid option name starting with \'--\'');
// 		}
// 		$options[$match[1]] = $argv[$i+1];
// 	}
// 	return array_merge($defaults, $options);
// }

// function valid($options) {
// 	$required = array(
// 		'db_hostname',
// 		'db_username',
// 		'db_password',
// 		'db_database',
// 		'db_prefix',
// 		'db_port',
// 		'username',
// 		'password',
// 		'email',
// 	);
// 	$missing = array();
// 	foreach ($required as $r) {
// 		if (!array_key_exists($r, $options)) {
// 			$missing[] = $r;
// 		}
// 	}

// 	$valid = count($missing) === 0;
// 	return array($valid, $missing);
// }

// function check_requirements() {
// 	$error = null;
// 	if (phpversion() < '5.4') {
// 		$error = 'Warning: You need to use PHP5.4+ or above for OpenCart to work!';
// 	}

// 	if (!ini_get('file_uploads')) {
// 		$error = 'Warning: file_uploads needs to be enabled!';
// 	}

// 	if (ini_get('session.auto_start')) {
// 		$error = 'Warning: OpenCart will not work with session.auto_start enabled!';
// 	}

// 	if (!extension_loaded('mysqli')) {
// 		$error = 'Warning: MySQLi extension needs to be loaded for OpenCart to work!';
// 	}

// 	if (!extension_loaded('gd')) {
// 		$error = 'Warning: GD extension needs to be loaded for OpenCart to work!';
// 	}

// 	if (!extension_loaded('curl')) {
// 		$error = 'Warning: CURL extension needs to be loaded for OpenCart to work!';
// 	}

// 	if (!function_exists('openssl_encrypt')) {
// 		$error = 'Warning: OpenSSL extension needs to be loaded for OpenCart to work!';
// 	}

// 	if (!extension_loaded('zlib')) {
// 		$error = 'Warning: ZLIB extension needs to be loaded for OpenCart to work!';
// 	}

// 	return array($error === null, $error);
// }

// function setup_db($data) {}

// function write_config_files($options) {}

// function install($options) {
// 	$check = check_requirements();
// 	if ($check[0]) {
// 		setup_db($options);
// 		write_config_files($options);
// 		dir_permissions();
// 	} else {
// 		echo 'FAILED! Pre-installation check failed: ' . $check[1] . "\n\n";
// 		exit(1);
// 	}
// }

// if (php_sapi_name() == 'cli') {
//     $args = $_SERVER['argv'];
// } else {
//     parse_str($_SERVER['QUERY_STRING'], $args);
// }

// $subcommand = array_shift($args);
// switch ($subcommand) {
// 	case "install":
// 		try {
// 			$options = get_options($argv);
// 			define('HTTP_OPENCART', $options['http_server']);
// 			$valid = valid($options);
// 			if (!$valid[0]) {
// 				echo "FAILED! Following inputs were missing or invalid: ";
// 				echo implode(', ', $valid[1]) . "\n\n";
// 				exit(1);
// 			}
// 			install($options);
// 			echo "SUCCESS! Opencart successfully installed on your server\n";
// 			echo "Store link: " . $options['http_server'] . "\n";
// 			echo "Admin link: " . $options['http_server'] . "admin/\n\n";
// 		} catch (ErrorException $e) {
// 			echo 'FAILED!: ' . $e->getMessage() . "\n";
// 			exit(1);
// 		}
// 		break;
	
// 	case "usage":
// 	default:
// 		echo usage();
// }

echo $skin_footer;

function opentable($title="") {
	echo "<div class='panel panel-default'>";
	echo ($title != "") ? "<div class='panel-heading clear'><span>$title</span></div>" : "";
	echo "<div class='panel-body'>";
}
function closetable($footer=false) {
	echo "</div>";
	if($footer !== false) {
		echo "<div class='panel-footer'>";
		echo "<button type='submit' class='btn bg-teal btn-sm btn-raised position-left'><i class='fa fa-arrow-circle-o-right position-left'></i>Next</button>";
		echo "</div>";
	}
	echo "</div>";
}

function isSSL() {
    if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
        || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
		|| (isset($_SERVER['CF_VISITOR']) && $_SERVER['CF_VISITOR'] == '{"scheme":"https"}')
		|| (isset($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR'] == '{"scheme":"https"}')
    ) return true; else return false;
}

if(!function_exists("debug")) {
	function debug($array) {
	    echo "<pre>";
	    echo htmlspecialchars(print_r($array, TRUE), ENT_QUOTES, 'utf-8');
	    echo "</pre>";
	}
}


function writeConfigFile($host,$username,$password,$name,$prefix) {

$config = <<<HTML
<?PHP

//Deny direct access
defined("_LOAD") or die("Access denied");

//Get configuration data
\$config = array();
\$result = \$db->query("SELECT label,value FROM #__configuration ORDER BY label")->rows;
foreach (\$result as \$row) \$config[Io::Output(\$row['label'])] = Io::Output(\$row['value']);

//System Configurations
\$config_sys_fields = array (
	"site_name"					=> "EasyLife",
	"site_url"					=> root_path(),
    "debug"                     => 0,
    "default_timezone"          => "UTC",
    "node"                      => 'cont',
    "default_home"              => 'post',
    "seo_urls"                  => 1,
    "seo_urls_suffix"           => '',
    "seo_urls_separator"        => '/',
    "title_order"               => "ASC",
    "title_separator"           => " | ",
    "breadcrumb_separator"      => " / ",
    "captcha"                   => 1,
    "captcha_for_users"         => 1,
    "dbserver_timezone"         => 0,
    "default_datestamp"         => "D, j M Y",
    "default_timestamp"         => "H:i",
    "default_language"          => "en",
    "default_template"          => "nuke",
    "default_mobiletemplate"    => "nuke",
    "email_mailer"              => "mail",
    "email_smtp_host"           => "",
    "email_smtp_user"           => "",
    "email_smtp_pass"           => "",
    "email_smtp_ssl"            => 1,
    "email_smtp_port"           => 25,
    "email_charset"             => "utf-8",
    "email_type"                => "html",
    "engine_version"            => "5.0.0.9",
    "login_cookie_expire"       => 7,
    "maintenance"               => 0,
    "maintenance_last"          => "2008-01-01 12:00:00",
    "maintenance_message"       => "The site is under maintenance.",
    "maintenance_pause"         => "10",
    "maintenance_whiteip"       => "127.0.0.1",
    "output_compression"        => 0,
    "social_login"              => 0,
    "statistics"                => 1,
    "statistics_full"           => 0,
    "texteditor"                => 1,
    "uniqueid"                  => md5(mt_rand(1,9999)),
    "user_signup"               => 1,
    "user_signup_confirm"       => 1,
    "user_signup_invite"        => 0,
    "user_signup_moderate"      => 0,
    "language"                  => "en",
    "error_display"             => true,
    "error_log"                 => true,
    "error_filename"            => "error.log",
    "config_compression"        => 0,
    "multilang_enable"          => "off",
    "multilang_default"         => "so",
    "multilang_country"         => '{"en":{"country":"English","system_lang":"english","flag":"GB"},"so":{"country":"Somali","system_lang":"somali","flag":"SO"}}',
    "csrf_protection"           => true,
    "persistent"                => false
);

\$config = array_merge(\$config_sys_fields,\$config);

//System constants
define("_NODE",\$config['node']);
define("_COOKIEPATH",preg_replace("`http://[^/]+`i","",\$config['site_url']));
define("_BREADCRUMB_SEPARATOR",\$config['breadcrumb_separator']);
define("_SITETITLE_SEPARATOR",\$config['title_separator']);

//Database server datetime
\$row = \$db->query("SELECT NOW() AS datetime")->row;
\$db_datetime = Io::Output(\$row['datetime']); //2009-10-12 19:12:42
\$dt = explode(" ",\$db_datetime);
define("_DB_DATETIME",\$db_datetime);
define("_DB_DATE",\$dt[0]);
define("_DB_TIME",\$dt[1]);

/*
//GMT datetime
\$gmt_datetime = strtotime(Hours2minutes(\$config['dbserver_timezone'])*(-1).' minutes',strtotime(\$db_datetime));
\$gmt_datetime = date('Y-m-d H:i:s',\$gmt_datetime);
\$gmt_dt = explode(" ",\$gmt_datetime);
define("_GMT_DATETIME",\$gmt_datetime);
define("_GMT_DATE",\$gmt_dt[0]);
define("_GMT_TIME",\$gmt_dt[1]);
*/

// Settings dependent functions
//date_default_timezone_set(\$config['default_timezone']);
if(!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}

?>
HTML;

$dbconfig = <<<HTML
<?php

//Deny direct access
defined("_LOAD") or die("Access denied");

define ("DBHOST", "{$host}");
define ("DBNAME", "{$name}");
define ("DBUSER", "{$username}");
define ("DBPASS", "{$password}");
define ("PREFIX", "{$prefix}");

define ("_INSTALLED", true);

?>
HTML;
	if(is_dir(BASEDIR."includes/storage/config/") === false) {
		mkdir(BASEDIR."includes/storage/config/", 0755);
	}

	$con_file = fopen(BASEDIR."includes/storage/config/config.php", "w+") or die("Sorry! Unable to create <b>.includes/storage/config/config.php</b>.<br />Check the CHMOD!");
	fwrite($con_file, $config);
	fclose($con_file);
	@chmod(BASEDIR."includes/storage/config/config.php", 0666);

	$con_file = fopen(BASEDIR."config.php", "w+") or die("Sorry! Unable to create <b>./config.php</b>.<br />Check the CHMOD!");
	fwrite($con_file, $dbconfig);
	fclose($con_file);
	@chmod(BASEDIR."config.php", 0666);
}