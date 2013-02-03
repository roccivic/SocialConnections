<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);

// Disable error reporting, it would look pretty bad if we
// start throwing errors while displaying an error message
error_reporting(0);

// Include configuration file
require_once 'config.php';

if (! empty($_REQUEST['error'])) {
	$error = 'Error ' . htmlspecialchars($_REQUEST['error']);
} else {
	$error = 'Unknown error';
}

echo '<h1>' . $error . '</h1>';
echo '<a href="' . Config::URL . '">Go back to home page</a>';

?>