<?php

namespace model;

/**
 * MYSQL table structure:

CREATE TABLE IF NOT EXISTS `entry_user_group_accesses` (
`id` int(11) NOT NULL,
`entry_id` int(11) NOT NULL,
`user_group_id` int(11) NOT NULL,
`access_type_id` int(11) NOT NULL,
`user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `entry_user_group_accesses`
ADD PRIMARY KEY (`id`);

ALTER TABLE `entry_user_group_accesses`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 ** access_types

CREATE TABLE IF NOT EXISTS `access_types` (
`id` int(11) NOT NULL,
`name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `access_types`
ADD PRIMARY KEY (`id`);

ALTER TABLE `access_types`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
 */

class EntryAccessDAL extends GeneralDAL {

    private $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    /**
     * Retrieves all entries (like checklists) that the user has access to
     * @param User $user
     * @param null $onlyOfEntryTypes  //array of \Settings::ENTRY_TYPE_...
     * @return array of \model\EntryRawInfo
     * @throws \Exception
     */
    public function getAllEntriesUserHasAccessTo(User $user, $onlyOfEntryTypes = null) {
        assert($onlyOfEntryTypes == null || is_array($onlyOfEntryTypes));

        $entriesArray = array();

        $useSpecificEntryTypes = ($onlyOfEntryTypes != null && count($onlyOfEntryTypes));

        //get a list of user groups with access type for this user and entry
        $stmt = $this->database->prepare("SELECT entry_type_id, entry_type_name, entry_specific_id
                                            FROM
                                            (
                                              SELECT e.entry_type_id, et.name AS 'entry_type_name', e.entry_specific_id
                                                    FROM " . \Settings::DATABASE_TABLE_ENTRIES . " AS e
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . " AS eua ON e.id = eua.entry_id
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug ON eua.user_group_id = ug.id
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . " AS ugm ON ug.id = ugm.user_group_id
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON ugm.user_id = u.id
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " AS et ON e.entry_type_id = et.id
                                                    WHERE (ugm.user_id = ? OR ug.user_id = ?)
                                                        " . ($useSpecificEntryTypes ? " AND et.name " . $this->getStatementINQuestionMarks($onlyOfEntryTypes) : "") . "
                                            UNION ALL
                                                SELECT e.entry_type_id, et.name AS 'entry_type_name', e.entry_specific_id
                                                    FROM " . \Settings::DATABASE_TABLE_ENTRIES . " AS e
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON e.user_id = u.id
                                                    LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " AS et ON e.entry_type_id = et.id
                                                    WHERE (e.user_id = ?)
                                                        " . ($useSpecificEntryTypes ? " AND et.name " . $this->getStatementINQuestionMarks($onlyOfEntryTypes) : "") . "
                                            ) results
                                            GROUP BY entry_type_id, entry_type_name, entry_specific_id");

        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //bind parameters
        $userId = $user->getId();
        $params = array();
        $params[] = 'ii' . ($useSpecificEntryTypes ? $this->getBindParamINParamTypes($onlyOfEntryTypes, 's') : '') . 'i' . ($useSpecificEntryTypes ? $this->getBindParamINParamTypes($onlyOfEntryTypes, 's') : '');

        $params[] = $userId;
        $params[] = $userId;
        if ($useSpecificEntryTypes) {
            foreach($onlyOfEntryTypes as $entryType) {
                $params[] = $entryType;
            }
        }
        $params[] = $userId;
        if ($useSpecificEntryTypes) {
            foreach($onlyOfEntryTypes as $entryType) {
                $params[] = $entryType;
            }
        }
        call_user_func_array(array($stmt, 'bind_param'), $this->makeValuesReferenced($params));

        //execute statement
        $stmt->execute();
        $stmt->bind_result($entry_type_id, $entry_type_name, $entry_specific_id);

        while ($stmt->fetch()) {

            $entryRawInfo = new \model\EntryRawInfo($entry_type_name, $entry_specific_id, $user->getId());
            $entriesArray[] = $entryRawInfo;
        }

        return $entriesArray;
    }

    public function getAccessToEntry(Entry $entry, User $user) {

        $accesses = array();

        //get a list of user groups with access type for this user and entry
        $stmt = $this->database->prepare("SELECT ug.id AS 'user_group_id', act.name AS 'access_type_name'
                                            FROM " . \Settings::DATABASE_TABLE_ENTRIES . " AS e
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . " AS eua ON e.id = eua.entry_id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug ON eua.user_group_id = ug.id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUP_MEMBERS . " AS ugm ON ug.id = ugm.user_group_id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ACCESS_TYPES . " AS act ON eua.access_type_id = act.id
                                            WHERE (ugm.user_id = ? OR ug.user_id = ?)
                                                AND eua.entry_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRIES . "
                                                                        WHERE entry_type_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " WHERE name = ?) AND entry_specific_id = ?)");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $userId = $user->getId();
        $entryTypeName = $entry->getEntryType();
        $entrySpecificId = $entry->getId();
        $stmt->bind_param("iisi", $userId, $userId, $entryTypeName, $entrySpecificId);

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

    /**
     * Gets all the user groups that has access to the entry
     * @param Entry $entry
     * @return array of \model\EntryUserGroupAccess
     * @throws \Exception
     */
    public function getUserGroupAccessesToEntry(Entry $entry) {

        $userGroupAccesses = array();

        //get a list of user groups with access type for this user and entry
        $stmt = $this->database->prepare("SELECT ug.id AS 'user_group_id', ug.user_id AS 'user_group_user_id', ug.name AS 'user_group_name', ug.created_at AS 'user_group_created_at', act.name AS 'access_type_name'
                                            FROM " . \Settings::DATABASE_TABLE_ENTRIES . " AS e
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . " AS eua ON e.id = eua.entry_id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_USER_GROUPS . " AS ug ON eua.user_group_id = ug.id
                                            LEFT JOIN " . \Settings::DATABASE_TABLE_ACCESS_TYPES . " AS act ON eua.access_type_id = act.id
                                            WHERE eua.entry_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRIES . "
                                                                        WHERE entry_type_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " WHERE name = ?) AND entry_specific_id = ?)");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $entryTypeName = $entry->getEntryType();
        $entrySpecificId = $entry->getId();
        $stmt->bind_param("si", $entryTypeName, $entrySpecificId);

        $stmt->execute();
        $stmt->bind_result($user_group_id, $user_group_user_id, $user_group_name, $user_group_created_at, $access_type_name);

        while ($stmt->fetch()) {
            $userGroup = new UserGroup($user_group_id, $user_group_user_id, $user_group_name, $user_group_created_at);
            $userGroupAccesses[] = new EntryUserGroupAccess($userGroup, $access_type_name);
        }

        return $userGroupAccesses;
    }

    public function saveNewUserGroupAccessToEntry(EntryUserGroupAccessAddCredentials $credentials, User $user) {

        //prepare the database statement
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . "
                                            (entry_id, user_group_id, access_type_id, user_id)
                                            VALUES ((SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRIES . "
                                                        WHERE entry_type_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " WHERE name = ?) AND entry_specific_id = ?),
                                                    ?,
                                                    (SELECT id FROM " . \Settings::DATABASE_TABLE_ACCESS_TYPES . " WHERE name = ?),
                                                    ?)");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $entryType = $credentials->getEntryType();
        $entrySpecificId = $credentials->getEntrySpecificId();
        $userGroupId = $credentials->getUserGroupId();
        $accessType = $credentials->getAccessType();
        $userId = $user->getId();

        $stmt->bind_param('siisi', $entryType, $entrySpecificId, $userGroupId, $accessType, $userId);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }

    public function deleteUserGroupAccessToEntry(EntryUserGroupAccessRemoveCredentials $credentials) {

        //prepare the database statement
        $stmt = $this->database->prepare("DELETE FROM " . \Settings::DATABASE_TABLE_ENTRY_USER_GROUP_ACCESSES . "
                                            WHERE entry_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRIES . "
                                                                WHERE entry_type_id = (SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " WHERE name = ?) AND entry_specific_id = ?)
                                              AND user_group_id = ?");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $entryType = $credentials->getEntryType();
        $entryId = $credentials->getEntryId();
        $userGroupId = $credentials->getUserGroupId();

        $stmt->bind_param('sii', $entryType, $entryId, $userGroupId);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }


    


}