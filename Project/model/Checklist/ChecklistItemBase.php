<?php

namespace model;

class IncorrectChecklistItemTitleException extends \Exception {};
class IncorrectChecklistItemDescriptionException extends \Exception {};
class IncorrectChecklistItemImportantException extends \Exception {};

class ChecklistItemBase {

    protected $title;
    protected $description;
    protected $important;

    protected function setTitle($value) {
        if (!is_string($value) || strlen($value) < 1) { throw new IncorrectChecklistItemTitleException(); }
        $this->title = $value;
    }
    protected function setDescription($value) {
        if (!is_string($value)) { throw new IncorrectChecklistItemDescriptionException(); }
        $this->description = $value;
    }
    protected function setImportant($value) {
        if (!is_bool($value)) { throw new IncorrectChecklistItemImportantException(); }
        $this->important = $value;
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



}