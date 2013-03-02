<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once('scripts/twitter/twitteroauth.php');
require_once('scripts/twitter/config.php');
/**
 * Abstract class that implements connecting to twitter
 */
abstract class Page_twitterAuth extends Page {
	public function __construct()
	{
		parent::__construct();
		if(!empty($_REQUEST['callback'])) {
			if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
				 	 $_SESSION['oauth_status'] = 'oldtoken';
				  	 $this->connect();
			}
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
			$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
			$_SESSION['access_token'] = $access_token;
			$this->writeKeys($access_token);
			unset($_SESSION['oauth_token']);
			unset($_SESSION['oauth_token_secret']);
			if (200 == $connection->http_code) {
			 $_SESSION['status'] = 'verified';
			  $this->display();
			} else {
			 $this->connect();
			}
		}
		$access_token = $this->readKeys();
		if(!empty($access_token['oauth_token'])) 
		{
			$_SESSION['access_token'] = $access_token;
			$this->display();
		}
		else if(empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) 
		{
    		$this->connect();
		}
	}
	/**
	 * This function must be implemented in a subclass
	 * Shows a page after access to twitter has been granted
	 *
	 * @return void
	 */
	protected abstract function display();
	/**
	 * This function connects to twitter
	 * @return void
	 */
	private function connect() {
		if (CONSUMER_KEY === '' || CONSUMER_SECRET === '') {
  			echo 'You need a consumer key and secret!';
  			exit;
  		}
  		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
		$request_token = $connection->getRequestToken(OAUTH_CALLBACK);
		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		switch ($connection->http_code) {
		  case 200:
		  $url = $connection->getAuthorizeURL($token);
		    header('Location: ' . $url); 
		    break;
		  	default: echo 'Could not connect to Twitter. Refresh the page or try again later.';
		}
	}
	/**
	 * This function gets the keys from the db
	 * and returns them
	 * @return array
	 */
	private function readKeys() {
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `token`, `token_secret` FROM `twitter` WHERE `uid` = ?"
				);
		$stmt->bind_param('i', $uid);
		$stmt->execute();
		$stmt->bind_result($token , $token_secret);
		$stmt->fetch();
		$stmt->close();
		return array(
			'oauth_token' => $token,
			'oauth_token_secret' => $token_secret
			);
	}
	/**
	 * This function writes the keys to the db
	 * 
	 * @return void
	 */
	private function writeKeys($token) {
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"INSERT INTO `twitter` (`uid`, `token`, `token_secret`) VALUES (?, ?, ?)"
				);
		$stmt->bind_param('iss', $uid, $token['oauth_token'], $token['oauth_token_secret']);
		$stmt->execute();
		$stmt->fetch();
		$stmt->close();
	}
}
?>