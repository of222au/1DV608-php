<?php

namespace model;

class EntryUserGroupAccess {

    private $userGroupId;
    private $userGroupName;
    private $access; //string, any of the \Settings::ACCESS_TYPE_...

    public function __construct($userGroupId, $userGroupName, $access) {
        assert(is_numeric($userGroupId));
        assert(is_string($userGroupName));
        assert($access == null || is_string($access));

        $this->userGroupId = $userGroupId;
        $this->userGroupName = $userGroupName;
        $this->access = $access;
    }

    public function getUserGroupId() {
        return $this->userGroupId;
    }
    public function getUserGroupName() {
        return $this->userGroupName;
    }
    public function getAccess() {
        return $this->userGroupId;
    }
}
