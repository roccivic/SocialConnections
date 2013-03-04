<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once('libs/twitter/twitteroauth.php');
require_once('libs/twitter/config.php');
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
			$_SESSION['access_tokenTwitter'] = $access_token;
			unset($_SESSION['oauth_token']);
			unset($_SESSION['oauth_token_secret']);
			if (200 == $connection->http_code) {
			 $_SESSION['status'] = 'verified';
		} else {
			 $this->connect();
			}
		}
		$access_token = $_SESSION['access_tokenTwitter'];
		if(!empty($access_token['oauth_token'])) 
		{
			$_SESSION['access_tokenTwitter'] = $access_token;
			$this->display();
		}
		else if(empty($_SESSION['access_tokenTwitter']) || empty($_SESSION['access_tokenTwitter']['oauth_token']) || empty($_SESSION['access_tokenTwitter']['oauth_token_secret'])) 
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
}
?>