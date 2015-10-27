<?php

namespace model;

require_once("model/Checklist/ChecklistItemBase.php");

class ChecklistItemEditCredentials extends ChecklistItemBase {

    private $checklistItem;

    public function __construct(ChecklistItem $checklistItem, $title, $description, $important) {
        $this->checklistItem = $checklistItem;
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setImportant($important);
    }

    public function getChecklistItemId() {
        return $this->checklistItem->getId();
    }



}