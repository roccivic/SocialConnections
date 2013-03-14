<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_twitterAuth.class.php';
/**
 * This page is used by lecturers to tweet
 */
class Page_twitter extends Page_twitterAuth {
	public static function getAccessLevel()
	{
		if(USER::isLecturer())
		{
			return User::LECTURER;
		}
		else if(USER::isStudent())
		{
			return User::STUDENT;
		}
		
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
	public function display2($gid) 
	{	
		$_SESSION['gid'] = NULL;
		$access_token = $_SESSION['access_tokenTwitter'];
		$connection = new TwitterOAuth(CONFIG::TWITTER_CONSUMER_KEY, CONFIG::TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$user = $connection->get('account/verify_credentials');
		$gid = intval($gid);
		$tweet = '';
		if(!empty($_REQUEST['userTweet']))
		{
			$tweet = $_REQUEST['userTweet'];
		}
		$gName = $this->getGroupName($gid);
		$id = '';
		if(!empty($_REQUEST['id']))
		{
			$id = $_REQUEST['id'];
		}
		$username = '';
		if(!empty($_REQUEST['username']))
		{
			$username = $_REQUEST['username'];
		}
		if(!empty($gName))
		{
			if(!empty($_REQUEST['tweeting']))
			{
				if(strlen($tweet) > 0 && $this->tweet($gid, $connection, $tweet))
				{
					$this->addNotification(
						'notice',
						__('You have tweeted successfully!')
					);
					if(USER::isLecturer())
					{
						$students = $this->getStudentsInGroup(false, $gid);
						$lecturers = $this->getLecturersInGroup(true, $gid);
						$this->saveStudentNotifications($students);
						$this->saveLecturerNotifications($lecturers);
					}
					else
					{
						$students = $this->getStudentsInGroup(true, $gid);
						$lecturers = $this->getLecturersInGroup(false, $gid);
						$this->saveStudentNotifications($students);
						$this->saveLecturerNotifications($lecturers);
					}
					$this->displayMenu($gid);
				}
				else
				{
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
					$this->tweetForm($gid);
				}
			}
			else if(!empty($_REQUEST['replyTweet']))
			{
				if(strlen($tweet) > 0 && $this->retweet($gid, $connection, $id, $tweet,$username))
				{
					$this->addNotification(
						'notice',
						__('You have replied successfully! Please refresh the page now.')
					);	
				}
				else
				{
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
				}
				$this->displayTweetReplies($gid, $connection, $id);
			}
			else if(!empty($_REQUEST['viewReplies']))
			{	
				$this->displayTweetReplies($gid, $connection, $id);
			}
			else if(!empty($_REQUEST['viewTweets']))
			{
				$this->displayTweets($gid, $connection);
			}
			else if(!empty($_REQUEST['tweetForm']))
			{
				$this->tweetForm($gid);
			}
			else{
				$this->displayMenu($gid);
			}
		}
		else
		{
			$this->addNotification(
						'error',
						__('Invalid group selected.')
					);
			$this->groupSelector();
		}
		
	}
	/**
	 * Displays menu to user
	 *
	 * @return void
	 */
	private function displayMenu($gid)
	{
		$html = '';
		$html .= '<a href="?action=twitter&tweetForm=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Tweet').'</a>';
		$html .= '<a href="?action=twitter&viewTweets=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('View Tweets').'</a>';
		$this->addHtml($html);
	}
	/**
	 * display tweeting form
	 *
	 * @return void
	 */
	private function tweetForm($gid)
	{
		$html = '<form method="post" action="?action=twitter&gid=' . $gid . '">';
		$html .= '<input name="tweeting" value="1" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="userTweet">' . __('Enter Text') . ': </label>';
		$html .= '<input type="text" name="userTweet" id="userTweet" ';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Tweet') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Tweet
	 *
	 * @return void
	 */
	private function tweet($gid, $connection, $tweet)
	{
		$hashtag = ' #' . Config::TWITTER_HASHTAG . $gid;
		$tweet .= $hashtag;
		$success = $connection->post('statuses/update', array('status' => $tweet));
		return $success;
	}
	/**
	 * Display tweets for group
	 *
	 * @return void
	 */
	private function displayTweets($gid, $connection)
	{
		$this->printListHeaderTweets();
		$tweets = $this->getTweets($gid, $connection);
		$count = 0;
		foreach($tweets as $key)
		{
			$username = $connection->get('users/show', array('id' => $key->user->id));
			if (strpos($key->text,' #' . Config::TWITTER_HASHTAG . $gid) !== false) 
			{
				$count++;
				$this->printListItemTweets($gid, $key->id, $key->text, $key->user->name, $username->screen_name);
			}
		}
		if($count == 0)
		{
			$this->addNotification('notice',__('No tweets to display'));
		}
		$this->printListFooterTweets();
	}
	/**
	 * get tweets for group
	 *
	 * @return void
	 */
	private function getTweets($gid, $connection)
	{
		$tweets = $connection->get('statuses/home_timeline');
		return $tweets;
	}
	/**
	 * Prints the header for the list of groups
	 *
	 * @return void
	 */
	private function printListHeaderTweets()
	{
		$html='';
		$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= 'Tweets';
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of groups
	 *
	 * @return void
	 */
	private function printListItemTweets($gid, $id, $text, $author, $username)
	{
		$this->addHtml(
	        sprintf(
	        	'<li><a href="?action=%s&viewReplies=1&gid=%d&id=%s&username=%s">%s
	        	 <span class="ui-li-count">
                  %s
                 </span></a></li>',
	        	urlencode(htmlspecialchars($_REQUEST['action'])),
	        	$gid,
	        	$id,
	        	$username, 
	        	preg_replace('@ #' . Config::TWITTER_HASHTAG . '\d*\s*$@', '', $text),
	        	$author
	        )
        );
	}
	/**
	 * Prints the footer for the list of groups
	 *
	 * @return void
	 */
	private function printListFooterTweets()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Retweet
	 *
	 * @return void
	 */
	private function retweet($gid, $connection, $id, $tweet, $username)
	{
		$tweet = '@'.$username. ' ' . $tweet;
		$success = $connection->post('statuses/update', array('status' => $tweet, 'in_reply_to_status_id' => $id));
		return $success;
	}
	/**
	 * Display tweets for group
	 *
	 * @return void
	 */
	private function displayTweetReplies($gid, $connection, $id)
	{
		$this->printListHeaderTweets();
		$tweets = $this->getTweets($gid, $connection);
		$count = 0;
		foreach($tweets as $key)
		{
			if (intval($key->in_reply_to_status_id) == intval($id)) 
			{
				$count++;
				$username = $connection->get('users/show', array('id' => $key->user->id));
				$this->printListItemTweets($gid, $key->id, $key->text, $key->user->name, $username->screen_name);
			}
		}
		$this->printListFooterTweets();
		if($count == 0)
		{
			$this->addNotification('notice',__('No tweets to display'));
		}
		$html = '<form method="post" action="?action=twitter&gid=' . $gid . '&id=' . $id . '&viewReplies=1">';
		$html .= '<input name="replyTweet" value="1" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="userTweet">' . __('Enter Text') . ': </label>';
		$html .= '<input type="text" name="userTweet" id="userTweet" ';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Reply') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Get All students from a given group
	 *
	 * @return array
	 */
	private function getStudentsInGroup($isUserStudent, $gid)
	{
		$uid = $_SESSION['uid'];
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`
			FROM `student`
			WHERE `id` IN (
				SELECT sid FROM `group_student` WHERE `gid`=?
			)"
		);
		$stmt->bind_param("i",$gid);
		$stmt->execute();
		$stmt->bind_result($id);
		while ($stmt->fetch()) {
			if($isUserStudent)
			{
				if($uid == $id)
				{

				}
				else
				{
					$arr[$id] = $id;
				}
			}
			else {
				$arr[$id] = $id;
			}
			
		}
		return $arr;
	}
	/**
	 * Get All lecturers from a given group
	 *
	 * @return array
	 */
	private function getLecturersInGroup($isUserLecturer, $gid)
	{
		$uid = $_SESSION['uid'];
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `lecturer`.`id`
			FROM `lecturer`
			WHERE `lecturer`.`id` IN (
			SELECT `moduleoffering_lecturer`.`lid` FROM `moduleoffering_lecturer` WHERE `moduleoffering_lecturer`.`moid`IN (
            SELECT `group`.`moid` FROM `group` WHERE `group`.`id` = ?
            ));"
		);
		$stmt->bind_param("i",$gid);
		$stmt->execute();
		$stmt->bind_result($id);
		while ($stmt->fetch()) {
			if($isUserLecturer)
			{
				if($uid == $id)
				{

				}
				else
				{
					$arr[$id] = $id;
				}
			}
			else {
				$arr[$id] = $id;
			}
			
		}
		return $arr;
	}
	/**
	 * save notifications for lecturer in db
	 *
	 * @return array
	 */
	private function saveLecturerNotifications($lecturers)
	{
		$db = Db::getLink();
		foreach($lecturers as $key => $value)
		{
			$stmt = $db->prepare(
				"INSERT INTO `twitter_lecturer_notifications` (`lid`) VALUES (?)"
			);
			$stmt->bind_param("i",$key);
			$stmt->execute();
			$stmt->fetch();
			$stmt->close();
		}
	}
	/**
	 * save notifications for student in db
	 *
	 * @return array
	 */
	private function saveStudentNotifications($students)
	{
		$db = Db::getLink();
		foreach($students as $key => $value)
		{
			$stmt = $db->prepare(
				"INSERT INTO `twitter_student_notifications` (`sid`) VALUES (?)"
			);
			$stmt->bind_param("i",$key);
			$stmt->execute();
			$stmt->fetch();
			$stmt->close();
		}
	}
}
?>