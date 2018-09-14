<?php


function tpl_header() {
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
	<meta charset="UTF-8" />
	<title>Pre-Installation</title>
	<!-- <base href="http://localhost/fusion/install/" /> -->
	<script type="text/javascript" src="http://code.jquery.com/jquery-2.1.1.min.js"></script>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" media="screen" />
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
	<link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" type="text/css" rel="stylesheet" />
	<link href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,300,700" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="stylesheet.css" />
	</head>
	<body>
	<div class="container">
	  <header>
	    <div class="row">
	      <div class="col-sm-6">
	        <h3>Pre-Installation<br>
	          <small>Check your server is set-up correctly</small></h3>
	      </div>
	      <div class="col-sm-6">
	        <div id="logo" class="pull-right hidden-xs"><img src="image/logo.png" alt="" title="" /></div>
	      </div>
	    </div>
	  </header>
        <div class="row">
            <div class="col-sm-9">
	<?php
}

function tpl_footer() {
	?>
            </div>

            <div class="col-sm-3">
                <ul id="steps" class='list-group'>
                    <?php
                    $current_step = Io::GetVar("GET", "cont", false, 'start');
                    $steps = array('start', 'database', 'account', 'complete');
                    foreach($steps as $step) {
                        echo "<li class='list-group-item'>";
                            if($step == $current_step) {
                                echo "<b>".MB::ucfirst($step)."</b>";
                            } else {
                                echo MB::ucfirst($step);
                            }
                        echo "</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
	</div>
	<footer>
	  <div class="container"><a href="http://www.opencart.com" target="_blank">Project Homepage</a>|<a href="http://www.opencart.com/index.php?route=documentation/introduction" target="_blank">Documentation</a>|<a href="http://forum.opencart.com" target="_blank">Support Forums</a><br />
	    </div>
	</footer>
	</body></html>
	<?php
}

function writeConfigFile($driver,$host,$username,$password,$name,$prefix) {
        global $success;

        $content = "<?php\n";
        $content .= "//Deny direct access\n";
        $content .= "defined(\"_LOAD\") or die(\"Access denied\");\n";

        $content .= "//Connection data\n";
        $content .= "define(\"DRIVER\", \"$driver\");\n";
        $content .= "define(\"PREFIX\", \"$prefix\");\n";
        $content .= "\n";
        $content .= "define(\"DBHOST\", \"$host\");\n";
        $content .= "define(\"DBUSER\", \"$username\");\n";
        $content .= "define(\"DBPASS\", \"$password\");\n";
        $content .= "define(\"DBNAME\", \"$name\");\n";

        $content .= "\n";
        $content .= "define(\"_INSTALLED\",true);\n";

        $myfile = PATH.'config.php';
        if (is_writable(PATH)) {
            $handle = fopen($myfile, 'w');
            fwrite($handle, $content);
            fclose($handle);
            $success = true;
        } else {
            $success = false;
            echo "<div class='tpl_page_title'>Warning</div>\n";
            echo "<div class='info'>\n";
                echo "<div><b>The folder root/includes/ does not exist or is not writable!</b></div>\n";
                echo "<div><b>What to do?</b></div>";
                echo "<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='images/sub.gif'> Download the configuration file from <a href='output.php?h=$host&u=$user&p=$password&n=$name&prefix=$prefix' target='_blank'><b style='color:#990000;'>HERE</b></a> and upload it into the root/includes/ folder.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The final configuration file path must be: root/includes/config.ing.php<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='images/sub.gif'> When done, continue the installation wizard.</div>\n";
            echo "</div>\n";
        }
}

/**
 * Checks whether the current web server is an Apache httpd
 *
 * @return bool
 */
function is_apache() {
    return stripos(PHP_SAPI, 'apache') !== false;
}

/**
 * Checks whether PHP is running as a CGI daemon
 *
 * @return bool
 */
function is_cgi() {
    return stripos(PHP_SAPI, 'cgi') !== false;
}

/**
 * Checks whether mod_rewrite is enabled
 *
 * @return bool
 */
function mod_rewrite() {
    if (is_apache() and function_exists('apache_get_modules')) {
        return in_array('mod_rewrite', apache_get_modules());
    }

    return getenv('HTTP_MOD_REWRITE') ? true : false;
}

/*
 *   Pre install checks
 */
$GLOBALS['errors'] = [];

/**
 * Checks a precondition for the installation
 *
 * @param string   $message User facing HTML message
 * @param \Closure $action  action to execute for the check
 *
 * @return void
 */
function check($message, $action) {
    if ( ! $action()) {
        $GLOBALS['errors'][] = $message;
    }
}