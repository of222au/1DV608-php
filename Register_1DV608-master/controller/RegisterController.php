<?php

namespace controller;

class RegisterController {

    private $view;
    private $model;

    public function __construct(\model\RegisterModel $model, \view\RegisterView $view) {
        $this->model = $model;
        $this->view = $view;
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
}

