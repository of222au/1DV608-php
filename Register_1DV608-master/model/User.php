<?php

namespace model;

class User {

    private $username;
    private $passwordHash;

    public function __construct($username, $passwordHash) {
        $this->username = $username;
        $this->passwordHash = $passwordHash;
    }

    public function setTempCredentials($tempCredentials) {

        $this->tempCredentials = $tempCredentials;
    }

    public function getUsername() {
        return $this->username;
    }
    public function getPasswordHash() {
        return $this->username;
    }

    public function checkPassword($password) {
        return password_verify($password, $this->passwordHash);
    }

}

