<?php

class Setup {
  	static function Install() {
    		global $db;

    		// ads_client
    		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."ads_client (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              `email` varchar(255) NOT NULL DEFAULT '',
              `website` varchar(255) NOT NULL DEFAULT '',
              `message` text NOT NULL,
              `options` longtext NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

    		// ads_ads
    		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."ads_ads (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              `client` int(10) NOT NULL,
              `start_date` date DEFAULT NULL,
              `end_date` date DEFAULT NULL,
              `status` enum('active','inactive','pending','expired') NOT NULL DEFAULT 'inactive',
              `options` longtext NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


        $data = $db->query("SELECT id FROM ".PREFIX."menu_acp WHERE title='Content' AND parent='0'")->row;
        $db->query("INSERT INTO ".PREFIX."menu_acp SET title='Advertisement', rights='advertisement', uniqueid='advertisement_main', menu='content', status='active', parent='".$data['id']."'");
        $lastid = $db->getLastId();
        
        $db->query("INSERT INTO ".PREFIX."menu_acp SET title='Client', rights='advertisement', uniqueid='advertisement_main', url='advertisement/client', menu='content', status='active', parent='{$lastid}'");
        $db->query("INSERT INTO ".PREFIX."menu_acp SET title='Advertisement', rights='advertisement', uniqueid='advertisement_main', url='advertisement/advertisement', menu='content', status='active', parent='{$lastid}'");

    		return "Installed";
  	}

  	static function Uninstall() {
  		  global $db;

        // ads_client
        $db->query("DROP TABLE ".PREFIX."ads_client");

        // ads_ads
        $db->query("DROP TABLE ".PREFIX."ads_ads");

        $db->query("DELETE FROM ".PREFIX."menu_acp WHERE uniqueid='advertisement_main'");
        return "Uninstalled";
  	}
}