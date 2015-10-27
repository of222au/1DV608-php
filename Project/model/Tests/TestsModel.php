<?php

namespace model;

/**
 * Class TestsModel
 * used when running the automatic tests
 * @package model
 */
class TestsModel {

    private $database;
    private $userDAL;
    private $registerModel;
    private $loginModel;
    private $userGroupModel;
    private $checklistModel;
    private $entryAccessModel;

    private $testUC1_1_RegisteredUserName;
    private $testUC1_1_RegisteredPassword;
    private $testUC1_2_UserClient;
    private $testUC1_2_LoggedInUser;    // \model\User
    private $testUC2_1_UserGroupName;
    private $testUC2_1_CreatedUserGroup; // \model\UserGroup
    private $testUC2_2_UserGroupAccess;  // \model\UserGroupAccess
    private $testUC2_3_SecondUser;      // \model\User
    private $testUC3_1_CreatedChecklist; // \model\Checklist
    private $testUC3_2_ChecklistAccess;  //  \model\EntryAccess
    private $testUC3_3_CreatedChecklistItem; // \model\ChecklistItem

    private $testResults = null;
    private $totalTimeElapsed = null;

    public function __construct(\mysqli $database) {
        $this->database = $database;
    }

    private function initialize() {
        $this->userDAL = new UserDAL($this->database);
        $this->registerModel = new RegisterModel($this->userDAL);
        $this->loginModel = new LoginModel($this->userDAL);
        $this->userGroupModel = new \model\UserGroupModel($this->database, $this->userDAL);
        $this->checklistModel = new \model\ChecklistModel($this->database, $this->userDAL);
        $this->entryAccessModel = new \model\EntryAccessModel($this->database, $this->userGroupModel);
    }

    public function runTests() {
        $this->testResults = array();
        $this->totalTimeElapsed = null;

        $totalStartTime = microtime(true);

        $testFunctions = array('initialize',
                                'testUC1_1',
                                'testUC1_2',
                                'testUC2_1',
                                'testUC2_2',
                                'testUC2_3',
                                'testUC3_1',
                                'testUC3_2',
                                'testUC3_3',
                                'testUC3_4',
                                'testUC4_1');

        foreach ($testFunctions as $testFunction) {

            $result = false;
            $error = '';

            $startTime = microtime(true);
            try {
                //run test case
                $this->$testFunction();
                $result = true; //success

            } catch (\Exception $e) {
                $error = $e->getMessage(); //failure
            }
            $timeConsumed = microtime(true) - $startTime;

            //add test result
            $testResult = new TestResult($testFunction, $result, $timeConsumed, $error);
            $this->testResults[] = $testResult;

            if (!$result && $testFunction == 'initialize') {
                break;
            }
        }

        $this->totalTimeElapsed = microtime(true) - $totalStartTime;
    }

    public function getTestResults() {
        return $this->testResults;
    }
    public function getTotalTimeElapsed() {
        return $this->totalTimeElapsed;
    }

    public function hasTestResults() {
        return ($this->testResults != null && count($this->testResults));
    }

    private function generateRandomString($stringLength = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $stringLength; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * UC1.1 Register user
     * @throws \Exception
     */
    private function testUC1_1() {
        $this->testUC1_1_RegisteredUserName = "user" . $this->generateRandomString();
        $this->testUC1_1_RegisteredPassword = $this->generateRandomString();
        $this->testUC1_1_RegisterUser($this->testUC1_1_RegisteredUserName, $this->testUC1_1_RegisteredPassword);
    }
    /**
     * @param $userName
     * @param $password
     * @return User|null
     * @throws \Exception
     */
    private function testUC1_1_RegisterUser($userName, $password) {
        $registerCredentials = new RegisterCredentials($userName, $password, $password);

        $result = $this->registerModel->doRegister($registerCredentials);
        if ($result) {
            $user = $this->userDAL->getUser($userName);
            if ($user == null) {
                throw new \Exception("User could not be loaded (" . $userName . ")");
            }
            else if ($user->getUsername() != $userName) {
                throw new \Exception("Loaded user does not match the added credentials");
            }
            else if ($user->getId() <= 0) {
                throw new \Exception("Loaded user does not have any id");
            }

            return $user;
        }

        return null;
    }

    /**
     * UC1.2 Authenticate user
     * @throws \Exception
     */
    private function testUC1_2() {
        $loginView = new \view\LoginView($this->loginModel);
        $this->testUC1_2_UserClient = $loginView->getUserClient();
        $loginCredentials = new LoginCredentials($this->testUC1_1_RegisteredUserName, $this->testUC1_1_RegisteredPassword, '', $this->testUC1_2_UserClient);

        //do the login
        $this->loginModel->doLogin($loginCredentials);

        //get the user
        $this->testUC1_2_LoggedInUser = $this->userDAL->getUser($this->loginModel->loggedInUserName($loginView->getUserClient()));

        $this->testUC1_2_CheckLoggedIn();
        if ($this->testUC1_2_LoggedInUser == null) {
            throw new \Exception("Logged in user is empty");
        }
    }
    private function testUC1_2_CheckLoggedIn() {
        if ($this->testUC1_2_UserClient == null || !$this->loginModel->isLoggedIn($this->testUC1_2_UserClient)) {
            throw new \Exception("Not logged in!");
        }
    }

    /**
     * UC2.1 Create user group
     * @throws \Exception
     */
    private function testUC2_1() {
        $this->testUC1_2_CheckLoggedIn();

        $this->testUC2_1_UserGroupName = 'UserGroup ' . $this->generateRandomString();
        $credentials = new \model\UserGroupAddCredentials($this->testUC2_1_UserGroupName);

        //save and get the user group
        $this->testUC2_1_CreatedUserGroup = $this->userGroupModel->saveNewUserGroup($credentials, $this->testUC1_2_LoggedInUser);

        $this->testUC2_1_CheckUserGroup();
    }
    private function testUC2_1_CheckUserGroup() {
        if ($this->testUC2_1_UserGroupName == null || $this->testUC2_1_UserGroupName == "" ||
            $this->testUC2_1_CreatedUserGroup == null || $this->testUC2_1_CreatedUserGroup->getId() <= 0) {

            throw new \Exception("User group was not loaded correctly");
        }
    }

    /**
     * UC2.2 View user group
     * @throws \Exception
     */
    private function testUC2_2() {
        $this->testUC1_2_CheckLoggedIn();
        $this->testUC2_1_CheckUserGroup();

        $this->testUC2_2_UserGroupAccess = new UserGroupUserAccess($this->testUC2_1_CreatedUserGroup, $this->testUC1_2_LoggedInUser);

        //make sure view can be created
        $userGroupView = new \view\UserGroupView($this->testUC2_1_CreatedUserGroup, $this->testUC2_2_UserGroupAccess);

        $this->testUC2_2_CheckUserGroupAccess();
    }
    private function testUC2_2_CheckUserGroupAccess() {
        if ($this->testUC2_2_UserGroupAccess == null || !$this->testUC2_2_UserGroupAccess->IsCreatorOrMember()) {
            throw new \Exception("User has no access to the user group");
        }
    }

    /**
     * UC2.3 Add member to user group
     * @throws \Exception
     */
    private function testUC2_3() {
        $this->testUC1_2_CheckLoggedIn();
        $this->testUC2_2_CheckUserGroupAccess();

        //register a second user
        $secondUserUsername = "user" . $this->generateRandomString();
        $secondUserPassword = $this->generateRandomString();
        $this->testUC2_3_SecondUser = $this->testUC1_1_RegisterUser($secondUserUsername, $secondUserPassword);

        if ($this->testUC2_3_SecondUser == null ||
            $this->testUC2_3_SecondUser->getId() <= 0) {

            throw new \Exception("Second user could not be created");
        }

        //save the second user as member to the user group
        $this->userGroupModel->saveNewUserGroupMember($this->testUC2_1_CreatedUserGroup, $this->testUC2_3_SecondUser);

        //get members of the group, to double check member was added
        $this->testUC2_1_CreatedUserGroup = $this->userGroupModel->getUserGroup($this->testUC2_1_CreatedUserGroup->getId());
        if (!$this->testUC2_1_CreatedUserGroup->isUserMember($this->testUC2_3_SecondUser)) {
            throw new \Exception("Second user was not a member of the user group after adding member and loading the group again");
        }
    }

    /**
     * UC3.1 Create a new checklist
     * @throws \Exception
     */
    private function testUC3_1() {
        $this->testUC1_2_CheckLoggedIn();

        $checklistName = 'Checklist . ' . $this->generateRandomString();
        $credentials = new \model\ChecklistAddCredentials($checklistName, '');

        //save and load the created checklist
        $this->testUC3_1_CreatedChecklist = $this->checklistModel->saveNewChecklist($credentials, $this->testUC1_2_LoggedInUser);

        $this->testUC3_1_CheckChecklist();
    }
    private function testUC3_1_CheckChecklist() {
        if ($this->testUC3_1_CreatedChecklist == null || $this->testUC3_1_CreatedChecklist->getId() <= 0) {
            throw new \Exception("Checklist is not be loaded");
        }
    }

    /**
     * UC3.2 View checklist
     * @throws \Exception
     */
    private function testUC3_2() {
        $this->testUC1_2_CheckLoggedIn();
        $this->testUC3_1_CheckChecklist();

        $this->testUC3_2_ChecklistAccess = $this->entryAccessModel->getEntryAccess($this->testUC3_1_CreatedChecklist, $this->testUC1_2_LoggedInUser);
        $this->testUC3_2_CheckChecklistAccess();

        //make sure view can be created
        $entryAccessView = new \view\EntryAccessView($this->testUC3_2_ChecklistAccess);
        $checklistView = new \view\ChecklistView($entryAccessView, $this->testUC3_1_CreatedChecklist, $this->testUC3_2_ChecklistAccess);
    }
    private function testUC3_2_CheckChecklistAccess() {
        if ($this->testUC3_2_ChecklistAccess == null) {
            throw new \Exception("Entry access is empty!");
        }
        else if (!$this->testUC3_2_ChecklistAccess->isCreator() || !$this->testUC3_2_ChecklistAccess->canWrite()) {
            throw new \Exception("Entry access is wrong for logged in user and the created checklist");
        }
    }

    /**
     * UC3.3 Add a checklist item to a checklist
     * @throws \Exception
     */
    private function testUC3_3() {
        $this->testUC1_2_CheckLoggedIn();
        $this->testUC3_2_CheckChecklistAccess();

        $title = 'Checklist item . ' . $this->generateRandomString();
        $description = '';
        $important = false;
        $credentials = new \model\ChecklistItemAddCredentials($this->testUC3_1_CreatedChecklist, $title, $description, $important);

        //save the checklist item
        $checklistItemId = $this->checklistModel->saveNewChecklistItem($credentials, $this->testUC1_2_LoggedInUser);

        //load the checklist again and check the checklist item state
        $this->testUC3_3_LoadChecklistAgainAndCheckChecklistItem($checklistItemId, \Settings::CHECKLIST_ITEM_STATE_UNCHECKED);
    }
    private function testUC3_3_LoadChecklistAgainAndCheckChecklistItem($checklistItemId, $wantedChecklistItemState = null) {

        //load the checklist again
        $this->testUC3_1_CreatedChecklist = $this->checklistModel->getChecklist($this->testUC3_1_CreatedChecklist->getId());

        //check that created checklist item is in the checklist
        $this->testUC3_3_CreatedChecklistItem = $this->testUC3_1_CreatedChecklist->findChecklistItem($checklistItemId);

        if ($this->testUC3_3_CreatedChecklistItem == null || $this->testUC3_3_CreatedChecklistItem->getId() <= 0) {
            throw new \Exception("Checklist item is not be loaded");
        }
        else if ($wantedChecklistItemState != null && $this->testUC3_3_CreatedChecklistItem->getCurrentStateType() != $wantedChecklistItemState) {
            throw new \Exception("Checklist item has wrong state (" . $this->testUC3_3_CreatedChecklistItem->getCurrentStateType() . ")");
        }
    }

    /**
     * Set a new state to a checklist item
     * @throws \Exception
     */
    private function testUC3_4() {
        $this->testUC1_2_CheckLoggedIn();
        $this->testUC3_2_CheckChecklistAccess();
        $this->testUC3_3_LoadChecklistAgainAndCheckChecklistItem($this->testUC3_3_CreatedChecklistItem->getId());

        $changeStateTo = \Settings::CHECKLIST_ITEM_STATE_CHECKED;
        $credentialsArray = array(new \model\ChecklistItemStateCredentials($this->testUC3_3_CreatedChecklistItem, $changeStateTo));

        //save new checklist item state
        $this->checklistModel->saveNewChecklistItemStates($credentialsArray, $this->testUC1_2_LoggedInUser);

        //load the checklist again and check checklist item status is correct
        $this->testUC3_3_LoadChecklistAgainAndCheckChecklistItem($this->testUC3_3_CreatedChecklistItem->getId(), $changeStateTo);
    }

    /**
     * UC4.1 Share an entry (like a checklist) with a user group
     * @throws \Exception
     */
    private function testUC4_1() {
        $this->testUC1_2_CheckLoggedIn();
        $this->testUC2_1_CheckUserGroup();
        $this->testUC3_2_CheckChecklistAccess();

        $accessType = \Settings::ACCESS_TYPE_WRITE;
        $credentials = new \model\EntryUserGroupAccessAddCredentials($this->testUC2_1_CreatedUserGroup, $this->testUC3_1_CreatedChecklist, $accessType);

        //save new state to the checklist item
        $this->entryAccessModel->saveNewUserGroupAccessToEntry($credentials, $this->testUC1_2_LoggedInUser);

        //update the checklist access to info for the second user
        $this->testUC3_2_ChecklistAccess = $this->entryAccessModel->getEntryAccess($this->testUC3_1_CreatedChecklist, $this->testUC2_3_SecondUser);
        $this->testUC4_1_CheckChecklistAccess();
    }
    private function testUC4_1_CheckChecklistAccess() {
        if ($this->testUC3_2_ChecklistAccess->getUserId() != $this->testUC2_3_SecondUser->getId() ||
            !$this->testUC3_2_ChecklistAccess->canWrite()) {

            throw new \Exception("Entry access is wrong");
        }
    }
}