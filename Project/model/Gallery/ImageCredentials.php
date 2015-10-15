<?php

namespace model;

class ImageCredentials {

    private $gallery;
    private $user;
    private $uniqueName;

    public function __construct(Gallery $gallery, User $user, $uniqueName) {
        $this->gallery = $gallery;
        $this->user = $user;
        $this->uniqueName = $uniqueName;
    }

    public function getGallery() {
        return $this->gallery;
    }
    public function getUser() {
        return $this->user;
    }
    public function getUniqueName() {
        return $this->uniqueName;
    }

}