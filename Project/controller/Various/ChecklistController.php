<?php

namespace controller;

require_once("model/Checklist/Checklist.php");
require_once("model/Checklist/ChecklistModel.php");
require_once('view/ChecklistView.php');

class ChecklistController extends EntryController implements SubController {

    private $checklist = null;
    private $view;

    private $checklistModel;

    public function __construct(\mysqli $database, \model\UserDal $userDAL, $checklistId, \model\User $loggedInUser) {

        $this->checklistModel = new \model\ChecklistModel($database, $userDAL);
        if ($checklistId != null) {
            $this->checklist = $this->checklistModel->getChecklist($checklistId);
        }

        parent::__construct($database, $userDAL, $loggedInUser);

        $this->view = new \view\ChecklistView($this->entryAccessView, $this->checklist, $this->entryAccess);
    }

    protected function getEntryModel() {
        return $this->checklist;
    }

    public function doControl() {

        $this->doEntryControl();

        if ($this->view->wantToEditChecklist() ||
            $this->view->wantToAddChecklist()) {

            $editNotAdd = ($this->view->wantToEditChecklist());
            $this->performEditAddChecklist($editNotAdd);
        }
        else if ($this->view->wantToAddChecklistItem()) {
            $this->performAddChecklistItem();
        }
        else if ($this->view->wantToDeleteChecklistItem()) {
            $this->performDeleteChecklistItem();
        }
        else if ($this->view->wantToSaveChecklistItemStates() ||
                 $this->view->wantToArchiveChecklistItem() ||
                 $this->view->wantToDeArchiveChecklistItem() ||
                 $this->view->wantToCheckAllItems() ||
                 $this->view->wantToUnCheckAllItems() ||
                 $this->view->wantToArchiveAllCheckedItems()) {

            $this->performChangeChecklistItemStates();
        }

    }

    private function performEditAddChecklist($editNotAdd) {
        $credentials = $this->view->getEditAddCredentials();
        if ($credentials != null) {

            //if user has write access
            if (!$editNotAdd || $this->entryAccess->canWrite()) {

                try {
                    if ($editNotAdd) {
                        //edit the checklist
                        $this->checklistModel->saveEditedChecklistDetails($credentials);
                        //success
                        $this->view->setEditChecklistDetailsSucceeded();
                    }
                    else {
                        //save the new checklist
                        $this->checklist = $this->checklistModel->saveNewChecklist($credentials, $this->loggedInUser);
                        //success
                        $this->updateAccess();
                        $this->view->setAddChecklistDetailsSucceeded($this->checklist, $this->entryAccess);
                    }
                    return;
                }
                catch (\Exception $e) { }
            }

            $this->view->setEditAddChecklistDetailsFailed();
        }
    }

    private function performAddChecklistItem() {
        $checklistItemCredentials = $this->view->getAddChecklistItemCredentials();
        if ($checklistItemCredentials != null) {

            //if user has write access
            if ($this->entryAccess->canWrite()) {
                try {

                    $this->checklistModel->saveNewChecklistItem($checklistItemCredentials, $this->loggedInUser);

                    //success
                    $this->view->setAddChecklistItemSucceeded();
                }
                catch(\Exception $e) {}
            }

            $this->view->setAddChecklistItemFailed();
        }
    }

    private function performDeleteChecklistItem() {
        $checklistItem = $this->view->getDeleteChecklistItem();
        if ($checklistItem != null) {

            //if user has write access
            if ($this->entryAccess->canWrite()) {

                try {
                    $this->checklistModel->deleteChecklistItem($checklistItem);

                    //success
                    $this->view->setDeleteChecklistItemSucceeded();
                }
                catch (\Exception $e) { }
            }

            $this->view->setDeleteChecklistItemFailed();
        }
    }

    private function performChangeChecklistItemStates() {

        $checklistChangeStateAction = ($this->view->wantToSaveChecklistItemStates() ? \view\ChecklistChangeStateAction::ChangedItems :
                                        ($this->view->wantToArchiveChecklistItem() ? \view\ChecklistChangeStateAction::ArchiveItem :
                                            ($this->view->wantToDeArchiveChecklistItem() ? \view\ChecklistChangeStateAction::DeArchiveItem :
                                                ($this->view->wantToCheckAllItems() ? \view\ChecklistChangeStateAction::CheckAllItems :
                                                    ($this->view->wantToUnCheckAllItems() ? \view\ChecklistChangeStateAction::UnCheckAllItems :
                                                        ($this->view->wantToArchiveAllCheckedItems() ? \view\ChecklistChangeStateAction::ArchiveAllCheckedItems :
                                        null))))));

        //get correct kind of checklist item states to change from view
        $credentials = $this->view->getChecklistItemStates($checklistChangeStateAction);
        if ($credentials != null && count($credentials)) {

            $success = false;

            //if user has write access
            if ($this->entryAccess->canWrite()) {

                try {
                    //perform the save
                    $this->checklistModel->saveNewChecklistItemStates($credentials, $this->loggedInUser);
                    $success = true;
                }
                catch (\Exception $e) { }
            }

            //report to view
            $this->view->setChecklistItemStatesSuccessOrFailure($checklistChangeStateAction, $success);
        }
        else {
            //no credentials supplied
            if ($checklistChangeStateAction == \view\ChecklistChangeStateAction::ChangedItems) {
                $this->view->setSaveChecklistItemStatesNotNeeded();
            }
        }
    }


    public function getView() {
        return $this->view;
    }

}
