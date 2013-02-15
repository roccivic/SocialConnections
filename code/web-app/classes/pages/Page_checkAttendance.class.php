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
		$this->addHtml("selected group is " . $gid);
	}
}