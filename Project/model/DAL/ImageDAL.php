<?php

namespace model;

class ImageDAL {

    private $database;
    private $images = null;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    public function getImages() {

        if ($this->images == null) {

            $this->images = [];

            //select all rows from images table
            $stmt = $this->database->prepare("SELECT * FROM " . \Settings::DATABASE_TABLE_IMAGES);
            if ($stmt === FALSE) {
                throw new \Exception($this->database->error);
            }

            $stmt->execute();
            $stmt->bind_result($id, $uniqueName, $galleryId, $userId, $createdAt);

            while ($stmt->fetch()) {
                $image = new Image($id, $uniqueName, $galleryId, $userId, $createdAt);
                $this->images[] = $image;
            }
        }

        return $this->images;
    }

    /**
    public function getGallery($userName) {
    $users = $this->getUsers();
    foreach($users as $user) {
    if ($user->getUserName() == $userName) {
    return $user;
    }
    }

    return null;
    }
     * */

    public function saveNewImage(ImageCredentials $credentials) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_IMAGES . "
                                            (unique_name, gallery_id, user_id, created_at)
                                            VALUES (?, ?, ?, NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $uniqueName = $credentials->getUniqueName();
        $galleryId = $credentials->getGallery()->getId();
        $userId = $credentials->getUser()->getId();
        $stmt->bind_param('sii', $uniqueName, $galleryId, $userId);

        //execute the insert
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }

        return true;
    }





}