<?php

class Data {
	/**
	 * Returns the name of a group given its id
	 *
	 * @static
	 * @return string
	 */
	public static function getGroupNameStatic($gid)
	{
		$data = new Data();
		return $data->getGroupName($gid);
	}
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
	/**
	 * Returns an array of students for a given department
	 *
	 * @return array
	 */
	protected function getStudentsInDepartment($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `cid` IN (
				SELECT id FROM `class` WHERE `did`=?
			)
			ORDER BY `fname` ASC, `lname` ASC;"
		);
		$stmt->bind_param("i",$did);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}
	/**
	 * Returns an array of students in a group
	 *
	 * @static
	 * @return array
	 */
	public static function getStudentsInGroupStatic($gid)
	{
		$data = new Data();
		return $data->getStudentsInGroup($gid);
	}
	/**
	 * Returns an array of students in a group
	 *
	 * @return array
	 */
	protected function getStudentsInGroup($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `id` IN (
				SELECT sid FROM `group_student` WHERE `gid`=?
			)
			ORDER BY `fname` ASC, `lname` ASC;"
		);
		$stmt->bind_param("i",$gid);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}
	/**
	 * Returns an array of students that are not in the group
	 *
	 * @return array
	 */
	protected function getStudentsNotInGroup($did, $gid)
	{
		$sArray = $this->getStudentsInGroup($gid);
		$success = true;
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `cid` IN (
				SELECT id FROM `class` WHERE `did`=?
			)
			ORDER BY `fname` ASC, `lname` ASC;"
		);
		$stmt->bind_param("i",$did);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			foreach ($sArray as $key => $value) {
				if ($key == $id) {
					$success = false;
				}
			}
			if ($success == true) {
				$arr[$id] = $fname . ' ' . $lname;
			}
			$success = true;
		}
		return $arr;
	}
	/**
	 * Returns an array of students's details
	 *
	 * @return array
	 */
	protected function getStudentsInClass($cid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `cid` = ?"
		);
		$stmt->bind_param('i', $cid);
		$stmt->execute();
		$stmt->bind_result($id, $fname, $lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		$stmt->close();
		return $arr;
	}
}

?>