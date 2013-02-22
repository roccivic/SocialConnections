<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * This is the main app page
 */
class Page_Main extends Page {
	/**
	 * A user must be at least a student to view this page
	 */
	public static function getAccessLevel()
	{
		return User::STUDENT;
	}

	public function __construct()
	{
		parent::__construct();

		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		// Get the full name of the user
		// We display it later in the greeting
		if (User::isAdmin()) {
			$table = 'admin';
		} else if (User::isLecturer()) {
			$table = 'lecturer';
			$this->addLecturerNotifications($uid);
		} else {
			$table = 'student';
			$this->addStudentNotifications($uid);
		}
		$stmt = $db->prepare(
			"SELECT fname, lname FROM $table WHERE id = ?;"
		);
		$stmt->bind_param('i', $uid);
		$stmt->execute();
		$stmt->bind_result($fname, $lname);
		$stmt->fetch();
		$stmt->close();

		// Generate the HTML code for the page
		$html  = '<h3>';
		$html .= sprintf(
			__('Welcome, %1$s %2$s.'),
			$fname,
			$lname
		);
		$html .= '</h3>';
		$html .= '<p>';
		$html .= __('To start, please select an action from the menu.');
		$html .= '</p>';
		$html .= '<img src="images/cit.jpg" alt="' . __('Photo of main campus') . '" />';

		// Add the HTML code to the page
		$this->addHtml($html);
	}
	/**
	 * Add any lecturer notifications to the page
	 */
	private function addLecturerNotifications($lid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			'SELECT COUNT(*) FROM `student_attendance`
			INNER JOIN `attendance`
			ON `attendance`.`id` = `student_attendance`.`aid`
			INNER JOIN `group`
			ON `attendance`.`gid` = `group`.`id`
			INNER JOIN `moduleoffering`
			ON `moduleoffering`.`id` = `group`.`moid`
			INNER JOIN `moduleoffering_lecturer`
			ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
			WHERE `excuse_viewed` = 0
			AND `moduleoffering_lecturer`.`lid` = ?;'
		);
		$stmt->bind_param('i', $lid);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();

		if ($count > 0) {
			$notification  = '<a style="float: right;" data-mini="true" ';
			$notification .= 'data-inline="true" data-role="button" ';
			$notification .= 'href="?action=viewExcuses">';
			$notification .= __('View');
			$notification .= '</a>';
			$notification .= sprintf(
				_ngettext(
					'There is %d unviewed excuse from a student that has missed a class',
					'There are %d unviewed excuses from students that have missed a class',
					$count
				),
				$count
			);
			$notification .= '<div style="clear:both"></div>';
			$this->addNotification(
				'notice',
				$notification
			);
		}
	}
	/**
	 * Add any student notifications to the page
	 */
	private function addStudentNotifications($sid)
	{

	}
}

?>