<?php

class Data {
	/**
	 * Returns the name of a group given its id
	 *
	 * @return string
	 */
	protected function getGroupName($gid)
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
	/**
	 * Checks if a student belongs to a group
	 *
	 * @return string
	 */
	protected function isStudentIngroup($sid, $gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT COUNT(*)
			FROM `group_student`
			WHERE `gid` = ?
			AND `sid` = ?;"
		);
		$stmt->bind_param('ii', $gid, $sid);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		return ! empty($result);
	}
}

?>