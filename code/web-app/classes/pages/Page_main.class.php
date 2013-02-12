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
	public function getAccessLevel()
	{
		return User::STUDENT;
	}

	public function __construct()
	{
		parent::__construct();

		// Get the full name of the user
		// We display it later in the greeting
		$db = Db::getLink();
		if (User::isAdmin()) {
			$table = 'admin';
		} else if (User::isLecturer()) {
			$table = 'lecturer';
		} else {
			$table = 'student';
		}
		$stmt = $db->prepare(
			"SELECT fname, lname FROM $table WHERE id = ?;"
		);
		$stmt->bind_param('s', $_SESSION['uid']);
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
		$this->addHtml($html . Auth::getToken());

	}
}

?>