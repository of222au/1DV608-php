<?php

namespace model;

class IncorrectChecklistTitleException extends \Exception {};
class IncorrectChecklistDescriptionException extends \Exception {};

class ChecklistBase {

    protected $title;
    protected $description;

    protected function setTitle($value) {
        if (!is_string($value) || strlen($value) < 3) { throw new IncorrectChecklistTitleException(); }
        $this->title = $value;
    }
    protected function setDescription($value) {
        if (!is_string($value)) { throw new IncorrectChecklistDescriptionException(); }
        $this->description = $value;
    }

    public function getTitle() {
        return $this->title;
    }
    public function getDescription() {
        return $this->description;
    }

}