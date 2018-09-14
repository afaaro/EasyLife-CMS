<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
<!-- <meta name='robots' content='index, follow' />
<meta name='revisit-after' content='7 days' /> -->
<title><?php echo $config['site_name']; ?></title>

<script src='<?php echo asset("assets/application.js"); ?>'></script>
<link href='<?php echo asset("assets/bootstrap/css/bootstrap.min.css"); ?>' rel='stylesheet' />
<link href='<?php echo asset("assets/font-awesome/css/font-awesome.min.css"); ?>' rel='stylesheet' />
<!-- <link href='https://use.fontawesome.com/releases/v5.0.13/css/all.css' rel='stylesheet'> -->
<link rel='stylesheet' type='text/css' href='<?php echo asset('assets/flag-icon/flag-icon.min.css'); ?>'>
<script src='<?php echo asset("assets/common.js"); ?>'></script>

<link href='<?php echo asset("assets/DataTables/datatables.min.css"); ?>' rel='stylesheet' />
<script src='<?php echo asset("assets/DataTables/datatables.min.js"); ?>'></script>


<link href='https://use.fontawesome.com/releases/v5.0.13/css/all.css' type='text/css' rel='stylesheet' />
<link rel='stylesheet' type='text/css' href='<?php echo asset("template/stylesheet.css"); ?>'>
<link rel='stylesheet' type='text/css' href='<?php echo asset("admin/navigation.css"); ?>'>
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
<script type='text/javascript'>

</script>
</head><body>
<?php
require_once BASEDIR."template/panels.php";
//ob_start();

?>
<header>
	<div class='alignment clear'><div class='borderit'>
		<div class='hbot'>
			<a class='logo' href='<?php echo BASEDIR; ?>'><img src='<?php echo asset("assets/images/logo.png"); ?>' alt='<?php echo $config['site_name']; ?>'></a>
			<div class='adv'></div>

			<nav class='menu-cont' id='horizontal'>
				<?php

				$result = $db->query("SELECT id, parent, title, url, icon FROM ".PREFIX."menu_acp ORDER BY menu ASC, parent ASC, title ASC");
				$refs = array(); $list = array();
				foreach($result->rows as $data) {
				    $thisref = &$refs[ $data['id'] ];
				    $thisref['id'] = $data['id'];
				    $thisref['parent'] = $data['parent'];
				    $thisref['name'] = $data['title'];
				    $thisref['href'] = $data['url'];
				    $thisref['icon'] = $data['icon'];
				    if ($data['parent'] == 0) {
				        $list[ $data['id'] ] = &$thisref;
				    } else {
				        $refs[ $data['parent'] ]['children'][] = &$thisref;
				    }
				}

				echo "<ul class='left-links'>";
				echo build_Horizontal_Menu($list);
				echo "</ul>";

				if($User->IsUser()) {
					echo "<ul class='right-links'>";
					echo iADMIN ? "<li><a href='".BASEDIR."admin'>Administration</a></li>" : "";
					echo "<li><a href='".BASEDIR.Url::link('user/logout')."'><i class='fa fa-sign-out-alt' style='color:white;'></i> Logout</a></li>";
					echo "</ul>";
				}

				?>
			</nav>
		</div>
	</div></div>
</header>

<div class='alignment clear'><div class='borderit clear' style='padding-bottom: 15px;'>
<div class='row body-wrap <?php echo $main_style; ?>'>
	<?php

	echo (LEFT) ? "<div id='side-border-left'>".LEFT."</div>" : "";
	echo (RIGHT) ? "<div id='side-border-right'>".RIGHT."</div>" : "";
	echo "<div id='main-bg'>";
		echo renderNotices(getNotices());
	?>