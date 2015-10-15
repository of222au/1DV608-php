<?php

namespace controller;

class ChecklistController {

    private $view;
    private $model;

    private $checklistDAL;

    public function __construct(\model\Checklist $checklist, \model\ChecklistDAL $checklistDAL, \model\ImageDAL $imageDAL, \model\EntryAccess $access, \view\ChecklistView $view) {
        $this->model = $checklist;
        $this->view = $view;

        $this->checklistDAL = $checklistDAL;
    }

    public function doControl() {

        /*
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
        */
    }

}
