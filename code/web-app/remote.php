<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);
// Include configuration file
require_once 'config.php';
// Never report any errors, they will corrupt the response
// If you are debugging, check for errors in the web server's log file
error_reporting(0);
// Include all other necessary libraries
require_once 'classes/Db.class.php';
require_once 'classes/Auth.class.php';
// Get incoming parameters
$username = ! empty($_POST['username']) ? $_POST['username'] : '';
$password = ! empty($_POST['password']) ? $_POST['password'] : '';

if (! Auth::speedLimitOk()) {
	// Speed limit exceeded
	header("HTTP/1.0 429 Too Many Requests");
	echo '<h1>Error 429: Too Many Requests</h1>';
} else if (Auth::login($username, $password)) {
	// Login was successful, get access level from db
	$db = Db::getLink();
	$stmt = $db->prepare("SELECT type FROM user WHERE uid = ?;");
	// The session is not persistent in this script
	// since we never called session_start(). This is
	// a conscious decision, as the authentication
	// will be done later via tokens and setting up
	// a session will just be a waste of resources.
	$stmt->bind_param('s', $_SESSION['uid']);
	$stmt->execute();
	$stmt->bind_result($accessLevel);
	$stmt->fetch();
	$stmt->close();
	// Reply with a token and access level
	echo Auth::getToken();
	echo "\n";
	echo $accessLevel;
} else {
	// Login failed
	header("HTTP/1.0 401 Unauthorized");
	echo '<h1>Error 401: Unauthorized</h1>';
}

?>