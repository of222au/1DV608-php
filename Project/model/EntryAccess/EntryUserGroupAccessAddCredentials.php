<?php

namespace model;

class EntryUserGroupAccessAddCredentials {

    private $userGroup;
    private $entryType;
    private $entrySpecificId;
    private $accessType; //one of the \Settings::ACCESS_TYPE_...

    public function __construct(UserGroup $userGroup, Entry $entry, $accessType) {
        assert($accessType == \Settings::ACCESS_TYPE_READ || $accessType == \Settings::ACCESS_TYPE_WRITE);

        $this->userGroup = $userGroup;
        $this->entryType = $entry->getEntryType();
        $this->entrySpecificId = $entry->getId();
        $this->accessType = $accessType;
    }

    public function getUserGroupId() {
        return $this->userGroup->getId();
    }
    public function getEntryType() {
        return $this->entryType;
    }
    public function getEntrySpecificId() {
        return $this->entrySpecificId;
    }
    public function getAccessType() {
        return $this->accessType;
    }
}