<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Main configuration file
 */
abstract class Config {
	// Base url for the web app
	// MUST END WITH A FORWARD SLASH!
	const URL = 'http://109.255.78.105/socialconnections/';
	//const URL = 'http://socialconnections.placella.com/';
	const FACE_REC_URL = 'http://109.255.78.105/facerec/';

	// 'Country/City'
	const TIMEZONE = 'GB-Eire';

	// Database access parameters
	const DB_HOST   = 'localhost';
	const DB_USER   = 'root';
	const DB_PWD    = '123456';
	const DB_DBNAME = 'socialconnections';

	// Set to "true" in development environment
	// Set to "false" in production environment
	const DISPLAY_ERRORS = true;

	// Web app version
	const VERSION = '1.0';
}

?>