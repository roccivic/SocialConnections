<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';
/**
 * This page is used by lecturers to take attendance
 */
class Page_takeAttendance extends Page_selectLecturerGroup {
	public static function getAccessLevel()
	{
		return User::LECTURER;
	}

	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_selectLecturerGroup superclass
	 * when the user has selected a group
	 *
	 * @return void
	 */
	public function display($gid) 
	{
		$html  = '<h3>';
		$html .= sprintf(
			 __('Take attendance for group `%s`'),
			 $gid
		);
		$html .= '</h3>';

		$dbStudents = $this->getStudents($gid);
		$isLecture = ! empty($_REQUEST['isLecture']) ? 1 : 0;
		$date = ! empty($_REQUEST['date']) ? $_REQUEST['date'] : date("m/d/y");
		$time = ! empty($_REQUEST['time']) ? $_REQUEST['time'] : date("H:i");
		$students = array();
		if (! empty($_REQUEST['students']) && is_array($_REQUEST['students'])) {
			$students = $_REQUEST['students'];
		}

		if (! empty($_REQUEST['process'])) {
			if ($this->validate($date, $time)) {
				if ($this->save($gid, $date, $time, $students, $isLecture, $dbStudents)) {
					$this->addNotification(
						'notice',
						__('Successfully saved the attendance data.')
					);
					$this->groupSelector();
				} else {
					$this->addNotification(
						'error',
						__('Database error: Could not save the data.')
					);
					$this->printForm($gid, $date, $time, $students, $dbStudents);
				}
			} else {
				$this->addNotification(
					'error',
					__('The values in the form were invalid. Please try again.')
				);
				$this->printForm($gid, $date, $time, $students, $dbStudents);
			}
		} else {
			$this->printForm($gid, $date, $time, $students, $dbStudents);
		}
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
			"SELECT `username`, `fname`, `lname` FROM `student` INNER JOIN `group_student` ON `sid` = `id` WHERE `gid` = ? ORDER BY `lname`, `fname`"
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
	 * Displays the form for taking attendance
	 *
	 * @return void
	 */
	private function printForm($gid, $date, $time, $students, $dbStudents) {
		if (count($dbStudents)) {
			$html  = '<form action="" method="post">';
			$html .= '<input type="hidden" name="process" value="1" />';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="date">' . __('Date:') . '</label>';
			$html .= '<input id="date" name="date" value="' . htmlspecialchars(date("m/d/y", strtotime($date))) . '" />';
			$html .= '</div>';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="time">' . __('Time:') . '</label>';
			$html .= '<input id="time" name="time" value="' . htmlspecialchars(date("H:i", strtotime($time))) . '" />';
			$html .= '</div>';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<fieldset data-role="controlgroup">';
			$html .= '<legend>' . __('Type') . ':</legend>';

			$html .= '<input type="radio" name="isLecture"';
			$html .= ' id="radio-1" value="1" checked="checked" />';
			$html .= '<label for="radio-1">' . __('Lecture') . '</label>';

			$html .= '<input type="radio" name="isLecture"';
			$html .= ' id="radio-2" value="0" />';
			$html .= '<label for="radio-2">' . __('Lab') . '</label>';

			$html .= '</fieldset>';
			$html .= '</div>';
			$html .= '<div id="checkboxes1" data-role="fieldcontain">';
			$html .= '<fieldset data-role="controlgroup" data-type="vertical">';
			$html .= '<legend>Students:</legend>';
	        $i = 0;
	        foreach ($dbStudents as $id => $name) {
	        	$i++;
		        $html .= '<input id="checkbox' . $i . '" name="students[]" type="checkbox" value="' . $id . '";';
		        foreach ($students as $value) {
		        	if ($value === $id) {
		        		$html .= ' checked="checked"';
		        		break;
		        	}
		        }
		        $html .= ' />';
		        $html .= '<label for="checkbox' . $i . '">' . htmlspecialchars($name) . '</label>';
		    }
			$html .= '</fieldset>';
	        $html .= '</div>';
	        $html .= '<input type="submit" data-theme="b" value="' . __('Save') . '" />';
			$html .= '</form>';
			$this->addHtml($html);
		} else {
			$this->addNotification(
				'error',
				__('There are no students assigned to this group')
			);
		}
	}
	/**
	 * Validates the form for taking attendance
	 *
	 * @return bool
	 */
	private function validate($date, $time)
	{
		$success = true;
		if (! preg_match('@^\d\d?/\d\d?/\d\d?$@', $date)) {
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
	/**
	 * Saves attendance data to the db
	 *
	 * @return bool
	 */
	private function save($gid, $date, $time, $students, $isLecture, $dbStudents)
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
				$stmt->bind_param('iii', $aid, $this->getStudentId($key), $present);
				;
				$stmt->execute();
				$stmt->close();
			}
		}
		return $success;
	}
	/**
	 * Returns a student id given a student's username
	 *
	 * @return int
	 */
	private function getStudentId($username)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id` FROM `student` WHERE `username` = ?;"
		);
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		$stmt->close();
		return $id;
	}
}

?>