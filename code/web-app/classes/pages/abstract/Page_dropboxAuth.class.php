<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';
require_once "libs/dropbox/rest.php";
require_once "libs/dropbox/session.php";
require_once "libs/dropbox/client.php";
/**
 * Abstract class that implements generic
 * selection of groups. A subclass must provide a query
 * to retrieve the list of groups from the db.
 */
abstract class Page_dropboxAuth extends Page_selectLecturerGroup {
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_selectLecturer_Group superclass
	 * when a group is picked
	 *
	 * @return void
	 */
	public function display($gid) 
	{
		$gname = $this->getGroupName($gid);
		if(!empty($gname)) {
			$_SESSION['gid'] = $gid;
			$config = array();
			$config["dropbox"]["app_key"] = CONFIG::DROPBOX_APP_KEY;
			$config["dropbox"]["app_secret"] = CONFIG::DROPBOX_APP_SECRET;
			$config["dropbox"]["access_type"] = CONFIG::DROPBOX_ACCESS_TYPE;
			$config["app"]["root"] = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . "/socialconnections/?action=postNotes";
			$config["app"]["datadir"] = "dropbox_files/";
			$session = new DropboxSession(
		    $config["dropbox"]["app_key"],
		    $config["dropbox"]["app_secret"],
		    $config["dropbox"]["access_type"]
		    );

		   	if(empty($_SESSION['access_tokenDropbox']))
		   	{
		   		if (!empty($_REQUEST["oauth_token"]) && !empty($_REQUEST["uid"])) {
						$token = array(
						"oauth_token_secret" => "",
			            "oauth_token" => $_REQUEST["oauth_token"]
			        );
					if (!empty($_SESSION["request_token"])) {
			            $token["oauth_token_secret"] = $_SESSION["request_token"]["oauth_token_secret"];
			        }
			       	$access_token = $session->obtainAccessToken($token);
			       	parse_str($access_token, $token);
            		$access_token = $token;
			       	$_SESSION['access_tokenDropbox'] = $access_token;
			       	$this->display2($access_token, $gid, $config);
			    }
				else {
					if ($request_token = $session->obtainRequestToken()) {
			          	parse_str($request_token, $token);
			            $_SESSION["request_token"] = $token;
						$url = $session->buildAuthorizeURL(
			                $token, 
			                $config["app"]["root"],
			                "en-US");
						header("location: $url");
			        }
		     	}
		    }
		   	else {
		    	$this->display2($_SESSION['access_tokenDropbox'], $gid, $config);
		    }
		 }
	    else 
	    {
	    	$this->groupSelector();
	    }
	}

	/**
	 * This function must be implemented in a subclass
	 * Shows a page after the user has been authorized to use dropbox
	 *
	 * @return void
	 */

	protected abstract function display2($access_token, $gid, $config);	

	/**
	 * Returns group name
	 *
	 * @return string
	 */
	private function getGroupName($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `group` WHERE `id`=?"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
}