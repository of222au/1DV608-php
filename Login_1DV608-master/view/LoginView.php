<?php

namespace view;

class LoginView {
	private static $login = 'LoginView::Login';
	private static $logout = 'LoginView::Logout';
	private static $name = 'LoginView::UserName';
	private static $password = 'LoginView::Password';
	private static $keep = 'LoginView::KeepMeLoggedIn';
	private static $messageId = 'LoginView::Message';
	private static $cookieName = 'LoginView::CookieName';
	private static $cookiePassword = 'LoginView::CookiePassword';

	private $loginStatePersistor;
	private $cookieStorage;

	public function __construct() {
		$this->loginStatePersistor = new LoginStatePersistor();
		$this->cookieStorage = new CookieStorage();
	}

	/**
	 * Create HTTP response
	 * Should be called after a login attempt has been determined
	 *
	 * @param $isLoggedIn bool|int (can be false, 1 (just logged in), 2 (logged in by session), 3 (logged in by cookies/server storage)
	 * @param $isLoggedOut
	 * @return string
	 */
	public function response($isLoggedIn, $isLoggedOut) {
		assert(is_bool($isLoggedIn) || $isLoggedIn == true); //to assert is a bool or can be evaluated to true
		assert(is_bool($isLoggedOut)); //to assert is a bool

		$message = ''; //message to show
		$username = ''; //username to pre-fill in the login form
		$keep = $this->getRequestKeep(); //if login is to be remembered

		//if a message should be output to the user, i.e if a POST was done, or login done by cookies from storage, or some incorrect credential cookies that needs to be deleted
		if ($this->isLoggingIn() || $this->isLoggingOut() || $isLoggedIn === 3 || ($isLoggedIn != true && !$this->isCookieUsernameAndPasswordEmpty())) {

			if ($this->isLoggingIn() || $isLoggedIn === 3) {
				if ($isLoggedIn == true) {
					if ($isLoggedIn === 3) { //logged in by storage
						$message = 'Welcome back with cookie';
					}
					else if ($isLoggedIn !== 2) { //not logged in by session
						if ($keep) {
							$message = 'Welcome and you will be remembered';
						}
						else {
							$message = 'Welcome';
						}
					}

					//clear stored login username since logged in now
					$this->loginStatePersistor->clearUserName();
				}
				else {
					$username = $this->getRequestUserName();
					if ($username == '') {
						$message = 'Username is missing';
					}
					else if ($this->getRequestPassword() == '') {
						$message = 'Password is missing';
					}
					else {
						$message = 'Wrong name or password';
					}

					//save the entered username, so it can be restored after redirect
					$this->loginStatePersistor->saveUsername($username);
				}
			}
			else if ($this->isLoggingOut() && $isLoggedOut) {
				$message = 'Bye bye!';
			}
			else if ($isLoggedIn != true && !$this->isCookieUsernameAndPasswordEmpty()) {
				$message = 'Wrong information in cookies';

				$this->deleteCookieCredentials();
			}

			//store the message (since a redirect will be done)
			$this->loginStatePersistor->saveMessage($message);

			//do a redirect since a POST was made
			header('Location: ' . $_SERVER['PHP_SELF']);
		}
		else {
			//load (and clear) any stored message and username
			$message = $this->loginStatePersistor->getMessage();
			$username = $this->loginStatePersistor->getUserName();
		}

		//produce the output
		$response = '';
		if ($isLoggedIn == true) {
			$response = $this->generateLogoutButtonHTML($message);
		}
		else {
			$response = $this->generateLoginFormHTML($message, $username);
		}

		return $response;
	}


	public function isLoggingIn() {
		//RETURN POST VARIABLE: Login
		if (isset($_POST[self::$login])) {
			return true;
		}
		return false;
	}
	public function isLoggingOut() {
		//RETURN REQUEST VARIABLE: Logout
		if (isset($_POST[self::$logout])) {
			return true;
		}
		return false;
	}

	public function getRequestUserName() {
		//RETURN REQUEST VARIABLE: USERNAME
		if (isset($_REQUEST[self::$name])) {
			return $_REQUEST[self::$name];
		}
		return null;
	}
	public function getRequestPassword() {
		//RETURN REQUEST VARIABLE: PASSWORD
		if (isset($_REQUEST[self::$password])) {
			return $_REQUEST[self::$password];
		}
		return null;
	}
	public function getRequestKeep() {
		//RETURN REQUEST VARIABLE: KEEP
		if (isset($_REQUEST[self::$keep])) {
			return $_REQUEST[self::$keep];
		}
		return null;
	}

	/**
	 * Retrieves username stored in cookies
	 * @return string|null
	 */
	public function getCookieUsername() {
		return $this->cookieStorage->load(self::$cookieName);
	}
	/**
	 * Retrieves password stored in cookies
	 * @return string|null
	 */
	public function getCookiePassword() {
		return $this->cookieStorage->load(self::$cookiePassword);
	}
	/**
	 * Checks if username and temp password stored in cookies are both empty strings or null
	 * @return bool
	 */
	public function isCookieUsernameAndPasswordEmpty() {
		$cookieUsername = $this->getCookieUsername();
		$cookiePassword = $this->getCookiePassword();
		return ($cookieUsername == null || $cookieUsername == '') && ($cookiePassword == null || $cookiePassword == '');
	}

	/**
	 * Saves username and temp password to cookies
	 * @param $username, String
	 * @param $tempPassword, String
	 */
	public function saveCookieCredentials($username, $tempPassword) {
		$this->cookieStorage->save(self::$cookieName, $username);
		$this->cookieStorage->save(self::$cookiePassword, $tempPassword);
	}
	/**
	 * Deletes username and temp password from cookies
	 * @param $username, String
	 * @param $tempPassword, String
	 */
	public function deleteCookieCredentials() {
		$this->cookieStorage->delete(self::$cookieName);
		$this->cookieStorage->delete(self::$cookiePassword);
	}



	/**
	 * Generate HTML code on the output buffer for the logout button
	 * @param $message, String output message
	 * @return  string
	 */
	private function generateLogoutButtonHTML($message) {
		return '
			<form  method="post" >
				<p id="' . self::$messageId . '">' . $message .'</p>
				<input type="submit" name="' . self::$logout . '" value="logout"/>
			</form>
		';
	}

	/**
	 * Generate HTML code on the output buffer for the logout button
	 * @param $message, String output message
	 * @param $preFilledUserName, String if to pre-fill username input
	 * @return  string
	 */
	private function generateLoginFormHTML($message, $preFilledUserName = '') {
		return '
			<form method="post" >
				<fieldset>
					<legend>Login - enter Username and password</legend>
					<p id="' . self::$messageId . '">' . $message . '</p>

					<label for="' . self::$name . '">Username :</label>
					<input type="text" id="' . self::$name . '" name="' . self::$name . '" value="'. $preFilledUserName . '" />

					<label for="' . self::$password . '">Password :</label>
					<input type="password" id="' . self::$password . '" name="' . self::$password . '" />

					<label for="' . self::$keep . '">Keep me logged in  :</label>
					<input type="checkbox" id="' . self::$keep . '" name="' . self::$keep . '" />

					<input type="submit" name="' . self::$login . '" value="login" />
				</fieldset>
			</form>
		';
	}

}