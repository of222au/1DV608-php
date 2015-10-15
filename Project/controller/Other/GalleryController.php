<?php

namespace controller;

class GalleryController {

    private $view;
    private $model;

    private $galleryDAL;
    private $imageDAL;

    public function __construct(\model\Gallery $gallery, \model\GalleryDAL $galleryDAL, \model\ImageDAL $imageDAL, \model\EntryAccess $access, \view\GalleryView $view) {
        $this->model = $gallery;
        $this->view = $view;

        $this->galleryDAL = $galleryDAL;
        $this->imageDAL = $imageDAL;
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
