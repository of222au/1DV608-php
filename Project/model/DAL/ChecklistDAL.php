<?php

namespace model;

class ChecklistDAL {

    private $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    public function getChecklist($checklistId) {
        assert(is_numeric($checklistId));

        //select from database
        $stmt = $this->database->prepare("SELECT * FROM " . \Settings::DATABASE_TABLE_CHECKLISTS .
                                          " WHERE id = " . $checklistId);
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $userId, $title, $description, $createdAt);
            $stmt->fetch();

            $checklist = new Checklist($id, $userId, $title, $description, $createdAt);

            //now get the checklist items
            $checklistItems = $this->getChecklistItems($checklist);
            $checklist->setChecklistItems($checklistItems);

            return $checklist;
        }

        return null;
    }

    private function getChecklistItems(Checklist $checklist) {

        $checklistItems = array();

        //select from database
        $stmt = $this->database->prepare("SELECT * FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEMS .
                                            " WHERE checklist_id = " . $checklist->getId());
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();

        $stmt->bind_result($id, $checklistId, $title, $description, $important, $createdAt, $updatedAt);

        while ($stmt->fetch()) {
            $checklistItem = new ChecklistItem($id, $checklistId, $title, $description, $important, $createdAt, $updatedAt);

            //add it
            $checklistItems[] = $checklistItem;
        }

        foreach ($checklistItems as $item) {

            //get latest state for this item and set as the current in the checklist item
            $latestState = $this->getLatestChecklistItemState($item);
            if ($latestState != null) {
                $item->setCurrentState($latestState);
            }

        }

        return $checklistItems;
    }

    private function getLatestChecklistItemState(ChecklistItem $forChecklistItem) {

        //select from database
        $stmt = $this->database->prepare("SELECT cis.*, cist.state FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATES . " AS cis
                                                INNER JOIN " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATE_TYPES . " AS cist ON cis.state_type_id = cist.id
                                                INNER JOIN (SELECT checklist_item_id, MAX(created_at) AS created_at FROM " . \Settings::DATABASE_TABLE_CHECKLIST_ITEM_STATES . " GROUP BY checklist_item_id) AS cis2
                                                    ON cis.checklist_item_id = cis2.checklist_item_id AND cis.created_at = cis2.created_at
                                            WHERE cis.checklist_item_id = " . $forChecklistItem->getId());
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $checklistItemId, $userId, $stateTypeId, $createdAt, $state);
            $stmt->fetch();

            return new ChecklistItemState($id, $checklistItemId, $userId, $state, $createdAt);
        }

        return null;
    }



}