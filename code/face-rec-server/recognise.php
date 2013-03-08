<?php

require_once 'config.php';

if (empty($_REQUEST['access_token'])
    || $_REQUEST['access_token'] !== Config::FACE_REC_SECRET
) {
    header("HTTP/1.0 401 Unauthorized");
    echo '<h1>Error 401: Unauthorized</h1>';
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
if (! $gid) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid group selected';
    die();
}

$image_path = scandir("face_cache/$session/");

array_shift($image_path); // .
array_shift($image_path); // ..

foreach ($image_path as $key => $value) {
    $image_path[$key] = "'face_cache/$session/$value'";
}

$output = trim(
    shell_exec(
        escapeshellcmd(
            "./face-rec-parent $gid " . implode(' ', $image_path)
        )
    )
);

$parsed = explode("\n", $output);
foreach ($parsed as $key => $line) {
    $parsed[$key] = explode(",", $line);
}

$fixed = array();
foreach ($parsed as $key => $value) {
    if (empty($fixed[$value[1]])
        || $fixed[$value[1]]["confidence"] > $value[2]
    ) {
        $fixed[$value[1]] = array(
            "file" => $value[0],
            "confidence" => $value[2]
        );
    }
}

$output = array();
foreach ($fixed as $key => $value) {
    $output[$value["file"]] = array(
        "sid" => $key,
        "confidence" => intval($value["confidence"])
    ); 
}

exit(json_encode($output));

?>