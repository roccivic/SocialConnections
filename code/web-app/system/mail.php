<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);
// Include configuration file
require_once 'config.php';
// Error reporting is set in configuration
// if (Config::DISPLAY_ERRORS) {
// 	error_reporting(E_ALL | E_STRICT);
// } else {
// 	error_reporting(0);
// }
// Fix timezone
//date_default_timezone_set(Config::TIMEZONE);
// Include all other necessary libraries
require_once '../classes/Db.class.php';
require_once '../libs/gettext/gettext.inc';
require_once '../classes/Lang.class.php';
// Initialise i18n
Lang::setLang();

$thresholdDetails = getAttendanceThreshold(); 

$attendance = $this->deptOverallAttendance();
foreach ($attendance as $value) {
	$deptDetails= $this->getDepartmentDetails($value['did']);
	if(!empty($deptDetails['fname'])){
		$labsBelow = false;
		$body = '';
		$subject ='';
		$email = 'hraad3@hotmail.com';
		$to = $deptDetails['email'];
		$headers = "From: postmaster@localhost";
		if(($value['overall']*100)<$thresholdDetails['overall']){
			
			
			if (($this->deptLabAttendance($value['did'])*100)<$thresholdDetails['labs']) {
				$labsBelow = true;
				//set to true if poor lab attendance in teh module
			}
				
			$body .= 'Dear '.$deptDetails['fname'].' '. $deptDetails['lname']. ', \n' ;
			$body .= 'This is an automated e-mail regarding poor attendance in the ';
			$body .= $deptDetails['name'].' department\n';
			$body .='The current threshold for overall attendance is : '. $thresholdDetails['overall'] .'%\n';
			$body .='The current overall attendance for your department is ';
			$body .= ($value['overall']*100). '% \n \n';
			$subject .='Low overall attendance '; 
			if($labsBelow){
				$body .='The current threshold for labs is : '. $thresholdDetails['labs'] .'%\n';
				$body .='The current attendance for labs for your department is ';
				$body .= ($this->deptLabAttendance($value['did'])*100). '% \n\n';
				$subject .='and low lab attendance ';
			}
			
			$subject .=' in the '.$deptDetails['name'].' department\n';
			 if (mail($to,$subject,$message,$headers)) {
   				echo("<p>Message successfully sent about overall!</p>");
  			} else {
   				echo("<p>Message delivery failed for overall...</p>");
  			}
		}elseif (($this->deptLabAttendance($value['did'])*100)<$thresholdDetails['labs']) {
			$body .= 'Dear '.$deptDetails['fname'].' '. $deptDetails['lname']. ', \n' ;
			$body .= 'This is an automated e-mail regarding poor attendance in the ';
			$body .= $deptDetails['name'].' department\n';
			$body .='The current threshold for labs is : '. $thresholdDetails['labs'] .'%\n';
			$body .='The current attendance for labs for your department is ';
			$body .= ($this->deptLabAttendance($value['did'])*100). '% \n\n';
			 
			$subject .='Low attendance for labs in the '.$deptDetails['name'].' department\n';
			 if (mail($to,$subject,$message,$headers)) {
   				echo("<p>Message successfully sent about labs!</p>");
  			} else {
   				echo("<p>Message delivery failed...labs</p>");
  			}
		}
	
	}

}


		/**
	 * Returns the threshold for attendance
	 *
	 * @return array
	 */
	 function getAttendanceThreshold(){

		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `overall`,`labs` FROM `threshold` WHERE `id` = 0;"
		);
		$stmt->execute();
		$stmt->bind_result($overall, $labs);
		$stmt->fetch();
		$stmt->close();
		$arr[] = array(
				'overall' => $overall,
				'labs' => $labs
			);
		return $arr;
	
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
	 * Returns the decimal fraction and department ID of the lab
	 * attendance of a students in a department
	 * @return double
	 */
	 function deptLabAttendance($did){
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
				WHERE `isLecture` = 0
				AND WHERE `department`.`id` = ?
				"
				
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($did, $deptLab);
		while ($stmt->fetch()) {
				$arr[] = array(
				'did' => $did,
				'deptLab' => $deptLab
			);
		}
		$stmt->close();
		
		return $arr;
		
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