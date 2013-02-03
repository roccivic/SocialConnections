<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);
// Include configuration file
require_once 'config.php';
// Error reporting is set in configuration
if (Config::DISPLAY_ERRORS) {
	error_reporting(E_ALL | E_STRICT);
} else {
	error_reporting(0);
}
// Initialise session management
session_name('SOCIALCONNECTIONS');
session_start();
// Include all other necessary libraries
require_once 'classes/Db.class.php';
require_once 'classes/User.class.php';
require_once 'classes/Auth.class.php';
require_once 'libs/gettext/gettext.inc';
require_once 'classes/Lang.class.php';
require_once 'classes/PageFactory.class.php';
// Process remote login
Auth::checkToken();
// Determine access level for current user
User::init();
// Initialise i18n
Lang::setLang();
// Serve the appropriate page
$action = ! empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$page = PageFactory::getInstance($action);
$page->sendHttpHeaders();
$page->displayPage();

?>