<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);
// Include configuration file
require_once '../config.php';
// Error reporting is set in configuration
if (Config::DISPLAY_ERRORS) {
    error_reporting(E_ALL | E_STRICT);
} else {
    error_reporting(0);
}
// Include all other necessary libraries
require_once '../classes/Db.class.php';

$db = Db::getLink();
for ($gid=1;$gid<=16;$gid++) {
    $students = getStudents($gid);
    $ratios = array();
    foreach ($students as $sid) {
        $ratios[$sid] = mt_rand(50, 90);
    }
    for ($i=0;$i<=1000;$i++) {
        $lecture = mt_rand(0, 1);
        $timestamp = time() - mt_rand(0, 3600 * 24 * 365 * 3); // 3 years
        $db->query(
            "INSERT INTO `attendance` (`gid`, `isLecture`, `timestamp`)
            VALUES ($gid, $lecture, $timestamp)"
        );
        $aid = $db->insert_id;
        foreach ($students as $sid) {
            $try = mt_rand(0, 100);
            if ($try < $ratios[$sid]) {
                $present = 1;
            } else {
                $present = 0;
            }
            $db->query(
                "INSERT INTO `student_attendance` (`sid`, `aid`, `present`)
                VALUES ($sid, $aid, $present)"
            );
        }
    }
}

function getStudents($gid)
{
    $db = Db::getLink();
    $stmt = $db->prepare(
        'SELECT `sid` FROM `group_student`
        WHERE `gid` = ?'
    );
    $stmt->bind_param('i', $gid);
    $stmt->execute();
    $stmt->bind_result($sid);
    $arr = array();
    while ($stmt->fetch()) {
        $arr[] = $sid;
    }
    return $arr;
}

?>