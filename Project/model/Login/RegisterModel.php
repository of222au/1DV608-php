<?php

namespace model;

require_once("RegisterCredentials.php");
require_once("model/User/User.php");

class RegisterModel {

    private $userDAL;
    private $registerErrors;

    public function __construct(UserDAL $userDAL) {
        assert(isset($_SESSION));

        $this->userDAL = $userDAL;
    }

    public function doRegister(RegisterCredentials $credentials) {

        $this->registerErrors = [];

        //check for any errors
        if (strlen($credentials->getUserName()) < \Settings::USERNAME_MIN_LENGTH) {
            $this->registerErrors[] = 'Username has too few characters, at least ' . \Settings::USERNAME_MIN_LENGTH . ' characters.';
        }
        else if (preg_match('/[^a-zA-Z0-9_]/', $credentials->getUserName())) {
            $this->registerErrors[] = 'Username contains invalid characters.';
        }
        else if ($this->userDAL->checkIfUserNameAlreadyExists($credentials->getUserName())) {
            $this->registerErrors[] = 'User exists, pick another username.';
        }
        if (strlen($credentials->getPassword()) < \Settings::PASSWORD_MIN_LENGTH) {
            $this->registerErrors[] = 'Password has too few characters, at least ' . \Settings::PASSWORD_MIN_LENGTH . ' characters.';
        }
        else if ($credentials->getPassword() != $credentials->getPasswordRepeat()) {
            $this->registerErrors[] = 'Passwords do not match.';
        }

        //if any errors
        if (count($this->registerErrors) > 0) {
            return false;
        }

        //register the user
        try {
            $result = $this->userDAL->saveNewUser($credentials);
            return $result;
        }
        catch(\Exception $e) {
            $this->registerErrors[] = 'Oops.. Something went wrong when trying to save.';
        }
    }

    public function getRegisterErrorMessages() {
        return $this->registerErrors;
    }



}
