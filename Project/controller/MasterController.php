<?php

namespace controller;

require_once("model/DAL/GeneralDAL.php");
require_once('view/PageView.php');
require_once('view/GeneralView.php');
require_once('SubController.php');
require_once('EntryController.php');
require_once('Various/TestsController.php');
require_once('Various/EntryCollectionController.php');
require_once('Various/ChecklistController.php');
require_once('Various/UserController.php');
require_once('Various/UserGroupController.php');
require_once('Login/LoginController.php');
require_once('Login/RegisterController.php');
require_once('view/BreadcrumbItem.php');
require_once('view/Login/LoginView.php');
require_once('view/Login/RegisterView.php');
require_once('view/General/NavigationView.php');
require_once('view/General/EntryAccessView.php');
require_once('view/EntryCollectionView.php');
require_once('view/UnknownView.php');
require_once('view/UserGroupView.php');
require_once('view/UserView.php');
require_once("model/User/UserInterface.php");
require_once("model/User/UserGroupBase.php");
require_once("model/User/UserGroup.php");
require_once("model/User/UserGroupModel.php");
require_once("model/User/UserGroupUserAccess.php");
require_once("model/DAL/EntryDAL.php");
require_once("model/DAL/EntryAccessDAL.php");
require_once("model/DAL/UserGroupDAL.php");
require_once("model/DAL/UserDAL.php");
require_once("model/EntryAccess/Entry.php");
require_once("model/EntryAccess/EntryRawInfo.php");
require_once('model/Login/LoginModel.php');
require_once('model/Login/RegisterModel.php');
require_once('model/EntryAccess/EntryAccessModel.php');

/**
 * The main controller which controls which sub-controller is to be used
 * Class MasterController
 * @package controller
 */
class MasterController {

    private $database;

    private $loggedInUser = null;
    private $isLoggedIn = false;

    private $subController;
    private $userDAL;
    private $loginModel;
    private $loginView;
    private $navigationView;

    public function __construct() {

        $this->database = new \mysqli(\Settings::DATABASE_SERVER, \Settings::DATABASE_USER, \Settings::DATABASE_PASSWORD, \Settings::DATABASE_NAME);
        if ($this->database->connect_error) {
            printf('Could not connect to the database (' . $this->database->connect_errno . ') ' . $this->database->connect_error);
            exit();
        }

        $this->userDAL = new \model\UserDAL($this->database);
        $this->loginModel = new \model\LoginModel($this->userDAL);
        $this->loginView = new \view\LoginView($this->loginModel);
    }

    /**
     * Gets the correct sub-controller and performs it's actions
     */
    public function handleInput() {

        $this->navigationView = new \view\NavigationView();

        if ($this->navigationView->onRegisterPage()) {
            $this->subController = new \controller\RegisterController($this->userDAL);
        }
        else if ($this->navigationView->onLoginPage() ||
                 $this->navigationView->onLogoutPage()) {
            $this->subController = new \controller\LoginController($this->loginModel, $this->loginView);
        }
        else if ($this->navigationView->onTestsPage() && \Settings::DEBUG_MODE) {
            $this->subController = new \controller\TestsController($this->database, $this->isLoggedIn);
        }
        else {
            //set logged in state
            $this->isLoggedIn = $this->loginModel->isLoggedIn($this->loginView->getUserClient());
            if ($this->isLoggedIn) {

                $this->loggedInUser = $this->userDAL->getUser($this->loginModel->loggedInUserName($this->loginView->getUserClient()));
                if ($this->loggedInUser != null) {

                    $this->handleLoggedInControllers();

                }
            }
        }

        //let the control do it's thing
        if ($this->subController != null) {
            $this->subController->doControl();
        }

        //close the database connection
        $this->database->close();
    }
    private function handleLoggedInControllers() {

        //if on some kind of entry collection page
        if ($this->navigationView->onHomePage() ||
            $this->navigationView->onUserGroupsPage() ||
            $this->navigationView->onChecklistsPage()) {

            $showUserGroups = false;
            $entryTypes = null;
            $glyphIcon = '';
            $title = '';
            if ($this->navigationView->onHomePage()) {
                $showUserGroups = true;
                $entryTypes = array(\Settings::ENTRY_TYPE_CHECKLIST); //more here when other entry types are added
                $glyphIcon = 'glyphicon-home';
                $title = 'Home';
            }
            else if ($this->navigationView->onUserGroupsPage()) {
                $showUserGroups = true;
                $glyphIcon = 'glyphicon-link';
                $title = 'User groups';
            }
            else if ($this->navigationView->onChecklistsPage()) {
                $entryTypes = array(\Settings::ENTRY_TYPE_CHECKLIST);
                $glyphIcon = 'glyphicon-edit';
                $title = 'Checklists';
            }

            $this->subController = new \controller\EntryCollectionController($this->database, $this->userDAL, $this->loggedInUser, $glyphIcon, $title, $showUserGroups, $entryTypes);
        }
        else if ($this->navigationView->onChecklistPage()) {
            $checklistId = $this->navigationView->getChecklistId();
            $this->subController = new \controller\ChecklistController($this->database, $this->userDAL, $checklistId, $this->loggedInUser);
        }
        else if ($this->navigationView->onUserGroupPage()) {
            $userGroupId = $this->navigationView->getUserGroupId();
            $this->subController = new \controller\UserGroupController($this->database, $this->userDAL, $userGroupId, $this->loggedInUser);
        }
        else if ($this->navigationView->onUserPage()) {
            $userId = $this->navigationView->getUserId();
            $this->subController = new \controller\UserController($this->database, $this->userDAL, $userId, $this->loggedInUser);
        }
    }

    /**
     * Generates output
     */
    public function generateOutput() {
        $pageView = null;
        if ($this->subController != null) {
            $pageView = $this->subController->getView();
        }
        else if (!$this->isLoggedIn) {
            $this->navigationView->redirectToLoginPage();
        }
        else {
            $pageView = new \view\UnknownView();
        }

        $navigationBarView = new \view\NavigationBarView($this->loginView, $this->isLoggedIn, $this->loggedInUser);
        $layoutView = new \view\LayoutView($navigationBarView);
        $breadcrumbOutput = $this->generateBreadcrumbResponse($pageView->responseBreadcrumbSubItems());

        $layoutView->render($pageView->response(), $breadcrumbOutput);
    }

    private function generateBreadcrumbResponse($breadcrumbs) {
        $html = '';
        if ($breadcrumbs != null && count($breadcrumbs)) {
            foreach ($breadcrumbs as $breadcrumb) {
                $html .= $breadcrumb->response();
            }
        }
        return $html;
    }
}
