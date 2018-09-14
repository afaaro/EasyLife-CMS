<?php

class audioController {
	function index() {
		global $db;

		opentable("Audio");

		closetable();
	}
}