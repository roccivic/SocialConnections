<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
/**
 * This page is used by admins to manage the
 * departments
 */
class Page_grantStudents extends Page_selectDepartment {
	public static function getAccessLevel()
	{
		return User::ADMIN;
	}

	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_selectDepartment superclass
	 * when the user has selected a department
	 *
	 * @return void
	 */
	public function display($did) 
	{
		$did = intval($did);

		$this->displayGrantStudents($did);
		
	}
	
	
	/**
	 * Calls print functions for classes
	 *
	 * @return void
	 */
	private function displayGrantStudents($did)
	{
		$students = $this->getGrantStudentsDetails($did);
		$html ='';
		foreach($students as $item) {
			$html .= $students['name'];
			//$html .= $students['grantOwed'];
		}
		
		$this->addHtml($html);
	}

		/**
	 * Returns an array of classes details
	 *
	 * @return array
	 */
	private function getGrantStudentsDetails($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `fname`, `lname`, `grantOwed`  FROM `student` where `hasGrant` = 1 "
		);
		
		//$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($fname,$lname, $grantOwed);
		$stmt->fetch();
		$stmt->close();
		return array(
			'name' => $fname . ' ' . $lname,			
			'grantOwed' => $grantOwed
			
		);
	}
	
}