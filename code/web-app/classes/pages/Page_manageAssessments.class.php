<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';
/**
 * This page is used by lecturers to manage the
 * groups
 */
class Page_manageAssessments extends Page_selectLecturerGroup
{
	public static function getAccessLevel()
	{
		return User::LECTURER;
	}

	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_selectGroup superclass
	 * when the user has selected a group
	 *
	 * @return void
	 */
	public function display($gid)
	{
		$gid = intval($gid);
		$aid = 0;
		if(!empty($_REQUEST['aid'])) {
			$aid = $_REQUEST['aid'];
		}
		$name = '';
		if(!empty($_REQUEST['name'])) {
			$name = $_REQUEST['name'];
		}
		$weight = 0;
		if(!empty($_REQUEST['weight'])) {
			$weight = $_REQUEST['weight'];
		}
		$results = $this->getResults($gid);
		foreach($results as $key => $value)
		{
			if(!empty($_REQUEST[$key])){
			
			}
		}
		if(!empty($_REQUEST['AssessmentDetails'])){
			if($aid < 0 || $gid < 0) {
				$this->addNotification(
					'error',
					__('No such assessment.')
				);
			}
			else {
				$this->displayAssessmentDetails($aid, $gid);
			}
		}else if(!empty($_REQUEST['create'])){
			if($this->validateForm(true,$aid, $gid, $name, $weight) &&
				$this->createAssessment($gid, $name, $weight)){
				$this->addNotification(
					'notice',
					__('The assessment was successfully created.')
				);
				$this->groupSelector();
			}
			else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->groupSelector();
			}
		}else if(!empty($_REQUEST['edit'])){
			if($this->validateForm(false,$aid, $gid, $name, $weight) &&
				$this->editAssessment($aid, $name, $weight)){
				$this->addNotification(
					'notice',
					__('The assessment was edited successfully.')
				);
				$this->groupSelector();
			}
			else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->groupSelector();
			}
		}else if(!empty($_REQUEST['editForm'])){
			$this->editForm($aid, $gid);
		}else if(!empty($_REQUEST['delete'])){ 
			if($this->deleteAssessment($gid, $aid)){
				$this->addNotification(
					'notice',
					__('The assessment was deleted successfully.')
				);
				$this->groupSelector();
			}
			else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->groupSelector();
			}
		}else if(!empty($_REQUEST['manageResults'])){
			$this->manageResults($aid, $gid);
		}else if(!empty($_REQUEST['publish'])){
			if($this->publishResults($aid)) {
				$this->addNotification(
					'notice',
					__('The assessment results were published.')
				);
				$this->groupSelector();
			}
			else {
				$this->addNotification(
					'error',
					__('An error occured while processing your request.')
				);
				$this->groupSelector();
			}
		}else if(!empty($_REQUEST['update'])){ 
			if($this->validateUpdateForm($results)
				&& $this->editResults($aid, $results)){
				$this->addNotification(
					'notice',
					__('The assessment results were updated successfully.')
				);
				$this->groupSelector();
			}else {
				$this->addNotification(
					'warning',
					__('An error occured while processing your request.')
				);
				$this->groupSelector();
			}
		}else {
			$this->displayAssessments($gid);
		}
		
	}
	/**
	 * Displays a list of assessments
	 *
	 * @return array
	 */
	private function displayAssessments($gid)
	{
		$assessments = $this->getAssessments($gid);
		$html = $this->printAssessmentsListHeader($gid);
		foreach($assessments as $key => $value) {
			$html .= $this->printAssessmentsListItem($key, $gid, $value);
		}
		$html .= $this->printAssessmentsListFooter();
		$this->addHtml($html);
	}
	/**
	 * Returns an array of assessments
	 *
	 * @return array
	 */
	private function getAssessments($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `name` FROM `assessment` where `moid` IN
			(SELECT `moid` FROM `group` WHERE `id` = ?);"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while ($stmt->fetch()) {
			$arr[$id] = $name;
		}
		return $arr;
	}
	/**
	 * Prints the header for the list of assessments
	 *
	 * @return void
	 */
	private function printAssessmentsListHeader($gid)
	{
		$html = '';
		 $html  = '<a href="?action=manageAssessments&editForm=1&gid='.$gid.'"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Create Assessment') . '</a>';
    	$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Assessment');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of assessments
	 *
	 * @return void
	 */
	private function printAssessmentsListItem($aid,$gid,$name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=manageAssessments&aid=%d&gid=%d&AssessmentDetails=1">%s</a></li>',
	        	$aid,
	        	$gid,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of assessments
	 *
	 * @return void
	 */
	private function printAssessmentsListFooter()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Displays the details of an assessment
	 * and links to edit and delete it, results of assessment
	 * 
	 *
	 * @return void
	 */
	private function displayAssessmentDetails($aid,$gid) {
		$details = $this->getAssessmentDetails($aid);
		if (isset($details['aid'])) {
			$html  = '<h3>' . $details['name'] . '</h3>';
			$html .= __('Weight: ');
			$html .= $details['weight'] . '%';
			$html .= '<br/>';	
			$html .= __('Results published: ');
			if($details['isDraft'] == 0){
				$html .= __('Yes');
			}
			else {
				$html .= __('No');
			}
			$			$html .= '<br/><br/>';
			$html .= '<a href="?action=manageAssessments&editForm=1&aid='.$aid.'&gid='.$gid.'" data-role="button" data-theme="b">'.__('Edit').'</a>';
			$html .= '<a href="?action=manageAssessments&manageResults=1&aid='.$aid.'&gid='.$gid.'" data-role="button" data-theme="b">'.__('Manage Results').'</a>';
			$html .= sprintf(
				'<a onclick="return confirm(\'%s\');" href="?action=manageAssessments&delete=1&gid=%d&aid=%d" data-role="button" data-theme="b">%s</a>',
				__('Are you sure you want to delete this assessment?'),
				$gid,
				$aid,
				__('Delete')
			);
			$this->addHtml($html);
		} else {
			$this->addNotification(
				'warning',
				__('The selected assessment does not exist')
			);
			
		}
	}
	/**
	 * Returns an array of assessment's details
	 *
	 * @return array
	 */
	private function getAssessmentDetails($aid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `moid`, `name`, `weight`, `isDraft` FROM `assessment` WHERE `id`=?"
		);
		$stmt->bind_param('i', $aid);
		$stmt->execute();
		$stmt->bind_result($moid, $name, $weight, $isDraft);
		$stmt->fetch();
		$stmt->close();
		return array(
			'aid' => $aid,
			'moid' => $moid,
			'name' => $name,
			'weight' => $weight,
			'isDraft' => $isDraft,
		);
	}
	/**
	 * Displays a form for editing a assessment
	 *
	 * @return void
	 */
	private function editForm($aid, $gid)
	{
		$details = $this->getAssessmentDetails($aid);
		$html = '<form method="post" action="">';
		$html .= '<input name="aid" value="'.$aid.'" type="hidden" />';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		if($aid == 0) {
			$html .= '<h3>' . __('Create Assessment') . '</h3>';
			$html .= '<input name="create" value="1" type="hidden" />';
		} else {
			$html .= '<h3>' . __('Edit Assessment') . '</h3>';
			$html .= '<input name="edit" value="1" type="hidden" />';
		}
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="name">' . __('Name') . ': </label>';
		$html .= '<input type="text" name="name" id="name" ';
		$html .= 'value="' . htmlspecialchars($details['name']) . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="weight">' . __('Weight') . ': </label>';
		$html .= '<input type="text" name="weight" id="weight" ';
		$html .= 'value="' . htmlspecialchars($details['weight']) . '" />';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Checks if the form details for editing/creating
	 * an assessment are valid
	 *
	 * @return bool
	 */
	private function validateForm($isCreate,$aid, $gid, $name, $weight)
	{
		$success = true;
		if (!$isCreate && $aid < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Invalid assessment selected')
			);
		} else if (strlen($name) > 64) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Assessment\'s name must be 64 characters long or less.')
			);
		} else if (strlen($name) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Assessment\'s name must be at least 1 character long.')
			);
		}else if (($weight+$this->findOverallWeight($gid)) > 100  || intval($weight) < 1) {
			$success = false;
			$this->addNotification(
				'warning',
				__('Weight should be more than 1 and overall weight of all assessments in a group should be 100 or less.')
			);
		}
		
		return $success;
	}
	/**
	 * Returns a value of all assessment's weights of a given group
	 * an assessment are valid
	 *
	 * @return int
	 */
	private function findOverallWeight($gid)
	{
		$result = 0;
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `weight` FROM `assessment` where `moid` IN
			(SELECT `moid` FROM `group` WHERE `id` = ?)"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($weight);
		while ($stmt->fetch()) {
			$result += $weight;
		}
		return $result;
	}
	/**
	 * Creates a new assessment
	 *
	 * @return bool success
	 */
	private function createAssessment($gid, $name, $weight) {
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		$stmt = $db->prepare(
			"SELECT `id` FROM `moduleoffering` WHERE `id` IN
			(SELECT `moid` FROM `group` WHERE `id`=?)"
		);
		$stmt->bind_param('i', $gid);
		$success = $stmt->execute();
		$stmt->bind_result($moid);
		$stmt->close();
		if($success){
			$stmt = $db->prepare(
				"INSERT INTO `assessment` (`name`, `weight`, `moid`) VALUES(?, ?, ?);"
			);
			$stmt->bind_param('sii', $name, $weight, $moid);
			$success = $stmt->execute();
			$aid = $db->insert_id;
			$stmt->close();
		}
		if($success)
		{
			$students = $this->getStudents($gid);
			foreach($students as $key => $value) {
				$stmt = $db->prepare(
					"INSERT INTO `results` (`aid`, `sid`, `grade`) VALUES(?, ?, 0);"
				);
				$stmt->bind_param('ii', $aid, $key);
				$success = $stmt->execute();
				$stmt->close();
			}
		}
		if($success) {
			$db->query("COMMIT");
		}else {
			$db->query("ROLLBACK");
		}
		return $success;
	}
	/**
	 * Update an assessment
	 *
	 * @return bool success
	 */
	private function editAssessment($aid, $name, $weight) {
		$db = Db::getLink();
		$stmt = $db->prepare(
			"UPDATE `assessment` SET `name` = ?, `weight` = ? WHERE `id`=? ;"
			);
		$stmt->bind_param('sii', $name, $weight, $aid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Deletes an assessment
	 *
	 * @return bool success
	 */
	private function DeleteAssessment($gid, $aid) 
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"DELETE FROM `assessment` WHERE `id` = ?;"
		);
		$stmt->bind_param("i", $aid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
	/**
	 * Manage the results of students
	 *
	 * @return void
	 */
	private function manageResults($aid, $gid)
	{
		$students = $this->getStudents($gid);
		$results = $this->getResults($gid);
		$html = '<form method="post" action="">';
		$html .= '<input name="aid" value="'.$aid.'" type="hidden" />';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<table border="0" width="100%" cellpadding="4">';
		$html .= '<tr><td><b>Name</b></td><td><b>Grade</b></td></tr>';
		foreach($students as $key => $value) {
			$html .= '<tr>';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<td width="20%"><label for="'.$key.'">' . $value. ': </label></td>';
			$html .= '<td><input type="text" name="'.$key.'" id="'.$key.'" ';
			foreach($results as $sid => $grade)
			{
				if($key == $sid) {
					$html .= 'value="' . $grade . '" />';
				}
			}
			$html .= '</td></div>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$assessment = $this->getAssessmentDetails($aid);
        if($assessment['isDraft']){
        		$html .= '<a href="?action=manageAssessments&update=1&aid='.$aid.'&gid='.$gid.'"';
		        $html .= ' data-role="button" data-theme="b">';
		        $html .= __('Update') . '</a>';
	       		 $html .= sprintf(
				'<a onclick="return confirm(\'%s\');" href="?action=manageAssessments&publish=1&gid=%d&aid=%d" data-role="button" data-theme="b">%s</a>',
				__('Are you sure you want to publish these results?'),
				$gid,
				$aid,
				__('Publish'));
        }
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Returns an array of students's details
	 *
	 * @return array
	 */
	private function getStudents($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `fname`, `lname` FROM `student` WHERE `id` IN
			(SELECT `sid` FROM `group_student` WHERE `gid` = ?)"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($id, $fname, $lname);
		while ($stmt->fetch()) {
			$arr[$id] = $fname . ' ' . $lname;
		}
		return $arr;
	}
	/**
	 * Publish Results
	 *
	 * @return bool
	 */
	private function publishResults($aid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"UPDATE `assessment` SET `isDraft` = 0 WHERE `id` = ?"
		);
		$stmt->bind_param('i', $aid);
		$success = $stmt->execute();
		return $success;
	}
	/**
	 * Returns an array of students's results
	 *
	 * @return array
	 */
	private function getResults($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `sid`, `grade` FROM `results` WHERE `aid` IN
			(SELECT `id` FROM `assessment` WHERE `moid` IN
			(SELECT `moid` FROM `group` WHERE `id`=?))"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($id, $grade);
		while ($stmt->fetch()) {
			$arr[$id] = $grade;
		}
		return $arr;
	}
	/**
	 * Validate form. Results of students
	 *
	 * @return array
	 */
	private function validateUpdateForm($results)
	{
		$success = true;
		foreach($results as $key => $value) {
			if($success) {
				if($value < 0 || $value > 100) {
					$success = false;
				}
			}
		}
		return $success;
	}
	/**
	 * Edits Results of students
	 *
	 * @return success
	 */
	private function editResults($aid, $results)
	{
		$success = true;
		$db = Db::getLink();
		$db->query("SET AUTOCOMMIT=0");
		$db->query("START TRANSACTION");
		foreach($results as $key => $value) {
			if($success) {
				$stmt = $db->prepare(
				"UPDATE `results` SET `grade` = ? WHERE `sid` = ? AND `aid` = ?"
				);
				$stmt->bind_param('iii',$value, $key, $aid);
				$success = $stmt->execute();
				$stmt->bind_result($id, $grade);
			}
		}
		if($success) {
			$db->query("COMMIT");
		}else {
			$db->query("ROLLBACK");
		}
		return $success;

	}
	


}
?>