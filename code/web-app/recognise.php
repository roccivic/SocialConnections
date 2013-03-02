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
// Initialise session management
session_name('SOCIALCONNECTIONS');
session_start();
// Include all other necessary libraries
require_once 'classes/Db.class.php';
require_once 'classes/User.class.php';
require_once 'classes/Auth.class.php';
require_once 'libs/gettext/gettext.inc';
require_once 'classes/Lang.class.php';
// Determine access level for current user
User::init();
// Initialise i18n
Lang::setLang();

if (! User::isLecturer()) {
	header("HTTP/1.0 401 Unauthorized");
	echo '<h1>Error 401: Unauthorized</h1>You do not have access to this page';
    die();
}

if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid request method';
    die();
}

$session = ! empty($_REQUEST["session"]) ? trim($_REQUEST["session"]) : '';
if (empty($session) || ! is_readable('face_cache/' . $session)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid session token';
    die();
}

$gid = ! empty($_REQUEST["gid"]) ? intval($_REQUEST["gid"]) : 0;
if (! strlen(getGroupName($gid))) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid group selected';
    die();
}

$image = ! empty($_REQUEST["image"]) ? basename($_REQUEST["image"]) : '';
if (empty($image) || ! is_readable('face_cache/' . $session . '/' . $image)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid image selected';
    die();
}

$ch = curl_init();
$data = array(
	'session' => $session,
	'gid' => $gid,
	'image' => $image
);
curl_setopt($ch, CURLOPT_URL, Config::FACE_REC_URL . 'recognise.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($code === 200) {
	echo $response;
} else {
	header("HTTP/1.0 500 Internal Server Error");
    echo '<h1>Error 500: Internal Server Error</h1>'
    . 'facial recognition failed';
    echo $response;
}
curl_close($ch);

function getGroupName($gid)
{
	$db = Db::getLink();
	$stmt = $db->prepare(
		"SELECT `name` FROM `group` WHERE `id` = ?;"
	);
	$stmt->bind_param('i', $gid);
	$stmt->execute();
	$stmt->bind_result($name);
	$stmt->fetch();
	$stmt->close();
	return $name;
}

?>