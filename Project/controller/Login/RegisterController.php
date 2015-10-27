<?php

namespace controller;

class RegisterController implements SubController {

    private $view;
    private $model;

    public function __construct(\model\UserDAL $userDAL) {
        $this->model =  new \model\RegisterModel($userDAL);
        $this->view = new \view\RegisterView($this->model);
    }

    public function doControl() {

        if ($this->view->wantToRegister()) {

            $credentials = $this->view->getRegisterCredentials();

            //try to register
            if ($this->model->doRegister($credentials)) {

                //success
                $this->view->setRegisterHasSucceeded();
            }
            else {

                //failed to register user
                $this->view->setRegisterHasFailed();
            }
        }
    }

    public function getView() {
        return $this->view;
    }
}

