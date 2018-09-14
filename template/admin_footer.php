<?php
// $output = ob_get_contents();
// ob_end_clean();
// echo $output;
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
echo "</body>\n</html>\n";

// $output = ob_get_contents();
// if (ob_get_length() !== FALSE){
// 	ob_end_clean();
// }
// echo handle_output($output);

// if (ob_get_length() !== FALSE){
// 	ob_end_flush();
// }