<?php

namespace model;

class UserGroup {

    private $id;
    private $userId;
    private $name;
    private $createdAt;

    public function __construct($id, $userId, $name, $createdAt = null) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->createdAt = $createdAt;
    }

    public function getId() {
        return $this->id;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getName() {
        return $this->name;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }
}