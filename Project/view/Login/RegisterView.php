<?php

namespace view;

class RegisterView {

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

        return $this->generateRegisterForm($messages);
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
            <h2>Register new user</h2>
			<form method="post">
				<fieldset>
				<legend>Register a new user - Write username and password</legend>
					<p id="' . self::$messageId . '">' . $messageHtml . '</p>
					<label for="' . self::$userName . '">Username :</label>
					<input type="text" size="20" name="' . self::$userName . '" id="' . self::$userName . '" value="' . strip_tags($this->getUserName()) . '">
					<br>
					<label for="' . self::$password . '">Password  :</label>
					<input type="password" size="20" name="' . self::$password . '" id="' . self::$password . '">
					<br>
					<label for="' . self::$passwordRepeat . '">Repeat password  :</label>
					<input type="password" size="20" name="' . self::$passwordRepeat . '" id="' . self::$passwordRepeat . '">
					<br>
					<input id="submit" type="submit" name="' . self::$register . '" value="Register">
					<br>
				</fieldset>
			</form>';
    }
}