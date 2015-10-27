<?php

namespace model;

require_once("EntryAccess.php");
require_once("EntryUserGroupAccess.php");
require_once("EntryUserGroupAccessAddCredentials.php");
require_once("EntryUserGroupAccessRemoveCredentials.php");

/**
 * Class EntryAccessModel
 * a model simplifying use of the EntryAccessDAL
 * @package model
 */
class EntryAccessModel {

    private $entryAccessDAL;
    private $userGroupModel;

    private $entryAccess = null;

    public function __construct(\mysqli $database, UserGroupModel $userGroupModel) {
        $this->entryAccessDAL = new EntryAccessDAL($database);
        $this->userGroupModel = $userGroupModel;
    }

    /**
     * @param User $user
     * @param null $onlyOfEntryTypes array of \Settings::ENTRY_TYPE_...
     * @return array|null
     */
    public function getAllEntriesUserHasAccessTo(User $user, $onlyOfEntryTypes = null) {
        try {
            return $this->entryAccessDAL->getAllEntriesUserHasAccessTo($user, $onlyOfEntryTypes);
        }
        catch(\Exception $e) { }

        return null;
    }

    public function getEntryAccess(Entry $entry, User $user) {
        $this->entryAccess = null;

        try {
            $access = $this->entryAccessDAL->getAccessToEntry($entry, $user);
        }
        catch (\Exception $e) {
            $access = null;
        }

        $isCreator = ($entry->getUserId() == $user->getId());

        try {
            $userGroupsAccess = $this->entryAccessDAL->getUserGroupAccessesToEntry($entry);
        }
        catch (\Exception $e) {
            $userGroupsAccess = null;
        }

        try {
            $groupsWithUser = $this->userGroupModel->getUserGroupsWithUser($user);
        }
        catch (\Exception $e) {
            $groupsWithUser = null;
        }

        $groupsCurrentlyWithoutAccess = array();
        if ($groupsWithUser != null && count($groupsWithUser)) {
            foreach ($groupsWithUser as $group) {
                $alreadyHasAccess = false;
                foreach ($userGroupsAccess as $groupWithAccess) {
                    if ($groupWithAccess->getUserGroupId() == $group->getId()) {
                        $alreadyHasAccess = true;
                        break;
                    }
                }

                if (!$alreadyHasAccess) {
                    //add the group to the ones without access
                    $groupsCurrentlyWithoutAccess[] = $group;
                }
            }
        }

        $this->entryAccess = new EntryAccess($entry->getEntryType(), $entry->getId(), $user->getId(), $access, $isCreator, $userGroupsAccess, $groupsCurrentlyWithoutAccess);

        return $this->entryAccess;
    }

    public function saveNewUserGroupAccessToEntry(EntryUserGroupAccessAddCredentials $credentials, User $user) {
        $this->entryAccessDAL->saveNewUserGroupAccessToEntry($credentials, $user);
    }

    public function deleteUserGroupAccessToEntry(EntryUserGroupAccessRemoveCredentials $credentials) {
        $this->entryAccessDAL->deleteUserGroupAccessToEntry($credentials);
    }

}
