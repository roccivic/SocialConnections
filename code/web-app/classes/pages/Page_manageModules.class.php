<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
/**
 * This page is used by admins to manage the
 * departments
 */
class Page_manageModules extends Page_selectDepartment {
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

		$mid = 0;
		if(!empty($_REQUEST['mid'])) {
			$mid = intval($_REQUEST['mid']);
		}
		$name = '';
		if(!empty($_REQUEST['name'])){
			$name = $_REQUEST['name'];
		}
		$crn = '';
		if(!empty($_REQUEST['crn'])){
			$crn = $_REQUEST['crn'];
		}
		$credits = '';
		if(!empty($_REQUEST['credits'])){
			$credits = $_REQUEST['credits'];
		}

		$dname = $this->getDepartmentName($did);
		if(!empty($dname))
			
		{	

			if(!empty($_REQUEST['view'])) {
					$mname = $this->getModuleName($mid);
					if(empty($mname)){
					$this->addNotification(
							'error',
							__('Invalid module selected')
						);
						$this->displayModules($did);
					}else{
							
							$this->viewModule($did,$mid);
					}
			}else if(!empty($_REQUEST['create'])){
					if($this->validateForm(true, $mid, $name, $crn, $credits)
				 && $this->createModule($name, $crn, $credits, $did))
					{
						$this->addNotification(
							'notice',
							__('The module was successfully created.')
						);
						$this->displayModules($did);
					}else{
						$this->addNotification(
							'error',
							__('An Error occured while processing your request.')
						);
						$this->editModuleForm($did, $mid);
					}
			}else if(!empty($_REQUEST['edit'])) { 
					if($this->validateForm(false,  $mid, $name, $crn, $credits)
				 && $this->updateModule($mid, $name, $crn, $credits))
					{
						$this->addNotification(
							'notice',
							__('The module was edited successfully.')
						);
						
						$this->viewModule($did, $mid);
					}
					else {
						$this->addNotification(
							'error',
							__('An error occured while processing your request.')
						);
						$this->editModuleForm($did, $mid);
					}
					
			}else if(!empty($_REQUEST['editForm'])){
					$details=$this->getModuleDetails($did,$mid);
						if(empty($details['name']) && $mid > 0) {
							$this->addNotification(
							'error',
							__('Invalid module selected.')
						);
							$this->displayModules($did);
						}
						else {
							$this->editModuleForm($did, $mid);
						}
					
			}else if (! empty($_REQUEST['delete'])) {
				if(!$this->deleteModule($did,$mid)){
					$this->displayModules($did);
				}else{
					$this->viewModule($did, $mid);
				}
				
			} else {
				$this->displayModules($did);
			}
		}else
		{
			$this->addNotification(
					'warning',
					__('Department does not exist.')
				);
			$this->departmentSelector();
		}				
		
	}
	private function displayModules($did)
	{
		$modules = $this->getModules($did);
		$html = $this->printModulesListHeader($did);
		foreach($modules as $key => $value) {
			$html .= $this->printModuleListItem($key, $did, $value);
		}
		$html .= $this->printModuleListFooter();
		$this->addHtml($html);
	}
		/**
	 * Returns an array of classes module
	 *
	 * @return array
	 */
	private function getModules($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `name` FROM `module` where `did` = ?"
		);
		$stmt->bind_param('i', $did);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while ($stmt->fetch()) {
			$arr[$id] = $name ;
		}
		return $arr;
	}
	/**
	 * Prints the header for the list of module
	 *
	 * @return void
	 */
	private function printModulesListHeader($did)
	{
		$html = '';
    	$html .= '<a href="?action=manageModules&did='.$did.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Create Module') . '</a>';
        $html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Module');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of module
	 *
	 * @return void
	 */
	private function printModuleListItem($mid, $did, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageModules&did=%d&mid=%d&view=1">%s</a></li>',
	        	$did,
	        	$mid,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of module
	 *
	 * @return void
	 */
	private function printModuleListFooter()
	{
        $this->addHtml('</ul>');
	}
	
	/**
	 * Prints the details of a module
	 *
	 * @return void
	 */
	private function viewModule($did,$mid)
	{
		$details = $this->getModuleDetails($did, $mid);
		
		
		$html = '';
		$html .= '<h3>' . $details['name'] . '</h3>';
		$html .=  __('Department: ');
		$html .= $this->getDepartmentName($did);
		$html .= '<br/><br/>';
		$html .=  __('CRN: ');
		$html .= $details['crn'];
		$html .= '<br/><br/>';
		$html .=  __('Credits: ');
		$html .= $details['credits'];
		$html .= '<br/><br/>';
		$html .= '<a href="?action=manageModules&did='.$did.'&mid='.$mid.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Edit') . '</a>';
    	$html .= sprintf(
				'<a class="delete" href="?action=manageModules&delete=1&did=%d&mid=%d" data-role="button" data-theme="b">%s</a>',
				$did,
				$mid,
				__('Delete')
			);
		$html .= '<span style="display: none">';
		$html .= __('Are you sure you want to delete this module?');
		$html .= '</span>';
    	$this->addHtml($html);
	}
	/**
	 * Returns an array of module details
	 *
	 * @return array
	 */
	private function getModuleDetails($did, $mid)
	{
		//$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `name`, `credits`,`CRN` FROM `module` where `id` = ?"
		);
		$stmt->bind_param('i', $mid);	
		$stmt->execute();
		$stmt->bind_result($id, $name, $credits, $crn);
		$stmt->fetch();
		$stmt->close();
		return array(
			'mid' => $id,
			'name' => $name,
			'credits' => $credits,
			'crn' => $crn
		);
	}
	/**
	 * Deletes a Module
	 *
	 * @return void
	 */
	private function deleteModule($did, $mid) 
	{
		$db = Db::getLink();
		if($db->query("DELETE FROM module WHERE id=$mid;")) {
			$this->addNotification(
				'notice',
				'The Module was successfully deleted'
			);
		} else {
			$this->addNotification(
				'error',
				'An error occured while processing the request'
			);
		}
	}
	/**
	 * Creates a new module
	 *
	 * @return bool success
	 */
	private function createModule($name, $crn, $credits,$did) {
			
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO `module` (`name`,`CRN`,`credits`,`did` ) VALUES(?, ?, ?, ?);"
		);
		$stmt->bind_param('siii', $name, $crn, $credits, $did);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	
/**
	 * Displays a form for editing/creating a module
	 *
	 * @return void
	 */
	private function editModuleForm($did, $mid)
	{
		$mdetails = $this->getModuleDetails($did, $mid);
		if($mid == 0) {
			$html = '<form method="post" action="?action=manageModules&did=' . $did . '">';
			$html .= '<h3>' . __('Create Module') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html = '<form method="post" action="?action=manageModules&did=' . $did . '&mid=' . $mid . '&view=1">';
			$html .= '<h3>' . __('Edit Module') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="name">' . __('Name') . ': </label>';
		$html .= '<input type="text" name="name" id="name" ';
		$html .= 'value="' . htmlspecialchars($mdetails['name']) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="crn">' . __('CRN') . ': </label>';
		$html .= '<input type="text" name="crn" id="crn" ';
		$html .= 'value="' . htmlspecialchars($mdetails['crn']) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="credits">' . __('Credits') . ': </label>';
		$html .= '<input type="text" name="credits" id="credits" ';
		$html .= 'value="' . htmlspecialchars($mdetails['credits']) . '" />';
		$html .= '</div>';	
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Updates the module details
	 *
	 * @return bool success
	 */

	private function updateModule($mid, $name, $crn, $credits) {
		$db = Db::getLink();
		$stmt = $db->prepare(
			"UPDATE `module` SET `name` = ?, `credits` = ?, `CRN` = ? WHERE `id`=?;"
		);
		$stmt->bind_param('siii', $name, $credits, $crn, $mid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	
	/**
	 * Checks if the form details for editing/creating
	 * a module are valid
	 *
	 * @return bool
	 */
	private function validateForm($isCreate, $mid, $name, $crn, $credits)
	{
		$success = true;
		if (! $isCreate && $mid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid module selected')
			);
		} else if (strlen($name) > 32) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module\'s name must be 32 characters long or less.')
			);
		} else if (strlen($name) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module\'s name must be at least 1 character long.')
			);
		} else if (strlen($crn)< 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module CRN must be greater than 1 character')
			);
		}else if (strlen($crn)> 30) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module CRN must be less than 30 characters')
			);
		} else if (is_int($credits)) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module credits must be a number ')
			);
		}else if (($credits)< 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module credits must be greater than 1 ')
			);
		}else if (($credits)> 60) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Module credits must be less than 60')
			);
		}
		
		return $success;
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
	/**
	 * Returns the name of a class given its id
	 *
	 * @return string
	 */
	private function getModuleName($mid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `module` WHERE `id` = ?;"
		);
		$stmt->bind_param('i', $mid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
}
?>
