<?php

namespace model;

class RegisterCredentials {

    private $userName;
    private $password;
    private $passwordRepeat;

    public function __construct($userName, $password, $passwordRepeat) {
        $this->userName = $userName;
        $this->password = $password;
        $this->passwordRepeat = $passwordRepeat;
    }

    public function getUserName() {
        return $this->userName;
    }
    public function getPassword() {
        return $this->password;
    }
    public function getPasswordRepeat() {
        return $this->passwordRepeat;
    }

}
