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
		$this->addHtml("selected group is " . $gid);
	}
}