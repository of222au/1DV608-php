<?php

namespace model;

require_once("model/Checklist/ChecklistBase.php");

class ChecklistEditCredentials extends ChecklistBase {

    private $checklist;

    public function __construct(Checklist $checklist, $newTitle, $newDescription) {
        $this->checklist = $checklist;
        $this->setTitle($newTitle);
        $this->setDescription($newDescription);
    }

    public function getChecklistId() {
        return $this->checklist->getId();
    }



}