<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/pages/abstract/Page_selectStudentGroup.class.php';

/**
 * This page is used by students to view their attendance
 */
class Page_checkAttendance extends Page_selectStudentGroup {
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
		if ($this->isStudentIngroup($_SESSION['uid'], $gid)) {
			$this->showAttendance($gid);
		} else {
			$this->addNotification(
				'error',
				__('Invalid group selected')
			);
			$this->groupSelector();
		}
	}
	/**
	 * Displays the attendance of a student
	 *
	 * @return void
	 */
	private function showAttendance($gid)
	{
		$sid = $_SESSION['uid'];
		$this->addHtml(
			"<h3>" . __('View Attendance') . "</h3>"
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
    		$html .= '<li>';
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
	 * Puts some HTML code into the footer of the page
	 *
	 * @return @string
	 */
    protected function getExtraFooter($sid) {
    	$html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
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
	    	$html .= '<li>';
	        $html .= $key;
	        $html .= '<span class="ui-li-count">';
	        $html .= round($value * 100) . ' %';
	        $html .= '</span>';
	        $html .= '</li>';
    	}

        $html .= '</ul>';
    	return $html;
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
	private function isStudentIngroup($sid, $gid)
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
		return $result;
	}
}