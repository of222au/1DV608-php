<?php

namespace model;

class UserGroupDAL {

    private static $table_user_groups = "user_groups";
    private static $table_user_group_members = "user_group_members";

    private $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    public function getUserGroups($userGroupIds = null, User $withUser = null) {

        $userGroups = array();

/*
 * SELECT ug.id, ugm.user_id FROM user_group_members AS ugm
  LEFT JOIN user_groups AS ug ON ug.id = ugm.user_group_id
  WHERE ugm.user_group_id IN (SELECT ug.id FROM user_groups AS ug
                                            LEFT JOIN user_group_members AS ugm ON ug.id = ugm.user_group_id
                                            WHERE ugm.user_id = 5)

 */
        //select all rows from table where
        $stmt = $this->database->prepare("SELECT * FROM " . self::$table_user_groups . " AS ug
                                            LEFT JOIN " . self::$table_user_group_members . " AS ugm ON ug.id = ugm.user_group_id
                                            WHERE ugm.user_id = " . $user->getId());
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->bind_result($id, $name, $createdAt);

        while ($stmt->fetch()) {
            $userGroup = new UserGroup($id, $name, $createdAt);
            $userGroups[] = $userGroup;
        }

        return $userGroups;
    }

    public function getUserGroupsWithUser(User $user) {

        $userGroups = array();

        //select all rows from table where
        $stmt = $this->database->prepare("SELECT * FROM " . self::$table_user_groups . " AS ug
                                            LEFT JOIN " . self::$table_user_group_members . " AS ugm ON ug.id = ugm.user_group_id
                                            WHERE ugm.user_id = " . $user->getId());
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->bind_result($id, $name, $createdAt);

        while ($stmt->fetch()) {
            $userGroup = new UserGroup($id, $name, $createdAt);
            $userGroups[] = $userGroup;
        }

        return $userGroups;
    }

    public function saveNewUserGroup($name) {

        //prepare the database statement
        $stmt = $this->database->prepare("INSERT INTO " . self::$table_user_groups . "
                                            (name, created_at)
                                            VALUES (?, NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $stmt->bind_param('s', $name);

        //execute the insert
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
        else {
            return $this->database->insert_id();
        }
    }

    public function saveNewUserGroupMember(UserGroup $group, User $user) {
        return $this->addOrRemoveUserFromGroup(true, $group, $user);
    }

    public function removeUserGroupMember(UserGroup $group, User $user) {
        return $this->addOrRemoveUserFromGroup(false, $group, $user);
    }

    private function addOrRemoveUserFromGroup($addNotRemove, UserGroup $group, User $user) {
        assert($group != null && $group->getId() > 0);
        assert($user != null && $user->getId() > 0);

        //prepare the database statement
        $statement = '';
        if ($addNotRemove) {
            $statement = "INSERT INTO " . self::$table_user_group_members . "
                                            (user_group_id, user_id, created_at)
                                            VALUES (?, ?, NOW())";
        }
        else {
            $statement = "DELETE FROM " . self::$table_user_group_members . "
                                            WHERE user_group_id = ? AND user_id = ?";
        }
        $stmt = $this->database->prepare($statement);
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $stmt->bind_param('ii', $group->getId(), $user->getId());

        //execute the insert
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }

        return true;
    }

    


}