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
	const FACE_REC_SECRET = 'orgL2CDpbVL3Rrec5BUn0X0FAle4F1DE505Cv7SV';

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

	//Twitter access parameters
	const TWITTER_CONSUMER_KEY = 'oBYtTlMTy6ChzMBz7WFVA';
	const TWITTER_CONSUMER_SECRET = 'EF442Jzj2sn8EZe0tjZBD4lsHuL8ucxtt6IaQKyAiw';
	const TWITTER_CALLBACK = '?action=twitter&callback=1';
	const TWITTER_HASHTAG = 'aStrInGthAtNoOneIsSupPosEdtOuSe';

	//Dropbox access parameters
	const DROPBOX_APP_KEY = 'htitu3nxdpjvl00';
	const DROPBOX_APP_SECRET = 'mlzzjt6j70lauw8';
	const DROPBOX_ACCESS_TYPE = 'app_folder';
}

?>
