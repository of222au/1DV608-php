<?php

namespace model;

/**
 * MYSQL table structure:

CREATE TABLE IF NOT EXISTS `entries` (
`id` int(11) NOT NULL,
`entry_type_id` int(11) NOT NULL,
`entry_specific_id` int(11) NOT NULL,
`user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `entries`
ADD PRIMARY KEY (`id`);

ALTER TABLE `entries`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 ** entry_types

CREATE TABLE IF NOT EXISTS `entry_types` (
`id` int(11) NOT NULL,
`name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `entry_types`
ADD PRIMARY KEY (`id`);

ALTER TABLE `entry_types`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 */

class EntryDAL extends GeneralDAL {

    protected $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    protected function getEntry($entryType, $entrySpecificId) {

        $entryArray = $this->getEntriesOfType($entryType, array($entrySpecificId));

        if ($entryArray != null && count($entryArray) === 1) {
            return $entryArray[0];
        }
        return null;
    }

    protected function getEntriesOfType($entryType, $entriesSpecificIdList) {
        assert(is_array($entriesSpecificIdList));

        if ($entryType == \Settings::ENTRY_TYPE_CHECKLIST) {
            $entryTable = \Settings::DATABASE_TABLE_CHECKLISTS;
        }
        else {
            throw new \Exception("Not implemented");
        }

        $stmt = $this->database->prepare("SELECT specificTable.*, e.user_id, u.username, u.password_hash AS 'user_password_hash', u.created_at AS 'user_created_at'
                                           FROM " . $entryTable . " AS specificTable
                                           LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRIES . " AS e ON specificTable.id = e.entry_specific_id
                                           LEFT JOIN " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " AS et ON e.entry_type_id = et.id
                                           LEFT JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON e.user_id = u.id
                                           WHERE specificTable.id " . $this->getStatementINQuestionMarks($entriesSpecificIdList) . "
                                                AND et.name = ?");

        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //bind parameters
        $params = array();
        $params[] = $this->getBindParamINParamTypes($entriesSpecificIdList, 's') . 's';
        if ($entriesSpecificIdList != null) {
            foreach($entriesSpecificIdList as $value) {
                $params[] = $value;
            }
        }
        $params[] = $entryType;
        call_user_func_array(array($stmt, 'bind_param'), $this->makeValuesReferenced($params));

        //execute statement
        $stmt->execute();

        if ($entryType == \Settings::ENTRY_TYPE_CHECKLIST) {
            $stmt->bind_result($id, $title, $description, $created_at, $user_id, $username, $user_password_hash, $user_created_at);
        }

        $entriesArray = array();
        while ($stmt->fetch()) {
            $entry = null;
            if ($entryType == \Settings::ENTRY_TYPE_CHECKLIST) {
                $entry = new Checklist($id, $user_id, $title, $description, $created_at);
            }

            $user = new User($user_id, $username, $user_password_hash, $user_created_at);
            $entry->setUser($user);

            //add it
            $entriesArray[] = $entry;
        }

        return $entriesArray;
    }

    protected function saveNewEntryRow(Entry $entry) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_ENTRIES . "
                                            (entry_type_id, entry_specific_id, user_id)
                                            VALUES ((SELECT id FROM " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " WHERE name = ?), ?, ?)");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $entryType = $entry->getEntryType();
        $entrySpecificId = $entry->getId();
        $userId = $entry->getUserId();

        $stmt->bind_param('sii', $entryType, $entrySpecificId, $userId);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }

    protected function deleteEntryRow(Entry $entry) {

        //prepare the database insert
        $stmt = $this->database->prepare("DELETE e.* FROM " . \Settings::DATABASE_TABLE_ENTRIES . " AS e
                                            INNER JOIN " . \Settings::DATABASE_TABLE_ENTRY_TYPES . " AS et ON e.entry_type_id = et.id
                                            WHERE et.name = ?
                                                AND e.entry_specific_id = ?");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //bind values
        $entryType = $entry->getEntryType();
        $entrySpecificId = $entry->getId();

        $stmt->bind_param('i', $entryType, $entrySpecificId);

        //execute the delete
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }

}