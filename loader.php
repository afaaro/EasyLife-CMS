<?php 

//error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
@error_reporting ( E_ALL, E_STRICT );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL );

if(!file_exists(dirname(__FILE__) . '/config.php')) {
    header('Location: install/install.php');
}

// Locate config.php and set the basedir path
$folder_level = ""; $i = 0;
while (!file_exists($folder_level."config.php")) {
    $folder_level .= "../"; $i++;
    if ($i == 7) { die("config.php file not found"); }
}
define("BASEDIR", $folder_level);
define("_LOAD", TRUE);

require_once(BASEDIR . 'config.php');
require_once(BASEDIR . 'includes/database.php');
$db = new Database(DBHOST, DBUSER, DBPASS, DBNAME);

require(BASEDIR . 'includes/io.php');
require(BASEDIR . 'includes/mb.php');
require(BASEDIR . 'includes/ram.php');
require(BASEDIR . 'includes/helpers.php');
require(BASEDIR . 'includes/config_info.php');
require(BASEDIR . 'includes/error.php');
require(BASEDIR . 'includes/exception.php');
require(BASEDIR . 'includes/utils.php');
require(BASEDIR . 'includes/form.php');
require(BASEDIR . 'includes/hash.php');
require(BASEDIR . 'includes/password.php');
require(BASEDIR . 'includes/handler.php');
require(BASEDIR . 'includes/minify.php');
require(BASEDIR . 'includes/language.php');
require(BASEDIR . 'includes/media.php');
require(BASEDIR . 'includes/pagination.php');
require(BASEDIR . 'includes/session.php');
require(BASEDIR . 'includes/url.php');
require(BASEDIR . 'includes/url_rewrite.php');
require(BASEDIR . 'includes/functions.php');
require(BASEDIR . 'includes/option.php');
require(BASEDIR . 'includes/template.php');
require(BASEDIR . 'includes/function_notify.php');
require(BASEDIR . 'includes/user.php');

// Prevent any possible XSS attacks via $_GET.
if (stripget($_GET)) {
    die("Prevented a XSS attack through a GET variable!");
}

// Sanitise $_SERVER globals
$_SERVER['PHP_SELF'] = cleanurl($_SERVER['PHP_SELF']);
$_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']) ? cleanurl($_SERVER['QUERY_STRING']) : "";
$_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? cleanurl($_SERVER['REQUEST_URI']) : "";
$PHP_SELF = cleanurl($_SERVER['PHP_SELF']);

// Common definitions
define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);
define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");
define("FUSION_SELF", basename($_SERVER['PHP_SELF']));
define("FUSION_IP", $_SERVER['REMOTE_ADDR']);
define("QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));

// //ob_start("ob_gzhandler"); //Uncomment this line and comment the one below to enable output compression.
// ob_start();

// // Make sure garbage collection is enabled
// ini_set('session.gc_probability', 1);
// ini_set('session.gc_divisor', 100);
// // Session lifetime. After this time stored data will be seen as 'garbage' and cleaned up by the garbage collection process.
// ini_set('session.gc_maxlifetime', 172800); // 48 hours
// // Session cookie life time
// ini_set('session.cookie_lifetime', 172800); // 48 hours
// // Prevent document expiry when user hits Back in browser
// session_cache_limiter('private, must-revalidate');
// // // Session cookie name
// // session_name('sid');

// // Start session. Gets destroyed on log out or user cookie expiration.
// Session::Start();

// Log in user
if (isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password'])) {
    $errors = [];
    if ($User->Authenticate()) {
        //TODO: Redirect on the requested page? Buffer?
        redirect(get_option('site_url'));
    } else $errors[] = $User->GetError();

    if (sizeof($errors)) {
        foreach($errors as $error) {
            echo "<div class='error_user'>\n<div><strong>{$error}</strong></div>";
            //if ($User->IsAdmin() && !empty($error)) { echo "<div><em>$error</em></div>"; }
            echo "</div>\n";
        }
    }
}

define("iGUEST", $User->GetInfo('user_level') == 0 ? 1 : 0);
define("iMEMBER", $User->GetInfo('user_level') <= 101 ? 1 : 0);
define("iADMIN", $User->GetInfo('user_level') >= 1 ? 1 : 0);
define("iSUPERADMIN", $User->GetInfo('user_level') == 1 ? 1 : 0);
define("iUSER", $User->GetInfo('user_level'));
//define("iUSER_RIGHTS", $User->GetInfo('user_rights'));

//debug($User->hasAccess('create_any'));

/**
 * Loads all functions into the global scope
 *
 * @return void
 * @throws \ErrorException
 * @throws \OverflowException
 */
function loadFunctions() {
    //if (!iADMIN) {}
    $fi = new FilesystemIterator(BASEDIR . "includes", FilesystemIterator::SKIP_DOTS);

    foreach ($fi as $file) {
        $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

        if ($file->isFile() and $file->isReadable() and '.' . $ext == '.php') {
            require $file->getPathname();
        }
    }
    // $controller = Ram::Get('controller');
    // // include plugin functions
    // if (is_readable($path = BASEDIR . 'plugins/'.$controller.'/acp/functions.php')) {
    //     require $path;
    // }
}