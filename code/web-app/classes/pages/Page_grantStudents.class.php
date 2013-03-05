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

		

		if (!empty($_REQUEST['did']))
		{
			$dname = $this->getDepartmentName($did);
			if(!empty($dname))
			{
				$this->displayGrantStudents($did);
			}else{

				$this->addNotification(
					'warning',
					__('Department does not exist.')
				);
				$this->departmentSelector();
		
			}
		}
	}
/**
	 * Prints the students who get the grant
	 *and how much they are owed
	 * @return void
	 */
	private function displayGrantStudents($did)
	{		

    		$html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">';
	        $html .= __('Grant Amounts Owed');
	        $html .= '</li>';
	       
	        $grants = $this->getGrantStudentsDetails($did);
	        $details = array(
	        	__('') => $grants['name'],
	        	__('€') => $grants['grantOwed'],
	        	
	        );

	        foreach ($details as $key => $value) {
		    	$html .= '<li>';
		        $html .= $key.$value;
		        		        
		        $html .= '</li>';
	    	}

	        $html .= '</ul>';
	        $this->addHtml($html);
	}

	/**
	 * Returns an array of students who get a grant 
	 *and how much they are owed given a department
	 * @return array
	 */
	private function getGrantStudentsDetails($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
	"SELECT `fname`, `lname`, `grantOwed` FROM `student` LEFT JOIN `class` ON `student`.`cid` = `class`.`id` LEFT JOIN `department` ON `department`.`id` = `class`.`did` WHERE `student`.`hasGrant` = 1 AND `department`.`id` = ?"
	);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($fname,$lname, $grantOwed);
		$stmt->fetch();
		$stmt->close();
		return array(
			'name' => $fname . ' ' . $lname,			
			'grantOwed' => $grantOwed
			
		);
	}

		/**
	 * Returns the name of a department given its id
	 *
	 * @return string
	 */
	private function getDepartmentName($did)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `department` WHERE `id` = ?;"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
	
}






