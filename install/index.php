<?php
require_once dirname(__DIR__)."/maincore.php";

// /**
//  * Helpers
//  */
// require SYS . 'helpers.php';

// class Autoload {
//     static public function loader($class_name) {
//         if (class_exists($class_name)) {
//             return TRUE;
//         } else if (file_exists(SYS."classes/".str_replace("\\", "/", strtolower($class_name)).".php")) {
//             include(SYS."classes/".str_replace("\\", "/", strtolower($class_name)).".php");
//         }
//         return FALSE;
//     }
// }

// spl_autoload_register('Autoload::loader');

require 'install.php';