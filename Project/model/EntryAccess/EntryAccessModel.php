<?php

namespace model;

require_once("EntryAccess.php");
require_once("EntryUserGroupAccess.php");

class EntryAccessModel {

    private $entryAccessDAL;

    private $entryAccess = null;

    public function __construct(EntryAccessDAL $entryAccessDAL) {
        $this->entryAccessDAL = $entryAccessDAL;
    }

    public function getEntryAccess(Entry $entry, User $user) {

        if ($this->entryAccess == null || !$this->entryAccess->checkIsSame($entry, $user)) {

            $access = $this->entryAccessDAL->getAccessToEntry($entry, $user);
            $isCreator = ($entry->getUserId() == $user->getId());
            $userGroupsAccess = $this->entryAccessDAL->getUserGroupAccessesToEntry($entry);

            $this->entryAccess = new EntryAccess($entry->getEntryType(), $entry->getId(), $user->getId(), $access, $isCreator, $userGroupsAccess);
        }

        return $this->entryAccess;
    }


}
