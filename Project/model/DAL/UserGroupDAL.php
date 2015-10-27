<?php

namespace model;

/**
 * MYSQL table structure:

CREATE TABLE IF NOT EXISTS `user_groups` (
`id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `user_groups`
ADD PRIMARY KEY (`id`);

ALTER TABLE `user_groups`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 ** user_group_members

CREATE TABLE IF NOT EXISTS `user_group_members` (
`id` int(11) NOT NULL,
`user_group_id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
`created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `user_group_members`
ADD PRIMARY KEY (`id`);

ALTER TABLE `user_group_members`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
 */

class UserGroupDAL extends GeneralDAL {

    private $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    public function getUserGroup($userGroupId) {
        assert(is_numeric($userGroupId));

        $userGroups = $this->getUserGroups(array($userGroupId), null, true);
        if ($userGroups != null && count($userGroups) === 1) {
            return $userGroups[0];
        }
        return null;
    }
    public function getUserGroupsWithUser(User $user) {
        return $this->getUserGroups(null, $user);
    }
    public function getAllUserGroups() {
        return $this->getUserGroups();
    }

    private function getUserGroups($userGroupIds = null, User $withUser = null ,$onlyWantToFindOne = false) {

        $userGroups = $this->doGetUserGroups($userGroupIds, $withUser);
        if ($userGroups != null && !$onlyWantToFindOne || ($onlyWantToFindOne && count($userGroups) === 1)) {

            //get creators (users)
            $this->getUserGroupCreators($userGroups);

            //get user group members
            $this->getUserGroupMembersForUserGroups($userGroups);

            return $userGroups;
        }
        return null;
    }
    private function doGetUserGroups($userGroupIds = null, User $withUser = null) {

        $userGroups = array();

        //select all rows from table where
        $stmt = $this->database->prepare("SELECT ug.* FROM " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . " AS ugm ON ug.id = ugm.user_group_id
                                            " . ($withUser != null || $userGroupIds != null ? "
                                            WHERE (
                                                " . ($withUser != null ? "(ugm.user_id = ? OR ug.user_id = ?)" : "") . "
                                                " . ($userGroupIds != null ? ($withUser != null ? " AND " : "") . "ug.id IN (?)" : "") . "
                                            )" : "") . "
                                            GROUP BY ug.id");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $userId = ($withUser != null ? $withUser->getId() : null);
        $userGroupIdsString = ($userGroupIds != null ? implode(',', $userGroupIds) : null);

        if ($userId != null && $userGroupIdsString != null) {
            $stmt->bind_param('iis', $userId, $userId, $userGroupIdsString);
        }
        else if ($userId != null) {
            $stmt->bind_param('ii', $userId, $userId);
        }
        else if ($userGroupIdsString != null) {
            $stmt->bind_param('s', $userGroupIdsString);
        }

        $stmt->execute();
        $stmt->bind_result($id, $user_id, $name, $createdAt);

        while ($stmt->fetch()) {
            $userGroup = new UserGroup($id, $user_id, $name, $createdAt);
            $userGroups[] = $userGroup;
        }

        return $userGroups;
    }

    private function getUserGroupCreators($userGroups) {
        assert(is_array($userGroups));

        $userGroupIdArray = array();
        foreach($userGroups as $userGroup) {
            $userGroupIdArray[] = $userGroup->getId();
        }

        if (count($userGroupIdArray)) {

            //select from database
            $stmt = $this->database->prepare("SELECT ug.id, u.id AS 'user_id', u.username, u.password_hash AS 'user_password_hash', u.created_at AS 'user_created_at'
                                                FROM " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug
                                                INNER JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON ug.user_id = u.id
                                                WHERE ug.id " . $this->getStatementINQuestionMarks($userGroupIdArray));
            if ($stmt === FALSE) {
                throw new \Exception($this->database->error);
            }

            //bind parameters
            $params = array();
            $params[] = $this->getBindParamINParamTypes($userGroupIdArray, 'i');
            foreach($userGroupIdArray as $value) {
                $params[] = $value;
            }
            call_user_func_array(array($stmt, 'bind_param'), $this->makeValuesReferenced($params));

            $stmt->execute();
            $stmt->bind_result($id, $userId, $userName, $userPasswordHash, $userCreatedAt);

            while ($stmt->fetch()) {
                $user = new User($userId, $userName, $userPasswordHash, $userCreatedAt);

                //now find the correct user group to store this member
                $userGroupFound = false;
                foreach($userGroups as $userGroup) {
                    if ($userGroup->getId() == $id) {

                        $userGroup->setUser($user);
                        $userGroupFound = true;
                        //continue if more groups have same creator
                    }
                }
                if (!$userGroupFound) {
                    throw new \Exception("Unexpected error, could not match user creator to a user group");
                }
            }

            return true;
        }
        return false;
    }
    private function getUserGroupMembersForUserGroups($userGroups) {
        assert(is_array($userGroups));

        $userGroupIdArray = array();
        foreach($userGroups as $userGroup) {
            $userGroupIdArray[] = $userGroup->getId();
        }

        if (count($userGroupIdArray)) {

            //select from database
            $stmt = $this->database->prepare("SELECT ugm.*, u.username, u.password_hash AS 'user_password_hash', u.created_at AS 'user_created_at'
                                                FROM " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . " AS ugm
                                                INNER JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON ugm.user_id = u.id
                                                WHERE ugm.user_group_id " . $this->getStatementINQuestionMarks($userGroupIdArray));
            if ($stmt === FALSE) {
                throw new \Exception($this->database->error);
            }

            //bind parameters
            $params = array();
            $params[] = $this->getBindParamINParamTypes($userGroupIdArray, 'i');
            foreach($userGroupIdArray as $value) {
                $params[] = $value;
            }
            call_user_func_array(array($stmt, 'bind_param'), $this->makeValuesReferenced($params));

            $stmt->execute();
            $stmt->bind_result($id, $userGroupId, $userId, $createdAt, $userName, $userPasswordHash, $userCreatedAt);

            while ($stmt->fetch()) {
                $member = new UserGroupMember($userId, $userName, $createdAt);

                //now find the correct user group to store this member
                $userGroupFound = false;
                foreach($userGroups as $userGroup) {
                    if ($userGroup->getId() == $userGroupId) {
                        $userGroup->addMember($member);
                        $userGroupFound = true;
                        break;
                    }
                }
                if (!$userGroupFound) {
                    throw new \Exception("Unexpected error, could not match user group member to a user group");
                }
            }

            return true;
        }
        return false;
    }

    public function saveNewUserGroup(UserGroupAddCredentials $credentials, User $user) {

        //prepare the database statement
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_USER_GROUPS . "
                                            (user_id, name, created_at)
                                            VALUES (?, ?, NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $userId = $user->getId();
        $name = $credentials->getName();
        $stmt->bind_param('is', $userId, $name);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
        else {
            return $this->database->insert_id;
        }
    }
    public function editUserGroup(UserGroupEditCredentials $credentials) {

        //prepare the database statement
        $stmt = $this->database->prepare("UPDATE " . \Settings::DATABASE_TABLE_USER_GROUPS . "
                                            SET name = ?
                                            WHERE id = ?");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $name = $credentials->getName();
        $userGroupId = $credentials->getUserGroupId();
        $stmt->bind_param('si', $name, $userGroupId);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }

    public function saveNewUserGroupMember(UserGroup $group, UserInterface $user) {
        $this->addOrRemoveUserFromGroup(true, $group, $user);
    }

    public function removeUserGroupMember(UserGroup $group, UserInterface $member) {
        $this->addOrRemoveUserFromGroup(false, $group, $member);
    }

    private function addOrRemoveUserFromGroup($addNotRemove, UserGroup $group, UserInterface $user) {
        assert($group != null && $group->getId() > 0);
        assert($user != null && $user->getId() > 0);

        //prepare the database statement
        if ($addNotRemove) {
            $statement = "INSERT INTO " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . "
                                            (user_group_id, user_id, created_at)
                                            VALUES (?, ?, NOW())";
        }
        else {
            $statement = "DELETE FROM " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . "
                                            WHERE user_group_id = ? AND user_id = ?";
        }
        $stmt = $this->database->prepare($statement);
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $userGroupId = $group->getId();
        $userId = $user->getId();
        $stmt->bind_param('ii', $userGroupId, $userId);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }







}