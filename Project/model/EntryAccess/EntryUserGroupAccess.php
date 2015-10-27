<?php

namespace model;

/**
 * Class EntryUserGroupAccess
 * keeps info on which access a specific user group has
 * @package model
 */
class EntryUserGroupAccess {

    private $userGroup;  // \model\UserGroup
    private $access; //string, any of the \Settings::ACCESS_TYPE_...

    public function __construct(UserGroup $userGroup, $access) {
        assert($access == null || is_string($access));

        $this->userGroup = $userGroup;
        $this->access = $access;
    }

    public function getUserGroupId() {
        return $this->userGroup->getId();
    }
    public function getUserGroupName() {
        return $this->userGroup->getName();
    }
    public function getUserGroup() {
        return $this->userGroup;
    }
    public function getAccess() {
        return $this->access;
    }
}
