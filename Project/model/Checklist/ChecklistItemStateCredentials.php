<?php

namespace model;

class ChecklistItemStateCredentials {

    private $checklistItem; // \model\ChecklistItem
    private $stateType;     // string, one of the \Settings::CHECKLIST_ITEM_STATE_...

    public function __construct(ChecklistItem $checklistItem, $stateType) {
        $this->checklistItem = $checklistItem;
        $this->stateType = $stateType;
    }

    public function getChecklistItemId() {
        return $this->checklistItem->getId();
    }
    public function getStateType() {
        return $this->stateType;
    }
}