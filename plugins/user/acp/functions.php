<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function getUser($user_id) {
	global $db;
	$query = $db->query("SELECT *, (SELECT ug.title FROM `" .PREFIX . "user_group` ug WHERE ug.user_level = u.user_level) AS user_group FROM `" . PREFIX . "user` u WHERE u.id = '" . (int)$user_id . "'");

	return $query->row;
}

function getUsers($data = array()) {
	global $db;

	$sql = "SELECT * FROM `" . PREFIX . "users`";

	$sort_data = array(
		'username',
		'status',
		'regdate'
	);

	if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		$sql .= " ORDER BY " . $data['sort'];
	} else {
		$sql .= " ORDER BY username";
	}

	if (isset($data['order']) && ($data['order'] == 'DESC')) {
		$sql .= " DESC";
	} else {
		$sql .= " ASC";
	}

	if (isset($data['start']) || isset($data['limit'])) {
		if ($data['start'] < 0) {
			$data['start'] = 0;
		}

		if ($data['limit'] < 1) {
			$data['limit'] = 20;
		}

		$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
	}
	$query = $db->query($sql);

	return $query->rows;
}

function getTotalUsers() {
	global $db;

	$query = $db->query("SELECT COUNT(*) AS total FROM `" . PREFIX . "users`");

	return $query->row['total'];
}



function getUserGroup($user_group_id) {
	global $db;
	$query = $db->query("SELECT DISTINCT * FROM " . PREFIX . "user_group WHERE user_level = '" . (int)$user_group_id . "'");

	$user_group = array(
		'id'			=> $query->row['id'],
		'title'       	=> $query->row['title'],
		'label'		 	=> $query->row['label'],
		'user_level'	=> $query->row['user_level'],
		'permission' 	=> json_decode($query->row['permission'], true)
	);

	return $user_group;
}