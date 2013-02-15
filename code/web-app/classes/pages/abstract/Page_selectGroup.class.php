<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Abstract class that implements generic
 * selection of groups. A subclass must provide a query
 * to retrieve the list of groups from the db.
 */
abstract class Page_selectGroup extends Page {
	/**
	 * A user must be at least a student to view this page
	 */
	public function getAccessLevel()
	{
		return User::STUDENT;
	}

	public function __construct()
	{
		parent::__construct();
		$gid = 0;
		if (! empty($_REQUEST['gid'])) {
			$gid = intval($_REQUEST['gid']);
		}
		if ($gid > 0) {
			$this->display(
				intval($_REQUEST['gid'])
			);
		} else {
			$this->groupSelector();
		}
	}
	/**
	 * This function must be implemented in a subclass
	 * Shows a page after a group has been selected
	 *
	 * @return void
	 */
	protected abstract function display($gid);
	/**
	 * This function must be implemented in a subclass
	 * Returns an SQL query for getting the groups
	 *
	 * @return string
	 */
	protected abstract function getQuery();
	/**
	 * Displays the list of groups
	 *
	 * @return void
	 */
	private function groupSelector()
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			$this->getQuery()
		);
		$stmt->bind_param('s', $_SESSION['uid']);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows) {
			$this->printListHeader();
			$stmt->bind_result($gid, $name);
			while ($stmt->fetch()) {
		        $this->printListItem($gid, $name);
		    }
		    $this->printListFooter();
		} else {
			$this->addNotification(
				'warning',
				__('You are not assigned to any groups')
			);
		}
		$stmt->close();
	}
	/**
	 * Prints the header for the list of groups
	 *
	 * @return void
	 */
	private function printListHeader()
	{
        $html  = '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Group');
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
}