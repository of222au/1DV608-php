<?php

namespace model;

class EntryAccessDAL {

    private $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    public function getAccessToEntry(Entry $entry, User $user) {

        $accesses = array();

        //get a list of user groups with access type for this user and entry
        $stmt = $this->database->prepare("SELECT ug.id AS 'user_group_id', act.name AS 'access_type_name' FROM " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . " AS eua
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug ON eua.user_group_id = ug.id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . " AS ugm ON ug.id = ugm.user_group_id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " AS et ON eua.entry_type_id = et.id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ACCESS_TYPES . " AS act ON eua.access_type_id = act.id
                                            WHERE ugm.user_id = " . $user->getId() . "
                                                AND et.name = '" . $entry->getEntryType() . "'
                                                AND eua.entry_id = " . $entry->getId());
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->bind_result($user_group_id, $access_type_name);

        while ($stmt->fetch()) {
            $accesses[] = $access_type_name;
        }

        $bestAccess = null;
        foreach ($accesses as $access) {
            if ($access == \Settings::ACCESS_TYPE_WRITE ||
                $access == \Settings::ACCESS_TYPE_READ && $bestAccess != \Settings::ACCESS_TYPE_WRITE) {

                $bestAccess = $access;
            }
        }

        return $bestAccess;
    }

    public function getUserGroupAccessesToEntry(Entry $entry) {

        $userGroupAccesses = array();

        //get a list of user groups with access type for this user and entry
        $stmt = $this->database->prepare("SELECT ug.id AS 'user_group_id', ug.name AS 'user_group_name', act.name AS 'access_type_name' FROM " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . " AS eua
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug ON eua.user_group_id = ug.id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " AS et ON eua.entry_type_id = et.id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ACCESS_TYPES . " AS act ON eua.access_type_id = act.id
                                            WHERE et.name = '" . $entry->getEntryType() . "'
                                                AND eua.entry_id = " . $entry->getId());
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->bind_result($user_group_id, $user_group_name, $access_type_name);

        while ($stmt->fetch()) {
            $userGroupAccesses[] =  new EntryUserGroupAccess($user_group_id, $user_group_name, $access_type_name);
        }

        return $userGroupAccesses;
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
        $stmt->bind_result($id, $user_id, $name, $createdAt);

        while ($stmt->fetch()) {
            $userGroup = new UserGroup($id, $user_id, $name, $createdAt);
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