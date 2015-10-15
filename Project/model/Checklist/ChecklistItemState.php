<?php

namespace model;

class ChecklistItemState {

    private $id;
    private $checklistItemId;
    private $userId;
    //private $stateTypeId;
    private $stateType; //string, one of the \Settings::CHECKLIST_ITEM_STATE_...
    private $createdAt;

    public function __construct($id, $checklistItemId, $userId, $stateType, $createdAt) {
        assert(is_numeric($id));
        assert(is_numeric($checklistItemId));
        assert(is_numeric($userId));
        assert(is_string($stateType));

        $this->id = $id;
        $this->checklistItemId = $checklistItemId;
        $this->userId = $userId;
        $this->stateType = $stateType;
        $this->createdAt = $createdAt;
    }

    public function getId() {
        return $this->id;
    }
    public function getChecklistItemId() {
        return $this->checklistItemId;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getStateType() {
        return $this->stateType;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }

}