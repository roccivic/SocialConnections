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
	public function __construct($haveCreateBtn = null, $disableAjax = null)
	{
		parent::__construct();
		$gid = 0;
		if(!empty($_SESSION['gid'])) {
			$gid = $_SESSION['gid'];
			$_SESSION['gid'] = NULL;
		}
		if (! empty($_REQUEST['gid'])) {
			$gid = intval($_REQUEST['gid']);
		}
		if ($gid > 0 || ! empty($_REQUEST['editForm'])) {
			$this->display($gid);
		} else {
			$this->groupSelector($haveCreateBtn, $disableAjax);
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
	protected function groupSelector($haveCreateBtn = null, $disableAjax = null)
	{
		$val = 0;
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
					if($val == 0)
					{
						$this->printListHeader($haveCreateBtn, $value['year'], $value['term']);
						$val = 1;
					}
					else {
						$this->printListHeader(null, $value['year'], $value['term']);
					}
					$stmt->bind_result($gid, $name);
					while ($stmt->fetch()) {
				        $this->printListItem(
				        	$gid,
				        	$name,
				        	(isset($disableAjax) ? ' data-ajax="false"' : '')
				        );
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
	private function getCreateGroupBtn()
	{
	    $html  = '<a href="?action=manageGroups&editForm=1"';
    	$html .= ' data-role="button" data-theme="b">';
    	$html .= __('Create Group') . '</a>';
    	return $html;
	}
	/**
	 * Prints the header for the list of groups
	 *
	 * @return void
	 */
	private function printListHeader($haveCreateBtn, $year, $term)
	{
		$html='';
		if (isset($haveCreateBtn)) {
			$html .= $this->getCreateGroupBtn();
        }
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
	private function printListItem($gid, $name, $ajax)
	{
		$args = $this->getArgs();
        $this->addHtml(
	        sprintf(
	        	'<li><a%s href="?action=%s&gid=%d' . $args . '">%s</a></li>',
	        	$ajax,
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
	 * Retrieves a list of terms that
	 * the user is registered for
	 *
	 * @return @array
	 */
    protected abstract function getTerms($id);
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

	protected function getArgs() {
		return '';
	}
}