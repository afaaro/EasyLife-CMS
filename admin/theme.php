<?php


function render_page($license=false) {
	global $User, $config, $main_style;
	?>
	<header>
		<div class='alignment clear'><div class='borderit'>
			<div class='hbot'>
				<a class='logo' href=''><img src='<?php echo asset("assets/images/logo.png"); ?>' alt='<?php echo $config['site_name']; ?>'></a>
				<div class='adv'></div>

				<nav class='menu-cont'>
					<ul class='left-links'>
						<li><a href='index.php'>Home</a></li>
						<li><a href='index.php?cont=contact-us'>Contact Us</a></li>
					</ul>

					<?php if($User->IsUser()) { ?>
						<ul class='right-links'>
							<?php echo iADMIN ? "<li><a href='".BASEDIR."admin'>Administration</a></li>" : ""; ?>
							<li><a href='index.php?cont=user&op=logout'>Logout</a></li>
						</ul>
					<?php } ?>
				</nav>
			</div>
		</div></div>
	</header>

	<div class='alignment clear'><div class='borderit clear' style='padding-bottom: 15px;'>
	<div class='body-wrap <?php echo $main_style; ?>'>
		<?php echo renderNotices(getNotices()); ?>

		<?php
		echo (LEFT) ? "<div id='side-border-left'>".LEFT."</div>" : "";
		echo (RIGHT) ? "<div id='side-border-right'>".RIGHT."</div>" : "";
		echo "<div id='main-bg'>".CONTENT."</div>";
		?>
	</div></div></div>
	<footer>
		<div class='alignment clear'><div class='borderit'>
			<div class='clear'></div>
			<p class='copyright'>Copyright © <?php echo $config['site_name']; ?>.</p>
			<ul id='footer-social'>
				<li class='facebook footer-social'><a href='' target='_blank'></a></li>
				<li class='twitter footer-social'><a href='' target='_blank'></a></li>
				<li class='youtube footer-social'><a href='' target='_blank'></a></li>
			</ul>
		</div></div>
	</footer>
	<?php
}