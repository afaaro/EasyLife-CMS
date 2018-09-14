<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<meta name='robots' content='index, follow' />
<meta name='revisit-after' content='7 days' />
<title><?php echo get_option('site_name'); ?></title>
<base href='<?php echo get_option('site_url'); ?>'>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i" rel="stylesheet">
<link rel='stylesheet' href='<?php echo asset("assets/bootstrap/css/bootstrap.min.css"); ?>'>
<link rel='stylesheet' href='<?php echo asset("template/stylesheet.css"); ?>'>
<?php 
	//js_minify(array(asset("template/application.js")), BASEDIR."template/", md5("Header").".js"); 
	//css_minify(array(asset("template/application.css"), asset("template/stylesheet.css")), BASEDIR."template/", md5("Header").".css");
?>

</head><body>
<?php ob_start(); ?>

<header>
	<div class='container'><div class='borderit'>
		<div class='htop'>
			<a class='logo' href=''><img src='<?php echo asset("assets/images/logo.png"); ?>' alt='<?php echo get_option('site_name'); ?>'></a>
			<div class='adv'></div>
		</div>
		<div class='hbot'>
			<nav id='nav'>
				<?php 
				echo "<ul class='left-links'>";
					echo "<li><a href='".BASEDIR."'>Home</a></li>";
					echo "<li><a href='".BASEDIR."".Url::link('contact-us')."'>Contact Us</a></li>";
				echo "</ul>";

				echo "<ul class='right-links'>";
				global $User;
				if($User->IsUser()) {
					echo iADMIN ? "<li><a href='".BASEDIR."admin/".Url::link('post/news')."'>Administration</a></li>" : "";
					echo "<li><a href='".BASEDIR.Url::link('user/logout')."'><i class='fa fa-sign-out-alt' style='color:white;'></i> Logout</a></li>";
				} else {
					echo "<li><a href='".BASEDIR.Url::link('user/login')."'><i class='fa fa-sign-in-alt' style='color:white;'></i> Login</a></li>";
				}
				echo "</ul>";
				?>
			</nav>
		</div>
	</div></div>
</header>

<div class='container'><div class='borderit clear' style='padding-bottom: 15px;'>
<div class='body-wrap'>

<?php 
echo renderNotices(getNotices());
//echo get_filename_id(); 
?>