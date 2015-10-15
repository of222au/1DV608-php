<?php

namespace model;

class LoginCredentials {

    private $userName;
    private $password;
    private $tempPassword;
    private $client;

    public function __construct($userName, $password, $tempPassword, $client) {
        $this->userName = $userName;
        $this->password = $password;
        $this->tempPassword = $tempPassword;
        $this->client = $client;
    }

    public function getUserName() {
        return $this->userName;
    }
    public function getPassword() {
        return $this->password;
    }
    public function getTempPassword() {
        return $this->tempPassword;
    }
    public function getClient() {
        return $this->client;
    }

}