<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'libs/dropbox/config.php';
require_once "libs/dropbox/rest.php";
require_once "libs/dropbox/session.php";
require_once "libs/dropbox/client.php";
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
	    $access_token = $_SESSION['access_tokenDropbox'];
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
		       	$_SESSION['access_tokenDropbox'] = $access_token;
		   		$this->display($access_token);
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
	    	$this->display($access_token);
	    }
	}
	/**
	 * This function must be implemented in a subclass
	 * Shows a page after a group has been selected
	 *
	 * @return void
	 */

	protected abstract function display($access_token);

	
}