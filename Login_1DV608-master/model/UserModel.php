<?php

namespace model;

class UserModel {

    private $userName;
    private $passwordHash;
    private $tempPassword;

    public function __construct($userName, $passwordHash = '') {
        assert(is_string($userName) && is_string($passwordHash));
        assert(strlen($userName) > 0);

        $this->userName = $userName;
        $this->passwordHash = $passwordHash;

        //generate temp password for the user
        $this->tempPassword = $this->generateRandomString(20);
    }

    public function getUserName() {
        return $this->userName;
    }
    public function getTempPassword() {
        return $this->tempPassword;
    }

    /**
     * Checks if the user has correct username and password
     * @return bool
     */
    public function hasCorrectLoginCredentials() {
        if ($this->userName == 'Admin' && password_verify('Password', $this->passwordHash)) {
            return true;
        }
        return false;
    }


    /**
     * Generates a random string of a specific length
     * function by Stephen Watkins: http://stackoverflow.com/questions/4356289/php-random-string-generator
     *
     * @param int $length
     * @return string
     */
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}