<?php

namespace model;

require_once("model/Checklist/ChecklistItemBase.php");

class ChecklistItem extends ChecklistItemBase {

    private $id;
    private $checklistId;
    private $userId;
    //..more fields are inherited from ChecklistItemBase
    private $createdAt;
    private $updatedAt;
    private $userName; //read from the user

    private $currentState; // \model\ChecklistItemState | null

    public function __construct($id, $checklistId, $userId, $title, $description, $important, $createdAt, $updatedAt, $userName) {
        $this->id = $id;
        $this->checklistId = $checklistId;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->important = $important;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->userName = $userName;
    }

    public function getId() {
        return $this->id;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getUserName() {
        return $this->userName;
    }
    public function getChecklistId() {
        return $this->checklistId;
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

        //compare state types
        $cmpStatesValue = self::cmpStatesArchiveOrOther($a, $b);

        return ($cmpStatesValue != 0 ? $cmpStatesValue : ($b->getUpdatedAt() < $a->getUpdatedAt() ? 1 : -1));
    }

    /**
     * Sorts archived first, then by date
     * @param $a
     * @param $b
     * @return int
     */
    private static function cmpStatesArchiveOrOther($a, $b) {
        $aStateType = $a->getCurrentStateType();
        $aStateSortPoints = ($aStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 1 : 0);
        $bStateType = $b->getCurrentStateType();
        $bStateSortPoints = ($bStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 1 : 0);

        return ($bStateSortPoints > $aStateSortPoints ? 1 : ($bStateSortPoints == $aStateSortPoints ? 0 : -1));
    }

    /**
     * An alternative compare function sorting by different states before date
     * @param $a
     * @param $b
     * @return int
     */
    private static function cmpByAllStatesAndDateIfSameState($a, $b) {
        $aStateType = $a->getCurrentStateType();
        $aStateSortPoints = ($aStateType == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED ? 1 : ($aStateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED ? 2 : ($aStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 5 : 3)));
        $bStateType = $b->getCurrentStateType();
        $bStateSortPoints = ($bStateType == \Settings::CHECKLIST_ITEM_STATE_UNCHECKED ? 1 : ($bStateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED ? 2 : ($bStateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED ? 5 : 3)));

        return ($bStateSortPoints > $aStateSortPoints ? 1 : ($bStateSortPoints == $aStateSortPoints ? 0 : -1));
    }
}