<?php

namespace model;

class UserGroupEditCredentials extends UserGroupBase {

    private $userGroup;

    public function __construct(UserGroup $userGroup, $name) {
        $this->userGroup = $userGroup;
        parent::__construct($name);
    }

    public function getUserGroupId() {
        return $this->userGroup->getId();
    }
    public function getUserId() {
        return $this->userGroup->getUserId();
    }
}