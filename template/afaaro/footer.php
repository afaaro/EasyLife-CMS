</div></div></div>
<footer>
	<div class='container'><div class='borderit'>
		<div class='clear'></div>
		<p class='copyright'>Copyright © <?php echo get_option('site_name'); ?>.</p>
		<ul id='footer-social'>
			<li class='facebook footer-social'><a href='' target='_blank'></a></li>
			<li class='twitter footer-social'><a href='' target='_blank'></a></li>
			<li class='youtube footer-social'><a href='' target='_blank'></a></li>
		</ul>
	</div></div>
</footer>
<?php
$html = "";
$fusion_jquery_tags = Handler::$jqueryTags;
if (!empty($fusion_jquery_tags)) {
    $html .= "<script type=\"text/javascript\">\n$(function() {\n";
    $html .= $fusion_jquery_tags;
    $html .= "});\n</script>\n";
}
echo $html;
?>
</body></html>