<?php

namespace model;

class GalleryCredentials {

    private $user;
    private $title;
    private $description;

    public function __construct(User $user, $title, $description) {
        $this->user = $user;
        $this->title = $title;
        $this->description = $description;
    }

    public function getUser() {
        return $this->user;
    }
    public function getTitle() {
        return $this->title;
    }
    public function getDescription() {
        return $this->description;
    }

}
