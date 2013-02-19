<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
/**
 * This page is used by admins to manage the
 * departments
 */
class Page_manageDepartments extends Page_selectDepartment {
	public static function getAccessLevel()
	{
		return User::ADMIN;
	}

	public function __construct()
	{
		parent::__construct(true);
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
		$head = 0;
		if (! empty($_REQUEST['head'])) {
			$head = intval($_REQUEST['head']);
		}

		if (! empty($_REQUEST['delete'])) {
			$this->deleteDepartment($did);
			$this->departmentSelector(true);
		} else if (! empty($_REQUEST['edit'])) {
			if ($this->validateForm(false, $did, $name, $head)
				&& $this->updateDepartment($did, $name, $head)
			) {
				$this->addNotification(
					'notice',
					__('The department details were successfully updated.')
				);
				$this->departmentSelector(true);
			} else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$details = $this->getDepartmentDetails($did);
				$name = $details['dname'];
				$this->editDepartmentForm($did, $name);
			}
		} else if (! empty($_REQUEST['create'])) {
			if ($this->validateForm(true, $did, $name, $head)
				&& $this->createDepartment($name, $head)
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
			$this->displayDepartmentDetails($did);
		}
	}
	/**
	 * Deletes a department
	 *
	 * @return void
	 */
	private function deleteDepartment($did) 
	{
		$db = Db::getLink();
		if($db->query("DELETE FROM department WHERE id=$did;")) {
			$this->addNotification(
				'notice',
				'The department was successfully deleted'
			);
		} else {
			$this->addNotification(
				'error',
				'An error occured while processing the request'
			);
		}
	}
	/**
	 * Displays the details of a department
	 * and links to edit and delete it
	 *
	 * @return void
	 */
	private function displayDepartmentDetails($did) {
		$details = $this->getDepartmentDetails($did);
		if (isset($details['did'])) {
			$html  = '<h3>' . $details['dname'] . '</h3>';
			$html .= __('Head of Department: ');
			if(isset($details['fname'])) {
			 	$html.= $details['fname'] . ' ' . $details['lname'];	
			} else {
				$html .= __('Not assigned');
			}
			$html .= '<br/><br/>';
			$html .= '<a href="?action=manageDepartments&editForm=1&did='.$did.'" data-role="button" data-theme="b">'.__('Edit').'</a>';

			$html .= sprintf(
				'<a onclick="return confirm(\'%s\');" href="?action=manageDepartments&delete=1&did=%d" data-role="button" data-theme="b">%s</a>',
				__('Are you sure you want to delete this department?'),
				$did,
				__('Delete')
			);
			$this->addHtml($html);
		} else {
			$this->addNotification(
				'warning',
				__('The selected department does not exist')
			);
			$this->departmentSelector(true);
		}
	}
	/**
	 * Displays a form for editing a department
	 *
	 * @return void
	 */
	private function editDepartmentForm($did, $name)
	{
		$html = '<form method="post" action="">';
		if($did == 0) {
			$html .= '<h3>' . __('Create Department') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html .= '<h3>' . __('Edit Department') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<input name="did" value="'.$did.'" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="name">' . __('Name') . ': </label>';
		$html .= '<input type="text" name="name" id="name" ';
		$html .= 'value="' . htmlspecialchars($name) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="head">' . __('Head of Department') . ': </label>';
		$html .= '<select id="head" name="head">';
		$html .= '<option value="0">' . __('Not assigned') . '</option>';
		$details = $this->getDepartmentDetails($did);
		$headId = $details['headId'];
		foreach($this->getLecturers() as $key => $value) {
			$html .= '<option value="' . $key . '"';
			if ($key == $headId) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . htmlspecialchars($value) . '</option>';	
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Returns an array of lecturer's details
	 *
	 * @return array
	 */
	private function getLecturers()
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname` FROM `lecturer`"
		);
		$stmt->execute();
		$stmt->bind_result($id, $fname, $lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}
	/**
	 * Returns an array of department's details
	 *
	 * @return array
	 */
	private function getDepartmentDetails($did)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `department`.`id`,`department`.`name`, `headId`, `fname`, `lname`
			FROM `department` LEFT JOIN `lecturer`
			ON `lecturer`.`id` = `headId`
			WHERE `department`.`id` = ?"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($id, $dname, $headId, $fname, $lname);
		$stmt->fetch();
		$stmt->close();
		return array(
			'did' => $id,
			'dname' => $dname,
			'headId' => $headId,
			'fname' => $fname,
			'lname' => $lname
		);
	}
	/**
	 * Creates a new department
	 *
	 * @return bool success
	 */
	private function createDepartment($name, $head) {
		if ($head < 1) {
			$head = null;
		}
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO department (name, headId) VALUES(?, ?);"
		);
		$stmt->bind_param('si', $name, $head);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Updates the department details
	 *
	 * @return bool success
	 */
	private function updateDepartment($did, $name, $head) {
		if ($head < 1) {
			$head = null;
		}
		$db = Db::getLink();
		$stmt = $db->prepare(
			"UPDATE department SET name = ?, headId = ? WHERE id = ?;"
		);
		$stmt->bind_param('sii', $name, $head, $did);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Checks if the form details for editing/creating
	 * a department are valid
	 *
	 * @return bool
	 */
	private function validateForm($isCreate, $did, $name, $head)
	{
		$success = true;
		if (! $isCreate && $did < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid department selected')
			);
		} else if (strlen($name) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Department name must be 64 characters long or less.')
			);
		} else if (strlen($name) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Department name must be at least 1 character long.')
			);
		} else if ($head > 0) {
			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT COUNT(*) FROM `lecturer` WHERE `id` = ?"
			);
			$stmt->bind_param('i', $head);
			$stmt->execute();
			$stmt->bind_result($valid);
			$stmt->fetch();
			$stmt->close();
			if (! $valid) {
				$success = false;
				$this->addNotification(
					'warning',
					__('Invalid lecturer selected as head of department.')
				);
			}
		}
		return $success;
	}
}