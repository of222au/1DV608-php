<?php

namespace model;

/**
 * Class EntryRawInfo
 * used when only interested in the basic entry info
 * @package model
 */
class EntryRawInfo implements Entry {

    private $entryType;
    private $entrySpecificId;
    private $userId;

    public function __construct($entryType, $entrySpecificId, $userId) {
        $this->entryType = $entryType;
        $this->entrySpecificId = $entrySpecificId;
        $this->userId = $userId;
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

}
