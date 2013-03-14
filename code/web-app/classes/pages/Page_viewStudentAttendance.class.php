<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

if (isset($_REQUEST['other']) || isset($_REQUEST['did'])) {
	require_once 'classes/pages/abstract/Page_selectDepartment.class.php';
	/**
	 * This page is used by lecturers to view the
	 * attendance of students in their groups
	 */
	class Page_viewStudentAttendance extends Page_selectDepartment {
		/**
	     * A user must be at least a student to view this page
	     */
	    public static function getAccessLevel()
	    {
	        return User::LECTURER;
	    }
		public function __construct()
		{
			parent::__construct();
		}
		protected function display($did) {

			$sid = 0;
			if (! empty($_REQUEST['sid'])) {
				$sid = intval($_REQUEST['sid']);
			}
			if ($sid > 0) {
				if (strlen($this->getStudentName($sid)) > 1) {
					$this->showTotalAttendance($sid);
					$this->showTermAttendance($sid);
				} else {
					$this->addNotification(
						'error',
						__('Invalid student selected')
					);
					$this->printStudentSelector($did);
				}
			} else {
				$this->printStudentSelector($did);
			}
		}
		/**
		 * Retrieves the total attendance 
		 * of a student from the database
		 *
		 * @return array
		 */
		private function getTotalStudentAttendance($sid)
		{
			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance` 
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
				) AS `overall` , (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance`
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
					AND `isLecture` = 0
				) AS `labs` , (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance`
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
					AND `isLecture` = 1
				) AS `lectures` "
			);
			$stmt->bind_param(
				'iii',
				$sid,
				$sid,
				$sid
			);
			$stmt->execute();
			$stmt->bind_result($overall, $labs, $lectures);
			$stmt->fetch();
			$stmt->close();

			return array(
				'overall' => $overall,
				'labs' => $labs,
				'lectures' => $lectures
			);
		}
		/**
		 * Retrieves the total attendance 
		 * of a student from the database
		 *
		 * @return array
		 */
		private function getTermStudentAttendance($sid, $year, $term)
		{
			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance` 
					ON `attendance`.`id` = `student_attendance`.`aid`
					INNER JOIN `group`
					ON `group`.`id` = `attendance`.`gid`
					INNER JOIN `moduleoffering`
					ON `moduleoffering`.`id` = `group`.`moid`
					WHERE `sid` = ?
					AND `year` = ?
					AND `term` = ?
				) AS `overall` , (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance`
					ON `attendance`.`id` = `student_attendance`.`aid`
					INNER JOIN `group`
					ON `group`.`id` = `attendance`.`gid`
					INNER JOIN `moduleoffering`
					ON `moduleoffering`.`id` = `group`.`moid`
					WHERE `sid` = ?
					AND `year` = ?
					AND `term` = ?
					AND `isLecture` = 0
				) AS `labs` , (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance`
					ON `attendance`.`id` = `student_attendance`.`aid`
					INNER JOIN `group`
					ON `group`.`id` = `attendance`.`gid`
					INNER JOIN `moduleoffering`
					ON `moduleoffering`.`id` = `group`.`moid`
					WHERE `sid` = ?
					AND `year` = ?
					AND `term` = ?
					AND `isLecture` = 1
				) AS `lectures` "
			);
			$stmt->bind_param(
				'iiiiiiiii',
				$sid,
				$year,
				$term,
				$sid,
				$year,
				$term,
				$sid,
				$year,
				$term
			);
			$stmt->execute();
			$stmt->bind_result($overall, $labs, $lectures);
			$stmt->fetch();
			$stmt->close();

			return array(
				'overall' => $overall,
				'labs' => $labs,
				'lectures' => $lectures
			);
		}
		/**
		 * Prints the header for the list of students
		 *
		 * @return void
		 */
		private function printStudentListHeader($did)
		{
			$html  = '<ul data-role="listview" data-divider-theme="b" ';
	        $html .= 'data-filter-placeholder="' . __('Search...') . '" ';
	        $html .= 'data-filter="true" data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">';
	        $html .= sprintf(
	        	__('Students of department `%s`'),
	        	htmlspecialchars($this->getDepartmentName($did))
	        );
	        $html .= '</li>';
	        $this->addHtml($html);
		}
		/**
		 * Prints a single item for the list of students
		 *
		 * @return void
		 */
		private function printStudentListItem($did, $sid, $name)
		{
	        $this->addHtml(
		        sprintf(
		        	'<li><a href="?action=viewStudentAttendance&did=%d&sid=%d">%s</a></li>',
		        	$did,
		        	$sid,
		        	$name
		        )
	        );
		}
		/**
		 * Prints the footer for the list of students
		 *
		 * @return void
		 */
		private function printStudentListFooter()
		{
	        $this->addHtml(
	        	'</ul>'
	        );
		}
				/**
		 * Displays a list of student
		 *
		 * @return void
		 */
		private function printStudentSelector($did)
		{
			if (! strlen($this->getDepartmentName($did))) {
				$this->addNotification(
					'error',
					__('Invalid department selected')
				);
				$this->departmentSelector();
			} else {
				$students = $this->getStudentsInDepartment($did);
				if (count($students) > 0) {
					$this->addHtml("<h3>" . __('Select Student') . "</h3>");
					$html = $this->printStudentListHeader($did);
					foreach ($students as $key => $value) {
						$html .= $this->printStudentListItem($did, $key, $value[0]);
					}
					$html .= $this->printStudentListFooter();
					$this->addHtml($html);
				} else {
					$this->addNotification(
						'warning',
						__('The are no students in this department')
					);
					$this->departmentSelector();
				}
			}
		}
		/**
		 * Displays the attendance of a student
		 *
		 * @return void
		 */
		private function showTotalAttendance($sid)
		{
			$this->addHtml(
				"<h3>" . htmlspecialchars($this->getStudentName($sid)) . "</h3>"
			 );

	        $html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">';
	        $html .= __('Average attendance');
	        $html .= '</li>';

	        $attendance = $this->getTotalStudentAttendance($sid);
	        $data = array(
	        	__('Lectures') => $attendance['lectures'],
	        	__('Labs') => $attendance['labs'],
	        	__('Total') => $attendance['overall']
	        );

	        foreach ($data as $key => $value) {
		    	$html .= '<li>';
		        $html .= $key;
		        $html .= '<span class="ui-li-count">';
		        $html .= round($value * 100) . ' %';
		        $html .= '</span>';
		        $html .= '</li>';
	    	}

	        $html .= '</ul>';

	        $this->addHtml($html);
		}
		/**
		 * Displays the attendance of a student
		 *
		 * @return void
		 */
		private function showTermAttendance($sid)
		{
			$html = '';
			foreach ($this->getTerms($sid) as $value) {
		        $html .= '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
		        $html .= '<li data-role="list-divider" role="heading">';
		        $html .= sprintf(
		        	__('Year %d, Semester %d'),
		        	$value['year'],
		        	$value['term']
		        );  
		        $html .= '</li>';

		        $attendance = $this->getTermStudentAttendance($sid, $value['year'], $value['term']);
		        $data = array(
		        	__('Lectures') => $attendance['lectures'],
		        	__('Labs') => $attendance['labs'],
		        	__('Total') => $attendance['overall']
		        );

		        foreach ($data as $key => $value) {
			    	$html .= '<li>';
			        $html .= $key;
			        $html .= '<span class="ui-li-count">';
			        $html .= round($value * 100) . ' %';
			        $html .= '</span>';
			        $html .= '</li>';
		    	}
		        $html .= '</ul>';
        	}
	        $this->addHtml($html);
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
		 * Retrieves a list of terms that
		 * the user is registered for
		 *
		 * @return @array
		 */
	    protected function getTerms($sid)
	    {
	        $arr = array();
	        $db = Db::getLink();
	        $stmt = $db->prepare(
	            'SELECT `year` , `term`
				FROM `moduleoffering`
				INNER JOIN `group` ON `group`.`moid` = `moduleoffering`.`id`
				INNER JOIN `group_student` ON `group_student`.`gid` = `group`.`id`
				INNER JOIN `student` ON `group_student`.`sid` = `student`.`id`
				WHERE `student`.`id` = ?
				GROUP BY `year`, `term`
				ORDER BY `year` DESC, `term` DESC;'
	        );
	        $stmt->bind_param('i', $sid);
	        $stmt->execute();
	        $stmt->bind_result($year, $term);
	        while ($stmt->fetch()) {
	            $arr[] = array(
	                'year' => $year,
	                'term' => $term
	            );
	        }
	        $stmt->close();
	        return $arr;
	    }
	}
} else {
	require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';

	/**
	 * This page is used by lecturers to view the
	 * attendance of students in their groups
	 */
	class Page_viewStudentAttendance extends Page_selectLecturerGroup {
		/**
	     * A user must be at least a student to view this page
	     */
	    public static function getAccessLevel()
	    {
	        return User::LECTURER;
	    }

		public function __construct()
		{
			parent::__construct();
		}
		/**
		 * Shows a page after a group has been selected
		 *
		 * @return void
		 */
		protected function display($gid) {
			$sid = 0;
			if (! empty($_REQUEST['sid'])) {
				$sid = intval($_REQUEST['sid']);
			}
			if ($sid > 0) {
				if ($this->isStudentIngroup($sid, $gid)) {
					$this->showAttendance($gid, $sid);
				} else {
					$this->addNotification(
						'error',
						__('Invalid student or group selected')
					);
					$this->printStudentSelector($gid);
				}
			} else {
				$this->printStudentSelector($gid);
			}
		}
		/**
		 * Displays the attendance of a student
		 *
		 * @return void
		 */
		private function showAttendance($gid, $sid)
		{
			$this->addHtml(
				"<h3>" . htmlspecialchars($this->getStudentName($sid)) . "</h3>"
			 );

	        $html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">`';
	        $html .= $this->getGroupName($gid);
	        $html .= '`</li>';

	        $attendance = $this->getStudentAttendance($gid, $sid);
	        $data = array(
	        	__('Lectures') => $attendance['lectures'],
	        	__('Labs') => $attendance['labs'],
	        	__('Total') => $attendance['overall']
	        );

	        foreach ($data as $key => $value) {
		    	$html .= '<li>';
		        $html .= $key;
		        $html .= '<span class="ui-li-count">';
		        $html .= round($value * 100) . ' %';
		        $html .= '</span>';
		        $html .= '</li>';
	    	}

	        $html .= '</ul>';

	        $html .= '<a data-role="button" data-theme="b" href="?action=viewStudentAttendance&did=1&sid=' . $sid . '">';
	        $html .=  __('View aggregate attendance') . '</a>';

	        $this->addHtml($html);
		}
		/**
		 * Puts some HTML code into the footer of the page
		 *
		 * @return @string
		 */
	    protected function getExtraFooter($id) {
	    	return '<a href="?action=viewStudentAttendance&other=1" data-role="button" data-theme="b">'
	    		. __('View Attendance of Other Students')
	    		. '</a>';
	    }
		/**
		 * Displays a list of student
		 *
		 * @return void
		 */
		private function printStudentSelector($gid)
		{
			if (! strlen($this->getGroupName($gid))) {
				$this->addNotification(
					'error',
					__('Invalid group selected')
				);
				$this->groupSelector();
			} else {
				$students = $this->getStudentsInGroup($gid);
				if (count($students) > 0) {
					$this->addHtml("<h3>" . __('Select Student') . "</h3>");
					$html = $this->printStudentListHeader($gid);
					foreach ($students as $key => $value) {
						$html .= $this->printStudentListItem($key, $gid, $value);
					}
					$html .= $this->printStudentListFooter();
					$this->addHtml($html);
				} else {
					$this->addNotification(
						'warning',
						__('The are no students in this group')
					);
					$this->groupSelector();
				}
			}
		}
		/**
		 * Prints the header for the list of students
		 *
		 * @return void
		 */
		private function printStudentListHeader($gid)
		{
			$html  = '<ul data-role="listview" data-divider-theme="b" ';
	        $html .= 'data-inset="true">';
	        $html .= '<li data-role="list-divider" role="heading">';
	        $html .= sprintf(
	        	__('Group `%s`'),
	        	$this->getGroupName($gid)
	        );
	        $html .= '</li>';
	        $this->addHtml($html);
		}
		/**
		 * Prints a single item for the list of students
		 *
		 * @return void
		 */
		private function printStudentListItem($sid, $gid, $name)
		{
	        $this->addHtml(
		        sprintf(
		        	'<li><a href="?action=viewStudentAttendance&gid=%d&sid=%d">%s</a></li>',
		        	$gid,
		        	$sid,
		        	$name
		        )
	        );
		}
		/**
		 * Prints the footer for the list of students
		 *
		 * @return void
		 */
		private function printStudentListFooter()
		{
	        $this->addHtml(
	        	'</ul>'
	        );
		}
		/**
		 * Retrieves the attendance of a student
		 * for a group from the database
		 *
		 * @return array
		 */
		private function getStudentAttendance($gid, $sid)
		{
			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance` 
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
					AND `gid` = ?
				) AS `overall` , (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance`
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
					AND `gid` = ?
					AND `isLecture` = 0
				) AS `labs` , (
					SELECT SUM(`present`) / COUNT(*)
					FROM `student_attendance`
					INNER JOIN `attendance`
					ON `attendance`.`id` = `student_attendance`.`aid`
					WHERE `sid` = ?
					AND `gid` = ?
					AND `isLecture` = 1
				) AS `lectures`;"
			);
			$stmt->bind_param(
				'iiiiii',
				$sid,
				$gid,
				$sid,
				$gid,
				$sid,
				$gid
			);
			$stmt->execute();
			$stmt->bind_result($overall, $labs, $lectures);
			$stmt->fetch();
			$stmt->close();

			return array(
				'overall' => $overall,
				'labs' => $labs,
				'lectures' => $lectures
			);
		}
	}
}