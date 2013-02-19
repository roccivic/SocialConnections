<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * User authentication class
 */
abstract class Auth {
	/**
	 * This function is used to rate-limit authentication requests.
	 * Must be called before calling Auth::login()
	 * The maximum rate is 3 requests in any 60 second interval
	 *
	 * @return bool Returns false, if the speed limit has been exceeded
	 */
	public static function speedLimitOk()
	{
		$db = Db::getLink();
		// Delete all old speedlimit entries
		$db->query(
			"DELETE FROM speedlimit
			 WHERE timestamp < CURRENT_TIMESTAMP - INTERVAL 1 MINUTE;"
		);
		// Check for limit violation
		$stmt = $db->prepare(
			"SELECT COUNT(*) FROM speedlimit WHERE ip = ?;"
		);
		$stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();
		if ($count >= 3) {
			// User is speeding
			return false;
		} else {
			// Record login attempt
			$stmt = $db->prepare(
				"INSERT INTO speedlimit(ip) VALUES (?)"
			);
			$stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);		
			$stmt->execute();
			$stmt->close();
			return true;
		}
	}
	/**
	 * This tells you how long the user has to wait, in seconds,
	 * before he is allowed another login attemp
	 *
	 * @return int
	 */
	public static function speedLimitExpiry()
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT 60 - TIMESTAMPDIFF(
				SECOND,
				MIN(timestamp),
				CURRENT_TIMESTAMP
			 )
			 FROM speedlimit WHERE ip = ?;"
		);
		$stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
		$stmt->execute();
		$stmt->bind_result($seconds);
		$stmt->fetch();
		$stmt->close();
		return $seconds;
	}
	/**
	 * Process a login request
	 *
	 * @return bool Whether the login attemp was successful
	 */
	public static function login($username, $password)
	{
		$success = false;
		$username = strtolower($username);

		$uid = self::checkLogin($username, $password, 'admin');
		if ($uid !== false) {
			$_SESSION['uid'] = $uid;
			$_SESSION['accesslevel'] = User::ADMIN;
			$success = true;
		} else {
			$uid = self::checkLogin($username, $password, 'lecturer');
			if ($uid !== false) {
				$_SESSION['uid'] = $uid;
				$_SESSION['accesslevel'] = User::LECTURER;
				$success = true;
			} else {
				$uid = self::checkLogin($username, $password, 'student');
				if ($uid !== false) {
					$_SESSION['uid'] = $uid;
					$_SESSION['accesslevel'] = User::STUDENT;
					$success = true;
				}
			}
		}
		return $success;
	}


	private static function checkLogin($username, $password, $table)
	{
		$db = Db::getLink();
		if (strpos($username, '@') !== false) {
			$stmt = $db->prepare(
				"SELECT id, salt, password FROM $table WHERE email = ?;"
			);
		} else {
			$stmt = $db->prepare(
				"SELECT id, salt, password FROM $table WHERE username = ?;"
			);
		}
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($uid, $salt, $hash);
		$stmt->fetch();
		$stmt->close();
		if ($hash === md5($password . $salt)) {
			return $uid;
		}
		return false;
	}

	/**
	 * Destroys user's session
	 *
	 * @return void
	 */
	public static function logout()
	{
		$params = session_get_cookie_params();
	    setcookie(
	    	session_name(),
	    	'',
	    	time() - 42000,
	        $params["path"],
	        $params["domain"],
	        $params["secure"],
	        $params["httponly"]
	    );
		session_destroy();
	}
	/**
	 * Creates an authentication token that will be used
	 * to remotely authenticate a user of the android app
	 *
	 * This function assumes that the user has
	 * already been successfully authenticated
	 *
	 * @return string The authentication token
	 */
	public static function getToken()
	{
		$token = md5(mt_rand() . ':' . time());
		$db = Db::getLink();
		// The token is valid for 5 minutes
		$stmt = $db->prepare(
			"INSERT INTO token(uid, accesslevel, token, expires, ip) VALUES (?,?,?,CURRENT_TIMESTAMP + INTERVAL 20 MINUTE,?)"
		);
		$stmt->bind_param(
			'iiss',
			$_SESSION['uid'],
			$_SESSION['accesslevel'],
			$token,
			$_SERVER['REMOTE_ADDR']
		);
		$stmt->execute();
		$stmt->close();
		return $token;
	}
	/**
	 * Checks if an incoming authentication token is valid
	 * If the token is valid, the user will be automatically logged in
	 *
	 * @return void
	 */
	public static function checkToken()
	{
		$db = Db::getLink();
		// delete expired tokens before starting to check for any valid ones
		$db->query("DELETE FROM token WHERE expires < CURRENT_TIMESTAMP;");
		if (! empty($_REQUEST['token'])) {
			// check for valid auth tokens
			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT accesslevel, uid FROM token WHERE ip = ? AND token = ?;"
			);
			$stmt->bind_param('ss', $_SERVER['REMOTE_ADDR'], $_REQUEST['token']);
			$stmt->execute();
			$stmt->bind_result($accesslevel, $uid);
			$stmt->fetch();
			$stmt->close();
			if ($uid > 0) {
				// Successful remote login
				$stmt = $db->prepare(
					"DELETE FROM token WHERE token = ?;"
				);
				$stmt->bind_param('s', $_REQUEST['token']);
				$stmt->execute();
				$stmt->close();
				$_SESSION['uid'] = $uid;
				$_SESSION['accesslevel'] = $accesslevel;
			}
		}
	}
}

?>