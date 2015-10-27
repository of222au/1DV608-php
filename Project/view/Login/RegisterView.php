<?php

namespace view;

class RegisterView implements PageView {

    private static $register = 'RegisterView::Register';
    private static $messageId = 'RegisterView::Message';
    private static $userName = 'RegisterView::UserName';
    private static $password = 'RegisterView::Password';
    private static $passwordRepeat = 'RegisterView::PasswordRepeat';

    private static $sessionRegisteredUserName = 'view\\RegisterView\\registeredUserName';

    private $model;

    private $registerHasFailed = false;
    private $registerHasSucceeded = false;

    public function __construct(\model\RegisterModel $model) {
        $this->model = $model;
    }

    public function setRegisterHasSucceeded() {
        $this->registerHasSucceeded = true;
    }
    public function setRegisterHasFailed() {
        $this->registerHasFailed = true;
    }

    public function hasRegisterSucceeded() {
        return $this->registerHasSucceeded;
    }

    public function response() {

        $messages = [];
        if ($this->registerHasFailed === true) {

            //get all error messages from the model
            $messages = $this->model->getRegisterErrorMessages();
        }
        else if ($this->registerHasSucceeded === true) {
            $this->redirectToLoginAfterSuccessfulRegistration($this->getUserName());
        }

        $html = $this->generateHeader();
        $html .= $this->generateRegisterForm($messages);
        return $html;
    }

    public function responseBreadcrumbSubItems() {
        $navigationView = new NavigationView();
        $breadCrumbs = array();
        $breadCrumbs[] = new BreadcrumbItem('Login', $navigationView->getURLToLogin(), false);
        $breadCrumbs[] = new BreadcrumbItem('Register', '', true);
        return $breadCrumbs;
    }

    private function redirectToLoginAfterSuccessfulRegistration($registeredUserName) {

        $_SESSION[self::$sessionRegisteredUserName] = $registeredUserName;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?');
    }

    public function getRegisteredUserName() {
        if (isset($_SESSION[self::$sessionRegisteredUserName])) {
            $username = $_SESSION[self::$sessionRegisteredUserName];
            unset($_SESSION[self::$sessionRegisteredUserName]);
            return $username;
        }
        return '';
    }

    /**
     * Retrieves the register form credentials
     * @return \model\RegisterCredentials
     */
    public function getRegisterCredentials() {
        return new \model\RegisterCredentials($this->getUserName(),
                                                $this->getPassword(),
                                                $this->getPasswordRepeat());
    }

    public function wantToRegister() {
        return isset($_POST[self::$register]);
    }

    private function getUserName() {
        if (isset($_POST[self::$userName])) {
            return trim($_POST[self::$userName]);
        }
        return "";
    }
    private function getPassword() {
        if (isset($_POST[self::$password])) {
            return trim($_POST[self::$password]);
        }
        return "";
    }
    private function getPasswordRepeat() {
        if (isset($_POST[self::$passwordRepeat])) {
            return trim($_POST[self::$passwordRepeat]);
        }
        return "";
    }


    private function generateHeader() {
        return '<header class="panel-heading">
                    <h1>Register</h1>
                </header>';
    }


    /**
     * @param $messages, array of strings
     * @return string
     */
    private function generateRegisterForm($messages) {

        $messageHtml = '';
        if ($messages != null && count($messages) > 0) {
            $messageHtml = implode("<br>", $messages);
        }

        return '
            <div class="panel-body">
			  <form method="post">

                <p id="' . self::$messageId . '">' . $messageHtml . '</p>

				<div class="form-group">
					<label class="control-label" for="' . self::$userName . '">Username</label>
                    <input type="text" id="' . self::$userName . '" name="' . self::$userName . '" class="form-control" value="' . strip_tags($this->getUserName()) . '" />
				</div>
				<div class="form-group">
					<label class="control-label" for="' . self::$password . '">Password</label>
                    <input type="password" id="' . self::$password . '" name="' . self::$password . '" class="form-control" />
				</div>
				<div class="form-group">
					<label class="control-label" for="' . self::$passwordRepeat . '">Repeat password</label>
                    <input type="password" id="' . self::$passwordRepeat . '" name="' . self::$passwordRepeat . '" class="form-control" />
				</div>

                <button type="submit" name="' . self::$register . '" class="btn btn-default">
                    Register
                </button>

              </form>
            </div>';
    }
}