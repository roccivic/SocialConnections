<?php

require_once 'config.php';

if (empty($_REQUEST['access_token'])
    || $_REQUEST['access_token'] !== Config::FACE_REC_SECRET
) {
    header("HTTP/1.0 401 Unauthorized");
    echo '<h1>Error 401: Unauthorized</h1>';
    die();
}

$data = array();
if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid request method';
    die();
}

$session = ! empty($_REQUEST["session"]) ? $_REQUEST["session"] : '';
if (empty($session) || ! is_readable('face_cache/' . $session)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>no session token';
    die();
}

$gid = ! empty($_REQUEST["gid"]) ? intval($_REQUEST["gid"]) : 0;
if (empty($gid)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid group';
    die();
}


$students = array();
if (! empty($_REQUEST['students']) && $_REQUEST['students']) {
	$tmp = json_decode($_REQUEST['students']);
	if (is_array($tmp)) {
		$students = $tmp;
	}
}
$images = array();
if (! empty($_REQUEST['images']) && $_REQUEST['images']) {
	$tmp = json_decode($_REQUEST['images']);
	if (is_array($tmp)) {
		$images = $tmp;
	}
}

foreach ($students as $key => $sid) {
	if (isset($images[$key])
		&& is_readable('face_cache/' . $session . '/' . $images[$key])
	) {
		@mkdir('faces/' . $sid);
		@rename(
			'face_cache/' . $session . '/' . $images[$key],
			'faces/' . $sid . '/' . uniqid(rand(), true) . '.jpg'
		);
	}
}

?>
