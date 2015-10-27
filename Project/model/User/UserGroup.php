<?php

namespace model;

class UserGroup extends UserGroupBase {

    private $id;
    private $userId;
    private $createdAt;
    //more field(s) are derived from UserGroupBase

    private $user;      // \model\User
    private $members;   // array of \model\UserGroupMember

    public function __construct($id, $userId, $name, $createdAt = null) {
        $this->id = $id;
        $this->userId = $userId;
        parent::__construct($name);
        $this->createdAt = $createdAt;

        $this->members = array();
    }

    public function getId() {
        return $this->id;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }
    public function getUser() {
        return $this->user;
    }

    public function addMember(UserGroupMember $member) {
        if ($this->members == null) { $this->members = array(); }
        $this->members[] = $member;
    }
    public function setMembers($members) {
        assert(is_array($members));

        $this->members = $members;
    }
    public function getMembers() {
        return $this->members;
    }
    public function getMemberCount() {
        return ($this->members != null ? count($this->members) : 0);
    }

    /**
     * @param $userId
     * @return UserGroupMember | null
     */
    public function findMember($userId) {
        foreach($this->members as $member) {
            if ($member->getId() == $userId) {
                return $member;
            }
        }
        return null;
    }


    public function isUserCreator(User $user) {
        if ($this->userId == $user->getId()) {
            return true;
        }
        return false;
    }
    public function isUserMember(User $user) {
        foreach($this->members as $member) {
            if ($user->getId() == $member->getId()) {
                return true;
            }
        }
        return false;
    }
    public function isUserMemberOrCreator(User $user) {
        if ($this->isUserCreator($user)) {
            return true;
        }
        else if ($this->isUserMember($user)) {
            return true;
        }
        return false;
    }
}