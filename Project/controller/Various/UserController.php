<?php

namespace controller;

class UserController implements SubController {

    private $user;     // \model\User | null
    private $view;

    public function __construct(\mysqli $database, \model\UserDAL $userDAL, $userId) {

        if ($userId != null) {
            $this->user = $userDAL->getUserById($userId);
        }

        $this->view = new \view\UserView($this->user);
    }

    public function doControl() {
        //nothing
    }

    public function getView() {
        return $this->view;
    }
}