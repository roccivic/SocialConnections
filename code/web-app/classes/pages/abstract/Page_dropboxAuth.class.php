<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'scripts/dropbox/config.php';
require_once "scripts/dropbox/rest.php";
require_once "scripts/dropbox/session.php";
require_once "scripts/dropbox/client.php";
/**
 * Abstract class that implements generic
 * selection of groups. A subclass must provide a query
 * to retrieve the list of groups from the db.
 */
abstract class Page_dropboxAuth extends Page {
	public function __construct()
	{
		parent::__construct();
		$config = array();
		$config["dropbox"]["app_key"] = APP_KEY;
		$config["dropbox"]["app_secret"] = APP_SECRET;
		$config["dropbox"]["access_type"] = ACCESS_TYPE;
		$config["app"]["root"] = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . "/socialconnections/?action=postNotes";
		$session = new DropboxSession(
	    $config["dropbox"]["app_key"],
	    $config["dropbox"]["app_secret"],
	    $config["dropbox"]["access_type"]
	    );
		$access_token = $this->readToken();
		if(empty($access_token)) {
			if (!empty($_REQUEST["oauth_token"]) && !empty($_REQUEST["uid"])) {
					$token = array(
		            "oauth_token" => $_REQUEST["oauth_token"],
		            "oauth_token_secret" => ""
		        );
				if (!empty($_SESSION["request_token"])) {
		            $token["oauth_token_secret"] = $_SESSION["request_token"]["oauth_token_secret"];
		        }
		       	$access_token = $session->obtainAccessToken($token);
		   		$this->writeToken($access_token);
		   		$this->display();
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
	    	$this->display();
	    }
	}
	/**
	 * This function must be implemented in a subclass
	 * Shows a page after a group has been selected
	 *
	 * @return void
	 */

	protected abstract function display();
	/**
	 * This function writes the keys to the db
	 * 
	 * @return void
	 */
	private function writeToken($access_token) {
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO `dropbox` (`uid`, `access_token`) VALUES (?, ?)"
				);
		$stmt->bind_param('is', $uid, $access_token);
		$stmt->execute();
		$stmt->fetch();
		$stmt->close();
	}
	/**
	 * This function gets the keys from the db
	 * and returns them
	 * @return array
	 */
	private function readToken() {
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `access_token` FROM `dropbox` WHERE `uid` = ?"
				);
		$stmt->bind_param('i', $uid);
		$stmt->execute();
		$stmt->bind_result($access_token);
		$stmt->fetch();
		$stmt->close();
		return $access_token;
	}
	
}