<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
/**
 * This page is used by admins to manage the
 * departments
 */
class Page_manageLecturers extends Page_selectDepartment {
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

		$lid = '';
		if(!empty($_REQUEST['lid'])) {
			$lid = $_REQUEST['lid'];
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
		$password = '';
		if(!empty($_REQUEST['password'])){
			$password = $_REQUEST['password'];
		}
		$varpass = '';
		if(!empty($_REQUEST['varpass'])){
			$varpass = $_REQUEST['varpass'];
		}
		
		$did = $_REQUEST['did'];
		$dname = $this->getDepartmentName($did);
		if(!empty($dname))
			
		{	

		if(!empty($_REQUEST['view'])) {
				$lid = $_REQUEST['lid'];
				$Lname = $this->getLecturerName($lid);
				if(!empty($Lname)){
			$this->addNotification(
						'error',
						__('Invalid lecturer selected')
					);
					
				}else{
						
						$this->viewLecturer($did,$lid);
				}
		}else if(!empty($_REQUEST['create'])){
				if($this->validateCreateForm(true, $did, $lid, $fname, $lname, $username, $email, $password, $varpass)
				 && $this->createLecturer($did, $fname, $lname, $username, $email, $password))
				{
					$this->addNotification(
						'notice',
						__('The lecturer was successfully created.')
					);
					
				}else{
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
					$this->departmentSelector();
				}
			}else if(!empty($_REQUEST['edit'])) { 
				if($this->validateEditForm(false, $did, $lid, $fname, $lname, $username, $email, $password, $varpass)
				 && $this->updateLecturer($fname, $lname, $username, $email, $lid))
				{
					$this->addNotification(
						'notice',
						__('The lecturer was edited successfully.')
					);
					
				}
				else {
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
				}
				$this->viewLecturer($lid, $did);
			}else if(!empty($_REQUEST['editForm'])){
				$details=$this->getLecturersDetails($did,$lid);
					if(empty($details['fname']) && $lid > 0) {
						$this->addNotification(
						'error',
						__('Invalid lecturer selected.')
					);
						
					}
					else {
						$this->editLecturerForm($lid, $did);
					}
				
			}else if (! empty($_REQUEST['delete'])) 
			{
			$this->deleteLecturer($did,$lid);
			
			}	 
		$this->displayLecturers($did);
		}else
		{
			$this->addNotification(
					'warning',
					__('Department does not exist.')
				);
			$this->departmentSelector();
		}				
		
	}
	/**
	 * Calls print functions for classes
	 *
	 * @return void
	 */
	private function displayLecturers($did)
	{
		$lecturers = $this->getlecturers($did);
		$html = $this->printlecturersListHeader($did);
		foreach($lecturers as $key => $value) {
			$html .= $this->printlecturersListItem($key, $did, $value);
		}
		$html .= $this->printlecturersListFooter();
		$this->addHtml($html);
	}

	/**
	 * Returns an array of a classes details
	 *
	 * @return array
	 */
	private function getlecturers($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname` FROM `lecturer` where `did` = ?"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}

	/**
	 * Prints the header for the list of classes
	 *
	 * @return void
	 */
	private function printlecturersListHeader($did)
	{
		$html = '';
    	$html .= '<a href="?action=manageLecturers&did='.$did.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Create lecturer') . '</a>';
        $html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select lecturer');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of classes
	 *
	 * @return void
	 */
	private function printlecturersListItem($did, $lid, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=managelecturers&did=%d&lid=%d&view=1">%s</a></li>',
	        	$lid,
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
	private function printlecturersListFooter()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Prints the details of a lecturer
	 *
	 * @return void
	 */
	private function viewLecturer($did,$lid)
	{
		$details = $this->getLecturersDetails($did, $lid);
		if (isset($details['lid']));
		$html = '';
		$html .= '<h3>' . $details['fname'] . ' ' . $details['lname'] . '</h3>';
		$html .=  __('Username: ') ;
		$html .= $details['username'];
		$html .= '<br/>';
		$html .= __('Email: ') ;
		$html .= $details['email'] ;
		$html .= '<br/><br/>';
		$html .= '<a href="?action=manageLecturers&did='.$did.'&lid='.$lid.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Edit') . '</a>';
    	$html .= '<a href="?action=manageLecturers&did='.$did.'&lid='.$lid.'&delete=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Delete') . '</a>';
    	$this->addHtml($html);
	}
		/**
	 * Returns an array of classes details
	 *
	 * @return array
	 */
	private function getLecturersDetails($did, $lid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`, `username`, `email`, `did` FROM `lecturer` where `id` = ?"
		);
		$stmt->bind_param('i', $lid);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname, $username, $email, $did);
		$stmt->fetch();
		$stmt->close();
		return array(
			'lid' => $id,
			'fname' => $fname,
			'lname' => $lname,
			'username' => $username,
			'email' => $email,
			'did' => $did
		);
	}
	/**
	 * Deletes a Lecturer
	 *
	 * @return void
	 */
	private function deleteLecturer($did, $lid) 
	{
		$db = Db::getLink();
		if($db->query("DELETE FROM lecturer WHERE id=$lid;")) {
			$this->addNotification(
				'notice',
				'The Lecturer was successfully deleted'
			);
		} else {
			$this->addNotification(
				'error',
				'An error occured while processing the request'
			);
		}
	}
	/**
	 * Creates a new Lecturer
	 *
	 * @return bool success
	 */
	private function createLecturer($did,$fname, $lname, $username, $email, $password) {
		
		$salt = $this->generateRandomString();
    	$password = md5($password.$salt);
    	   	
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO `lecturer` (`fname`, `lname`,`username`,`email`,`password`,`salt`,`did` ) VALUES(?, ?, ?, ?, ?, ?, ?);"
		);
		$stmt->bind_param('sssssss', $fname, $lname, $username, $email, $password, $salt, $did);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Updates the Lecturer details
	 *
	 * @return bool success
	 */
	private function updateLecturer($fname, $lname, $username, $email, $lid) {
		
		   	   	
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"UPDATE `lecturer` SET `fname` = ?, `lname` = ?, `username` = ?, `email` = ? WHERE `id` = ?;"
		);
		$stmt->bind_param('ssssi', $fname, $lname, $username, $email, $lid);
		$success = $stmt->execute();
		$stmt->close();
		if($success) {
			$db->query("COMMIT");
		}else {
			$db->query("ROLLBACK");
		}
		return $success;
	}


	/**
	*Generate a random string
	*
	*@return string
	*/
	private function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    	}
    return $randomString;
	}
	/**
	 * Displays a form for editing/creating a Lecturer
	 *
	 * @return void
	 */
	private function editLecturerForm($lid, $did)
	{
		$details = $this->getLecturersDetails($did,$lid);
		$html = '<form method="post" action="">';
		if($lid == 0) {
			$html .= '<h3>' . __('Create Lecturer') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html .= '<h3>' . __('Edit Lecturer') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<input name="lid" value="'.$lid.'" type="hidden" />';
		$html .= '<input name="did" value="'.$did.'" type="hidden" />';
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
		if($lid < 1){
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="pass">' . __('Password') . ': </label>';
			$html .= '<input type="password" name="password" id="password" ';
			$html .= '</div>';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="varpass">' . __('Verify password') . ': </label>';
			$html .= '<input type="password" name="varpass" id="varpass" ';
			$html .= '</div>';
		}
		
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	
	/**
	 * Checks if the form details for creating
	 * a lecturer are valid
	 *
	 * @return bool
	 */
	private function validateCreateForm($isCreate, $did, $lid, $fname, $lname, $username, $email, $password, $varpass)
	{
		$success = true;
		if (! $isCreate && $did < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid department selected')
			);
		}else if (! $isCreate && $lid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid Lecturer selected')
			);
		} else if (strlen($fname) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s first name must be 32 characters long or less.')
			);
		} else if (strlen($fname) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s first name must be at least 1 character long.')
			);
		} else if (strlen($lname) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s last name must be 32 characters long or less.')
			);
		} else if (strlen($lname) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s last name must be at least 1 character long.')
			);
		} else if (strlen($username) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s username must be 64 characters long or less.')
			);
		} else if (strlen($username) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s username must be at least 1 character long.')
			);
		} else if (strlen($email) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s email must be 64 characters long or less.')
			);
		} else if (strlen($email) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s email must be at least 1 character long.')
			);
		}else if (! preg_match('/.+@.+/', $email)) {
    		$success = false;
    		$this->addNotification(
				'warning',
				__('Invalid e-mail address.')
			);
		} else if ($isCreate && strlen($password) < 6) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s password must be at least 6 characters long.')
			);
		} else if($isCreate && $password != $varpass) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s varification password and password must match.')
			);
		}
		
		return $success;
	}
	/**
	 * Checks if the form details for editing
	 * a lecturer are valid
	 *
	 * @return bool
	 */
	private function validateEditForm($isCreate, $did, $lid, $fname, $lname, $username, $email)
	{
		$success = true;
		if (! $isCreate && $did < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid department selected')
			);
		}else if (! $isCreate && $lid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid Lecturer selected')
			);
		} else if (strlen($fname) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s first name must be 32 characters long or less.')
			);
		} else if (strlen($fname) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s first name must be at least 1 character long.')
			);
		} else if (strlen($lname) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s last name must be 32 characters long or less.')
			);
		} else if (strlen($lname) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s last name must be at least 1 character long.')
			);
		} else if (strlen($username) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s username must be 64 characters long or less.')
			);
		} else if (strlen($username) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s username must be at least 1 character long.')
			);
		} else if (strlen($email) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s email must be 64 characters long or less.')
			);
		} else if (strlen($email) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Lecturer\'s email must be at least 1 character long.')
			);
		}else if (! preg_match('/.+@.+/', $email)) {
    		$success = false;
    		$this->addNotification(
				'warning',
				__('Invalid e-mail address.')
			);
		}
		
		return $success;
	}
	/**
	 * Returns the name of a lecturer given its id
	 *
	 * @return string
	 */
	private function getLecturerName($lid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `fname` FROM `lecturer` WHERE `id` = ?;"
		);
		$stmt->bind_param('i', $cid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
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
