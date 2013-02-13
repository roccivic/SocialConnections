<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Handles the creation of the HTML menus
 * Used by the Page class
 */
class Menu {
	/**
	 * Returns the menu for a student
	 *
	 * @return string HTML
	 */
	public function getStudentMenu()
	{
		return $this->getMenuHtml(
			__('Student Menu'),
			array(
				__('Check Attendance') => 'checkAttendance',
				__('View Your Results') => 'viewResults',
				__('Excuse Yourself for Missing a Class') => 'makeExcuse',
				__('Notes') => 'notes',
				__('Twitter') => 'twitter'
			)
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
			array(
				__('Post Notes on Dropbox') => 'postNotes',
				__('View Student Attendance') => 'viewStudentAttendance',
				__('Twitter') => 'twitter',
				__('Manage Assessments') => 'manageAssessments',
				__('Manage Students') => 'manageStudents',
				__('Manage Groups') => 'manageGroups'
			)
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
			array(
				__('Manage Departments') => 'manageDepartments',
				__('Manage Lecturers') => 'manageLecturers',
				__('Manage Classes') => 'manageClasses',
				__('Manage Modules') => 'manageModules',
				__('Check Attendance') => 'checkAttendance',
				__('Attendance Threshold') => 'attendanceThreshold',
				__('Grant Students') => 'grantStudents'
				
			)
		);
	}
	/**
	 * Returns the menu for a super user
	 *
	 * @return string HTML
	 */
	public function getSuperMenu()
	{
		return $this->getMenuHtml(
			__('Superuser Menu'),
			array(
				__('Super Functionality 1') => 'dosuper1',
				__('Super Functionality 2') => 'dosuper2'
			)
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
		$action = ! empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
		$retval  = '<nav>';
		$retval .= '<ul data-role="listview" data-theme="c" data-dividertheme="d">';
		$retval .= '<li data-role="list-divider">';
		$retval .= htmlspecialchars($title);
		$retval .= '</li>';
		foreach ($items as $label => $link) {
			if ($link === $action) {
				$retval .= '<li data-theme="e">';
			} else {
				$retval .= '<li>';
			}
			$retval .= '<a href="?action=' . $link . '">';
			$retval .= htmlspecialchars($label);
			$retval .= '</a>';
			$retval .= '</li>';
		}
		$retval .= '</ul>';
		$retval .= '</nav>';
		return $retval;
	}
}

?>