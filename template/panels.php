<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

$admin_mess = '';
// Declare panels side
$p_name = array(
	array('name' => 'LEFT', 'side' => 'left'),
	array('name' => 'U_CENTER', 'side' => 'upper'),
	array('name' => 'L_CENTER', 'side' => 'lower'),
	array('name' => 'RIGHT', 'side' => 'right')
);

// Get panels data to array
$panels_cache = array();

foreach ($p_name as $p_key => $p_side) {
	if (isset($panels_cache[$p_key + 1]) || defined("ADMIN_PANEL")) {
		ob_start();
		if (!defined("ADMIN_PANEL")) {
			foreach ($panels_cache[$p_key + 1] as $p_data) {
				debug($p_data);
			}
			unset($p_data);
		} 
		// else if ($p_key == 0) {
		// 	require_once BASEDIR."admin/navigation.php";
		// }
		define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : '').ob_get_contents());
		ob_end_clean();
	} else {
		define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : ''));
	}
}
unset($panels_cache);
// if (defined("ADMIN_PANEL") || LEFT && !RIGHT) {
// 	$main_style = "side-left";
if (defined("ADMIN_PANEL") || !LEFT && !RIGHT) {
	$main_style = "side-both";
} elseif (LEFT && RIGHT) {
	$main_style = "side-both";
} elseif (!LEFT && RIGHT) {
	$main_style = "side-right";
} elseif (!LEFT && !RIGHT) {
	$main_style = "";
}