<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Setup {

	static function Install() {
		global $db;

		// post
		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."videos (
			`id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
			`uniq_id` varchar(10) NOT NULL DEFAULT '',
			`title` varchar(255) NOT NULL DEFAULT '',
			`name` varchar(255) NOT NULL DEFAULT '',
			`description` text NOT NULL,
			`yt_id` varchar(50) NOT NULL DEFAULT '',
			`yt_length` mediumint(5) unsigned NOT NULL DEFAULT '0',
			`yt_thumb` varchar(255) NOT NULL DEFAULT '',
			`yt_views` int(10) NOT NULL DEFAULT '0',
			`category` varchar(30) NOT NULL DEFAULT '',
			`author` int(10) unsigned NOT NULL DEFAULT '0',
			`submitted` varchar(100) NOT NULL DEFAULT '',
			`lastwatched` int(10) unsigned NOT NULL DEFAULT '0',
			`added` int(10) unsigned NOT NULL DEFAULT '0',
			`views` int(9) NOT NULL DEFAULT '0',
			`url_flv` varchar(255) NOT NULL DEFAULT '',
			`source_id` smallint(2) unsigned NOT NULL DEFAULT '0',
			`last_check` int(10) unsigned NOT NULL DEFAULT '0',
			`status` tinyint(1) unsigned NOT NULL DEFAULT '0',
			`featured` enum('0','1') NOT NULL DEFAULT '0',
			`restricted` enum('0','1') NOT NULL DEFAULT '0',
			`allow_comments` enum('0','1') NOT NULL DEFAULT '1',
			`allow_embedding` enum('0','1') NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			KEY `uniq_id` (`uniq_id`),
			KEY `added` (`added`),
			KEY `yt_id` (`yt_id`),
			KEY `featured` (`featured`),
			KEY `author` (`author`),
			FULLTEXT KEY `fulltext_index` (`title`)
        ) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

		$data = $db->query("SELECT id FROM ".PREFIX."menu_acp WHERE title='Content' AND parent='0'")->row;
		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Videos', rights='videos', uniqueid='videos_main', menu='content', status='active', parent='".$data['id']."'");
		$lastid = $db->getLastId();

		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Category', rights='videos', uniqueid='videos_main', url='videos/category', menu='content', status='active', parent='{$lastid}'");
		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Video', rights='videos', uniqueid='videos_main', url='videos/video', menu='content', status='active', parent='{$lastid}'");

		return "Installed";
	}

	static function Uninstall() {
		global $db;

        // post
        $db->query("DROP TABLE ".PREFIX."videos");
        // // post_categories
        // $db->query("DROP TABLE ".PREFIX."videos_categories");

        $db->query("DELETE FROM ".PREFIX."menu_acp WHERE uniqueid='videos_main'");
        
        return "Uninstalled";
	}
}