<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function sys_rewrite_full($string) {
	$string = preg_replace_callback('#href=([\'|"])index.php\?([^\'|"]+)([^\'|"])#is','_sys_modrewrite',sys_rewrite_core($string));
	
	//TODO: HTML code minimization???
	//$string = preg_replace("#\n|\r|\n\r#","",$string);
	//$string = preg_replace("#\s{2,}#","",$string);
	return $string;
}

function _sys_modrewrite($matches) {
	global $config;

	$anchor = preg_match("#\#[a-zA-Z0-9]+#is",$matches[2],$ancmatch) ? $ancmatch[0] : "" ;
	$matches[2] = str_replace("&amp;","&",$matches[2]);
	$pieces = explode("&",preg_replace("#\#[a-zA-Z0-9]+#is","",$matches[2],-1,$count));
	foreach ($pieces as $piece) $rewritten[] = preg_replace("#[a-zA-Z0-9]+=#is","",$piece);
	$rewritten = implode($config['seo_urls_separator'],$rewritten);
	$suffix = ($rewritten!="index.php") ? $config['seo_urls_suffix'] : "";
	return "href=".$matches[1].$config['site_url'].$rewritten.$suffix.$anchor.$matches[3];
}

function sys_rewrite_core($string) {
	global $config;

	$st = array();
	//$st[] = $string;
	if ($config['title_order']=="ASC") $st = array_reverse($st);	
	return (sizeof($st)>0) ? str_replace("<title>".$config['site_name']."</title>","<title>".implode(" "._SITETITLE_SEPARATOR." ",$st)."</title>",$string) : $string ;
}

//Manual rewrite
function RewriteUrl($url) {
	global $config;
	
	if ($url=="index.php") return $config['site_url'];
	if ($config['seo_urls']==0) return $url; //ModRewrite inactive
	if ($config['site_url']==$url || $config['site_url']."/index.php"==$url) return $url; //No rewrite needed

	preg_match('#index.php\?([^\'|"]+)#is',$url,$match);
	$url = explode(preg_match("#&amp;#is",$match[1]) ? "&amp;" : "&" ,$match[1]);
	$rewritten = array();
	foreach ($url as $piece) $rewritten[] = preg_replace("#[a-zA-Z0-9]+=#is","",$piece);
	$url = implode($config['seo_urls_separator'],$rewritten);
	$url .= ($rewritten!="index.php") ? $config['seo_urls_suffix'] : "";

	$base_url = file_exists(BASEDIR."index.php") ? $config['site_url'].$url : $config['site_url']."infusions/media/".$url;
	return $base_url;
}

/**
 * Append File Root
 *
 * Append the ROOT Dir Path to all relative links, which are from website
 * This function will append the root directory path for all links, which
 * are in website. (Not External HTTP links)
 */
function appendRootAll($string) {
    if (preg_match("/(href|src|action)='((?!(htt|ft)p(s)?:\/\/)[^\']*)'/i", $string)) {
        $basedir = str_replace(array(".", "/"), array("\.", "\/"), BASEDIR);
        $basedir = preg_replace("~(href|src|action)=(\'|//\")(".$basedir.")*([^(\'|\"):]*)(\'|\")~i", "$1=$2".$basedir."$3$4$5", $string);

        $loop = 7;
        for ($i = 1; $i <= $loop; $i++) {
            $basedir = str_replace(str_repeat('../', $i), '', $basedir);
        }
        $basedir = str_replace("../../", BASEDIR, $basedir);
        return $basedir;
    }
}

?>