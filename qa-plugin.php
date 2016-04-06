<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-plugin/qq-login/qa-plugin.php
	Description: Initiates Facebook login plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

/*
	Plugin Name: QQ Login
	Plugin URI: https://github.com/pjkui/q2a-qq-login
	Plugin Description: Allows users to log in via QQ
	Plugin Version: 1.0.0
	Plugin Date: 2016-03-18
	Plugin Author: QuinnPan
	Plugin Author URI: http://www.pjkui.com/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Minimum PHP Version: 5
	Plugin Update Check URI:
*/


if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

if (!defined('QQ_LOGIN_DIR')) {
	define('QQ_LOGIN_DIR', dirname(__FILE__));
}

if (!defined('QQ_LOGIN_DIR_NAME')) {
	define('QQ_LOGIN_DIR_NAME', basename(dirname(__FILE__)));
}

if (!defined('QQ_LOGIN_ASSETS')) {
	define('QQ_LOGIN_ASSETS', (dirname(__FILE__)).'/assets');
}

// login modules don't work with external user integration
if (!QA_FINAL_EXTERNAL_USERS) {

	qa_register_plugin_module('login', 'qa-qq-login.php', 'qa_qq_login', 'QQ Login');
	qa_register_plugin_module('page', 'qa-qq-login-page.php', 'qa_qq_login_page', 'QQ Login Page');
	qa_register_plugin_layer('qa-qq-layer.php', 'QQ Login Layer');
}
