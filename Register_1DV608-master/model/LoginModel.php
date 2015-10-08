<?php

namespace model;

require_once("LoginCredentials.php");
require_once("TempCredentials.php");
require_once("TempCredentialsDAL.php");
require_once("LoggedInUser.php");
require_once("UserClient.php");

class LoginModel {

    private static $sessionUserLocation = 'LoginModel::loggedInUser';

    private $tempCredentials = null;
    private $tempCredentialsDAL;
    private $userDAL;

    public function __construct(UserDAL $userDAL) {
        assert(isset($_SESSION));

        self::$sessionUserLocation .= \Settings::APP_SESSION_NAME;

        $this->tempCredentialsDAL = new TempCredentialsDAL();
        $this->userDAL = $userDAL;
    }

    /**
     * checks if logged in
     * @param UserClient $userClient
     * @return bool
     */
    public function isLoggedIn(UserClient $userClient) {
        if (isset($_SESSION[self::$sessionUserLocation])) {
            $loggedInUser = $_SESSION[self::$sessionUserLocation];
            if ($loggedInUser->isSameAsLastTime($userClient)) {
                return true;
            }
        }
        return false;
    }

    /**
     * tries to login
     * @param LoginCredentials $loginCredentials
     * @return bool
     */
    public function doLogin(LoginCredentials $loginCredentials) {

        //get user
        $user = $this->userDAL->getUser($loginCredentials->getUserName());
        if ($user != null) {

            //try to retrieve stored temp credentials
            $this->tempCredentials = $this->tempCredentialsDAL->loadLogin($loginCredentials->getUserName());

            //check if login is possible
            $loginByUsernameAndPassword = ($user != null && $user->checkPassword($loginCredentials->getPassword())); //($loginCredentials->getUserName() == \Settings::USERNAME && $loginCredentials->getPassword() == \Settings::PASSWORD);
            $loginByTempCredentials = ($this->tempCredentials != null && $this->tempCredentials->isValid($loginCredentials->getTempPassword()));

            if ($loginByUsernameAndPassword || $loginByTempCredentials) {
                //create new logged in user and store in session
                $loggedInUser = new LoggedInUser($loginCredentials);
                $_SESSION[self::$sessionUserLocation] = $loggedInUser;
                return true;
            }
        }

        return false;
    }

    /**
     * unsets the session variable
     */
    public function doLogOut() {
        unset($_SESSION[self::$sessionUserLocation]);
    }

    /**
     * renews the temporary credentials (if logged in)
     * @param  UserClient $userClient
     */
    public function renewTempCredentials(UserClient $userClient) {
        if ($this->isLoggedIn($userClient)) {
            $loggedInUser = $_SESSION[self::$sessionUserLocation];
            $this->tempCredentials = new TempCredentials();
            $this->tempCredentialsDAL->saveLogin($loggedInUser, $this->tempCredentials);
        }
    }

    public function getTempCredentials() {
        return $this->tempCredentials;
    }

}