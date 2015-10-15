<?php

namespace model;

require_once("model/Checklist/ChecklistItem.php");
require_once("model/Checklist/ChecklistItemState.php");

class Checklist implements Entry {

    private $id;
    private $userId;
    private $title;
    private $description;
    private $createdAt;

    private $checklistItems = null;

    public function __construct($id, $userId, $title, $description, $createdAt) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt;
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
    public function getTitle() {
        return $this->title;
    }
    public function getDescription() {
        return $this->description;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setChecklistItems($checkListItems) {
        $this->checklistItems = $checkListItems;

        //sort them as well
        $this->sortChecklistItems();
    }
    public function getChecklistItems() {
        return $this->checklistItems;
    }

    public function getUncheckedCount() {
        $count = 0;
        foreach($this->checklistItems as $item) {
            if ($item->getCurrentStateType() == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED) {
                $count += 1;
            }
        }

        return $count;
    }


    private function sortChecklistItems() {

        /*
        $newArray = array();
        $used = array(count($this->checklistItems));
        for ($i = 0; $i < count($this->checklistItems); $i++) {

            $nextItem = null;
            foreach ($this->checklistItems as $item) {

                //compare state type (the \Settings::CHECKLIST_ITEM_STATE_...)
                $nextItemStateType = ($nextItem != null && $nextItem->getCurrentState() != null ? $nextItem->getCurrentState()->getStateType() : '');
                $nextItemStateSortPoints = ($nextItemStateType == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED ? 1 : ($nextItemStateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED ? 2 : ($nextItemStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 5 : 3)));
                $itemStateType = ($item->getCurrentState() != null ? $item->getCurrentState()->getStateType() : '');
                $itemStateSortPoints = ($itemStateType == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED ? 1 : ($itemStateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED ? 2 : ($itemStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 5 : 3)));

                if ($nextItem == null ||
                    $itemStateSortPoints > $nextItemStateSortPoints ||
                    ($itemStateSortPoints == $nextItemStateSortPoints && $item->getUpdatedAt() < $nextItem->getUpdatedAt())) {

                    $nextItem = $item;
                }
            }

            if ($nextItem != null) {
                throw new \Exception("Sort error in checklist items");
            }

            $newArray[] = $nextItem;
            $this->checklistItems[]
        }
        */


        usort($this->checklistItems, array("model\\ChecklistItem", "cmp"));

    }




}