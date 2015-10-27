<?php

namespace view;

class LoginView implements PageView {

	private static $login = 'LoginView::Login';
	private static $logout = 'LoginView::Logout';
	private static $name = 'LoginView::UserName';
	private static $password = 'LoginView::Password';
	private static $keep = 'LoginView::KeepMeLoggedIn';
	private static $messageId = 'LoginView::Message';
	private static $cookieName = 'LoginView::CookieName';
	private static $cookiePassword = 'LoginView::CookiePassword';

	private static $sessionMessageLocation = 'view\\LoginView\\message';

	private $navigationView;
	private $cookieStorage;

	private $loginHasSucceeded = false;
	private $loginHasFailed = false;
	private $userLoggedOut = false;

	private $registerDone = false;
	private $registeredUserName = '';

	private $model;

	public function __construct(\model\LoginModel $model) {
		$this->navigationView = new NavigationView();
		$this->cookieStorage = new CookieStorage();

		$this->model = $model;
	}

	public function setLoginHasSucceeded() {
		$this->loginHasSucceeded = true;
	}
	public function setLoginHasFailed() {
		$this->loginHasFailed = true;
	}
	public function setUserLoggedOut() {
		$this->userLoggedOut = true;
	}
	public function setHasRegistered($registeredUserName) {
		$this->registerDone = true;
		$this->registeredUserName = $registeredUserName;
	}

	public function response() {
		$html = $this->generateHeader();
		if ($this->model->isLoggedIn($this->getUserClient())) {
			$html .= $this->doLogoutForm();
		}
		else {
			$html .= $this->doLoginForm();
		}
		return $html;
	}

	public function responseBreadcrumbSubItems() {
		$breadCrumbs = array();
		$breadCrumbs[] = new BreadcrumbItem('Login', '', true);
		return $breadCrumbs;
	}

	private function doLoginForm() {

		if ($this->registerDone === true) {
			$message = "Registered new user.";
		}
		else if ($this->loginHasFailed === true) {
			if ($this->getTempPassword() != "") {
				$message = "Wrong information in cookies";
			}
			else if ($this->getUserName() == "") {
				$message = "Username is missing";
			}
			else if ($this->getPassword() == "") {
				$message = "Password is missing";
			}
			else {
				$message = "Wrong name or password";
			}
		}
		else if ($this->wantToLogOut() && $this->userLoggedOut) {
			$message = "Bye bye!";
			$this->redirect($message, $this->navigationView->getURLToHomePage());
		}
		else {
			$message = $this->getSessionMessage();
		}

		//remove any temp credentials
		$this->deleteTempCredentials();

		return $this->generateLoginFormHTML($message);
	}

	private function doLogoutForm() {

		$message = '';
		if ($this->loginHasSucceeded === true) {
			$message = "Welcome";
			if (isset($_COOKIE[self::$cookiePassword])) {
				$message .= " back with cookie";
			}
			else if ($this->getRememberMe() == true) {
				$message .= " and you will be remembered";
			}
			$this->redirect($message);
		}
		else {
			//load message
			$message = $this->getSessionMessage();
		}

		//update or delete temp credentials
		if ($this->getRememberMe()) {
			$this->saveNewTempCredentials();
		}
		else {
			$this->deleteTempCredentials();
		}

		return $this->generateLogoutButtonHTML($message);
	}

	private function redirect($message, $url = '') {

		$_SESSION[self::$sessionMessageLocation] = $message;
		if ($url == '') {
			$url = $_SERVER['PHP_SELF'];
		}
		header('Location: ' . $url);
	}


	/**
	 * Retrieves the user's client info
	 * @return \model\UserClient
	 */
	public function getUserClient() {
		return new \model\UserClient($_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]);
	}

	/**
	 * Retrieves the user's credentials
	 * @return \model\LoginCredentials
	 */
	public function getLoginCredentials() {
		return new \model\LoginCredentials($this->getUserName(),
											$this->getPassword(),
											$this->getTempPassword(),
											$this->getUserClient());
	}


	public function wantToLogIn() {
		return isset($_POST[self::$login]) ||
			   isset($_COOKIE[self::$cookieName]);
	}
	public function wantToLogOut() {
		return $this->navigationView->onLogoutPage(); //isset($_POST[self::$logout]);
	}

	private function getRequestUserName() {
		if ($this->registeredUserName != '') {
			return $this->registeredUserName;
		}
		if (isset($_POST[self::$name])) {
			return $_POST[self::$name];
		}
		return "";
	}
	private function getUserName() {
		if (isset($_POST[self::$name])) {
			return $_POST[self::$name];
		}
		$cookieUserName = $this->cookieStorage->load(self::$cookieName);
		if ($cookieUserName != null) {
			return $cookieUserName;
		}
		return "";
	}
	private function getPassword() {
		if (isset($_POST[self::$password])) {
			return $_POST[self::$password];
		}
		return "";
	}
	private function getRememberMe() {
		return isset($_POST[self::$keep]) ||
		 	   isset($_COOKIE[self::$cookiePassword]);
	}


	private function getTempPassword() {
		return $this->cookieStorage->load(self::$cookiePassword);
	}

	/**
	 * Saves new temp credentials to cookies
	 */
	private function saveNewTempCredentials() {
		$tempCredentials = $this->model->getTempCredentials();
		if ($tempCredentials) {
			$this->cookieStorage->save(self::$cookieName, $this->getUserName(), $tempCredentials->getExpire());
			$this->cookieStorage->save(self::$cookiePassword, $tempCredentials->getTempPassword(), $tempCredentials->getExpire());
		}
	}
	/**
	 * Deletes temp credentials from cookies
	 */
	private function deleteTempCredentials() {
		$this->cookieStorage->delete(self::$cookieName);
		$this->cookieStorage->delete(self::$cookiePassword);
	}

	private function getSessionMessage() {
		if (isset($_SESSION[self::$sessionMessageLocation])) {
			$message = $_SESSION[self::$sessionMessageLocation];
			unset($_SESSION[self::$sessionMessageLocation]);
			return $message;
		}
		return "";
	}



	private function generateHeader() {
		return '<header class="panel-heading">
                    <h1>Login</h1>
                </header>';
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
	 * @return  string
	 */
	private function generateLoginFormHTML($message) {
		return '
			<div class="panel-body">

			  <form method="post" class="form-horizontal">
				<p id="' . self::$messageId . '">' . $message . '</p>

				<div class="form-group">
					<label class="col-sm-2 control-label" for="' . self::$name . '">Username</label>
					<div class="col-sm-10">
						<input type="text" id="' . self::$name . '" name="' . self::$name . '" class="form-control" value="'. $this->getRequestUserName() . '" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="' . self::$password . '">Password</label>
					<div class="col-sm-10">
						<input type="password" id="' . self::$password . '" name="' . self::$password . '" class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<label>
							<input type="checkbox" id="' . self::$keep . '" name="' . self::$keep . '" /> Remember me
						</label>
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="' . self::$login . '" class="btn btn-default">
							Login
						</button>
					</div>
				</div>
			  </form>

			  <div>
			  	<p>
			  		<a href="' . $this->navigationView->getURLToRegister() . '">
						Register a new user
			  		</a>
			  	</p>
			  </div>
		  	</div>
		';
	}

	public function generateOutputForNavigationBar(\model\User $loggedInUser = null) {

		$html = '<ul class="nav navbar-nav navbar-right">';

		if ($this->model->isLoggedIn($this->getUserClient())) {
            $html .= '<li>' . ($loggedInUser != null ? '<a href="' . $this->navigationView->getURLToUser($loggedInUser) . '">' : '') . '<span class="glyphicon glyphicon-user"></span> ' . $this->model->loggedInUserName($this->getUserClient()) . ($loggedInUser != null ? '</a>' : '') . '</li>
                        <li><a href="' . $this->navigationView->getURLToLogout() . '">Log out</a></li>';
        }
        else {
            $html .= '<li><a href="' . $this->navigationView->getURLToLogin() . '">Log in</a></li>
					  <li><a href="' . $this->navigationView->getURLToRegister() . '">Register</a></li>';
        }

        $html .= '</ul>';

		return $html;
	}

}