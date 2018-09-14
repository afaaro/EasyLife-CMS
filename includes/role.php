<?php

class Role {
	protected $permissions;

	protected function __construct() {
		$this->permissions = array();
	}
}