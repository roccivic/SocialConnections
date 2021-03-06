<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);
// Include configuration file
require_once 'config.php';

if (empty($_REQUEST['access_token'])
    || $_REQUEST['access_token'] !== Config::FACE_REC_SECRET
) {
    header("HTTP/1.0 401 Unauthorized");
    die("<h1>Unauthorized</h1>");
}

// Include all other necessary libraries
require_once 'classes/Db.class.php';

$db = Db::getLink();
$stmt = $db->prepare(
	"SELECT `gid`, GROUP_CONCAT(`sid`)
	FROM `group_student`
	GROUP BY `gid`
	ORDER BY `gid` ASC;"
);
$stmt->execute();
$stmt->bind_result($gid, $sids);
echo "\n";
while ($stmt->fetch()) {
	echo "$gid,$sids\n";
}
$stmt->close();

?>