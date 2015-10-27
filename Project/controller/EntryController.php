<?php

namespace controller;

/**
 * An abstract sub-class to the various kinds of entries (like checklist), with functionality around entry access
 * Class EntryController
 * @package controller
 */
abstract class EntryController {

    private $entryAccessModel;  //   \model\EntryAccessModel

    protected $loggedInUser;    //   \model\User
    protected $entryAccess;     //   \model\EntryAccess | null
    protected $entryAccessView; //   \view\EntryAccessView

    public function __construct(\mysqli $database, \model\UserDAL $userDAL, \model\User $loggedInUser) {

        $this->loggedInUser = $loggedInUser;

        $userGroupModel = new \model\UserGroupModel($database, $userDAL);
        $this->entryAccessModel = new \model\EntryAccessModel($database, $userGroupModel);
        $this->entryAccessView = new \view\EntryAccessView($this->entryAccess);
        $this->updateAccess();
    }

    /**
     * Should return the entry model (like checklist)
     * @return mixed
     */
    protected abstract function getEntryModel();

    protected function updateAccess() {
        $this->entryAccess = null;

        $entry = $this->getEntryModel();
        if ($entry != null) {
            $this->entryAccess = $this->entryAccessModel->getEntryAccess($entry, $this->loggedInUser);
        }

        //also make sure to update the view to the new model
        $this->entryAccessView->updateAccess($this->entryAccess);
    }


    /**
     * Entry access specific actions
     */
    public function doEntryControl() {

        if ($this->entryAccessView->wantToAddUserGroupAccess() ||
            $this->entryAccessView->wantToRemoveUserGroupAccess()) {

            $credentials = $this->entryAccessView->getUserGroupAccessAddRemoveCredentials();
            if ($credentials != null) {

                $addNotRemove = ($this->entryAccessView->wantToAddUserGroupAccess());
                if ($this->entryAccess->canWrite()) {

                    try {
                        if ($addNotRemove) {
                            $this->entryAccessModel->saveNewUserGroupAccessToEntry($credentials, $this->loggedInUser);
                        }
                        else {
                            $this->entryAccessModel->deleteUserGroupAccessToEntry($credentials);
                        }

                        //success
                        $this->entryAccessView->setAddRemoveUserGroupAccessSuccess();
                        return;
                    }
                    catch (\Exception $e) { }
                }

                if ($addNotRemove) {
                    $this->entryAccessView->setAddUserGroupAccessFailure();
                }
                else {
                    $this->entryAccessView->setRemoveUserGroupAccessFailure();
                }
            }
        }
    }

}