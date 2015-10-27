<?php

namespace model;

require_once("model/Checklist/ChecklistBase.php");
require_once("model/EntryAccess/Entry.php");
require_once("model/Checklist/ChecklistItem.php");
require_once("model/Checklist/ChecklistItemState.php");

class Checklist extends ChecklistBase implements Entry {

    private $id;
    private $userId;
    private $createdAt;

    private $checklistItems = null; //array of \model\ChecklistItem | null
    private $user = null;           // \model\User | null

    public function __construct($id, $userId, $title, $description, $createdAt) {
        assert(is_numeric($id));
        assert(is_numeric($userId));

        $this->id = $id;
        $this->userId = $userId;
        $this->setTitle($title);
        $this->setDescription($description);
        $this->createdAt = $createdAt;

        $this->checklistItems = array();
    }

    public function getEntryType() {
        return \Settings::ENTRY_TYPE_CHECKLIST;
    }

    public function getId() {
        return $this->id;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setChecklistItems($checkListItems) {
        $this->checklistItems = $checkListItems;

        //sort them as well
        $this->sortChecklistItems();
    }
    public function addChecklistItem(ChecklistItem $item) {
        if ($this->checklistItems == null) { $this->checklistItems = array(); }
        $this->checklistItems[] = $item;
    }
    public function getChecklistItems() {
        return $this->checklistItems;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }
    public function getUser() {
        return $this->user;
    }

    public function getCheckedCount() {
        return $this->getItemCountPerStateType(\Settings::CHECKLIST_ITEM_STATE_CHECKED);
    }
    public function getUncheckedCount() {
        return $this->getItemCountPerStateType(\Settings::CHECKLIST_ITEM_STATE_UNCHECKED);
    }
    public function getArchivedCount() {
        return $this->getItemCountPerStateType(\Settings::CHECKLIST_ITEM_STATE_ARCHIVED);
    }
    public function getNonArchivedCount() {
        $archivedCount = $this->getArchivedCount();
        return count($this->checklistItems) - $archivedCount;
    }
    private function getItemCountPerStateType($stateType) {
        $count = 0;
        foreach($this->checklistItems as $item) {
            if ($item->getCurrentStateType() == $stateType) {
                $count += 1;
            }
        }

        return $count;
    }

    public function findChecklistItem($id) {
        foreach ($this->checklistItems as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }
        return null;
    }

    public function getChecklistItemsByCurrentStateType($stateType) {
        $result = array();
        foreach ($this->checklistItems as $item) {
            if ($item->getCurrentStateType() == $stateType) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function getTotalCount($calculateIncludingArchived = false) {
        $itemCount = count($this->getChecklistItems());
        $uncheckedCount = $this->getUncheckedCount();
        $checkedCount = $this->getCheckedCount();

        if ($calculateIncludingArchived) {
            return $itemCount;
        }
        else {
            return ($uncheckedCount + $checkedCount);
        }

    }
    public function getDoneCount($calculateIncludingArchived = false) {
        $itemCount = count($this->getChecklistItems());
        $uncheckedCount = $this->getUncheckedCount();
        $checkedCount = $this->getCheckedCount();

        if ($calculateIncludingArchived) {
            return ($itemCount - $uncheckedCount);
        }
        else {
            return $checkedCount;
        }
    }

    public function sortChecklistItems() {
        if ($this->checklistItems != null) {
            usort($this->checklistItems, array("model\\ChecklistItem", "cmp"));
        }
    }




}