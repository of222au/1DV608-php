<?php

namespace model;

class GalleryDAL {

    private $database;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    /**
    public function getGalleries() {

        if ($this->galleries == null) {

            $this->galleries = [];

            //select all rows from galleries table
            $stmt = $this->database->prepare("SELECT * FROM " . self::$table_galleries);
            if ($stmt === FALSE) {
                throw new \Exception($this->database->error);
            }

            $stmt->execute();
            $stmt->bind_result($id, $userId, $title, $description, $createdAt, $updatedAt);

            while ($stmt->fetch()) {
                $gallery = new Gallery($id, $userId, $title, $description, $createdAt, $updatedAt);
                $this->galleries[] = $gallery;
            }
        }

        return $this->galleries;
    }
    */

    public function getGallery($galleryId) {
        assert(is_numeric($galleryId));

        //select from database
        $stmt = $this->database->prepare("SELECT * FROM " . \Settings::DATABASE_TABLE_GALLERIES .
                                          " WHERE id = " . $galleryId);
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $userId, $title, $description, $createdAt, $updatedAt);
            $stmt->fetch();

            return new Gallery($id, $userId, $title, $description, $createdAt, $updatedAt);
        }

        return null;
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

    public function saveNewGallery(GalleryCredentials $credentials) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_GALLERIES . "
                                            (user_id, title, description, created_at, updated_at)
                                            VALUES (?, ?, ?, NOW(), NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $userId = $credentials->getUser()->getId();
        $title = $credentials->getTitle();
        $description = $credentials->getDescription();
        $stmt->bind_param('iss', $userId, $title, $description);

        //execute the insert
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }

        return true;
    }

    


}