<?php

namespace model;

/**
 * Class UserGroupUserAccess
 * stores info on which access/role a user has to a user group
 * @package model
 */
class UserGroupUserAccess {

    const NO_ACCESS = 0;
    const IS_CREATOR = 1;
    const IS_MEMBER = 2;

    private $access;
    private $userId;

    public function __construct(UserGroup $userGroup, User $user) {
        if ($userGroup->isUserCreator($user)) {
            $this->access = self::IS_CREATOR;
        }
        else if ($userGroup->isUserMember($user)) {
            $this->access = self::IS_MEMBER;
        }
        else {
            $this->access = self::NO_ACCESS;
        }
        $this->userId = $user->getId();
    }

    public function isCreator() {
        return ($this->access == self::IS_CREATOR);
    }
    public function isMember() {
        return ($this->access == self::IS_MEMBER);
    }
    public function isCreatorOrMember() {
        return ($this->isCreator() || $this->isMember());
    }
    public function getUserId() {
        return $this->userId;
    }
}