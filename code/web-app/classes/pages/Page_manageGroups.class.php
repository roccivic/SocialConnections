<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';
/**
 * This page is used by lecturers to manage the
 * groups
 */
class Page_manageGroups extends Page_selectLecturerGroup
{
	public static function getAccessLevel()
	{
		return User::LECTURER;
	}

	public function __construct()
	{
		parent::__construct(true);
	}
	/**
	 * Called from the Page_selectGroup superclass
	 * when the user has selected a group
	 *
	 * @return void
	 */
	public function display($gid)
	{
		$gid=intval($gid);
		$name ='';
		if (!empty($_REQUEST['name'])) 
		{
			$name = $_REQUEST['name'];
		}
		$module = 0;
		if(!empty($_REQUEST['module']))
		{
			$module=$_REQUEST['module'];
		}
		$year = 0;
		if(!empty($_REQUEST['year']))
		{
			$year=$_REQUEST['year'];
		}
		$term = 0;
		if(!empty($_REQUEST['term']))
		{
			$term=$_REQUEST['term'];
		}
		$did = 0;
		if(!empty($_REQUEST['did'])){
			$did = $_REQUEST['did'];
		}
		$sid = 0;
		if(!empty($_REQUEST['sid'])){
			$sid = $_REQUEST['sid'];
		}
		if (! empty($_REQUEST['delete'])) 
		{
			$this->deleteGroup($gid);
			$this->groupSelector(true);
		} else if (! empty($_REQUEST['edit'])) {
			if ($this->validateForm(false, $gid, $name, $module, $year, $term)
				&& $this->updateGroup($gid, $name, $module, $year, $term)
			) {
				$this->addNotification(
					'notice',
					__('The group details were successfully updated.')
				);
				$this->displayGroupDetails($gid);
			} else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$details = $this->getGroupDetails($gid);
				$name = $details['gname'];
				$this->editGroupForm($gid, $name);
			}
		}else if (! empty($_REQUEST['create'])) 
			{
			if ($this->validateForm(true, $gid, $name, $module, $year, $term)
				&& $this->createGroup($name, $module, $year, $term)) 
			{
				$this->addNotification(
					'notice',
					__('The Group was successfully created.')
				);
				$this->groupSelector(true);
			} else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->editGroupForm($gid, $name);
			}
		}else if (! empty($_REQUEST['editForm'])) {
			$details = $this->getGroupDetails($gid);
			$name = $details['gname'];
			if ($gid > 0 && empty($name)) {
				$this->addNotification(
					'error',
					__('The selected group does not exist')
				);
				$this->groupSelector(true);
			} else {
				$this->editGroupForm($gid, $name);
			}
		}else if(! empty($_REQUEST['addingStudent'])){
			if($this->addStudentToGroup($gid, $sid)){
				$this->addNotification(
					'notice',
					__('The Student was successfully added to the group.')
				);
			}
			else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
			}
			$this->displayGroupDetails($gid);
		}else if(!empty($_REQUEST['addStudent'])){
			$this->departmentSelector($gid);
		}else if(!empty($_REQUEST['addStudentForm'])){
			if($did > 0){
				$this->addStudentForm($gid,$did);
			}
			else {
				$this->addNotification(
					'error',
					__('The selected department does not exist')
				);
				$this->groupSelector(true);
			}
		}else if(!empty($_REQUEST['removingStudentFromGroup'])){
			if($this->removeStudentFromGroup($gid, $sid)){
				$this->addNotification(
					'notice',
					__('The Student was successfully removed from the group.')
				);
			}
			else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
			}
			$this->displayGroupDetails($gid);
		}else if(!empty($_REQUEST['removeStudent'])){
			$this->removeStudentForm($gid);
		}else {
			$this->displayGroupDetails($gid);
		}
		
	}
	/**
	 * Displays the details of a group
	 * and links to edit and delete it
	 *
	 * @return void
	 */
	private function displayGroupDetails($gid) {
		$details = $this->getGroupDetails($gid);
		if (isset($details['gid'])) {
			$html  = '<h3>' . $details['gname'] . '</h3>';
			$html .= __('Module: ');
			if(isset($details['module'])) {
			 	$html.= $details['module'];	
			}
			$html .= '<br/>';
			$html .= __('Year: ');
			$html .= $details['year'];
			$html .= '<br/>';
			$html .= __('Semester: ');
			$html .= $details['term'];
			$html .= '<br/><br/>';
			$html .= '<a href="?action=manageGroups&editForm=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Edit').'</a>';
			$html .= '<a href="?action=manageGroups&addStudent=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Add Student').'</a>';
			$html .= '<a href="?action=manageGroups&removeStudent=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Remove Student').'</a>';
			$html .= sprintf(
				'<a onclick="return confirm(\'%s\');" href="?action=manageGroups&delete=1&gid=%d" data-role="button" data-theme="b">%s</a>',
				__('Are you sure you want to delete this group?'),
				$gid,
				__('Delete')
			);
			$this->addHtml($html);
		} else {
			$this->addNotification(
				'warning',
				__('The selected group does not exist')
			);
			$this->groupSelector(true);
		}
	}
	/**
	 * Returns an array of group's details
	 *
	 * @return array
	 */
	private function getGroupDetails($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `group`.`id` , `group`.`name` , `module`.`id`, `moduleoffering`.`term`,`moduleoffering`.`year`
			FROM `group` INNER JOIN `moduleoffering` 
			ON `group`.`moid` = `moduleoffering`.`id`
			LEFT JOIN `module` 
			ON `moduleoffering`.`mid` = `module`.`id`
			WHERE `group`.`id` =?"
				);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($id, $gname, $module,$term,$year);
		$stmt->fetch();
		$stmt->close();
		return array(
			'gid' => $id,
			'gname' => $gname,
			'module' => $module,
			'term' => $term,
			'year' => $year
		);
	}
	/**
	 * Deletes a group
	 *
	 * @return void
	 */
	private function deleteGroup($gid) 
	{
		$db = Db::getLink();
		if($db->query("DELETE FROM `moduleoffering` WHERE `id` IN(SELECT `moid` FROM `group` WHERE `id`=$gid);")) {
			$this->addNotification(
				'notice',
				'The group was successfully deleted'
			);
		} else {
			$this->addNotification(
				'error',
				'An error occured while processing the request'
			);
		}
	}
	/**
	 * Displays a form for editing a group
	 *
	 * @return void
	 */

	
	private function editGroupForm($gid, $name)
	{
		$html = '<form method="post" action="">';
		if($gid == 0) {
			$html .= '<h3>' . __('Create Group') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html .= '<h3>' . __('Edit Group') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="name">' . __('Name') . ': </label>';
		$html .= '<input type="text" name="name" id="name" ';
		$html .= 'value="' . htmlspecialchars($name) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="module">' . __('Module') . ': </label>';
		$html .= '<select id="module" name="module">';
		$details = $this->getGroupDetails($gid);
		$module = $details['module'];
		foreach($this->getModules() as $key => $value) {
			$html .= '<option value="' . $key . '"';
			if ($key == $module) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . htmlspecialchars($value) . '</option>';	
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$year = $details['year'];
		$html .= '<label for="year">' . __('Year') . ': </label>';
		$html .= '<input type="text" name="year" id="year" ';
		$html .= 'value="' . htmlspecialchars($year) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
		$html .= '<legend>'. __('Semester').':</legend>';
		$html .= '<input name="term" id="first" value="1" type="radio"';
		if($details[term] == 1) {
			$html .= 'checked="checked"';
		}
		$html .= '>';
		$html .= '<label for="first">1</label>';
		$html .= '<input name="term" id="second" value="2" type="radio"';
		if($details[term] == 2) {
			$html .= 'checked="checked"';
		}
		$html .= '>';
		$html .= '<label for="second">2</label>';
		$html .= '</fieldset>';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Returns an array of module's details
	 *
	 * @return array
	 */
	private function getModules()
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `name` FROM `module` ORDER BY `name`"
		);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while ($stmt->fetch()) {
			$arr[$id] = $name;
		}
		return $arr;
	}
	/**
	 * Checks if the form details for editing/creating
	 * a group are valid
	 *
	 * @return bool
	 */
	private function validateForm($isCreate, $gid, $name, $module, $year, $term)
	{
		$success = true;
		if (! $isCreate && $gid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid group selected')
			);
		} else if (strlen($name) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Group name must be 64 characters long or less.')
			);
		} else if (strlen($name) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Group name must be at least 1 character long.')
			);
		} else if ($module > 0) {
			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT COUNT(*) FROM `module` WHERE `id` = ?"
			);
			$stmt->bind_param('i', $module);
			$stmt->execute();
			$stmt->bind_result($valid);
			$stmt->fetch();
			$stmt->close();
			if (!$valid) {
				$success = false;
				$this->addNotification(
					'warning',
					__('Invalid module selected.')
				);
			}
		}else if($module < 1) {
			$success = false;
				$this->addNotification(
					'warning',
					__('Invalid module selected.')
				);
		}if(intval($year) < date("Y")) {
			$success = false;
				$this->addNotification(
					'warning',
					__('Invalid year selected.')
				);
		}else if ($term > 0) {
			$valid = false;
			for($i=1;$i<3;$i++)
			{
				if($term==$i)
				{
					$valid = true;
				}
			}
			if (! $valid) {
				$success = false;
				$this->addNotification(
					'warning',
					__('Invalid semester selected.')
				);
			}
		}
		return $success;
	}
	/**
	 * Creates a new group
	 *
	 * @return bool success
	 */
	private function createGroup($name, $module, $year, $term) {
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"INSERT INTO moduleoffering (mid, year, term) VALUES(?, ?, ?);"
		);
		$stmt->bind_param('iii', $module, $year, $term);
		$success = $stmt->execute();
		$last = $db->insert_id;
		$stmt->close();
		if($success){
			$uid = intval($_SESSION['uid']);
			$stmt = $db->prepare(
			"INSERT INTO moduleoffering_lecturer (moid, lid) VALUES(?, ?);"
			);
			$stmt->bind_param('ii', $last, $uid);
			$success = $stmt->execute();
			$stmt->close();
		}
		if($success){
			$stmt = $db->prepare(
			"INSERT INTO `socialconnections`.`group` (
			`id` ,
			`name` ,
			`moid`
			)
			VALUES (
			NULL , ?, ?
			);"
			);
			$stmt->bind_param('si', $name, $last);
			$success = $stmt->execute();
			$stmt->close();
		}
		if($success) {
			$db->query("COMMIT");
		}else {
			$db->query("ROLLBACK");
		}
		return $success;
	}
	
	/**
	 * Updates the group details
	 *
	 * @return bool success
	 */
	private function updateGroup($gid, $name, $module, $year, $term) {
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"UPDATE `group` SET `name` = ? WHERE `id` = ?;"
		);
		$stmt->bind_param('si', $name, $gid);
		$success = $stmt->execute();
		$stmt->close();
		if($success) {
			$stmt = $db->prepare(
			"UPDATE `moduleoffering` SET `mid` = ?, `year` = ?,`term` = ? WHERE `id` IN(SELECT `moid` FROM `group` WHERE `id` = ?);"
			);
			$stmt->bind_param('iiii',$module, $year, $term, $gid);
			$success = $stmt->execute();
			$stmt->close();
		}
		if($success) {
			$db->query("COMMIT");
		}else {
			$db->query("ROLLBACK");
		}
		return $success;
	}
	/**
	 * Displays the list of department
	 *
	 * @return void
	 */
	private function departmentSelector($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT id, name FROM department ORDER BY name"
		);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows) {
			$this->printListHeaderDept();
			$stmt->bind_result($did, $name);
			while ($stmt->fetch()) {
		        $this->printListItemDept($did, $name,$gid);
		    }
		    $this->printListFooterDept();
		} else {
			$this->addNotification(
				'warning',
				__('There are no departments in the system.')
			);
		}
		$stmt->close();
	}
	/**
	 * Prints the header for the list of departments
	 *
	 * @return void
	 */
	private function printListHeaderDept()
	{
        $html  = '';
        $html .= '<input name="addStudentForm" value="1" type="hidden" />';
        $html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Department');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of departments
	 *
	 * @return void
	 */
	private function printListItemDept($did, $name,$gid)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=%s&did=%d&gid=%d&addStudentForm=1">%s</a></li>',
	        	urlencode(htmlspecialchars($_REQUEST['action'])),
	        	$did,
	        	$gid,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of departments
	 *
	 * @return void
	 */
	private function printListFooterDept()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Displays a form for adding a student
	 *
	 * @return void
	 */
	private function addStudentForm($gid, $did)
	{
		$html = '<form method="post" action="">';
		$html .= '<h3>' . __('Add Student') . '</h3>';
		$html .= '<input name="addingStudent" value="1" type="hidden" />';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="sid">' . __('Student') . ': </label>';
		$html .= '<select id="sid" name="sid">';
		foreach($this->getStudentsNotInGroup($did,$gid) as $key => $value) {
			$html .= '<option value="' . $key . '"';
			$html .= '>' . htmlspecialchars($value) . '</option>';	
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Add') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Returns an array of students
	 *
	 * @return array
	 */
	private function getStudents($did)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `cid` IN (
				SELECT id FROM `class` WHERE `did`=?
			)
			ORDER BY `fname` ASC, `lname` ASC;"
		);
		$stmt->bind_param("i",$did);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}
	/**
	 * Adds a student to a group
	 *
	 * @return bool success
	 */
	private function addStudentToGroup($gid, $sid) 
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO `group_student` (`gid`, `sid`) VALUES(?, ?);"
		);
		$stmt->bind_param("ii", $gid, $sid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Displays a form for removing a student
	 *
	 * @return void
	 */
	private function removeStudentForm($gid)
	{
		$html = '<form method="post" action="">';
		$html .= '<h3>' . __('Remove Student') . '</h3>';
		$html .= '<input name="removingStudentFromGroup" value="1" type="hidden" />';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="sid">' . __('Student') . ': </label>';
		$html .= '<select id="sid" name="sid">';
		foreach($this->getStudentsInGroup($gid) as $key => $value) {
			$html .= '<option value="' . $key . '"';
			$html .= '>' . htmlspecialchars($value) . '</option>';	
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Remove') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Returns an array of students in a group
	 *
	 * @return array
	 */
	private function getStudentsInGroup($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `id` IN (
				SELECT sid FROM `group_student` WHERE `gid`=?
			)
			ORDER BY `fname` ASC, `lname` ASC;"
		);
		$stmt->bind_param("i",$gid);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}
	/**
	 * Removes a student from a group
	 *
	 * @return bool success
	 */
	private function removeStudentFromGroup($gid, $sid) 
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"DELETE FROM `group_student` WHERE `gid`=? AND `sid`=?;"
		);
		$stmt->bind_param("ii", $gid, $sid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Returns an array of students that are not in the group
	 *
	 * @return array
	 */
	private function getStudentsNotInGroup($did,$gid)
	{
		$sArray = $this->getStudentsInGroup($gid);
		$success = true;
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `student`
			WHERE `cid` IN (
				SELECT id FROM `class` WHERE `did`=?
			)
			ORDER BY `fname` ASC, `lname` ASC;"
		);
		$stmt->bind_param("i",$did);
		$stmt->execute();
		$stmt->bind_result($id, $fname,$lname);
		while ($stmt->fetch()) {
			foreach($sArray as $key => $value) {
				if($key == $id) {
					$success = false;
				}
			}
			if($success == true) {
				$arr[$id] = $fname . ' ' . $lname;
			}
			$success = true;
		}
		return $arr;
	}
}
?>