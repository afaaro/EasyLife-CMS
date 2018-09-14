<?php

//Mod-rewrite querystring rebuild map
$map['social']		= array("op","engine");
$map['logout']		= array("op");
$map['info']		= array("op","uid");
$map['register']	= array("op");
$map['activate']	= array("op","uid","code");
$map['lostpass']	= array("op");
$map['repass']		= array("op","uid","code","subop");
$map['profile']		= array("op");
$map['password']	= array("op");