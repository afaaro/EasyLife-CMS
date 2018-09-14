<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Setup {
	static function Install() {
		global $db;

		// post
		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."post (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			`category` int(10) unsigned NOT NULL,
			`text` text COLLATE utf8_unicode_ci NOT NULL,
			`embed` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			`image` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			`created` int(10) NOT NULL DEFAULT '0',
			`changed` int(10) NOT NULL DEFAULT '0',
			`author` int(10) NOT NULL DEFAULT '1',
			`promote` tinyint(4) NOT NULL DEFAULT '0',
			`status` tinyint(4) NOT NULL DEFAULT '0',
			`role` tinyint(4) NOT NULL DEFAULT '0',
			`fbauto` tinyint(4) NOT NULL DEFAULT '0',
			`views` int(10) NOT NULL DEFAULT '0',
			`viewed` int(10) NOT NULL DEFAULT '0',
			`options` longtext COLLATE utf8_unicode_ci NOT NULL,
			`type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

		// post_param
		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."post_param (
			`id` bigint(32) NOT NULL AUTO_INCREMENT,
			`post_id` bigint(32) NOT NULL,
			`param` text COLLATE utf8_unicode_ci NOT NULL,
			`value` longtext COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

		// fusion_post_alias
		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."post_images (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`item` int(11) NOT NULL DEFAULT '0',
			`image` text NOT NULL,
			`author` int(11) NOT NULL,
			`date` datetime NOT NULL,
			PRIMARY KEY (`id`),
			KEY `item` (`item`)
        ) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

		// post_categories
		$db->query("CREATE TABLE IF NOT EXISTS ".PREFIX."post_categories (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			`name` varchar(255) NOT NULL,
			`parent` int(10) NOT NULL DEFAULT '0',
			`controller` varchar(255) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `name` (`name`),
			KEY `controller` (`controller`),
			KEY `parent` (`parent`)
        ) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

		$data = $db->query("SELECT id FROM ".PREFIX."menu_acp WHERE title='Content' AND parent='0'")->row;
		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Post', rights='post', uniqueid='post_main', menu='content', status='active', parent='".$data['id']."'");
		$lastid = $db->getLastId();

		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Category', rights='post', uniqueid='post_main', url='post/category', menu='content', status='active', parent='{$lastid}'");
		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='News', rights='post', uniqueid='post_main', url='post/news', menu='content', status='active', parent='{$lastid}'");
		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Video', rights='post', uniqueid='post_main', url='post/video', menu='content', status='active', parent='{$lastid}'");
		$db->query("INSERT INTO ".PREFIX."menu_acp SET title='Audio', rights='post', uniqueid='post_main', url='post/audio', menu='content', status='active', parent='{$lastid}'");

		return "Installed";
	}

	static function Uninstall() {
		global $db;

        // post
        $db->query("DROP TABLE ".PREFIX."post");
        // post_categories
        $db->query("DROP TABLE ".PREFIX."post_categories");
        // post_param
        $db->query("DROP TABLE ".PREFIX."post_param");
        // post_alias
        $db->query("DROP TABLE ".PREFIX."post_alias");
        // post_images
        $db->query("DROP TABLE ".PREFIX."post_images");

        $db->query("DELETE FROM ".PREFIX."menu_acp WHERE uniqueid='post_main'");
        
        return "Uninstalled";
	}
}