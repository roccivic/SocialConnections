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
	public function __construct($haveCreateBtn)
	{
		parent::__construct();
		$gid = 0;
		if (! empty($_REQUEST['gid'])) {
			$gid = intval($_REQUEST['gid']);
		}
		if ($gid > 0 || ! empty($_REQUEST['editForm'])) {
			$this->display(
				intval($_REQUEST['gid'])
			);
		} else {
			$this->groupSelector($haveCreateBtn);
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
	protected function groupSelector($haveCreateBtn)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			$this->getQuery()
		);
		$stmt->bind_param('s', $_SESSION['uid']);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows) {
			$this->printListHeader($haveCreateBtn);
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
	private function printListHeader($haveCreateBtn)
	{
		$html='';
		if (isset($haveCreateBtn)) {
        	$html .= '<a href="?action=manageGroups&editForm=1"';
        	$html .= ' data-role="button" data-theme="b">';
        	$html .= __('Create Group') . '</a>';
        }
        $html .= '<ul data-role="listview" data-divider-theme="b" ';
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