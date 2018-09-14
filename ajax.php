<?php
require_once __DIR__.'/maincore.php';

//Nice & SEO urls + Site name
add_handler( ($config['seo_urls']==1) ? "sys_rewrite_full" : "sys_rewrite_core" );

$operation = Io::GetVar("REQUEST", "op");

$outputjson = "";

try {
	if(!isset($operation)) {
		$outputjson['error'] = "Operation missing in request.";
	} else if(file_exists(BASEDIR."system/ajax/".$operation.".php")) {		 
	    include(BASEDIR."system/ajax/".$operation.".php");
		if(is_callable($operation) ){
			$params = $_REQUEST;
			$operation($params);
		} else {
			$outputjson['error'] = "Operation does not exists" ;
		}
	} else {
		$outputjson['error'] = "file does not exist";
	}
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
