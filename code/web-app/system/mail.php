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
// Fix timezone
date_default_timezone_set(Config::TIMEZONE);
// Include all other necessary libraries
require_once 'classes/Db.class.php';
require_once 'libs/gettext/gettext.inc';
require_once 'classes/Lang.class.php';
// Initialise i18n
Lang::setLang();




// do something awesome



?>