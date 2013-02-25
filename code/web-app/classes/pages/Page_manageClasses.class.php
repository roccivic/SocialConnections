<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
/**
 * This page is used by admins to manage the
 * departments
 */
class Page_manageClasses extends Page_selectDepartment {
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

		$cid = 0;
		if(!empty($_REQUEST['cid'])){
			$cid = $_REQUEST['cid'];
		}
		if (! empty($_REQUEST['view'])) 
		{
			$details = $this->getClasses($did);
			$name = $details['name']
			$this->viewClass($cid,$did,$name);
			
		} elseif (! empty($_REQUEST['delete'])) 
		{
			$this->deleteClass($cid);
			$this->displayClasses($did);
		} else if (! empty($_REQUEST['edit'])) {
			if ($this->validateForm(false, $cid, $name, $did )
				&& $this->updateClass($name, $cid)
			) {
				$this->addNotification(
					'notice',
					__('The class details were successfully updated.')
				);
				$this->displayClasses($did);
			} else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$details = $this->getClasses($did);
				$name = $details['name'];
				$this->editClassForm($cid, $name);
			}
		}else if (! empty($_REQUEST['create'])) 
			{
			if ($this->validateForm(true, $cid, $name, $did)
				&& $this->createClass($name, $did)) 
			{
				$this->addNotification(
					'notice',
					__('The Group was successfully created.')
				);
				$this->displayClasses($did);
			} else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->editClassForm($cid, $name);
			}
		}else if (! empty($_REQUEST['editForm'])) {
			$details = $this->getClasses($did);
			$name = $details['name'];
			if ($cid > 0 && empty($name)) {
				$this->addNotification(
					'error',
					__('The selected class does not exist')
				);
				$this->displayClasses($did);
			} else {
				$this->editClassForm($cid, $name);
			}
		}
		
		$this->displayClasses($did);
	}
	
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
	 * Returns an array of classes details
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
    	$html .= '<a href="?action=manageClasses&did='.$did.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Create Class') . '</a>';
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
	        	'<li><a href="?action=manageClasses&cid=%d&did=%d&view=1">%s</a></li>',
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

	/**
	 * Creates a new class
	 *
	 * @return bool success
	 */
	private function createClass($name, $did) {
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO class (name, did) VALUES(?, ?);"
		);
		$stmt->bind_param('si', $name, $did);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	/**
	 * Deletes a Class
	 *
	 * @return bool success
	 */
		private function deleteClass($cid) 
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"DELETE FROM `class` WHERE `id`=?;"
		);
		$stmt->bind_param("i", $cid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Updates the class details
	 *
	 * @return bool success
	 */
	private function updateClass($name, $cid) {
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"UPDATE `class` SET `name` = ? WHERE `id` = ?;"
		);
		$stmt->bind_param('si', $name, $cid);
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
	 * Displays a form for editing a class
	 *
	 * @return void
	 */

	
	private function editClassForm($cid, $name)
	{
		$html = '<form method="post" action="">';
		if($cid == 0) {
			$html .= '<h3>' . __('Create Class') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html .= '<h3>' . __('Edit Group') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<input name="cid" value="'.$cid.'" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="name">' . __('Name') . ': </label>';
		$html .= '<input type="text" name="name" id="name" ';
		$html .= 'value="' . htmlspecialchars($name) . '" />';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Checks if the form details for editing/creating
	 * a group are valid
	 *
	 * @return bool
	 */
	private function validateForm($isCreate, $cid, $name, $did)
	{
		$success = true;
		if (! $isCreate && $cid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid group selected')
			);
		}elseif(! $isCreate && $did < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid department selected')
			);
		} else if (strlen($name) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Class name must be 64 characters long or less.')
			);
		} else if (strlen($name) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Class name must be at least 1 character long.')
			);
		} 
		}
		return $success;
	}
	
	private function viewClass($did,$cid, $name)
	{
		$html = '';
		$html .= '<h2>' .$name. '</h2>';
		$html .= '<h3>' __('Department: ') '</h3>';
		$html .= '<a href="?action=manageClasses&did='.$did.'&cid='.$cid.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Edit') . '</a>';
    	$html .= '<a href="?action=manageClasses&did='.$did.'&cid='.$cid.'&delete=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Delete') . '</a>';
    	$html .= '<a href="?action=manageClasses&cid='.$cid'&did='.$did.'&view=1"';
    	$html .= ' data-role="button" data-theme="e">';
    	$html .= __('Back') . '</a>';
	}
	
}
?>
