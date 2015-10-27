<?php

namespace model;

require_once("model/User/UserGroupAddCredentials.php");
require_once("model/User/UserGroupEditCredentials.php");
require_once("model/User/UserGroupMember.php");
require_once("model/DAL/UserGroupDAL.php");

/**
 * Class UserGroupModel
 * model for simplifying use of UserGroupDAL
 * @package model
 */
class UserGroupModel {

    private $userGroupDAL;
    private $userDAL;

    public function __construct(\mysqli $database, UserDAL $userDAL) {
        $this->userGroupDAL = new \model\UserGroupDAL($database);
        $this->userDAL = $userDAL;
    }

    public function getUserGroup($userGroupId) {
        try {
            return $this->userGroupDAL->getUserGroup($userGroupId);
        }
        catch (\Exception $e) { }

        return null;
    }
    public function getAllUserGroups() {
        return $this->userGroupDAL->getAllUserGroups();
    }
    public function getUserGroupsWithUser(User $withUser) {
        return $this->userGroupDAL->getUserGroupsWithUser($withUser);
    }

    public function saveNewUserGroup(UserGroupAddCredentials $credentials, User $user) {
        $userGroupId = $this->userGroupDAL->saveNewUserGroup($credentials, $user);
        return $this->userGroupDAL->getUserGroup($userGroupId);
    }
    public function saveEditedUserGroupDetails(UserGroupEditCredentials $credentials) {
        $this->userGroupDAL->editUserGroup($credentials);
    }

    public function saveNewUserGroupMember(UserGroup $userGroup, UserInterface $user) {
        $this->userGroupDAL->saveNewUserGroupMember($userGroup, $user);
    }

    public function removeUserGroupMember(UserGroup $userGroup, UserInterface $member) {
        $this->userGroupDAL->removeUserGroupMember($userGroup, $member);
    }


}
