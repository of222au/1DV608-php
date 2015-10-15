<?php

namespace model;

class Gallery implements Entry {

    private $id;
    private $userId;
    private $title;
    private $description;
    private $createdAt;
    private $updatedAt;

    public function __construct($id, $userId, $title, $description, $createdAt, $updatedAt) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getEntryType() {
        return \Settings::ENTRY_TYPE_GALLERY;
    }

    public function getId() {
        return $this->id;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getTitle() {
        return $this->title;
    }
    public function getDescription() {
        return $this->description;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

}