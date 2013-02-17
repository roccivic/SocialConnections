<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Abstract class that implements generic
 * selection of groups. A subclass must provide a query
 * to retrieve the list of groups from the db.
 */
abstract class Page_selectDepartment extends Page {
	public function __construct($haveCreateBtn = null)
	{
		parent::__construct();
		$did = 0;
		if (! empty($_REQUEST['did'])) {
			$did = intval($_REQUEST['did']);
		}
		if ($did > 0 || ! empty($_REQUEST['editForm'])) {
			$this->display($did);
		} else {
			$this->departmentSelector($haveCreateBtn);
		}
	}
	/**
	 * This function must be implemented in a subclass
	 * Shows a page after a group has been selected
	 *
	 * @return void
	 */
	protected abstract function display($did);
	
	
	/**
	 * Displays the list of groups
	 *
	 * @return void
	 */
	protected function departmentSelector($haveCreateBtn)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT id, name FROM department"
		);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows) {
			$this->printListHeader($haveCreateBtn);
			$stmt->bind_result($did, $name);
			while ($stmt->fetch()) {
		        $this->printListItem($did, $name);
		    }
		    $this->printListFooter();
		} else {
			$this->addNotification(
				'warning',
				__('There are no departments in the system.')
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
        $html  = '';
        if (isset($haveCreateBtn)) {
        	$html .= '<a href="?action=manageDepartments&editForm=1"';
        	$html .= ' data-role="button" data-theme="b">';
        	$html .= __('Create Department') . '</a>';
        }
        $html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select Department');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of groups
	 *
	 * @return void
	 */
	private function printListItem($did, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=%s&did=%d">%s</a></li>',
	        	urlencode(htmlspecialchars($_REQUEST['action'])),
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
	private function printListFooter()
	{
        $this->addHtml('</ul>');
	}
}