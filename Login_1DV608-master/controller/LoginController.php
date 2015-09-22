<?php

namespace controller;

class LoginController {

    //views
    private $layoutView;
    private $loginView;
    private $dateTimeView;

    //models
    private $user;
    private $loginPersistor;

    public function __construct(\view\LayoutView $layoutView, \view\LoginView $loginView, \view\dateTimeView $dateTimeView) {
        $this->layoutView = $layoutView;
        $this->loginView = $loginView;
        $this->dateTimeView = $dateTimeView;

        $this->loginPersistor = new \model\LoginPersistor();
    }

    /**
     * Do all the login actions and return the rendered html from views
     */
    public function doLogin() {
        $isLoggedIn = false; //can be untouched or set to 1 (did log in now) or 2 (was already logged in by session) or 3 (login retrieved from storage)
        $isLoggedOut = false; //true or false

        $userAgent = $this->loginView->getUserAgent();

        //try to load user from session
        $sessionUser = $this->loginPersistor->getSessionLogin($userAgent); //$lm->loadUserModel($this->loginView->getCookiePassword());

        //check if possible to load user from storage by view's cookie credentials
        $storageUser = null;
        if ($this->loginView->getCookieUsername() != '' && $this->loginView->getCookiePassword() != '') {

            try {
                //try to read user from storage
                $cookieUsername = $this->loginView->getCookieUsername();
                $cookiePassword = $this->loginView->getCookiePassword();
                $storageUser = $this->loginPersistor->getSavedLogin($cookieUsername, $cookiePassword, $userAgent);
            } catch (\Exception $e) {
                //do nothing
            }
        }

        if ($this->loginView->isLoggingOut() && $sessionUser != null) {

            //user requests to log out and the session has a logged in user, then perform the logout actions
            //delete cookie credentials in view
            $this->loginView->deleteCookieCredentials();
            //clear the storage file from the login
            try {
                $this->loginPersistor->clearLoginFile();
            }
            catch (\Exception $e) {
                //do nothing
            }
            //clear the server session from the login
            $this->loginPersistor->logOutUser();

            $isLoggedOut = true;
        }
        else if ($sessionUser !== null) {

            //successful authentication from session
            $this->user = $sessionUser;
            $isLoggedIn = 2; // 2 = already logged in by session
        }
        else if ($storageUser !== null) {

            //successful authentication from storage
            $this->user = $storageUser;
            $isLoggedIn = 3; // 3 = login by storage/cookies authentication
        }
        else if ($this->loginView->isLoggingIn()) {

            //retrieve the information needed from view
            $userName = $this->loginView->getRequestUserName();
            $password = $this->loginView->getRequestPassword();
            $keep = $this->loginView->getRequestKeep();

            if (is_string($userName) && $userName != '' && is_string($password) && $password != '') {

                //create user
                $this->user = new \model\UserModel($userName, password_hash($password, PASSWORD_DEFAULT));

                //check if correct login credentials
                if ($this->user->hasCorrectLoginCredentials()) {

                    //successful login, save to session
                    $this->loginPersistor->logInUser($this->user, $userAgent);
                    $isLoggedIn = 1; // 1 = has performed a new login
                }
            }
        }

        //if is logged in and to keep credentials, OR if there are credentials already stored which needs to be updated
        if (($isLoggedIn == 1 && $keep == true) || !$this->loginView->isCookieUsernameAndPasswordEmpty()) {

            if ($isLoggedIn == true) {
                //store the new temp password to view's cookies..
                $expiresAt = time() + $this->loginPersistor->getTimeInSecondsToRememberLogins();
                $this->loginView->saveCookieCredentials($this->user->getUserName(), $this->user->getTempPassword(), $expiresAt);

                //..and to server storage file
                try {
                    $this->loginPersistor->saveLoginFile($this->user->getUserName(), $this->user->getTempPassword());
                }
                catch (\Exception $e) {
                    //do nothing
                }
            }
            else {
                //clear the login storage since not logged in
                try {
                    $this->loginPersistor->clearLoginFile();
                }
                catch (\Exception $e) {
                    //do nothing
                }
            }
        }

        //render the HTML
        $this->layoutView->render($isLoggedIn, $isLoggedOut, $this->loginView, $this->dateTimeView);
    }

}

