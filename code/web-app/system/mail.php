<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);
// Include configuration file
require_once '../config.php';
// Error reporting is set in configuration
// if (Config::DISPLAY_ERRORS) {
// 	error_reporting(E_ALL | E_STRICT);
// } else {
// 	error_reporting(0);
// }
// Fix timezone
date_default_timezone_set(Config::TIMEZONE);
// Include all other necessary libraries
require_once '../classes/Db.class.php';
require_once '../libs/gettext/gettext.inc';
require_once '../classes/Lang.class.php';
// Initialise i18n
Lang::setLang();

$debug = true;


students();
headOfDepartments();
echo "here we are";

$thresholdOverall = getOverallThreshold();
$thresholdLabs = getOverallThreshold();
var_dump($thresholdLabs);
var_dump($thresholdOverall);
function students(){
	global $thresholdDetails;
	$students = getStudents();
	
	foreach ($students as $value) {


		$body = '';
		$subject ='';
		
		$to = $value['email'];
		$headers = __('From: postmaster@localhost');

				
		$body .= __('Dear ').$value['fname'].__(' '). $value['lname']. __(', \n') ;
		$body .= __('This is an automated e-mail regarding attendance \n');
		$body .= __('The current threshold for overall attendance is : '). $thresholdDetails['overall'] .__('%\n');
		$body .=__('Your current overall attendance: ');
		$body .= ($value['overall']*100). __('% \n \n');		
		
		
		$subject .=__('Weekly attendance notification');
		 if (mymail($to,$subject,$body,$headers)) {
				echo("<p>Message successfully sent about overall!</p>");
			} else {
				echo("<p>Message delivery failed for overall...</p>");
			}
	}
}
function headOfDepartments(){
		global $thresholdDetails;

	$attendance = deptOverallAttendance();
	//var_dump($attendance);
	foreach ($attendance as $value) {
		$deptDetails= getDepartmentDetails($value['did']);
		if(!empty($deptDetails['fname'])){
			$labsBelow = false;
			$body = '';
			$subject ='';
			
			$to = $deptDetails['email'];
			$headers = __('From: postmaster@localhost');
			if(($value['deptOverall']*100)<$thresholdDetails['overall']){
				
				
				if ((deptLabAttendance($value['did'])*100)>$thresholdDetails['labs']) {
					$labsBelow = true;
					//set to true if poor lab attendance in teh module
				}
					
				$body .= __('Dear ').$deptDetails['fname'].' '. $deptDetails['lname']. __(', \n') ;
				$body .= __('This is an automated e-mail regarding poor attendance in the ');
				$body .= $deptDetails['name'].__(' department\n');
				$body .= __('The current threshold for overall attendance is : '). $thresholdDetails['overall'] .__('%\n');
				$body .= __('The current overall attendance for your department is ');
				$body .= ($value['overall']*100). __('% \n \n');
				$subject .=__('Low overall attendance '); 
				if($labsBelow){
					$body .=__('The current threshold for labs is : '). $thresholdDetails['labs'] .__('%\n');
					$body .=__('The current attendance for labs for your department is ');
					$body .= (deptLabAttendance($value['did'])*100). __('% \n\n');
					$subject .=__('and low lab attendance ');
				}
				
				$subject .=__(' in the ').$deptDetails['name'].__(' department\n');
				 if (mymail($to,$subject,$body,$headers)) {
	   				echo("<p>Message successfully sent about overall!</p>");
	  			} else {
	   				echo("<p>Message delivery failed for overall...</p>");
	  			}
			}elseif ((deptLabAttendance($value['did'])*100)<$thresholdDetails['labs']) {
				$body .= __('Dear ').$deptDetails['fname'].__(' '). $deptDetails['lname']. __(', \n') ;
				$body .= __('This is an automated e-mail regarding poor attendance in the ');
				$body .= $deptDetails['name'].__(' department\n');
				$body .=__('The current threshold for labs is : '). $thresholdDetails['labs'] .__('%\n');
				$body .=__('The current attendance for labs for your department is ');
				$body .= (deptLabAttendance($value['did'])*100). __('% \n\n');
				 
				$subject .=__('Low attendance for labs in the ').$deptDetails['name'].__(' department\n');
				 if (mymail($to,$subject,$body,$headers)) {
	   				echo("<p>Message successfully sent about labs!</p>");
	  			} else {
	   				echo("<p>Message delivery failed...labs</p>");
	  			}
			}
		
		}

	}
}

function myMail($to,$subject,$message,$headers){
	global $debug;
	if ($debug) {
		var_dump($to,$subject,$message,$headers);
		return true;
	} else {
		return mail($to,$subject,$message,$headers);
	}
}

	/**
	 * Returns the decimal fraction of the total
	 * attendance of a student given their ID
	 * @return double
	 */
	 function getStudents()
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT SUM(`present`) / COUNT(*), fname, lname, email
					FROM `student_attendance`
					INNER JOIN `attendance` 
					ON `attendance`.`id` = `student_attendance`.`aid`
					INNER JOIN `student`
					ON `student`.`id` = `sid`"
		);
		
		$stmt->execute();
		$stmt->bind_result($overall, $fname, $lname, $email);
		while ($stmt->fetch()) {
			$arr[] = array(
				'overall' => $overall,
				'fname' => $fname,
				'lname' => $lname,
				'email'=> $email
			);
		}
		$stmt->close();
		return $arr;
	}
function getStudentsDetails(){

}


		/**
	 * Returns the threshold for overall attendance
	 *
	 * @return int
	 */
	 function getOverallThreshold(){

		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `overall` FROM `threshold` WHERE `id` = 0;"
		);
		$stmt->execute();
		$stmt->bind_result($overall);
		$stmt->fetch();
		$stmt->close();
		
		return $overall;
	
	}
	/**
	 * Returns the threshold for lab attendance
	 *
	 * @return int
	 */
	 function getlabThreshold(){

		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `labs` FROM `threshold` WHERE `id` = 0;"
		);
		$stmt->execute();
		$stmt->bind_result($labs);
		$stmt->fetch();
		$stmt->close();
		
		return $labs;
	
	}
	/**
	 * Returns the decimal fraction and department ID of the total
	 * attendance of students in a department
	 * @return double
	 */
	 function deptOverallAttendance(){
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
				"SELECT `department`.`id`, SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance` 
				ON `attendance`.`id` = `student_attendance`.`aid`
				INNER JOIN `group` 
				ON `attendance`.`gid` = `group`.`id` 
				INNER JOIN `group_student`
				ON `group`.`id` = `group_student`.`gid`
				INNER JOIN `student`
				ON `group_student`.`sid` = `student`.`id`
				INNER JOIN `class`
				ON `student`.`cid` = `class`.`id`
				INNER JOIN `department` 
				ON `department`.`id` = `class`.`did`
				"
				
		);
		$stmt->execute();
		$stmt->bind_result($did, $deptOverall);
		while ($stmt->fetch()) {
				$arr[] = array(
				'did' => $did,
				'deptOverall' => $deptOverall
			);
		}
		$stmt->close();
		
		return $arr;
		
	}

	/**
	 * Returns the decimal fraction of the lab attendance of students
	 * in a department selected by its ID
	 * @return double
	 */
	 function deptLabAttendance($did){
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
				"SELECT SUM(`present`) / COUNT(*)
				FROM `student_attendance`
				INNER JOIN `attendance` 
				ON `attendance`.`id` = `student_attendance`.`aid`
				INNER JOIN `group` 
				ON `attendance`.`gid` = `group`.`id` 
				INNER JOIN `group_student`
				ON `group`.`id` = `group_student`.`gid`
				INNER JOIN `student`
				ON `group_student`.`sid` = `student`.`id`
				INNER JOIN `class`
				ON `student`.`cid` = `class`.`id`
				INNER JOIN `department` 
				ON `department`.`id` = `class`.`did`
				WHERE `isLecture` = 0
				AND `department`.`id` = ?
				"
				
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($deptLab);
		$stmt->close();
		
		return $deptLab;
		
	}
/**
	 * Returns an Head, the name of a department 
	 * and the email of the head
	 * @return string
	 */
	 function getDepartmentDetails($did)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `department`.`name`, `fname`, `lname`,`email`
			FROM `department` LEFT JOIN `lecturer`
			ON `lecturer`.`id` = `headId`
			WHERE `department`.`id` = ?"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($dname,$fname, $lname, $email);
		$stmt->fetch();
		$stmt->close();
		return array(
			'dname' => $dname,
			'fname' => $fname,
			'lname' => $lname,
			'email'=> $email
		);
	}

	/*$to = "gary.brady@mycit.ie";
	$subject = "Test mail";
	$message = "Hello! This is a simple email\n message.";
	$from = "postmaster@localhost";
	$headers = "From: postmaster@localhost";
	 if (mail($to,$subject,$message,$headers)) {
   echo("<p>Message successfully sent!</p>");
  } else {
   echo("<p>Message delivery failed...</p>");
  }*/

	//mail($to,$subject,$message,$headers);
	//echo "should have sent";







?>