<?php

namespace controller;

class LoginController {

    private $view;
    private $model;

    public function __construct(\model\LoginModel $model, \view\LoginView $view) {
        $this->model = $model;
        $this->view = $view;
    }

    public function doControl() {

        $userClient = $this->view->getUserClient();

        if ($this->model->isLoggedIn($this->view->getUserClient())) {
            if ($this->view->wantToLogOut()) {
                $this->model->doLogOut();
                $this->view->setUserLoggedOut();
            }
        }
        else if ($this->view->wantToLogIn()) {

            $loginCredentials = $this->view->getLoginCredentials();
            if ($this->model->doLogin($loginCredentials) == true) {
                $this->view->setLoginHasSucceeded();
            }
            else {
                $this->view->setLoginHasFailed();
            }
        }

        //create new temp credentials (if logged in)
        $this->model->renewTempCredentials($userClient);
    }

}

