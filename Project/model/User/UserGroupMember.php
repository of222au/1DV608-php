<?php

namespace model;

class UserGroupMember implements UserInterface {

    private $userId;
    private $username;
    private $addedToGroupAt;

    public function __construct($userId, $username, $addedToGroupAt) {
        $this->userId = $userId;
        $this->username = $username;
        $this->addedToGroupAt = $addedToGroupAt;
    }

    public function getId() {
        return $this->userId;
    }
    public function getUsername() {
        return $this->username;
    }
    public function getAddedToGroupAt() {
        return $this->addedToGroupAt;
    }
}