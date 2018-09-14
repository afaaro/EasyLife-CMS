<?php 

// Load the library.
require_once( dirname(__FILE__) . '/loader.php' );

get_header();

//Nice & SEO urls + Site name
Handler::addHandler( (get_option('seo_urls') == 1) ? "sys_rewrite_full" : "sys_rewrite_core" );

require(BASEDIR . 'includes/router.php');
$Router = new Router;
$Router->Run();

get_footer();