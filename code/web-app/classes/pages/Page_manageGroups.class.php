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
		$lid = 0;
		if(!empty($_REQUEST['lid'])){
			$lid = $_REQUEST['lid'];
		}
		$gname = $this->getGroupName($gid);
		$dname = $this->getDepartmentName($did);
		if(!empty($gname) || !empty($_REQUEST['editForm'])) {
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
					$message = __('The student was successfully added to the group.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => true,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'notice',
							$message
						);
					}
				} else {
					$message = __('An error occured while processing your request.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => false,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'error',
							$message
						);
					}
				}
				$this->addStudentForm($gid, $did);
			}else if(!empty($_REQUEST['addStudent'])){
				$this->departmentSelector(true, $gid);
			}else if(!empty($_REQUEST['addStudentForm'])){
				if(!empty($dname)){
					$this->addStudentForm($gid,$did);
				}
				else {
					$this->addNotification(
						'error',
						__('The selected department does not exist')
					);
					$this->groupSelector(true);
				}
			} else if(!empty($_REQUEST['removingStudentFromGroup'])){
				if ($this->removeStudentFromGroup($gid, $sid)){
					$message = __('The student was successfully removed from the group.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => true,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'notice',
							$message
						);
					}
				} else {
					$message = __('An error occured while processing your request.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => false,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'error',
							$message
						);
					}
				}
				$this->removeStudentForm($gid, $did);
			}
			else if(!empty($_REQUEST['removeStudent']))
			{
				if(!empty($gname)) {
					$this->removeStudentForm($gid, $did);
				}
				else {
					$this->addNotification(
						'error',
						__('The selected group does not exist.')
					);
				}
			}
			else if(!empty($_REQUEST['addLecturer'])) 
			{
				$this->departmentSelector(false, $gid);
			}
			else if(! empty($_REQUEST['addingLecturer']))
			{
				if($this->addLecturerToGroup($gid, $lid))
				{
					$message = __('The Lecturer was successfully added to teach group.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => true,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'notice',
							$message
						);

					}
				} else {
					$message = __('An error occured while processing your request.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => false,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'error',
							$message
						);

					}
				}
				$this->addLecturerForm($gid, $did);
			}
			else if(!empty($_REQUEST['removingLecturerFromGroup']))
			{
				if($this->removeLecturerFromGroup($gid, $lid)) {	
					$message = __('The Lecturer was successfully removed from the group.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => true,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'notice',
							$message
						);

					}
				} else {
					$message = __('An error occured while processing your request.');
					if (! empty($_REQUEST['ajax'])) {
						header("Content-Type: application/json; charset=UTF-8");
						exit(
							json_encode(
								array(
									'success' => false,
									'message' => $message
								)
							)
						);
					} else {
						$this->addNotification(
							'error',
							$message
						);

					}
				}
				$this->removeLecturerForm($gid);
			}
			else if(!empty($_REQUEST['removeLecturer']))
			{
				if(!empty($gname)) {
					$this->removeLecturerForm($gid);
				}
				else {
					$this->addNotification(
						'error',
						__('The selected group does not exist.')
					);
				}
			}
			else if(!empty($_REQUEST['addLecturerForm'])) 
			{
				if(!empty($dname))
				{
					$this->addLecturerForm($gid, $did);
				}
				else 
				{
					$this->addNotification(
						'error',
						__('The selected department does not exist.')
					);
					$this->departmentSelector(false, $gid);
				}
			}
			else 
			{
				$this->displayGroupDetails($gid);
			}
		}
		else 
		{
			$this->addNotification(
						'error',
						__('The selected group does not exist!.')
					);
			$this->groupSelector(true);
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
			$html .= '<a href="?action=manageGroups&addLecturer=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Add Lecturer').'</a>';
			$html .= '<a href="?action=manageGroups&removeLecturer=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Remove Lecturer').'</a>';
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
			"SELECT `group`.`id` , `group`.`name` , `module`.`name`, `module`.`id`, `moduleoffering`.`term`,`moduleoffering`.`year`
			FROM `group` INNER JOIN `moduleoffering` 
			ON `group`.`moid` = `moduleoffering`.`id`
			LEFT JOIN `module` 
			ON `moduleoffering`.`mid` = `module`.`id`
			WHERE `group`.`id` =?"
				);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($id, $gname, $module,$mid,$term,$year);
		$stmt->fetch();
		$stmt->close();
		return array(
			'gid' => $id,
			'gname' => $gname,
			'module' => $module,
			'mid' => $mid,
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
		$module = $details['mid'];
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
		if(empty($details['year'])){
			$year = date("Y");
		}
		else {
			$year = $details['year'];
		}
		$html .= '<label for="year">' . __('Year') . ': </label>';
		$html .= '<input type="text" name="year" id="year" ';
		$html .= 'value="' . htmlspecialchars($year) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
		$html .= '<legend>'. __('Semester').':</legend>';
		$html .= '<input name="term" id="first" value="1" type="radio"';
		if($details['term'] == 1 || empty($details['term'])) {
			$html .= 'checked="checked"';
		}
		$html .= '>';
		$html .= '<label for="first">1</label>';
		$html .= '<input name="term" id="second" value="2" type="radio"';
		if($details['term'] == 2) {
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
	private function departmentSelector($isStudent, $gid)
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
		        $this->printListItemDept($isStudent, $did, $name,$gid);
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
	private function printListItemDept($isStudent, $did, $name,$gid)
	{
		if($isStudent)
		{
			$param = 'addStudentForm';
		}
		else 
		{
			$param = 'addLecturerForm';
		}
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=%s&did=%d&gid=%d&'.$param.'=1">%s</a></li>',
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
		$students = $this->getStudentsNotInGroup($did, $gid);
		$this->addHtml(
			"<h3>"
			. sprintf(
				__('Select student to add to group `%s`'),
				$this->getGroupName($gid)
			)
			. "</h3>"
		);
		$html = $this->displayListHeader($gid);
		foreach ($students as $key => $value) {
			$html .= $this->printAddStudentListItem($key, $gid, $value, $did);
		}
		$html .= $this->displayListFooter();
		$this->addHtml($html);
	}
	/**
		 * Prints the header for the list of students
		 *
		 * @return void
		 */
		private function displayListHeader($gid)
		{
			$html  = '<ul id="ajaxlist" data-role="listview" data-divider-theme="b" ';
	        $html .= 'data-filter-placeholder="' . __('Search...') . '" ';
	        $html .= 'data-filter="true" data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">';
	        $html .= '</li>';
	        $this->addHtml($html);
		}
		/**
		 * Prints a single item for the list of students
		 *
		 * @return void
		 */
		private function printAddStudentListItem($sid, $gid, $name, $did)
		{
	        $this->addHtml(
		        sprintf(
		        	'<li><a href="?action=manageGroups&addingStudent=1&gid=%d&sid=%d&did=%d">%s</a></li>',
		        	$gid,
		        	$sid,
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
		private function displayListFooter()
		{
	        $this->addHtml(
	        	'</ul>'
	        );
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
		if (! $this->isStudentIngroup($sid, $gid)) {
			return false;
		}
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
	private function removeStudentForm($gid,$did)
	{
		$students = $this->getStudentsInGroup($gid);
		$this->addHtml(
			"<h3>"
			. sprintf(
				__('Select student to remove from group `%s`'),
				$this->getGroupName($gid)
			)
			. "</h3>"
		);
		$html = $this->displayListHeader($gid);
		foreach ($students as $key => $value) {
			$html .= $this->printRemoveStudentListItem($key, $gid, $value, $did);
		}
		$html .= $this->displayListFooter();
		$this->addHtml($html);
	}
	/**
		 * Prints a single item for the list of students
		 *
		 * @return void
		 */
		private function printRemoveStudentListItem($sid, $gid, $name, $did)
		{
	        $this->addHtml(
		        sprintf(
		        	'<li><a href="?action=manageGroups&removingStudentFromGroup=1&gid=%d&sid=%d&did=%d">%s</a></li>',
		        	$gid,
		        	$sid,
		        	$did,
		        	$name
		        	
		        )
	        );
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
		if (! $this->isStudentIngroup($sid, $gid)) {
			return false;
		}
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
	/**
	 * Returns the name of a group given its id
	 *
	 * @return string
	 */
	private function getGroupName($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `group` WHERE `id` = ?;"
		);
		$stmt->bind_param('i', $gid);
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
	/**
	 * A form for adding lecturers
	 *
	 * @return void
	 */
	private function addLecturerForm($gid, $did)
	{
		$students = $this->getLecturersNotInGroup($did, $gid);
		$this->addHtml("<h3>" . __('Select Lecturer') . "</h3>");
		$html = $this->displayListHeader($gid);
		foreach ($students as $key => $value) {
			$html .= $this->printAddLecturerListItem($key, $gid, $value, $did);
		}
		$html .= $this->displayListFooter();
		$this->addHtml($html);
	}
		/**
		 * Prints a single item for the list of students
		 *
		 * @return void
		 */
		private function printAddLecturerListItem($lid, $gid, $name, $did)
		{
	        $this->addHtml(
		        sprintf(
		        	'<li><a href="?action=manageGroups&addingLecturer=1&gid=%d&lid=%d&did=%d">%s</a></li>',
		        	$gid,
		        	$lid,
		        	$did,
		        	$name
		        	
		        )
	        );
		}
		/**
	 * Returns an array of lecturers that are not teaching the group
	 *
	 * @return array
	 */
	private function getLecturersNotInGroup($did,$gid)
	{
		$sArray = $this->getLecturersInGroup($gid);
		$success = true;
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `lecturer`
			WHERE `did` = ?
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
	/**
	 * Returns an array of  lecturers teaching the group
	 *
	 * @return array
	 */
	private function getLecturersInGroup($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname`
			FROM `lecturer`
			WHERE `id` IN (
				SELECT `lid` FROM `moduleoffering_lecturer` WHERE `moid` IN
				(SELECT `group`.`moid` FROM `group` WHERE `group`.`id` = ?)
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
	 * links a lecturer to a group
	 *
	 * @return bool success
	 */
	private function addLecturerToGroup($gid, $lid) 
	{
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"SELECT `moid` FROM `group` WHERE `id` = ?;"
		);
		$stmt->bind_param("i", $gid);
		$success = $stmt->execute();
		$stmt->bind_result($moid);
		$stmt->fetch();
		$stmt->close();
		if($success){
			$stmt = $db->prepare(
			"INSERT INTO moduleoffering_lecturer (moid, lid) VALUES(?, ?);"
			);
			$stmt->bind_param('ii', $moid, $lid);
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
	 * Displays a form for removing a lecturer
	 *
	 * @return void
	 */
	private function removeLecturerForm($gid)
	{
		$lecturers = $this->getLecturersInGroup($gid);
		$this->addHtml("<h3>" . __('Select Lecturer') . "</h3>");
		$html = $this->displayListHeader($gid);
		foreach ($lecturers as $key => $value) {
			$html .= $this->printRemoveLecturerListItem($key, $gid, $value);
		}
		$html .= $this->displayListFooter();
		$this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of students
	 *
	 * @return void
	 */
	private function printRemoveLecturerListItem($lid, $gid, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageGroups&removingLecturerFromGroup=1&gid=%d&lid=%d">%s</a></li>',
	        	$gid,
	        	$lid,
	        	$name
	        	
	        )
        );
	}
	/**
	 * Removes a lecturer from a group
	 *
	 * @return bool success
	 */
	private function removeLecturerFromGroup($gid, $lid) 
	{
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"SELECT `moid` FROM `group` WHERE `id` = ?;"
		);
		$stmt->bind_param("i", $gid);
		$success = $stmt->execute();
		$stmt->bind_result($moid);
		$stmt->fetch();
		$stmt->close();
		if($success){
			$stmt = $db->prepare(
			"DELETE FROM `moduleoffering_lecturer` WHERE `moid` = ? AND `lid` = ?;"
			);
			$stmt->bind_param('ii', $moid, $lid);
			$success = $stmt->execute();
			$stmt->fetch();
			$stmt->close();
		}
		if($success) {
			$db->query("COMMIT");
		}else {
			$db->query("ROLLBACK");
		}
		return $success;
	}

	private function isStudentIngroup($sid, $gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT COUNT(*)
			FROM `group_student`
			WHERE `gid` = ?
			AND `sid` = ?;"
		);
		$stmt->bind_param('ii', $gid, $sid);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		return $result;
	}
}
?>