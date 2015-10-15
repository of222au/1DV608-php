<?php

namespace model;

class EntryAccess {

    private $entryType; //string, any of the \Settings::ENTRY_TYPE_...
    private $entryId;
    private $userId;

    private $access; //string, any of the \Settings::ACCESS_TYPE_...
    private $creator; //boolean

    private $userGroupsAccess; //array of EntryUserGroupAccess

    public function __construct($entryType, $entryId, $userId, $access, $creator, $userGroupsAccess) {
        assert(is_string($entryType));
        assert(is_numeric($entryId));
        assert(is_numeric($userId));
        assert($access == null || is_string($access));
        assert(is_bool($creator));

        $this->entryType = $entryType;
        $this->entryId = $entryId;
        $this->userId = $userId;
        $this->access = $access;
        $this->creator = $creator;
        $this->userGroupsAccess = $userGroupsAccess;
    }

    public function checkIsSame(Entry $entry, User $user) {
        return ($this->entryType == $entry->getEntryType() &&
                $this->entryId == $entry->getId() &&
                $this->userId == $user->getId());
    }

    public function canRead() {
        return ($this->creator === true ||
                $this->access == \Settings::ACCESS_TYPE_WRITE ||
                $this->access == \Settings::ACCESS_TYPE_READ);
    }
    public function canWrite() {
        return ($this->creator === true ||
                $this->access == \Settings::ACCESS_TYPE_WRITE);
    }
    public function isCreator() {
        return $this->creator;
    }

    public function getUserGroupsAccess() {
        return $this->userGroupsAccess;
    }

}
