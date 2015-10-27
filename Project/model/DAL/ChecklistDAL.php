<?php

namespace model;

require_once("model/DAL/EntryDAL.php");

/**
 * MYSQL table structure:

CREATE TABLE IF NOT EXISTS `checklists` (
`id` int(11) NOT NULL,
`title` varchar(100) NOT NULL,
`description` text NOT NULL,
`created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `checklists`
ADD PRIMARY KEY (`id`);

ALTER TABLE `checklists`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 ** checklist_items:

CREATE TABLE IF NOT EXISTS `checklist_items` (
`id` int(11) NOT NULL,
`checklist_id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
`title` varchar(100) NOT NULL,
`description` text NOT NULL,
`important` tinyint(1) NOT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `checklist_items`
ADD PRIMARY KEY (`id`),
ADD KEY `id` (`id`),
ADD KEY `id_2` (`id`),
ADD KEY `checklist_id` (`checklist_id`);

ALTER TABLE `checklist_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 ** checklist_item_states

CREATE TABLE IF NOT EXISTS `checklist_item_states` (
`id` int(11) NOT NULL,
`checklist_item_id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
`state_type_id` int(11) NOT NULL,
`created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `checklist_item_states`
ADD PRIMARY KEY (`id`),
ADD KEY `checklist_item_id` (`checklist_item_id`),
ADD KEY `user_id` (`user_id`),
ADD KEY `state_type_id` (`state_type_id`);

ALTER TABLE `checklist_item_states`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `checklist_item_states`
ADD CONSTRAINT `fk_checklist_item_id` FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_items` (`id`) ON DELETE CASCADE;

 ** checklist_item_state_types

CREATE TABLE IF NOT EXISTS `checklist_item_state_types` (
`id` int(11) NOT NULL,
`name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `checklist_item_state_types`
ADD PRIMARY KEY (`id`);

ALTER TABLE `checklist_item_state_types`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
 */

class ChecklistDAL extends EntryDAL {

    public function __construct(\mysqli $db) {
        parent::__construct($db);
    }

    public function getChecklists($checklistIdArray) {
        assert(is_array($checklistIdArray));

        //get checklists
        $entries = $this->getEntriesOfType(\Settings::ENTRY_TYPE_CHECKLIST, $checklistIdArray);

        //get checklist items
        $this->getChecklistItemsForChecklists($entries);

        return $entries;
    }
    public function getChecklist($checklistId) {
        assert(is_numeric($checklistId));

        //get checklists
        $entry = $this->getEntry(\Settings::ENTRY_TYPE_CHECKLIST, $checklistId);

        if ($entry != null) {

            //get checklist items
            $this->getChecklistItemsForChecklists(array($entry));
        }

        return $entry;
    }

    private function getChecklistItemsForChecklists($checklists) {
        assert(is_array($checklists));

        //create array of checklist ids to read from database
        $checklistIdArray = array();
        foreach($checklists as $checklist) {
            $checklistIdArray[] = $checklist->getId();
        }

        if (count($checklistIdArray)) {
            $checklistItems = array();

            //select from database
            $stmt = $this->database->prepare("SELECT ci.*, u.username
                                                FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEMS . " AS ci
                                                INNER JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON ci.user_id = u.id
                                                WHERE ci.checklist_id " . $this->getStatementINQuestionMarks($checklistIdArray));
            if ($stmt === FALSE) {
                throw new \Exception($this->database->error);
            }

            //bind parameters
            $params = array();
            $params[] = $this->getBindParamINParamTypes($checklistIdArray, 'i');
            foreach($checklistIdArray as $value) {
                $params[] = $value;
            }
            call_user_func_array(array($stmt, 'bind_param'), $this->makeValuesReferenced($params));

            //execute statement
            $stmt->execute();
            $stmt->bind_result($id, $checklistId, $userId, $title, $description, $important, $createdAt, $updatedAt, $userName);

            while ($stmt->fetch()) {
                $checklistItem = new ChecklistItem($id, $checklistId, $userId, $title, $description, $important, $createdAt, $updatedAt, $userName);

                //add it
                $checklistItems[] = $checklistItem;
            }

            //loop all found checklist items and set them to it's checklist
            foreach ($checklistItems as $item) {

                //get latest state for this item and set as the current in the checklist item
                $latestState = $this->getLatestChecklistItemState($item);
                if ($latestState != null) {
                    $item->setCurrentState($latestState);
                }

                //now find the correct checklist to store this item
                $checklistFound = false;
                foreach($checklists as $checklist) {
                    if ($checklist->getId() == $item->getChecklistId()) {
                        $checklist->addChecklistItem($item);
                        $checklistFound = true;
                        break;
                    }
                }
                if (!$checklistFound) {
                    throw new \Exception("Unexpected error, could not match checklist item to a checklist");
                }
            }

            //sort all checklist items
            foreach($checklists as $checklist) {
                $checklist->sortChecklistItems();
            }

            return true;
        }
        return false;
    }

    private function getLatestChecklistItemState(ChecklistItem $forChecklistItem) {

        //select from database
        $stmt = $this->database->prepare("SELECT cis.*, cist.name, u.username FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATES . " AS cis
                                                INNER JOIN " . \Settings::DATABASE_TABLE_USERS . " AS u ON cis.user_id = u.id
                                                INNER JOIN " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATE_TYPES . " AS cist ON cis.state_type_id = cist.id
                                                INNER JOIN (SELECT checklist_item_id, MAX(created_at) AS created_at FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATES . " GROUP BY checklist_item_id) AS cis2
                                                    ON cis.checklist_item_id = cis2.checklist_item_id AND cis.created_at = cis2.created_at
                                            WHERE cis.checklist_item_id = ?");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $checklistItemId = $forChecklistItem->getId();
        $stmt->bind_param('i', $checklistItemId);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $checklistItemId, $userId, $stateTypeId, $createdAt, $state, $userName);
            $stmt->fetch();

            return new ChecklistItemState($id, $checklistItemId, $userId, $state, $createdAt, $userName);
        }

        return null;
    }

    public function saveEditedChecklistDetails(ChecklistEditCredentials $credentials) {

        //prepare the database insert
        $stmt = $this->database->prepare("UPDATE " . \Settings::DATABASE_TABLE_CHECKLISTS . "
                                            SET title = ?, description = ?
                                            WHERE id = ?");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $title = $credentials->getTitle();
        $description = $credentials->getDescription();
        $checklistId = $credentials->getChecklistId();

        $stmt->bind_param('ssi', $title, $description, $checklistId);

        //execute the update
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }
    public function saveNewChecklist(ChecklistAddCredentials $credentials, User $user) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_CHECKLISTS . "
                                            (title, description, created_at)
                                            VALUES (?, ?, NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $title = $credentials->getTitle();
        $description = $credentials->getDescription();
        $stmt->bind_param('ss', $title, $description);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }

        $insertId = $this->database->insert_id;

        //now insert a new row into the entries table as well (important!)
        $entryRawInfo = new EntryRawInfo(\Settings::ENTRY_TYPE_CHECKLIST, $insertId, $user->getId());
        $this->saveNewEntryRow($entryRawInfo);

        return $insertId;
    }

    public function saveNewChecklistItem(ChecklistItemAddCredentials $credentials, User $loggedInUser) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_CHECKLIST_ITEMS . "
                                            (checklist_id, user_id, title, description, important, created_at, updated_at)
                                            VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $checklistId = $credentials->getChecklistId();
        $userId = $loggedInUser->getId();
        $title = $credentials->getTitle();
        $description = $credentials->getDescription();
        $important = $credentials->getImportant();

        $stmt->bind_param('iissi', $checklistId, $userId, $title, $description, $important);

        //execute the statement
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }

        $insertId = $this->database->insert_id;

        return $insertId;
    }

    public function deleteChecklistItem(ChecklistItem $checklistItem) {

        //prepare the database insert
        $stmt = $this->database->prepare("DELETE FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEMS . "
                                            WHERE id = ?");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //bind values
        $checklistItemId = $checklistItem->getId();
        $stmt->bind_param('i', $checklistItemId);

        //execute the delete
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }
    }

    public function saveNewChecklistItemStates($credentialsArray, User $loggedInUser) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATES . "
                                            (checklist_item_id, user_id, state_type_id, created_at)
                                            VALUES (?, ?, (SELECT id FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATE_TYPES . " WHERE name = ?), NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $bindHasBeenDone = false;
        foreach($credentialsArray as $credentials) {

            $checklistItemId = $credentials->getChecklistItemId();
            $userId = $loggedInUser->getId();
            $stateType = $credentials->getStateType();

            if (!$bindHasBeenDone) {
                $stmt->bind_param('iis', $checklistItemId, $userId, $stateType);
                $bindHasBeenDone = true;
            }

            //execute the statement
            $result = $stmt->execute();
            if ($result === FALSE) {
                throw new \Exception($this->database->error);
            }
        }
    }
}