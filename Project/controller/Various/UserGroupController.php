<?php

namespace controller;

require_once("model/User/UserGroupUserAccess.php");

class UserGroupController implements SubController {

    private $loggedInUser;  // \model\User | null
    private $userDAL;
    private $userGroupModel;

    private $userGroup;     // \model\UserGroup | null
    private $access;        // \model\UserGroupUserAccess | null
    private $view;

    public function __construct(\mysqli $database, \model\UserDAL $userDAL, $userGroupId, \model\User $loggedInUser) {

        $this->loggedInUser = $loggedInUser;
        $this->userDAL = $userDAL;
        $this->userGroupModel = new \model\UserGroupModel($database, $this->userDAL);

        if ($userGroupId != null) {
            $this->userGroup = $this->userGroupModel->getUserGroup($userGroupId);
            if ($this->userGroup != null) {
                $this->updateAccess();
            }
        }

        $this->view = new \view\UserGroupView($this->userGroup, $this->access);
    }
    private function updateAccess() {
        $this->access = null;
        if ($this->userGroup != null) {
            $this->access = new \model\UserGroupUserAccess($this->userGroup, $this->loggedInUser);
        }
    }

    public function doControl() {

        if ($this->view->wantToSearchMember()) {
            $this->performSearchForUser();
        }
        else if ($this->view->wantToAddMember()) {
            $this->performAddMember();
        }
        else if ($this->view->wantToSkipFoundMember()) {
            $this->view->removeFoundMember();
        }
        else if ($this->view->wantToRemoveMember()) {
            $this->performRemoveMember();
        }
        else if ($this->view->wantToEditUserGroup() ||
                 $this->view->wantToAddUserGroup()) {

            $editNotAdd = ($this->view->wantToEditUserGroup());
            $this->performEditAddUserGroup($editNotAdd);
        }
    }

    private function performEditAddUserGroup($editNotAdd) {
        $credentials = $this->view->getEditAddCredentials();
        if ($credentials != null) {

            //if user has access
            if (!$editNotAdd || $this->access->isCreatorOrMember()) {

                try {
                    if ($editNotAdd) {
                        //edit the checklist
                        $this->userGroupModel->saveEditedUserGroupDetails($credentials);
                        //success
                        $this->view->setEditUserGroupDetailsSucceeded();
                    }
                    else {
                        //save the new checklist
                        $this->userGroup = $this->userGroupModel->saveNewUserGroup($credentials, $this->loggedInUser);
                        //success
                        $this->updateAccess();
                        $this->view->setAddUserGroupDetailsSucceeded($this->userGroup, $this->access);
                    }
                    return;
                }
                catch (\Exception $e) { }
            }

            $this->view->setEditAddDetailsFailed();
        }
    }

    private function performSearchForUser() {
        $searchForUserName = $this->view->getSearchMember();
        if ($searchForUserName != null && $searchForUserName != "") {

            try {
                $user = $this->userDAL->getUser($searchForUserName);

                if ($user != null) {

                    //check not already a member
                    if ($this->userGroup->isUserMember($user)) {
                        $this->view->setSearchMemberFailureAlreadyMember();
                    }
                    else if ($this->userGroup->isUserCreator($user)) {
                        $this->view->setSearchMemberFailureIsTheCreator();
                    }
                    else {
                        //success
                        $this->view->setSearchMemberSuccess($user);
                    }
                }
                else {
                    $this->view->setSearchMemberFailureUserNotFound();
                }

                return;
            }
            catch (\Exception $e) { }

            $this->view->setSearchMemberFailureGeneral();
        }
    }
    private function performAddMember() {
        $memberToAdd = $this->view->getAddMember();
        if ($memberToAdd != null) {

            try {
                $this->userGroupModel->saveNewUserGroupMember($this->userGroup, $memberToAdd);

                //success
                $this->view->setAddMemberSuccess();
                return;
            }
            catch (\Exception $e) { }

            $this->view->setAddMemberFailure();
        }
    }
    private function performRemoveMember() {
        $memberToRemove = $this->view->getRemoveMemberUser();
        if ($memberToRemove != null) {

            try {
                $this->userGroupModel->removeUserGroupMember($this->userGroup, $memberToRemove);

                //success
                $this->view->setRemoveMemberSucceeded();
                return;
            }
            catch (\Exception $e) { }

            $this->view->setRemoveMemberFailed();
        }
    }

    public function getView() {
        return $this->view;
    }
}