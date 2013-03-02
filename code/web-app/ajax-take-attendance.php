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
    ajax_response(
		false,
		__('You do not have access to this page')
	);
}

$data = array();
if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
    ajax_response(
		false,
		__('Invalid request method')
	);
}

$session = ! empty($_REQUEST["session"]) ? trim($_REQUEST["session"]) : '';

if (empty($session) || ! is_readable('face_cache/' . $session)) {
	ajax_response(
		false,
		__('Invalid session token')
	);
}

$gid = ! empty($_REQUEST["gid"]) ? intval($_REQUEST["gid"]) : 0;
$date = ! empty($_REQUEST["date"]) ? trim($_REQUEST["date"]) : '';
$time = ! empty($_REQUEST["time"]) ? trim($_REQUEST["time"]) : '';
$isLecture = ! empty($_REQUEST["isLecture"]) ? intval($_REQUEST["isLecture"]) : 0;

if (! strlen(getGroupName($gid))) {
	ajax_response(
		false,
		__('Invalid group selected')
	);
}

$dbStudents = getStudents($gid);

$students = array();
if (! empty($_REQUEST['students']) && is_array($_REQUEST['students'])) {
	$students = $_REQUEST['students'];
}
$images = array();
if (! empty($_REQUEST['images']) && is_array($_REQUEST['images'])) {
	$images = $_REQUEST['images'];
	foreach ($images as $index => $image) {
		$images[$index] = basename($image);
	}
}

if (validate($date, $time)) {
	if (save($gid, $date, $time, $students, $isLecture, $dbStudents)) {
		if (forward($gid, $students, $images, $session)) {
			ajax_response(
				true,
				__('Successfully saved the attendance data.')
			);
		} else {
			ajax_response(
				true,
				__('Successfully saved the attendance data.')
				. '<br />'
				. __('(But failed to forward the facial recognition data to the server)')
			);
		}
	} else {
		ajax_response(
			false,
			__('Database error: Could not save the data.')
		);
	}
} else {
	ajax_response(
		false,
		__('The values in the form were invalid. Please try again.')
	);
}

function ajax_response($success, $message) {
	header("Content-Type: application/json; charset=UTF-8");
	echo json_encode(
		array(
			'success' => $success,
			'message' => $message
		)
	);
	die();
}

function validate($date, $time)
{
	$success = true;
	if (! preg_match('@^\d\d+-\d\d?-\d\d?$@', $date)) {
		$this->addNotification(
			'warning',
			__('Invalid date format')
		);
		$success = false;
	}
	if (! preg_match('@^\d\d?:\d\d?$@', $time)) {
		$this->addNotification(
			'warning',
			__('Invalid time format')
		);
		$success = false;
	}
	return $success;
}

function save($gid, $date, $time, $students, $isLecture, $dbStudents)
{
	$db = Db::getLink();
	$stmt = $db->prepare(
		"INSERT INTO `attendance`
		(`gid`, `isLecture`, `timestamp`)
		VALUES (?,?,?);"
	);
	$timestamp = date(
		'Y-m-d H:i:s',
		strtotime($date . ' ' .$time)
	);
	$stmt->bind_param('iis', $gid, $isLecture, $timestamp);
	$success = $stmt->execute();
	$aid = $stmt->insert_id;

	if ($success) {
		foreach ($dbStudents as $key => $value) {
			$present = 0;
			foreach ($students as $stid) {
				if ($stid === $key) {
					$present = 1;
					break;
				}
			}
			$stmt = $db->prepare(
				"INSERT INTO `student_attendance`
				(`aid`, `sid`, `present`)
				VALUES (?,?,?);"
			);
			$stmt->bind_param('iii', $aid, $key, $present);
			;
			$stmt->execute();
			$stmt->close();
		}
	}
	return $success;
}

function getStudents($gid)
{
	$arr = array();
	$db = Db::getLink();
	$stmt = $db->prepare(
		"SELECT `id`, `fname`, `lname` FROM `student` INNER JOIN `group_student` ON `sid` = `id` WHERE `gid` = ? ORDER BY `lname`, `fname`"
	);
	$stmt->bind_param('i', $gid);
	$stmt->execute();
	$stmt->bind_result($id, $fname, $lname);
	while ($stmt->fetch()) {
		$arr[$id] = $lname . ' ' . $fname;
	}
	return $arr;
}

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

function forward($gid, $students, $images, $session)
{
	$ch = curl_init();
	$data = array(
		'gid' => $gid,
	    'students' => json_encode($students),
	    'images' => json_encode($images),
	    'session' => $session
	);
	curl_setopt($ch, CURLOPT_URL, Config::FACE_REC_URL . 'save.php');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $code === 200;
}
?>