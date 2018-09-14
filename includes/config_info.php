<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

//Get configuration data
$config = array();
$result = $db->query("SELECT label,value FROM #__configuration ORDER BY label")->rows;
foreach ($result as $row) $config[Io::Output($row['label'])] = Io::Output($row['value']);

//System Configurations
$config_sys_fields = array (
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

$config = array_merge($config_sys_fields,$config);

//System constants
define("_NODE",$config['node']);
define("_COOKIEPATH",preg_replace("`http://[^/]+`i","",$config['site_url']));
define("_BREADCRUMB_SEPARATOR",$config['breadcrumb_separator']);
define("_SITETITLE_SEPARATOR",$config['title_separator']);

//Database server datetime
$row = $db->query("SELECT NOW() AS datetime")->row;
$db_datetime = Io::Output($row['datetime']); //2009-10-12 19:12:42
$dt = explode(" ",$db_datetime);
define("_DB_DATETIME",$db_datetime);
define("_DB_DATE",$dt[0]);
define("_DB_TIME",$dt[1]);

/*
//GMT datetime
$gmt_datetime = strtotime(Hours2minutes($config['dbserver_timezone'])*(-1).' minutes',strtotime($db_datetime));
$gmt_datetime = date('Y-m-d H:i:s',$gmt_datetime);
$gmt_dt = explode(" ",$gmt_datetime);
define("_GMT_DATETIME",$gmt_datetime);
define("_GMT_DATE",$gmt_dt[0]);
define("_GMT_TIME",$gmt_dt[1]);
*/

// Settings dependent functions
//date_default_timezone_set($config['default_timezone']);
if(!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}

?>