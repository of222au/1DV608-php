<?php

namespace controller;

require_once('LoginController.php');
require_once('RegisterController.php');
require_once('view/LoginView.php');
require_once('view/RegisterView.php');
require_once('view/NavigationView.php');
require_once("model/UserDAL.php");
require_once('model/LoginModel.php');
require_once('model/RegisterModel.php');

class MasterController {

    private $database;

    private $userDAL;
    private $view;

    private $isLoggedIn = false;

    public function __construct() {

        $this->database = new \mysqli(\Settings::DATABASE_SERVER, \Settings::DATABASE_USER, \Settings::DATABASE_PASSWORD, \Settings::DATABASE_NAME);
        if ($this->database->connect_error) {
            printf('Could not connect to the database (' . $this->database->connect_errno . ') ' . $this->database->connect_error);
            exit();
        }

        $this->userDAL = new \model\UserDAL($this->database);
    }

    public function handleInput() {

        $registerModel = new \model\RegisterModel($this->userDAL);
        $registerView = new \view\RegisterView($registerModel);

        $navigationView = new \view\NavigationView();
        if ($navigationView->onRegisterPage() ) {
            //register page

            $model = $registerModel;
            $view = $registerView;
            $register = new \controller\RegisterController($model, $view);

            //Handle input
            $register->doControl();

        } else {
            //login page

            $model = new \model\LoginModel($this->userDAL);
            $view = new \view\LoginView($model);
            $login = new \controller\LoginController($model, $view);

            //get registered username (if any)
            $registeredUsername = $registerView->getRegisteredUserName();
            if ($registeredUsername != '') {
                $view->setHasRegistered($registeredUsername);
            }

            //Handle input
            $login->doControl();

            //set login state
            $this->isLoggedIn = $model->isLoggedIn($view->getUserClient());
        }

        //set view
        $this->view = $view;

        //close the database connection
        $this->database->close();
    }

    /**
     * @return \view\RegisterView | \view\LoginView
     */
    public function generateOutput() {
        return $this->view;
    }

    /**
     * @return bool
     */
    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

}
