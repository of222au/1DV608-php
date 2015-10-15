<?php

namespace model;

class ChecklistItem {

    private $id;
    private $checklistId;
    private $title;
    private $description;
    private $important;
    private $createdAt;
    private $updatedAt;

    private $currentState; //ChecklistItemState

    public function __construct($id, $checklistId, $title, $description, $important, $createdAt, $updatedAt) {
        $this->id = $id;
        $this->checklistId = $checklistId;
        $this->title = $title;
        $this->description = $description;
        $this->important = $important;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId() {
        return $this->id;
    }
    public function getChecklistId() {
        return $this->checklistId;
    }
    public function getTitle() {
        return $this->title;
    }
    public function getDescription() {
        return $this->description;
    }
    public function getImportant() {
        return ($this->important == true);
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setCurrentState(ChecklistItemState $state) {
        $this->currentState = $state;
    }
    public function getCurrentState() {
        return $this->currentState;
    }
    public function getCurrentStateType() {
        return ($this->currentState != null ? $this->currentState->getStateType() : \Settings::CHECKLIST_ITEM_STATE_DEFAULT_WHEN_NO_STATE);
    }

    public static function cmp($a, $b) {
        if ($a == $b) { return 0; }

        //compare state type (the \Settings::CHECKLIST_ITEM_STATE_...)
        $aStateType = $a->getCurrentStateType();
        $aStateSortPoints = ($aStateType == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED ? 1 : ($aStateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED ? 2 : ($aStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 5 : 3)));
        $bStateType = $b->getCurrentStateType();
        $bStateSortPoints = ($bStateType == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED ? 1 : ($bStateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED ? 2 : ($bStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 5 : 3)));

        return ($bStateSortPoints > $aStateSortPoints ||
            ($bStateSortPoints == $aStateSortPoints && $b->getUpdatedAt() < $a->getUpdatedAt())) ? 1 : -1;
    }
}