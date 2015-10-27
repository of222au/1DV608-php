<?php

namespace model;

/**
 * Class EntryAccess
 * handles access info for an entry and user
 * @package model
 */
class EntryAccess implements Entry {

    private $entryType; //string, any of the \Settings::ENTRY_TYPE_...
    private $entrySpecificId;
    private $userId;

    private $access; //string, any of the \Settings::ACCESS_TYPE_...
    private $creator; //boolean

    private $userGroupsAccess; //array of EntryUserGroupAccess
    private $otherUserGroups; //array of UserGroup (which the user is member of, and that is not in the $userGroupsAccess array)

    public function __construct($entryType, $entrySpecificId, $userId, $access, $creator, $userGroupsAccess, $otherUserGroups) {
        assert(is_string($entryType));
        assert(is_numeric($entrySpecificId));
        assert(is_numeric($userId));
        assert($access == null || is_string($access));
        assert(is_bool($creator));

        $this->entryType = $entryType;
        $this->entrySpecificId = $entrySpecificId;
        $this->userId = $userId;
        $this->access = $access;
        $this->creator = $creator;
        $this->userGroupsAccess = $userGroupsAccess;
        $this->otherUserGroups = $otherUserGroups;
    }

    public function getEntryType() {
        return $this->entryType;
    }
    public function getId() {
        return $this->entrySpecificId;
    }
    public function getUserId() {
        return $this->userId;
    }

    public function checkIsSame(Entry $entry, User $user) {
        return ($this->entryType == $entry->getEntryType() &&
                $this->entrySpecificId == $entry->getId() &&
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
    public function getUserGroupsCurrentlyWithoutAccess() {
        return $this->otherUserGroups;
    }
    public function findUserGroupWithoutAccess($userGroupId) {
        if ($userGroupId != null && is_numeric($userGroupId) &&
            $this->otherUserGroups != null) {

            foreach ($this->otherUserGroups as $userGroup) {
                if ($userGroup->getId() == $userGroupId) {
                    return $userGroup;
                }
            }
        }
        return null;
    }
    public function findUserGroupWithAccess($userGroupId) {
        if ($userGroupId != null && is_numeric($userGroupId) &&
            $this->userGroupsAccess != null) {

            foreach ($this->userGroupsAccess as $userGroup) {
                if ($userGroup->getUserGroupId() == $userGroupId) {
                    return $userGroup;
                }
            }
        }
        return null;
    }

}
