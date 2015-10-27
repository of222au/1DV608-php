<?php

namespace controller;

/**
 * Class EntryCollectionController
 * A controller used for all kind of lists (not single objects) for ex checklists (not a single checklist) and home page where multiple lists are shown
 * @package controller
 */
class EntryCollectionController implements SubController {

    private $view;

    private $showUserGroups = false;
    private $showEntryTypesArray = null;

    private $userGroups = null;
    private $checklists = null;

    /**
     * @param \mysqli $database
     * @param \model\UserDal $userDAL
     * @param \model\User $loggedInUser
     * @param $title                        //  The title used in various parts of the output
     * @param $showUserGroups bool          //  If to show user groups
     * @param $showEntryTypesArray          //  Array of \Settings::ENTRY_TYPE_... | null if to show all
     */
    public function __construct(\mysqli $database, \model\UserDal $userDAL, \model\User $loggedInUser,
                                $glyphIcon, $title, $showUserGroups = false, $showEntryTypesArray = null) {

        $userGroupModel = new \model\UserGroupModel($database, $userDAL);
        $entryAccessModel = new \model\EntryAccessModel($database, $userGroupModel);

        $this->showEntryTypesArray = $showEntryTypesArray;
        $this->showUserGroups = $showUserGroups;

        if ($this->showUserGroups) {
            $this->userGroups = $userGroupModel->getUserGroupsWithUser($loggedInUser);
        }

        //get all entries accessible by user
        $entriesArray = $entryAccessModel->getAllEntriesUserHasAccessTo($loggedInUser, $this->showEntryTypesArray);

        //sort all entries by type
        if ($this->wantToShowEntryType(\Settings::ENTRY_TYPE_CHECKLIST)) { //checklist

            $checklistIds = array();
            if ($entriesArray != null) {
                foreach ($entriesArray as $entry) {
                    if ($entry->getEntryType() == \Settings::ENTRY_TYPE_CHECKLIST) {
                        $checklistIds[] = $entry->getId();
                    }
                }
            }

            //load all entries
            if (count($checklistIds)) {
                $checklistModel = new \model\ChecklistModel($database, $userDAL);
                $this->checklists = $checklistModel->getChecklists($checklistIds);
            }
        }

        $this->view = new \view\EntryCollectionView($loggedInUser, $glyphIcon, $title);

        //set what to show in view
        if ($this->showUserGroups) {
            $this->view->showUserGroups($this->userGroups);
        }

        //loop all entry types to show
        if ($this->showEntryTypesArray != null) {
            foreach ($this->showEntryTypesArray as $showEntryType) {

                if ($showEntryType == \Settings::ENTRY_TYPE_CHECKLIST) {
                    $this->view->showEntryType($showEntryType, $this->checklists);
                }
            }
        }
    }

    private function wantToShowEntryType($entryType) {
        if ($this->showEntryTypesArray != null) {
            foreach ($this->showEntryTypesArray as $showEntryType) {
                if ($entryType == $showEntryType) {
                    return true;
                }
            }
        }

        return false;
    }

    public function doControl() {
        //nothing
    }

    public function getView() {
        return $this->view;
    }

}
