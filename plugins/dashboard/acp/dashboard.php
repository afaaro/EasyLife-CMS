<?php

class dashboardController {
	function index() {
		global $db, $User, $config;

		$order = array(0=>1,1=>2,2=>3);
		$boxes = array(1=>"quickicons",2=>"comments",3=>"quicknote");

		echo "<div class='col-md-8'>";
			foreach ($order as $value) include(BASEDIR."plugins/dashboard/acp/boxes/".$boxes[$value].".php");
		echo "</div>";

		echo "<div class='col-md-4'>";
        	opentable();
            echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
			echo "<tr><td width='55%'>Version:</td><td width='45%'>".$config['engine_version']."</td></tr>\n";
			echo "<tr><td>SEO_URLS:</td><td>".(($config['seo_urls']) ? "ENABLED" : "DISABLED")."</td></tr>\n";
			echo "<tr><td>Captcha:</td><td>".(($config['captcha']) ? "ENABLED" : "DISABLED")."</td></tr>\n";
			echo "<tr><td>MAINTENANCE_MODE:</td><td>".(($config['maintenance']) ? "ENABLED" : "DISABLED")."</td></tr>\n";
			echo "<tr><td>WHITELIST_IP:</td><td>".Utils::Num2ip($config['maintenance_whiteip'])."</td></tr>\n";
			echo "<tr><td>LAST_MAINTENANCE:</td><td>".$config['maintenance_last']."</td></tr>\n";
			echo "</table>";
			closetable();

        	opentable();
            echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
			echo "<tr><td width='55%'>OS:</td><td width='45%'>".@php_uname('s')."</td></tr>\n";
			echo "<tr><td>SERVER_NAME:</td><td><div style='width:105px;overflow:auto;'>".@php_uname('n')."</div></td></tr>\n";
			echo "<tr><td>DISK_FREE_SPACE:</td><td>".Utils::Bytes2str(@disk_free_space("/"))."</td></tr>\n";
			echo "</table>";
			closetable();

        	opentable();
            echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
			echo "<tr><td width='55%'>PHP VERSION:</td><td width='45%'>".phpversion()."</td></tr>\n";
			$row = $db->query("SELECT VERSION() AS version")->row;
			echo "<tr><td>MySQL VERSION:</td><td>".Io::Output($row['version'])."</td></tr>\n";
			echo "<tr><td>REGISTER_GLOBALS:</td><td>".((get_cfg_var('register_globals')) ? "ENABLED" : "DISABLED")."</td></tr>\n";
			echo "<tr><td>MEMORY_LIMIT:</td><td>".get_cfg_var('memory_limit')."</td></tr>\n";
			echo "<tr><td>UPLOAD_MAX_FILESIZE:</td><td>".get_cfg_var('upload_max_filesize')."</td></tr>\n";
			echo "</table>";
			closetable();

        	opentable();
            echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
			echo "<tr><td width='55%'>STATUS:</td><td width='45%'>".((extension_loaded('gd')) ? "AVAILABLE" : "UNAVAILABLE")."</td></tr>\n";
			$gdinfo = @gd_info();
			echo "<tr><td>VERSION:</td><td>".@$gdinfo['GD Version']."</td></tr>\n";
			
			$gdtypes = array();
			if (isset($gdinfo['FreeType Support'])) $gdtypes[] = "FreeType";
			if (isset($gdinfo['T1Lib Support'])) $gdtypes[] = "T1Lib";
			if (isset($gdinfo['GIF Read Support'])) $gdtypes[] = "GIF Read";
			if (isset($gdinfo['GIF Create Support'])) $gdtypes[] = "GIF Create";
			if (isset($gdinfo['JPEG Support'])) $gdtypes[] = "JPEG";
			if (isset($gdinfo['JPG Support'])) $gdtypes[] = "JPG";
			if (isset($gdinfo['PNG Support'])) $gdtypes[] = "PNG";
			if (isset($gdinfo['WBMP Support'])) $gdtypes[] = "WBMP";
			if (isset($gdinfo['XBM Support'])) $gdtypes[] = "XBM";
			$gdtypes = implode(", ",$gdtypes);
			echo "<tr><td>SUPPORTED_TYPES:</td><td>".$gdtypes."</td></tr>\n";
			echo "</table>";
			closetable();
		echo "</div>";
	}
}