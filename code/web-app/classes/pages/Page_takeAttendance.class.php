<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';
/**
 * This page is used by lecturers to take attendance
 */
class Page_takeAttendance extends Page_selectLecturerGroup {
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
		$dbStudents = $this->getStudents($gid);
		$isLecture = ! empty($_REQUEST['isLecture']) ? 1 : 0;
		$date = ! empty($_REQUEST['date']) ? $_REQUEST['date'] : date("Y-m-d");
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
	 * Displays the form for taking attendance
	 *
	 * @return void
	 */
	private function printForm($gid, $date, $time, $students, $dbStudents) {
		if (strlen($this->getGroupName($gid))) {
			$this->addHtml(
				'<h3>'
				. sprintf(
					 __('Group `%s`'),
					 $this->getGroupName($gid)
				)
				. '</h3>'
			);
			if (count($dbStudents)) {
				$html  = '<form action="" method="post">';
				$html .= '<input type="hidden" name="process" value="1" />';
				$html .= '<div data-role="fieldcontain">';
				$html .= '<label for="date">' . __('Date:') . '</label>';
				$html .= '<input type="date" data-role="datebox" ';
				$html .= 'data-options=\'{"mode":"calbox", "useNewStyle":true}\' id="date" ';
				$html .= 'name="date" value="' . htmlspecialchars(
					date("Y-m-d", strtotime($date))
				) . '" />';
				$html .= '</div>';
				$html .= '<div data-role="fieldcontain">';
				$html .= '<label for="time">' . __('Time:') . '</label>';
				$html .= '<input type="time" data-role="datebox" ';
				$html .= 'data-options=\'{"mode":"timebox", "useNewStyle":true}\' id="time" ';
				$html .= 'name="time" value="' . htmlspecialchars(
					date("H:i", strtotime($time))
				) . '" />';
				$html .= '</div>';

				$html .= '<div data-role="fieldcontain">';
				$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
				$html .= '<legend>' . __('Type') . ':</legend>';
				$html .= '<input type="radio" name="isLecture"';
				$html .= ' id="radio-1" value="1" checked="checked" />';
				$html .= '<label for="radio-1">' . __('Lecture') . '</label>';
				$html .= '<input type="radio" name="isLecture"';
				$html .= ' id="radio-2" value="0" />';
				$html .= '<label for="radio-2">' . __('Lab') . '</label>';
				$html .= '</fieldset>';
				$html .= '</div>';

				$html .= '<div data-role="fieldcontain">';
				$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
				$html .= '<legend>&nbsp;</legend>';
				$html .= '<a style="width: 49%" data-role="button"';
				$html .= ' data-theme="e" id="checkall">';
				$html .= __('Check All') . '</a>';
				$html .= '<a style="width: 49%" data-role="button"';
				$html .= ' data-theme="e" id="uncheckall">';
				$html .= __('Uncheck All') . '</a>';
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
				$this->groupSelector();
			}
		} else {
			$this->addNotification(
				'error',
				__('Invalid group selected')
			);
			$this->groupSelector();
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
				$present = in_array($key, $students);
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
}

?>