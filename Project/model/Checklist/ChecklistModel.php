<?php

namespace model;

require_once("model/Checklist/ChecklistEditCredentials.php");
require_once("model/Checklist/ChecklistAddCredentials.php");
require_once("model/Checklist/ChecklistItemAddCredentials.php");
require_once("model/Checklist/ChecklistItemEditCredentials.php");
require_once("model/Checklist/ChecklistItemStateCredentials.php");
require_once("model/DAL/ChecklistDAL.php");

/**
 * Class ChecklistModel
 * a model simplifying use of the ChecklistDAL
 * @package model
 */
class ChecklistModel {

    private $checklistDAL;
    private $userDAL;

    public function __construct(\mysqli $database, UserDAL $userDAL) {
        assert(isset($_SESSION));

        $this->checklistDAL = new \model\ChecklistDAL($database);
        $this->userDAL = $userDAL;
    }

    public function getChecklist($checklistId) {
        try {
            $checklist = $this->checklistDAL->getChecklist($checklistId);

            if ($checklist != null) {
                $user = $this->userDAL->getUserById($checklist->getUserId());
                if ($user != null) {
                    $checklist->setUser($user);
                    return $checklist;
                }
            }
        }
        catch (\Exception $e) { }

        return null;
    }
    public function getChecklists($checklistIdArray) {
        return $checklist = $this->checklistDAL->getChecklists($checklistIdArray);
    }

    public function saveNewChecklist(ChecklistAddCredentials $credentials, User $user) {
        $checklistId = $this->checklistDAL->saveNewChecklist($credentials, $user);

        //success, return the new checklist
        return $this->checklistDAL->getChecklist($checklistId);
    }
    public function saveEditedChecklistDetails(ChecklistEditCredentials $credentials) {
        $this->checklistDAL->saveEditedChecklistDetails($credentials);
    }

    public function saveNewChecklistItem(ChecklistItemAddCredentials $credentials, User $loggedInUser) {
        return $this->checklistDAL->saveNewChecklistItem($credentials, $loggedInUser);
    }

    public function deleteChecklistItem(ChecklistItem $checklistItem) {
        $this->checklistDAL->deleteChecklistItem($checklistItem);
    }

    public function saveNewChecklistItemStates($credentialsArray, User $loggedInUser) {
        $this->checklistDAL->saveNewChecklistItemStates($credentialsArray, $loggedInUser);
    }


}
