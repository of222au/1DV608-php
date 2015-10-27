<?php

namespace model;

require_once("model/Checklist/ChecklistBase.php");

class ChecklistAddCredentials extends ChecklistBase {

    public function __construct($title, $description) {
        $this->setTitle($title);
        $this->setDescription($description);
    }
}

