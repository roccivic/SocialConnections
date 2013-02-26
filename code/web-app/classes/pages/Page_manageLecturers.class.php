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
		$name = '';
		if (! empty($_REQUEST['name'])) {
			$name = $_REQUEST['name'];
		}
		

		if (! empty($_REQUEST['delete'])) {
			$details = $this->getLecturersDetails($did, $lid);
			if (empty($details['name'])) {
				$this->addNotification(
					'error',
					__('The selected department does not exist')
				);
			} else {
				$this->deleteLecturer($did, $lid);
			}
			$this->departmentSelector(false);
		} else if (! empty($_REQUEST['editForm'])) {
			$details = $this->getLecturersDetails($did, $lid);
			if (empty($details['name'])) {
				$this->addNotification(
					'error',
					__('The selected Lecturer does not exist')
				);
				$this->departmentSelector(false);
			} else {
				if ($this->validateForm(false, $did,$lid,$fname, $lname, $username, $email)
					&& $this->updateLecturer($did, $lid,$fname, $lname, $username, $email)
				) {
					$this->addNotification(
						'notice',
						__('The department details were successfully updated.')
					);
					$this->departmentSelector(false);
				} else {
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
					$details = $this->getDepartmentDetails($did);
					$name = $details['name'];
					$this->updateLecturer($did, $lid,$fname, $lname, $username, $email);
			//Where do i send it to $this->editLecturerForm($lid, $did)
				}
			}
		} else if (! empty($_REQUEST['create'])) {
			if ($this->validateForm(true, $did, $name, $head)
				&& $this->createLecturer($name, $head)
			) {
				$this->addNotification(
					'notice',
					__('The department was successfully created.')
				);
				$this->departmentSelector(true);
			} else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->editDepartmentForm($did, $name);
			}
		} else if (! empty($_REQUEST['editForm'])) {
			$details = $this->getDepartmentDetails($did);
			$name = $details['dname'];
			if ($did > 0 && empty($name)) {
				$this->addNotification(
					'error',
					__('The selected department does not exist')
				);
				$this->departmentSelector(true);
			} else {
				$this->editDepartmentForm($did, $name);
			}
		} else {
			$details = $this->getDepartmentDetails($did);
			if (empty($details['dname'])) {
				$this->addNotification(
					'error',
					__('The selected department does not exist')
				);
				$this->departmentSelector(true);
			} else {
				$this->displayDepartmentDetails($did, $details);
			}
		}
	}
	}

	private function displayLecturers($did)
	{
		$classes = $this->getLecturers($did);
		$html = $this->printLecturersListHeader($did);
		foreach($classes as $key => $value) {
			$html .= $this->printLecturersListItem($key, $did, $value);
		}
		$html .= $this->printLecturersListFooter();
		$this->addHtml($html);
	}
		/**
	 * Returns an array of classes details
	 *
	 * @return array
	 */
	private function getLecturers($did)
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
	 * Prints the header for the list of lecturers
	 *
	 * @return void
	 */
	private function printLecturersListHeader($did)
	{
		$html = '';
    	$html .= '<a href="?action=manageLecturers&did='.$did.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Create Lecturer') . '</a>';
        $html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Lecturer');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of lecturers
	 *
	 * @return void
	 */
	private function printLecturersListItem($lid, $did, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageLecturers&did=%d&lid=%d&view=1">%s</a></li>',
	        	$did,
	        	$lid,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of lecturers
	 *
	 * @return void
	 */
	private function printLecturersListFooter()
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
		$details = $this->getLecturersDetails($lid);
		if (isset($details['lid']));
		$html = '';
		$html .= '<h2>' . $details['name'] . '</h2>';
		$html .= '<h3>' __('Username: ') . $details['username'] . '</h3>';
		$html .= '<h3>' __('Email: ') . $details['email'] . '</h3>';
		$html .= '<a href="?action=manageLecturers&did='.$did.'&lid='.$lid.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Edit') . '</a>';
    	$html .= '<a href="?action=manageLecturers&did='.$did.'&lid='.$lid.'&delete=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Delete') . '</a>';
    	$html .= '<a href="?action=manageLecturers&lid='.$lid'&did='.$did.'&view=1&delete=1"';
    	$html .= ' data-role="button" data-theme="e">';
    	$html .= __('Back') . '</a>';
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
			'name' => $fname . ' ' . $lname,
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
    	$password = md5($pass.$salt);
    	   	
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
		$details = $this->getLecturersDetails($lid);
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
	 * Updates the Lecturer details
	 *
	 * @return bool success
	 */
	private function updateLecturer($did, $lid, $fname, $lname, $username, $email) {
		
		   	   	
		$db = Db::getLink();
		$stmt = $db->prepare(
			"UPDATE `lecturer` (`fname`, `lname`,`username`,`email`) VALUES(?, ?, ?, ?) WHERE `id`=?;"
		);
		$stmt->bind_param('ssssi', $fname, $lname, $username, $email, $lid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

}
?>
