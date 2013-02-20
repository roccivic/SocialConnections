<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';

/**
 * This page is used by lecturers to view the
 * attendance of students in their groups
 */
class Page_viewStudentAttendance extends Page_selectLecturerGroup {
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Shows a page after a group has been selected
	 *
	 * @return void
	 */
	protected function display($gid) {
		$sid = 0;
		if (! empty($_REQUEST['sid'])) {
			$sid = intval($_REQUEST['sid']);
		}
		if ($sid > 0) {
			$this->showAttendance($gid, $sid);
		} else {
			$this->printStudentSelector($gid);
		}
	}
	/**
	 * Displays the attendance of a student
	 *
	 * @return void
	 */
	private function showAttendance($gid, $sid)
	{
		$this->addHtml(
			"<h3>" . __('View Attendance')
		 	. " &gt; " . $this->getStudentName($sid)
		 	. "</h3>"
		 );

        $html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">`';
        $html .= $this->getGroupName($gid);
        $html .= '`</li>';

        $attendance = $this->getStudentAttendance($gid, $sid);
        $data = array(
        	__('Lectures') => $attendance['lectures'],
        	__('Labs') => $attendance['labs'],
        	__('Total') => $attendance['overall']
        );

        foreach ($data as $key => $value) {
        	if ($key === __('Total')) {
	        	$html .= '<li data-theme="e">';
	    	} else {
	    		$html .= '<li>';
	    	}
	        $html .= $key;
	        $html .= '<span class="ui-li-count">';
	        $html .= round($value * 100) . ' %';
	        $html .= '</span>';
	        $html .= '</li>';
    	}

        $html .= '</ul>';

        $html .= '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Average attendance for all groups');
        $html .= '</li>';

        $attendance = $this->getTotalStudentAttendance($sid);
        $data = array(
        	__('Lectures') => $attendance['lectures'],
        	__('Labs') => $attendance['labs'],
        	__('Total') => $attendance['overall']
        );

        foreach ($data as $key => $value) {
        	if ($key === __('Total')) {
	        	$html .= '<li data-theme="e">';
	    	} else {
	    		$html .= '<li>';
	    	}
	        $html .= $key;
	        $html .= '<span class="ui-li-count">';
	        $html .= round($value * 100) . ' %';
	        $html .= '</span>';
	        $html .= '</li>';
    	}

        $html .= '</ul>';

        $this->addHtml($html);
	}
	/**
	 * Displays a list of student
	 *
	 * @return void
	 */
	private function printStudentSelector($gid)
	{
		$this->addHtml("<h3>" . __('View Attendance') . "</h3>");
		$html = $this->printStudentListHeader($gid);
		foreach ($this->getStudents($gid) as $key => $value) {
			$html .= $this->printStudentListItem($key, $gid, $value);
		}
		$html .= $this->printStudentListFooter();
		$this->addHtml($html);
	}
	/**
	 * Returns a list of students for a given group id
	 *
	 * @return array
	 */
	private function getStudents($gid) {
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
	/**
	 * Prints the header for the list of students
	 *
	 * @return void
	 */
	private function printStudentListHeader($gid)
	{
		$html  = '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= sprintf(
        	__('Group `%s`'),
        	$this->getGroupName($gid)
        );
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of students
	 *
	 * @return void
	 */
	private function printStudentListItem($sid, $gid, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=viewStudentAttendance&gid=%d&sid=%d">%s</a></li>',
	        	$gid,
	        	$sid,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of students
	 *
	 * @return void
	 */
	private function printStudentListFooter()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Returns the name of a group given its id
	 *
	 * @return string
	 */
	private function getGroupName($gid)
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
	 * Returns the full name of a student given his id
	 *
	 * @return string
	 */
	private function getStudentName($sid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `fname`, `lname` FROM `student` WHERE `id` = ?;"
		);
		$stmt->bind_param('i', $sid);
		$stmt->execute();
		$stmt->bind_result($fname, $lname);
		$stmt->fetch();
		$stmt->close();
		return $fname . ' ' . $lname;
	}
	/**
	 * Retrieves the attendance of a student
	 * for a group from the database
	 *
	 * @return array
	 */
	private function getStudentAttendance($gid, $sid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT (
				SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance` 
				ON `attendance`.`id` = `student_attendance`.`aid`
				WHERE `sid` = ?
				AND `gid` = ?
			) AS `overall` , (
				SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance`
				ON `attendance`.`id` = `student_attendance`.`aid`
				WHERE `sid` = ?
				AND `gid` = ?
				AND `isLecture` = 0
			) AS `labs` , (
				SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance`
				ON `attendance`.`id` = `student_attendance`.`aid`
				WHERE `sid` = ?
				AND `gid` = ?
				AND `isLecture` = 1
			) AS `lectures`;"
		);
		$stmt->bind_param(
			'iiiiii',
			$sid,
			$gid,
			$sid,
			$gid,
			$sid,
			$gid
		);
		$stmt->execute();
		$stmt->bind_result($overall, $labs, $lectures);
		$stmt->fetch();
		$stmt->close();

		return array(
			'overall' => $overall,
			'labs' => $labs,
			'lectures' => $lectures
		);
	}
	/**
	 * Retrieves the total attendance 
	 * of a student from the database
	 *
	 * @return array
	 */
	private function getTotalStudentAttendance($sid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT (
				SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance` 
				ON `attendance`.`id` = `student_attendance`.`aid`
				WHERE `sid` = ?
			) AS `overall` , (
				SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance`
				ON `attendance`.`id` = `student_attendance`.`aid`
				WHERE `sid` = ?
				AND `isLecture` = 0
			) AS `labs` , (
				SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance`
				ON `attendance`.`id` = `student_attendance`.`aid`
				WHERE `sid` = ?
				AND `isLecture` = 1
			) AS `lectures` "
		);
		$stmt->bind_param(
			'iii',
			$sid,
			$sid,
			$sid
		);
		$stmt->execute();
		$stmt->bind_result($overall, $labs, $lectures);
		$stmt->fetch();
		$stmt->close();

		return array(
			'overall' => $overall,
			'labs' => $labs,
			'lectures' => $lectures
		);
	}
}