<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Handles the creation of the HTML menus
 * Used by the Page class
 */
class Menu {
	private $studentArray;
	private $lecturerArray;
	private $adminArray;

	public function __construct()
	{
		$this->studentArray = array(
			'checkAttendance' => __('Check Attendance'),
			'viewResults' => __('View Your Results'),
			'makeExcuse' => __('Excuse Yourself for Missing a Class'),
			'notes' => __('Notes'),
			'twitter' => __('Twitter')
		);
		$this->lecturerArray = array(
			'postNotes' => __('Post Notes on Dropbox'),
			'viewStudentAttendance' => __('View Student Attendance'),
			'twitter' => __('Twitter'),
			'manageAssessments' => __('Manage Assessments'),
			'manageStudents' => __('Manage Students'),
			'manageGroups' => __('Manage Groups'),
			'takeAttendance' => __('Take Student Attendance'),
			'viewExcuses' => __('View Student Excuses')
		);
		$this->adminArray = array(
			'manageDepartments' => __('Manage Departments'),
			'manageLecturers' => __('Manage Lecturers'),
			'manageClasses' => __('Manage Classes'),
			'manageModules' => __('Manage Modules'),
			'adminCheckAttendance' => __('Check Attendance'),
			'attendanceThreshold' => __('Attendance Threshold'),
			'grantStudents' => __('Grant Students')
			
		);
	}
	/**
	 * Returns the menu for a student
	 *
	 * @return string HTML
	 */
	public function getStudentMenu()
	{
		return $this->getMenuHtml(
			__('Student Menu'),
			$this->studentArray
		);
	}
	/**
	 * Returns the menu for a lecturer
	 *
	 * @return string HTML
	 */
	public function getLecturerMenu()
	{
		return $this->getMenuHtml(
			__('Lecturer Menu'),
			$this->lecturerArray
		);
	}
	/**
	 * Returns the menu for an admin
	 *
	 * @return string HTML
	 */
	public function getAdminMenu()
	{
		return $this->getMenuHtml(
			__('Admin Menu'),
			$this->adminArray
		);
	}
	/**
	 * Generates an HTML menu given an array
	 * of elements that the menu should contain
	 *
	 * @return string HTML
	 */
	private function getMenuHtml($title, $items)
	{
		ksort($items);
		$action = ! empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
		$retval  = '<nav>';
		$retval .= '<ul data-role="listview" data-theme="c" data-dividertheme="d">';
		$retval .= '<li data-role="list-divider">';
		$retval .= htmlspecialchars($title);
		$retval .= '</li>';
		foreach ($items as $link => $label) {
			if ($link === $action) {
				$retval .= '<li data-theme="e">';
			} else {
				$retval .= '<li>';
			}
			$retval .= '<a ';
			if ($link === 'postNotes' || $link === 'twitter') {
				$retval .= 'data-ajax="false" ';
			}
			$retval .= 'href="?action=' . $link . '">';
			$retval .= htmlspecialchars($label);
			$retval .= '</a>';
			$retval .= '</li>';
		}
		$retval .= '</ul>';
		$retval .= '</nav>';
		return $retval;
	}
	/**
	 * Returns a list of all pages from all menus
	 *
	 * @return array
	 */
	public function getAllPages()
	{
		return array_merge(
			$this->studentArray,
			$this->lecturerArray,
			$this->adminArray
		);
	}
}

?>