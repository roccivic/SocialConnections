<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
/**
 * This page is used by admins to manage the
 * departments
 */
class Page_manageStudents extends Page_selectDepartment {
	public static function getAccessLevel()
	{
		return User::LECTURER;
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
		$cid  = 0;
		if(!empty($_REQUEST['cid'])) {
			$cid = $_REQUEST['cid'];
		}
		$sid = 0;
		if(!empty($_REQUEST['sid'])){
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
		$username = '';
		if(!empty($_REQUEST['username']))
		{
			$username = $_REQUEST['username'];
		}
		$email = '';
		if(!empty($_REQUEST['email'])){
			$email = $_REQUEST['email'];
		}
		$pass = '';
		if(!empty($_REQUEST['pass'])){
			$pass = $_REQUEST['pass'];
		}
		$varpass = '';
		if(!empty($_REQUEST['varpass'])){
			$varpass = $_REQUEST['varpass'];
		}
		$hasGrant = 0;
		if(!empty($_REQUEST['hasGrant'])){
			$hasGrant = $_REQUEST['hasGrant'];
		}
		$grantOwed = null;
		if(!empty($_REQUEST['grantOwed'])){
			$grantOwed = $_REQUEST['grantOwed'];
		}
		$dName = $this->getDepartmentName($did);
		$cName = $this->getClassName($cid);
		$details = $this->getStudentDetails($sid);
		if(!empty($dName)) {
			if(!empty($_REQUEST['listStudents'])) {
				if(!empty($cName)){
				$this->displayStudents($cid,$did);	
				}
				else {
					$this->addNotification(
						'error',
						__('Invalid class selected')
					);
					$this->displayClasses($did);
				}
			}else if(!empty($_REQUEST['delete'])){
				if($this->deleteStudent($sid)){
					$this->addNotification(
						'notice',
						__('The student was successfully deleted.')
					);
					
				}
				else{
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
				}
				$this->displayStudents($cid, $did);
			}else if(!empty($_REQUEST['studentDetails'])){
				$this->displayStudentDetails($sid, $did, $cid);
			}else if(!empty($_REQUEST['create'])){
				if($this->validateForm(true, $sid, $fname, $lname, $username, $email, $pass, $varpass, $hasGrant, $grantOwed)
				 && $this->createStudent($fname, $lname, $username, $email, $pass, $cid, $hasGrant, $grantOwed))
				{
					$this->addNotification(
						'notice',
						__('The student was successfully created.')
					);
					$this->displayStudents($cid, $did);
				}
				else {
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
					$this->editForm($sid, $cid, $did);
				}
			}else if(!empty($_REQUEST['edit'])) { 
				if($this->validateForm(false, $sid, $fname, $lname, $username, $email, $pass, $varpass, $hasGrant, $grantOwed)
				 && $this->editStudent($sid, $fname, $lname, $username, $email, $hasGrant, $grantOwed))
				{
					$this->addNotification(
						'notice',
						__('The student was edited successfully.')
					);
					
				}
				else {
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
				}
				$this->displayStudentDetails($sid, $did, $cid);
			}else if(!empty($_REQUEST['editForm'])){
				if((empty($cName) && $cid > 0)) {
					$this->addNotification(
						'error',
						__('Invalid class selected.')
					);
					$this->displayClasses($did);
				}
				else {
					if(empty($details['fname']) && $sid > 0) {
						$this->addNotification(
						'error',
						__('Invalid student selected.')
					);
						$this->displayStudents($cid, $did);
					}
					else {
						$this->editForm($sid,$cid,$did);
					}
					
				}
			}else {
				$this->displayClasses($did);
			}
				
			
		}
		else {
			$this->addNotification(
						'error',
						__('Invalid department.')
					);
			$this->departmentSelector();		
		}
	}
	/**
	 * Displays a list of classes
	 *
	 * @return array
	 */
	private function displayClasses($did)
	{
		$classes = $this->getClasses($did);
		$html = $this->printClassesListHeader($did);
		foreach($classes as $key => $value) {
			$html .= $this->printClassesListItem($key, $did, $value);
		}
		$html .= $this->printClassesListFooter();
		$this->addHtml($html);
	}

	/**
	 * Returns an array of class's details
	 *
	 * @return array
	 */
	private function getClasses($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `name` FROM `class` where `did` = ?"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while ($stmt->fetch()) {
			$arr[$id] = $name;
		}
		$stmt->close();
		return $arr;
	}

	/**
	 * Prints the header for the list of classes
	 *
	 * @return void
	 */
	private function printClassesListHeader($did)
	{
		$html = '';
    	$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Class');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of classes
	 *
	 * @return void
	 */
	private function printClassesListItem($cid, $did, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageStudents&cid=%d&did=%d&listStudents=1">%s</a></li>',
	        	$cid,
	        	$did,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of classes
	 *
	 * @return void
	 */
	private function printClassesListFooter()
	{
        $this->addHtml('</ul>');
	}

	private function displayStudents($cid,$did)
	{
		$students = $this->getStudentsInClass($cid);
		$html = $this->printStudentsListHeader($cid, $did);
		foreach($students as $key => $value) {
			$html .= $this->printStudentsListItem($key,$cid,$did, $value);
		}
		$html .= $this->printStudentsListFooter();
		$this->addHtml($html);
	}

	/**
	 * Prints the header for the list of students
	 *
	 * @return void
	 */
	private function printStudentsListHeader($cid, $did)
	{
		$html = '';
		$html .= '<a href="?action=manageStudents&editForm=1&cid=' . $cid . '&did='.$did.'"';
        $html .= ' data-role="button" data-theme="b">';
        $html .= __('Create Student') . '</a>';
    	$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Student');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of students
	 *
	 * @return void
	 */
	private function printStudentsListItem($sid,$cid,$did, $name)
	{
       $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageStudents&sid=%d&cid=%d&did=%d&studentDetails=1">%s</a></li>',
	        	$sid,
	        	$cid,
	        	$did,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of students
	 *
	 * @return void
	 */
	private function printStudentsListFooter()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Displays the details of a student
	 * and links to edit and delete it
	 *
	 * @return void
	 */
	private function displayStudentDetails($sid,$did,$cid) {
		$details = $this->getStudentDetails($sid);
		if (isset($details['sid'])) {
			$html  = '<h3>' . $details['fname'] . ' ' . $details['lname'] . '</h3>';
			$html .= __('Username: ');
			$html.= $details['username'];	
			$html .= '<br/><br/>';
			$html .= __('Class: ');
			$html.= $this->getClassName($details['cid']);	
			$html .= '<br/><br/>';
			$html .= '<a href="?action=manageStudents&editForm=1&sid='.$sid.'&did='.$did.'&cid='.$cid.'" data-role="button" data-theme="b">'.__('Edit').'</a>';

			$html .= sprintf(
				'<a class="delete" href="?action=manageStudents&delete=1&did=%d&sid=%d&cid=%d" data-role="button" data-theme="b">%s</a>',
				$did,
				$sid,
				$cid,
				__('Delete')
			);
			$html .= '<span style="display: none">';
			$html .= __('Are you sure you want to delete this student?');
			$html .= '</span>';
			$this->addHtml($html);
		} else {
			$this->addNotification(
				'warning',
				__('The selected student does not exist')
			);
			$this->departmentSelector();
		}
	}
	/**
	 * Returns an array of student's details
	 *
	 * @return array
	 */
	private function getStudentDetails($sid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `fname`,`lname`,`username`,`email`,`cid`,`hasGrant`,`grantOwed` FROM `student` WHERE `id`=?"
		);
		$stmt->bind_param('i', $sid);
		$stmt->execute();
		$stmt->bind_result($fname, $lname, $username, $email, $cid, $hasGrant, $grantOwed);
		$stmt->fetch();
		$stmt->close();
		return array(
			'sid' => $sid,
			'fname' => $fname,
			'lname' => $lname,
			'username' => $username,
			'email' => $email,
			'cid' => $cid,
			'hasGrant' => $hasGrant,
			'grantOwed' => $grantOwed
		);
	}
	/**
	 * Returns class name by given id
	 * @return array
	 */
	private function getClassName($cid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `class` WHERE `id`=?"
		);
		$stmt->bind_param('i', $cid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
	/**
	 * Deletes a student
	 *
	 * @return bool success
	 */
	private function deleteStudent($sid) 
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"DELETE FROM `student` WHERE `id`=?"
		);
		$stmt->bind_param('i', $sid);
		$success=$stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Displays a form for editing a department
	 *
	 * @return void
	 */
	private function editForm($sid, $cid, $did)
	{
		$details = $this->getStudentDetails($sid);
		if($sid == 0) {
			$html = '<form method="post" action="?action=manageStudents&listStudents=1&did=' . $did . '&cid=' . $cid . '">';
			$html .= '<h3>' . __('Create Student') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html = '<form method="post" action="?action=manageStudents&studentDetails=1&did=' . $did . '&cid=' . $cid . '&sid=' . $sid . '">';
			$html .= '<h3>' . __('Edit Student') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<input name="editForm" value="1" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="fname">' . __('First name') . ': </label>';
		$html .= '<input type="text" name="fname" id="fname" ';
		$html .= 'value="' . htmlspecialchars($details['fname']) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="lname">' . __('Last name') . ': </label>';
		$html .= '<input type="text" name="lname" id="lname" ';
		$html .= 'value="' . htmlspecialchars($details['lname']) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="username">' . __('Username') . ': </label>';
		$html .= '<input type="text" name="username" id="username" ';
		$html .= 'value="' . htmlspecialchars($details['username']) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="email">' . __('E-mail') . ': </label>';
		$html .= '<input type="text" name="email" id="email" ';
		$html .= 'value="' . htmlspecialchars($details['email']) . '" />';
		$html .= '</div>';
		if($sid < 1){
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="pass">' . __('Password') . ': </label>';
			$html .= '<input type="password" name="pass" id="pass" ';
			$html .= '</div>';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="varpass">' . __('Verify password') . ': </label>';
			$html .= '<input type="password" name="varpass" id="varpass" ';
			$html .= '</div>';
		}	
		$html .= '<div data-role="fieldcontain">';
		$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
		$html .= '<legend>' . __('Grant aided') . ': </legend>';
		$html .= '<label for="hasGrant1">' . __('Yes') . '</label>';
		$html .= '<input id="hasGrant1" type="radio" name="hasGrant" value="1" ';
		if($details['hasGrant']) {
			$html .= 'checked="checked"';
		}
		$html .= '/>';
		$html .= '<label for="hasGrant0">' . __('No') . '</label>';
		$html .= '<input id="hasGrant0" type="radio" name="hasGrant" value="0" ';
		if(!$details['hasGrant'] || empty($details['hasGrant'])) {
			$html .= 'checked="checked"';
		}
		$html .= '/>';
		$html .= '</fieldset>';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="grantOwed">' . __('Grant amount') . ': </label>';
		$html .= '<input type="text" name="grantOwed" id="grantOwed" value="'.$details['grantOwed'].'"" ';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Checks if the form details for editing/creating
	 * a student are valid
	 *
	 * @return bool
	 */
	private function validateForm($isCreate, $sid, $fname, $lname, $username, $email, $pass, $varpass, $hasGrant, $grantOwed)
	{
		$success = true;
		if (! $isCreate && $sid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid student selected')
			);
		} else if (strlen($fname) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s first name must be 32 characters long or less.')
			);
		} else if (strlen($fname) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s first name must be at least 1 character long.')
			);
		} else if (strlen($lname) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s last name must be 32 characters long or less.')
			);
		} else if (strlen($lname) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s last name must be at least 1 character long.')
			);
		} else if (strlen($username) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s username must be 64 characters long or less.')
			);
		} else if (strlen($username) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s username must be at least 1 character long.')
			);
		} else if (strlen($email) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s email must be 64 characters long or less.')
			);
		} else if (strlen($email) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s email must be at least 1 character long.')
			);
		}else if (! preg_match('/.+@.+/', $email)) {
    		$success = false;
    		$this->addNotification(
				'warning',
				__('Invalid e-mail address.')
			);
		} else if ($isCreate && strlen($pass) < 6) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s password must be at least 6 characters long.')
			);
		} else if($isCreate && $pass != $varpass) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Student\'s varification password and password must match.')
			);
		} else if($hasGrant!=0 && $hasGrant!=1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Unacceptable value(grant aided).')
			);
		}
		else if($hasGrant == 1 && $grantOwed < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid amount of money for grant aided student.')
			);
		}
		
		return $success;
	}
	/**
	 * Creates a new student
	 *
	 * @return bool success
	 */
	private function createStudent($fname, $lname, $username, $email, $pass, $cid, $hasGrant, $grantOwed) {
		$salt = md5($pass);
    	$pass = md5($pass.$salt);
    	if($hasGrant == 0) {
    		$grantOwed = null;
    	}
    	
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO `student` (`fname`, `lname`,`username`,`email`,`password`,`salt`, `cid`, `hasGrant`,`grantOwed` ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);"
		);
		$stmt->bind_param('ssssssiii', $fname, $lname, $username, $email, $pass, $salt, $cid, $hasGrant, $grantOwed);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Updates the student details
	 *
	 * @return bool success
	 */
	private function editStudent($sid, $fname, $lname, $username, $email, $hasGrant, $grantOwed) {
		$db = Db::getLink();
		if($hasGrant == 0) {
			$grantOWed = null;
		}
		$stmt = $db->prepare(
			"UPDATE `student` SET `fname` = ?, `lname` = ?, `username` = ?, `email` = ?, `hasGrant` = ?, `grantOwed` = ?  WHERE id = ?;"
		);
		$stmt->bind_param('ssssiii', $fname, $lname, $username, $email, $hasGrant, $grantOwed, $sid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Returns department name
	 *
	 * @return string
	 */
	private function getDepartmentName($did) {
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `department` WHERE `id` = ?"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
}