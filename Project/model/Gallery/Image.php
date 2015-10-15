<?php

namespace model;

class Image {

    private $id;
    private $uniqueName;
    private $galleryId;
    private $userId;
    private $createdAt;

    public function __construct($id, $uniqueName, $galleryId, $userId, $createdAt) {
        $this->id = $id;
        $this->uniqueName = $uniqueName;
        $this->galleryId = $galleryId;
        $this->userId = $userId;
        $this->createdAt = $createdAt;
    }

    public function getId() {
        return $this->id;
    }
    public function getUniqueName() {
        return $this->uniqueName;
    }
    public function getGalleryId() {
        return $this->galleryId;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }

}