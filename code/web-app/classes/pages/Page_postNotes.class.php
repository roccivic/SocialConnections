<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_dropboxAuth.class.php';
/**
 * This page is used by lecturers to tweet
 */
class Page_postNotes extends Page_dropboxAuth {
	public static function getAccessLevel()
	{
		return User::LECTURER;
	}
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_twitterAuth superclass
	 * when access to twitter is granted
	 *
	 * @return void
	 */
	public function display() 
	{	
		$gid = 0;
		if(!empty($_REQUEST['gid'])) {
			$gid = $_REQUEST['gid'];
		}
		$gname = $this->getGroupName($gid);
		if(!empty($gname)) {
			
		}
		else {
			$this->groupSelector();
		}
		
	}
	/**
	 * Displays the list of groups
	 *
	 * @return void
	 */
	protected function groupSelector()
	{
		
		$db = Db::getLink();
		$terms = $this->getTerms($_SESSION['uid']);
		if (count($terms) > 0) {
			$this->addHtml(
				'<h3>' . __('Select Group') . '</h3>'
			);
			foreach ($terms as $value) {
				$stmt = $db->prepare(
					$this->getQuery($value['year'], $value['term'])
				);
				$stmt->bind_param('iii', $_SESSION['uid'], $value['year'], $value['term']);
				$stmt->execute();
				$stmt->store_result();
				if ($stmt->num_rows) {
					$this->printListHeader($value['year'], $value['term']);
					$stmt->bind_result($gid, $name);
					while ($stmt->fetch()) {
				        $this->printListItem($gid, $name);
				    }
				    $this->printListFooter();
				}
				$stmt->close();
			}
			$this->addHtml(
				$this->getExtraFooter($_SESSION['uid'])
			);
		} else {
			$this->addNotification(
				'warning',
				__('You are not assigned to any groups')
			);
			if (isset($haveCreateBtn)) {
				$this->addHtml($this->getCreateGroupBtn());
			}
		}
	}
	/**
	 * Prints the header for the list of groups
	 *
	 * @return void
	 */
	private function printListHeader($year, $term)
	{
		$html='';
		$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= sprintf(
        	__('Year %d, Semester %d'),
        	$year,
        	$term
        );
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of groups
	 *
	 * @return void
	 */
	private function printListItem($gid, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=%s&gid=%d">%s</a></li>',
	        	urlencode(htmlspecialchars($_REQUEST['action'])),
	        	$gid,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of groups
	 *
	 * @return void
	 */
	private function printListFooter()
	{
        $this->addHtml('</ul>');
	}
	
	/**
	 * Puts some HTML code into the footer of the page,
	 * override in a subclass
	 *
	 * @return @string
	 */
	protected function getExtraFooter($sid)
	{
        return '';
	}
	/**
	 * This function must be implemented in a subclass
	 * Returns an SQL query for getting the groups
	 *
	 * @return string
	 */
	protected function getQuery()
	{
        return "SELECT `group`.`id`, `group`.`name`
				FROM `group`
				INNER JOIN `moduleoffering_lecturer`
				ON `group`.`moid` = `moduleoffering_lecturer`.`moid`
				INNER JOIN `moduleoffering`
				ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
				WHERE `lid` = ?
				AND `moduleoffering`.`year` = ?
				AND `moduleoffering`.`term` = ?";
	}
	/**
	 * Retrieves a list of terms that
	 * the user is registered for
	 *
	 * @return @array
	 */
    protected function getTerms($lid)
    {
        $arr = array();
        $db = Db::getLink();
        $stmt = $db->prepare(
            'SELECT `year`, `term`
            FROM `moduleoffering`
            INNER JOIN `group`
            ON `group`.`moid` = `moduleoffering`.`id`
            INNER JOIN `moduleoffering_lecturer`
            ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
			INNER JOIN `lecturer`
            ON `moduleoffering_lecturer`.`lid` = `lecturer`.`id`
            WHERE `moduleoffering_lecturer`.`lid` = ?
            GROUP BY `year`,`term`
            ORDER BY `year` DESC, `term` DESC;'
        );
        $stmt->bind_param('i', $lid);
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
}
?>