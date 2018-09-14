<?php require_once __DIR__.'/../loader.php';

//Deny direct access
defined("_LOAD") or die("Access denied");
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

$plugin = Io::GetVar("GET","cont",'/[^a-zA-Z0-9_\/]/',true,"dashboard");
global $User, $config;

if($User->hasPermission('access', $plugin)) {
	require(BASEDIR . 'includes/router_cp.php');
	require(BASEDIR . 'includes/menu_cp.php');
	get_template('admin_header2');
	$Router = new Router($plugin);
	$Router->Run();
	get_template('admin_footer2');
} 
else {
	redirect(get_option('site_url'));
}


//Flush buffer and turn it off
if (ob_get_status()) ob_end_flush();

//DEBUG
if ($User->IsAdmin() && $config['debug']==1) {
    //debug(Error::GetLog()); //TODO FIX: Get log from DB, should give errors of the session
    //debug($_GET);
    //debug(Ram::GetAll());
    //debug($Visitor);
}