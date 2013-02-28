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
		//is this not correct
		if (!empty($_REQUEST['view']) ) 
		{			
			$did = $_REQUEST['did'];
			$cid = $_REQUEST['cid'];
			$this->viewClass($did,$cid);			
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
	 * Returns an array of lecturer's details
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
	private function printClassesListItem($did, $cid, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageClasses&did=%d&cid=%d&view=1">%s</a></li>',
	        	$cid,
	        	$did,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of groups
	 *
	 * @return void
	 */
	private function printClassesListFooter()
	{
        $this->addHtml('</ul>');
	}
		/**
	 * Prints the details of a lecturer
	 *
	 * @return void
	 */
	private function viewClass($did,$cid)
	{
		$details = $this->getClassDetails($did, $cid);
		if (isset($details['cid']));
		$html = '';
		$html .= '<h2>' . $details['name'] . '</h2>';
		$html .=  __('Department: ');
		$html .= $this->getDepartmentName($did);
		$html .= '<br/><br/>';
		$html .= '<a href="?action=manageLecturers&did='.$did.'&cid='.$cid.'&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Edit') . '</a>';
    	$html .= '<a href="?action=manageLecturers&did='.$did.'&cid='.$cid.'&delete=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Delete') . '</a>';
    	$this->addHtml($html);
    	
	}
		/**
	 * Returns an array of a classes details
	 *
	 * @return array
	 */
	private function getClassDetails($did, $cid)
	{
		//$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `name`, `did` FROM `class` where `id` = ?"
		);		
		$stmt->bind_param('i', $cid);
		$stmt->execute();
		$stmt->bind_result($id, $name, $did);
		$stmt->fetch();
		$stmt->close();
		return array(
			'cid' => $id,
			'name' => $name,		
			'did' => $did
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