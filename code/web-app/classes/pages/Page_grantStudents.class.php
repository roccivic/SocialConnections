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

		$sid = '';
		if(!empty($_REQUEST['sid'])) {
			$sid = $_REQUEST['sid'];
		}
		$fname = '';
		if(!empty($_REQUEST['fname'])){
			$fname = $_REQUEST['fname'];
		}
		$lname = '';
		if(!empty($_REQUEST['lname'])){
			$lname = $_REQUEST['lname'];
		}
		$grantOwed = '';
		if(!empty($_REQUEST['grantOwed'])){
			$grantOwed = $_REQUEST['grantOwed'];
		}
		

		if (!empty($_REQUEST['did']))
		{
			$dname = $this->getDepartmentName($did);
			if(!empty($dname) )
			{
				if(empty($_REQUEST['view'])){
					$this->displayGrantStudents($did);
				}
				if(!empty($_REQUEST['view'])){
			 		$did = $_REQUEST['did'];
			 		$dname = $this->getDepartmentName($did);
			 		if(!empty($dname) ){
						$sname = $this->getStudentName($sid);
						if(!empty($sname)){
						$sid = $_REQUEST['sid'];
						$this->viewGrantStudent($sid);
						}else{
						$this->addNotification(
						'error',
						__('Student does not exist.')
						);
						$this->displayGrantStudents($did);
						}
					}
				}
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
	 * Prints a list of students who get the grant
	 *
	 * @return void
	 */
	private function displayGrantStudents($did)
	{		

    		$html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">';
	        $html .= __('Select Student');
	        $html .= '</li>';
	       
	        $grants = $this->getGrantsDepartment($did);

	        foreach ($grants as $value) {
		    	$html .= '<li>';
		       
		        $html .= '<a href="?action=grantStudents&did='.$did.'&sid='.$value['sid'].'&view=1">'.$value['name'].'</a>';
		        $html .= '</li>';
	    	}

	        $html .= '</ul>';
	        $this->addHtml($html);
	}

	/**
	 * Returns an array of students who get a grant 
	 * that are in a department given the deptartment ID
	 * @return array
	 */
	private function getGrantsDepartment($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
	"SELECT `student`.`id`,`fname`, `lname` FROM `student` LEFT JOIN `class` ON `student`.`cid` = `class`.`id` LEFT JOIN `department` ON `department`.`id` = `class`.`did` WHERE `student`.`hasGrant` = 1 AND `department`.`id` = ?"
	);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($sid, $fname,$lname);
		while ($stmt->fetch()) {
			$arr[] = array(
				'sid' => $sid,
				'name' => $fname . ' ' . $lname
			);
		}
		$stmt->close();
		return $arr;
	}
	/**
	 * Returns an array of a students details who get a grant 
	 *
	 * @return array
	 */
	private function getGrantsStudent($sid)
	{
		
		$db = Db::getLink();
		$stmt = $db->prepare(
	"SELECT `fname`, `lname`, `grantOwed` FROM `student` LEFT JOIN `class` ON `student`.`cid` = `class`.`id` LEFT JOIN `department` ON `department`.`id` = `class`.`did` WHERE `student`.`hasGrant` = 1 AND `student`.`id` = ?"
	);
		$stmt->bind_param('i', $sid);
		$stmt->execute();
		$stmt->bind_result($fname,$lname, $grantOwed);
		$stmt->fetch();
		$stmt->close();
		return array(
			'sid' => $sid,
			'name' => $fname . ' ' . $lname,			
			'grantOwed' => $grantOwed
		);
	}
	/**
	 * Prints the grant details of a student
	 *
	 * @return void
	 */
	private function viewGrantStudent($sid)
	{
		$grantDetails = $this->getGrantsStudent($sid);
		$percent = $this->getAttendance($sid);
		$payable = $percent*$grantDetails['grantOwed'];
		$html = '';
		$html .= '<h3>' . $grantDetails['name']. '</h3>';
		$html .=  __('Total grant: ') ;
		$html .= $grantDetails['grantOwed'];
		$html .= '<br/>';
		$html .= __('Grant payable: ') ;
		$html .= $payable ;
		
    	$this->addHtml($html);
	}
	/**
	 * Returns the decimal fraction of the total
	 * attendance of a student given their ID
	 * @return double
	 */
	private function getAttendance($sid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance` 
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
				)"
		);
		$stmt->bind_param('i', $sid);
		$stmt->execute();
		$stmt->bind_result($percent);
		$stmt->fetch();
		$stmt->close();
		return $percent;
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






