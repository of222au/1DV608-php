<?php

namespace model;

class User {

    private $id;
    private $username;
    private $passwordHash;
    private $createdAt;

    public function __construct($id, $username, $passwordHash, $createdAt) {
        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
    }

    public function setTempCredentials($tempCredentials) {

        $this->tempCredentials = $tempCredentials;
    }

    public function getId() {
        return $this->id;
    }
    public function getUsername() {
        return $this->username;
    }
    public function getPasswordHash() {
        return $this->username;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function checkPassword($password) {
        return password_verify($password, $this->passwordHash);
    }

}

