<?php

namespace model;

require_once("model/Checklist/ChecklistItemBase.php");

class ChecklistItemAddCredentials extends ChecklistItemBase {

    private $checklist;

    public function __construct(Checklist $checklist, $title, $description, $important) {
        $this->checklist = $checklist;
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setImportant($important);
    }

    public function getChecklistId() {
        return $this->checklist->getId();
    }



}