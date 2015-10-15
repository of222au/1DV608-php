<?php

namespace controller;

require_once('Login/LoginController.php');
require_once('Login/RegisterController.php');
require_once('view/Login/LoginView.php');
require_once('view/Login/RegisterView.php');
require_once('view/General/NavigationView.php');
require_once('view/General/EntryAccessView.php');
require_once('view/HomeView.php');
require_once('view/UnknownView.php');
require_once('view/GalleryView.php');
require_once('view/ChecklistView.php');
require_once("model/User/UserGroup.php");
require_once("model/DAL/EntryAccessDAL.php");
require_once("model/DAL/UserDAL.php");
require_once("model/DAL/GalleryDAL.php");
require_once("model/DAL/ChecklistDAL.php");
require_once("model/EntryAccess/Entry.php");
require_once("model/Gallery/Gallery.php");
require_once("model/Checklist/Checklist.php");
require_once('model/Login/LoginModel.php');
require_once('model/Login/RegisterModel.php');
require_once('model/EntryAccess/EntryAccessModel.php');

class MasterController {

    private $database;

    private $entryAccessModel;
    private $userDAL;
    private $view;

    private $loggedInUser = null;
    private $isLoggedIn = false;

    public function __construct() {

        $this->database = new \mysqli(\Settings::DATABASE_SERVER, \Settings::DATABASE_USER, \Settings::DATABASE_PASSWORD, \Settings::DATABASE_NAME);
        if ($this->database->connect_error) {
            printf('Could not connect to the database (' . $this->database->connect_errno . ') ' . $this->database->connect_error);
            exit();
        }

        $entryAccessDAL = new \model\EntryAccessDAL($this->database);
        $this->entryAccessModel = new \model\EntryAccessModel($entryAccessDAL);
        $this->userDAL = new \model\UserDAL($this->database);
    }

    public function handleInput() {

        $registerModel = new \model\RegisterModel($this->userDAL);
        $registerView = new \view\RegisterView($registerModel);

        $loginModel = new \model\LoginModel($this->userDAL);
        $loginView = new \view\LoginView($loginModel);

        $view = null;

        $navigationView = new \view\NavigationView();
        if ($navigationView->onRegisterPage()) {
            //register page

            $model = $registerModel;
            $view = $registerView;
            $register = new \controller\RegisterController($model, $view);

            //Handle input
            $register->doControl();

        } else if ($navigationView->onLoginPage()) {
            //login page

            $login = new \controller\LoginController($loginModel, $loginView);
            $view = $loginView;

            //Handle input
            $login->doControl();
        }

        //set login state
        $this->isLoggedIn = $loginModel->isLoggedIn($loginView->getUserClient());

        if ($this->isLoggedIn) {

            //store the logged in user
            $this->loggedInUser = $this->userDAL->getUser($loginModel->loggedInUserName($loginView->getUserClient()));

            if ($navigationView->onHomePage()) {

                $view = new \view\HomeView();
            }
            else if ($navigationView->onGalleryPage()) {

                $galleryId = $navigationView->getGalleryId();
                $galleryDAL = new \model\GalleryDAL($this->database);
                $selectedGallery = $galleryDAL->getGallery($galleryId);

                $access = null;
                if ($selectedGallery != null) {
                    $access = $this->entryAccessModel->getEntryAccess($selectedGallery, $this->loggedInUser);
                }

                $view = new \view\GalleryView($selectedGallery, $access);
            }
            else if ($navigationView->onChecklistPage()) {

                $checklistId = $navigationView->getChecklistId();
                $checklistDAL = new \model\ChecklistDAL($this->database);
                $selectedChecklist = $checklistDAL->getChecklist($checklistId);

                $access = null;
                if ($selectedChecklist != null) {
                    $access = $this->entryAccessModel->getEntryAccess($selectedChecklist, $this->loggedInUser);
                }

                $view = new \view\ChecklistView($selectedChecklist, $access);
            }
            else {

                //something wrong, TODO

            }

        }
        else if (!$navigationView->onRegisterPage() &&
                 !$navigationView->onLoginPage()) {

            //need to login, redirect
            $navigationView->redirectToLoginPage();

            $view = new \view\UnknownView();
        }

        //set view
        $this->view = $view;

        //close the database connection
        $this->database->close();
    }

    public function generateOutput() {
        return $this->view;
    }

    /**
     * @return bool
     */
    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

    public function getLoggedInUser() {
        return $this->loggedInUser;
    }
}
