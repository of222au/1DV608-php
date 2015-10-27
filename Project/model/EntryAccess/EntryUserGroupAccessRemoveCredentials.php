<?php

namespace model;

class EntryUserGroupAccessRemoveCredentials {

    private $userGroup;
    private $entryType;
    private $entryId;

    public function __construct(EntryUserGroupAccess $userGroup, EntryAccess $entryAccess) {
        $this->userGroup = $userGroup;
        $this->entryType = $entryAccess->getEntryType();
        $this->entryId = $entryAccess->getId();
    }

    public function getUserGroupId() {
        return $this->userGroup->getUserGroupId();
    }
    public function getEntryType() {
        return $this->entryType;
    }
    public function getEntryId() {
        return $this->entryId;
    }
}