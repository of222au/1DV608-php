<?php

namespace view;

require_once("ChecklistChangeStateAction.php");
require_once('view/GeneralView.php');

class ChecklistView extends GeneralView implements PageView {

    private static $addItem = 'ChecklistView::AddItem';
    private static $addTitle = 'ChecklistView::Title';
    private static $addDescription = 'ChecklistView::Description';
    private static $addImportant = 'ChecklistView::Important';
    private static $archiveItem = 'ChecklistView::ArchiveItem';
    private static $deArchiveItem = 'ChecklistView::DeArchiveItem';
    private static $deleteItem = 'ChecklistView::DeleteItem';
    private static $checklistItemCheckboxEarlierStates = 'ChecklistView::CheckboxEarlierStates[]';
    private static $checklistItemCheckboxStates = 'ChecklistView::CheckboxStates[]';
    private static $saveChecklistItemStates = 'ChecklistView::SaveStates';
    private static $checkAllItems = 'ChecklistView::CheckAllItems';
    private static $unCheckAllItems = 'ChecklistView::UnCheckAllItems';
    private static $archiveAllCheckedItems = 'ChecklistView::ArchiveAllCheckedItems';
    private static $addChecklist = 'ChecklistView::AddChecklist';
    private static $editChecklist = 'ChecklistView::EditChecklist';
    private static $editAddTitle = 'ChecklistView::EditTitle';
    private static $editAddDescription = 'ChecklistView::EditDescription';

    private $addErrorMessage = "";
    private $addErrorElement = "";
    private $addSuccess = false;

    private $deleteErrorMessage = "";
    private $deleteError = false;
    private $deleteSuccess = false;

    private $checklistItemsErrorMessage = "";
    private $saveChangedItemStatesSuccess = false;
    private $saveChangedItemStatesSuccessMessage = "";
    private $archiveOrDeArchiveChecklistItemSuccess = false;
    private $changeMultipleChecklistItemsByTypeSuccess = false;

    private $editAddErrorMessage = "";
    private $editAddErrorElement = "";
    private $editAddSuccess = false;


    private $access;
    private $checklist;

    private $entryAccessView;
    private $navigationView;


    private static $sessionSaveChangedChecklistItemStatesSuccessMessage = 'session\\ChecklistView\\SaveStatesSuccessMessage';

    public function __construct(EntryAccessView $entryAccessView, \model\Checklist $model = null, \model\EntryAccess $access = null) {
        $this->checklist = $model;
        $this->access = $access;

        $this->entryAccessView = $entryAccessView;
        $this->navigationView = new NavigationView();
    }

    public function response() {

        if ($this->checklist != null && $this->access != null &&
            $this->access->canRead()) {

            $changedItemStatesSuccessMessage = "";
            if ($this->addSuccess ||
                $this->deleteSuccess ||
                $this->archiveOrDeArchiveChecklistItemSuccess ||
                $this->changeMultipleChecklistItemsByTypeSuccess ||
                $this->entryAccessView->needToRedirect()) {

                $this->redirect();
            }
            else if ($this->editAddSuccess) {

                //redirect back to general page for checklist
                $url = $this->navigationView->getURLToChecklistPage($this->checklist, $this->navigationView->checklistPageShowArchived(), false);
                $this->redirect($url);
            }
            else if ($this->saveChangedItemStatesSuccess) {
                $this->saveSaveChangedItemStatesSuccessMessage($this->saveChangedItemStatesSuccessMessage);
                $this->redirect();
            }
            else {
                $changedItemStatesSuccessMessage = $this->getSaveChangedItemStatesSuccessMessage();
            }

            if ($this->navigationView->checklistPageEditDetails()) { //if on checklist edit page
                if ($this->access->canWrite()) {
                    return $this->generateEditAddHtml(true);
                }
            }
            else { //general checklist page
                return $this->generateViewHtml($changedItemStatesSuccessMessage);
            }
        }
        else if ($this->navigationView->checklistPageAdd()) { //checklist add page
            return $this->generateEditAddHtml(false);
        }
        else if ($this->checklist == null) { //unknown checklist
            return $this->generateUnknownHtml('checklist');
        }

        //if no return has been done, it means user has no access to this checklist
        return $this->generateNoAccessHtml('checklist');
    }

    public function responseBreadcrumbSubItems() {
        $breadCrumbs = array();
        $breadCrumbs[] = new BreadcrumbItem('Checklists', $this->navigationView->getURLToChecklists(), false);
        $checklistName = ($this->checklist != null && $this->access != null && $this->access->canRead() ? $this->checklist->getTitle() : ($this->navigationView->checklistPageAdd() ? 'Add checklist' : ($this->checklist == null ? 'Unknown page' : 'Unknown checklist')));
        $breadCrumbs[] = new BreadcrumbItem($checklistName, '', true);
        return $breadCrumbs;
    }

    public function wantToAddChecklistItem() {
        return isset($_POST[self::$addItem]);
    }
    public function wantToSaveChecklistItemStates() {
        return isset($_POST[self::$saveChecklistItemStates]);
    }
    public function wantToArchiveChecklistItem() {
        return isset($_POST[self::$archiveItem]);
    }
    public function wantToDeArchiveChecklistItem() {
        return isset($_POST[self::$deArchiveItem]);
    }
    public function wantToDeleteChecklistItem() {
        return isset($_POST[self::$deleteItem]);
    }
    public function wantToCheckAllItems() {
        return isset($_POST[self::$checkAllItems]);
    }
    public function wantToUnCheckAllItems() {
        return isset($_POST[self::$unCheckAllItems]);
    }
    public function wantToArchiveAllCheckedItems() {
        return isset($_POST[self::$archiveAllCheckedItems]);
    }
    public function wantToEditChecklist() {
        return isset($_POST[self::$editChecklist]);
    }
    public function wantToAddChecklist() {
        return isset($_POST[self::$addChecklist]);
    }


    public function setAddChecklistItemFailed() {
        $this->addErrorMessage = "Something went wrong when saving.";
        $this->addErrorUnknown = true;
    }
    public function setAddChecklistItemSucceeded() {
        $this->addSuccess = true;
    }

    public function setEditAddDetailsFailed() {
        $this->editAddErrorMessage = "Something went wrong when saving checklist details.";
        $this->editAddErrorElement = "Unknown";
    }
    public function setEditChecklistDetailsSucceeded() {
        $this->editAddSuccess = true;
    }
    public function setAddChecklistDetailsSucceeded(\model\Checklist $createdChecklist, \model\EntryAccess $accessForCreatedChecklist) {
        assert($createdChecklist != null);
        assert($accessForCreatedChecklist != null);

        $this->checklist = $createdChecklist;
        $this->access = $accessForCreatedChecklist;
        $this->editAddSuccess = true;
    }

    public function setDeleteChecklistItemFailed() {
        $this->deleteErrorMessage = "Something went wrong when deleting.";
        $this->deleteError = true;
    }
    public function setDeleteChecklistItemSucceeded() {
        $this->deleteSuccess = true;
    }

    public function setChecklistItemStatesSuccessOrFailure($checklistChangeStateAction, $successNotFailure, $message = '') {
        switch($checklistChangeStateAction) {
            case \view\ChecklistChangeStateAction::ChangedItems:
                if ($successNotFailure) {
                    $this->saveChangedItemStatesSuccess = true;
                    $this->saveChangedItemStatesSuccessMessage = "Saved.";
                }
                else {
                    $this->checklistItemsErrorMessage = ($message != '' ? $message : "Something went wrong when saving.");
                }
                break;
            case \view\ChecklistChangeStateAction::ArchiveItem:
            case \view\ChecklistChangeStateAction::DeArchiveItem:
                if ($successNotFailure) {
                    $this->archiveOrDeArchiveChecklistItemSuccess = true;
                }
                else {
                    $action = ($checklistChangeStateAction == \view\ChecklistChangeStateAction::ArchiveItem ? 'archive' : 're-activate');
                    $this->checklistItemsErrorMessage = "Something went wrong when trying to " . $action . " item.";
                }
                break;
            case \view\ChecklistChangeStateAction::CheckAllItems:
            case \view\ChecklistChangeStateAction::UnCheckAllItems:
            case \view\ChecklistChangeStateAction::ArchiveAllCheckedItems:
                if ($successNotFailure) {
                    $this->changeMultipleChecklistItemsByTypeSuccess = true;
                }
                else {
                    $action = ($checklistChangeStateAction == \view\ChecklistChangeStateAction::CheckAllItems ? 'check all items' :
                        ($checklistChangeStateAction == \view\ChecklistChangeStateAction::UnCheckAllItems ? 'un-check all items' :
                            'archive all checked items'));
                    $this->checklistItemsErrorMessage = "Something went wrong when trying to " . $action;
                }
                break;
        }
    }
    public function setSaveChecklistItemStatesNotNeeded() {
        $this->saveChangedItemStatesSuccess = true;
        $this->saveChangedItemStatesSuccessMessage = "No changes to save.";
    }

    private function getEditAddTitle() {
        if (isset($_POST[self::$editAddTitle])) {
            return $_POST[self::$editAddTitle];
        }
        return null;
    }
    private function getEditAddDescription() {
        if (isset($_POST[self::$editAddDescription])) {
            return $_POST[self::$editAddDescription];
        }
        return null;
    }

    private function getAddTitle() {
        if (isset($_POST[self::$addTitle])) {
            return $_POST[self::$addTitle];
        }
        return "";
    }
    private function getAddDescription() {
        if (isset($_POST[self::$addDescription])) {
            return $_POST[self::$addDescription];
        }
        return "";
    }
    private function getAddImportant() {
        if (isset($_POST[self::$addImportant])) {
            return ($_POST[self::$addImportant] == true);
        }
        return false;
    }

    private function getChecklistItemCheckboxesEarlierValues() {
        $name = str_replace('[]', '', self::$checklistItemCheckboxEarlierStates);
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return null;
    }
    private function getChecklistItemCheckboxesValues() {
        $name = str_replace('[]', '', self::$checklistItemCheckboxStates);
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return null;
    }

    private function getArchiveChecklistItemId() {
        if (isset($_POST[self::$archiveItem])) {
            return $_POST[self::$archiveItem];
        }
        return "";
    }
    private function getDeArchiveChecklistItemId() {
        if (isset($_POST[self::$deArchiveItem])) {
            return $_POST[self::$deArchiveItem];
        }
        return "";
    }
    public function getDeleteChecklistItem() {
        if (isset($_POST[self::$deleteItem])) {
            $id = $_POST[self::$deleteItem];
            return $this->checklist->findChecklistItem($id);
        }
        return null;
    }

    public function getEditAddCredentials() {
        assert($this->wantToAddChecklist() || $this->wantToEditChecklist());

        $editNotAdd = ($this->wantToEditChecklist());
        try {
            if ($editNotAdd) {
                return new \model\ChecklistEditCredentials($this->checklist,
                    $this->getEditAddTitle(),
                    $this->getEditAddDescription());
            }
            else {
                return new \model\ChecklistAddCredentials(
                    $this->getEditAddTitle(),
                    $this->getEditAddDescription());
            }
        }
        catch (\model\IncorrectChecklistTitleException $e) {
            $this->editAddErrorMessage = "Title needs to be filled in correctly";
            $this->editAddErrorElement = self::$editAddTitle;
        }
        catch (\model\IncorrectChecklistDescriptionException $e) {
            $this->editAddErrorMessage = "Description needs to be filled in correctly";
            $this->editAddErrorElement = self::$editAddDescription;
        }
        return null;
    }
    public function getAddChecklistItemCredentials() {
        try {
            return new \model\ChecklistItemAddCredentials($this->checklist,
                $this->getAddTitle(),
                $this->getAddDescription(),
                $this->getAddImportant());
        } catch (\model\IncorrectChecklistItemTitleException $e) {
            $this->addErrorMessage = "Title needs to be filled in correctly";
            $this->addErrorElement = self::$addTitle;
        } catch (\model\IncorrectChecklistItemDescriptionException $e) {
            $this->addErrorMessage = "Description was not filled in correctly";
            $this->addErrorElement = self::$addDescription;
        } catch (\model\IncorrectChecklistItemImportantException $e) {
            $this->addErrorMessage = "Important field was not set correctly";
            $this->addErrorElement = self::$addImportant;
        } catch (\Exception $e) {
            $this->addErrorMessage = "Unknown error";
            $this->addErrorElement = 'Unknown';
        }
        return null;
    }

    public function getChecklistItemStates($checklistChangeStateAction) {
        assert(($checklistChangeStateAction == ChecklistChangeStateAction::ArchiveItem && $this->wantToArchiveChecklistItem()) ||
                ($checklistChangeStateAction == ChecklistChangeStateAction::DeArchiveItem &&  $this->wantToDeArchiveChecklistItem()) ||
                ($checklistChangeStateAction == ChecklistChangeStateAction::ChangedItems && $this->wantToSaveChecklistItemStates()) ||
                ($checklistChangeStateAction == ChecklistChangeStateAction::CheckAllItems && $this->wantToCheckAllItems()) ||
                ($checklistChangeStateAction == ChecklistChangeStateAction::UnCheckAllItems && $this->wantToUnCheckAllItems()) ||
                ($checklistChangeStateAction == ChecklistChangeStateAction::ArchiveAllCheckedItems && $this->wantToArchiveAllCheckedItems()));

        switch($checklistChangeStateAction) {
            case ChecklistChangeStateAction::ArchiveItem:
                return $this->getChecklistItemToArchiveOrDeArchive(true);
            case ChecklistChangeStateAction::DeArchiveItem:
                return $this->getChecklistItemToArchiveOrDeArchive(false);
            case ChecklistChangeStateAction::ChangedItems:
                return $this->getChangedChecklistItemStates();
            case ChecklistChangeStateAction::CheckAllItems:
            case ChecklistChangeStateAction::UnCheckAllItems:
            case ChecklistChangeStateAction::ArchiveAllCheckedItems:
                return $this->getChecklistItemsToMultiChange($checklistChangeStateAction);
        }

        return null;
    }
    private function getChangedChecklistItemStates() {
        $checklistItemStates = array();

        $earlierValues = $this->getChecklistItemCheckboxesEarlierValues();
        $newValues = $this->getChecklistItemCheckboxesValues();

        if ($earlierValues != null) {
            foreach ($earlierValues as $key => $value) {

                $checklistItem = $this->checklist->findChecklistItem($key);
                if ($checklistItem != null) {

                    if ($checklistItem->getCurrentStateType() != \Settings::CHECKLIST_ITEM_STATE_ARCHIVED) { //don't modify state type of archived items here

                        $newValue = ($newValues != null && array_key_exists($key, $newValues) ? $newValues[$key] : 0); //if no value in $newValues array it means the value is 0 (not submitted in the POST).
                        if ($newValue != $value) {

                            //a change has been done for this checklist item, create new state to save
                            $newState = ($newValue == 1 ? \Settings::CHECKLIST_ITEM_STATE_CHECKED : \Settings::CHECKLIST_ITEM_STATE_UNCHECKED);
                            $item = new \model\ChecklistItemStateCredentials($checklistItem, $newState);
                            $checklistItemStates[] = $item;
                        }
                    }
                }
                else {
                    $this->checklistItemsErrorMessage = "Unexpected error when trying to save.";
                }
            }
        }

        return $checklistItemStates;
    }
    private function getChecklistItemsToMultiChange($checklistChangeStateAction) {
        $checklistItemStates = array();

        $newStateType = "";
        $checklistItemsByStateType = "";
        switch($checklistChangeStateAction) {
            case ChecklistChangeStateAction::CheckAllItems:
                $newStateType = \Settings::CHECKLIST_ITEM_STATE_CHECKED;
                $checklistItemsByStateType = \Settings::CHECKLIST_ITEM_STATE_UNCHECKED; //check all UNCHECKED items
                break;
            case ChecklistChangeStateAction::UnCheckAllItems:
                $newStateType = \Settings::CHECKLIST_ITEM_STATE_UNCHECKED;
                $checklistItemsByStateType = \Settings::CHECKLIST_ITEM_STATE_CHECKED; //un-check all CHECKED items
                break;
            case ChecklistChangeStateAction::ArchiveAllCheckedItems:
                $newStateType = \Settings::CHECKLIST_ITEM_STATE_ARCHIVED;
                $checklistItemsByStateType = \Settings::CHECKLIST_ITEM_STATE_CHECKED; //archive all CHECKED items
                break;
        }
        if ($newStateType != "" && $checklistItemsByStateType != "") {
            $checklistItems = $this->checklist->getChecklistItemsByCurrentStateType($checklistItemsByStateType);

            if ($checklistItems != null && count($checklistItems)) {
                foreach($checklistItems as $item) {

                    $itemState = new \model\ChecklistItemStateCredentials($item, $newStateType);
                    $checklistItemStates[] = $itemState;
                }
            }
        }

        return $checklistItemStates;
    }
    private function getChecklistItemToArchiveOrDeArchive($archiveNotDeArchive) {
        if ($archiveNotDeArchive) {
            $id = $this->getArchiveChecklistItemId();
        }
        else {
            $id = $this->getDeArchiveChecklistItemId();
        }
        if ($id != null && $id != "") {

            $checklistItem = $this->checklist->findChecklistItem($id);
            if ($checklistItem != null) {

                //check for okay current state before changing to new state
                $currentState = $checklistItem->getCurrentStateType();
                if (($archiveNotDeArchive && $currentState == \Settings::CHECKLIST_ITEM_STATE_CHECKED) ||
                    (!$archiveNotDeArchive && $currentState == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED)) {

                    $credentials = new \model\ChecklistItemStateCredentials($checklistItem, ($archiveNotDeArchive ? \Settings::CHECKLIST_ITEM_STATE_ARCHIVED : \Settings::CHECKLIST_ITEM_STATE_CHECKED));
                    return array($credentials);
                }
            }
        }

        $this->checklistItemsErrorMessage = "Unexpected error when trying to archive item.";
        return null;
    }

    private function saveSaveChangedItemStatesSuccessMessage($message) {
        $_SESSION[self::$sessionSaveChangedChecklistItemStatesSuccessMessage] = $message;
    }
    private function getSaveChangedItemStatesSuccessMessage() {
        if (isset($_SESSION[self::$sessionSaveChangedChecklistItemStatesSuccessMessage])) {
            $message = $_SESSION[self::$sessionSaveChangedChecklistItemStatesSuccessMessage];
            unset($_SESSION[self::$sessionSaveChangedChecklistItemStatesSuccessMessage]);
            return $message;
        }
        else if ($this->saveChangedItemStatesSuccessMessage != "") {
            return $this->saveChangedItemStatesSuccessMessage;
        }
        return "";
    }


    private function isAnyAddError() {
        return ($this->addErrorElement != '');
    }

    private function generateEditAddHtml($editNotAdd) {
        $html = $this->generateHeader(($editNotAdd ? 1 : 2));
        $html .= $this->generateBody(($editNotAdd ? 1 : 2));
        return $html;
    }

    private function generateViewHtml($changedItemStatesSuccessMessage) {
        $edit1_add2_view3 = 3; //view
        $html = $this->generateHeader($edit1_add2_view3);
        $html .= $this->generateBody($edit1_add2_view3, $changedItemStatesSuccessMessage);
        $html .= $this->generateAddPanel();
        $html .= $this->generateStatusPanel();
        return $html;
    }

    private function generateHeader($edit1_add2_view3) {
        assert($edit1_add2_view3 == 1 || $edit1_add2_view3 == 2 || $edit1_add2_view3 == 3);

        $html = '<header class="panel-heading">';

        if ($edit1_add2_view3 == 1 ||
            $edit1_add2_view3 == 2) { //edit or add page

            $editNotAdd = ($edit1_add2_view3 == 1);
            if ($editNotAdd) {
                $title = 'Edit checklist "' . $this->checklist->getTitle() . '"';
            }
            else {
                $title = 'Create new checklist';
            }
            $html .= '<h1>' . $title . '</h1>';
        }
        else if ($edit1_add2_view3 == 3) { //view page
            if ($this->access->canWrite()) {
                $html .= '<a href="' . $this->getUrl($this->navigationView->checklistPageShowArchived(), true) . '" class="pull-top-right">
                                <button type="button" class="btn btn-info btn-sm" title="Edit checklist details">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </button>
                            </a>';
            }

            $html .= '<h1><span class="glyphicon glyphicon-edit"></span> ' . $this->checklist->getTitle() . '</h1>
                      <p class="lead">
                        ' . $this->checklist->getDescription() . '
                      </p>
                      ' . $this->generateEntryInfo();
        }

        $html .= '</header>';
        return $html;
    }

    private function generateEntryInfo($includeForm = true) {
        return '  <hr>
                    <p>
                        <span class="glyphicon glyphicon-user"></span>
                        Created by
                        <a href="' . $this->navigationView->getURLToUser($this->checklist->getUser()) . '">' . $this->checklist->getUser()->getUserName() . '</a>
                        on ' . $this->formatDateTimeToReadableDate($this->checklist->getCreatedAt()) . '
                        ' . $this->entryAccessView->response($includeForm) . '
                    </p>';
    }

    private function generateBody($edit1_add2_view3, $changedItemStatesSuccessMessage = "") {
        assert($edit1_add2_view3 == 1 || $edit1_add2_view3 == 2 || $edit1_add2_view3 == 3);

        $html = '<div class="panel-body">';

        if ($edit1_add2_view3 == 1 ||
            $edit1_add2_view3 == 2) { //edit or add page

            $editNotAdd = ($edit1_add2_view3 == 1);
            $html .= $this->generateEditAddBody($editNotAdd);
        }
        else if ($edit1_add2_view3 == 3) { //view page
            $html .= $this->generateViewBody($changedItemStatesSuccessMessage);
        }

        $html .= '</div>';
        return $html;
    }

    private function generateEditAddBody($editNotAdd) {

        $title = $this->getEditAddTitle();
        if ($title === null && $editNotAdd) {
            $title = $this->checklist->getTitle();
        }
        $description = $this->getEditAddDescription();
        if ($description === null && $editNotAdd) {
            $description = $this->checklist->getDescription();
        }
        if ($editNotAdd) {
            $backUrl = $this->getUrl($this->navigationView->checklistPageShowArchived(), false);
        }
        else {
            $backUrl = $this->navigationView->getURLToHomePage();
        }

        $html = '<div class="main-content">
                  <form method="post">

                    <div class="content-main">
                        <div class="form-group' . ($this->editAddErrorElement == self::$editAddTitle ? ' has-error' : '') . '">
                            <label>Title:</label>
                            <input type="text" name="' . self::$editAddTitle . '" class="form-control" placeholder="Title" value="' . $title . '">
                        </div>
                        <div class="form-group' . ($this->editAddErrorElement == self::$editAddDescription ? ' has-error' : '') . '">
                            <label>Description:</label>
                            <textarea name="' . self::$editAddDescription . '" class="form-control" placeholder="Description (optional)">' . $description . '</textarea>
                        </div>
                        ' . ($editNotAdd ?
                        $this->generateEntryInfo(false)
                        : '') . '
                    </div>

                    <div class="form-group form-inline save-panel">
                        <label class="text-danger control-label">' . $this->editAddErrorMessage . '</label>
                        <input type="submit" class="btn btn-success form-control"
                                value="' . ($editNotAdd ? 'Save changes' : 'Add checklist') . '"
                                name="' . ($editNotAdd ? self::$editChecklist : self::$addChecklist) . '">
                        <a href="' . $backUrl . '">
                            <button type="button" class="btn btn-link">Back to checklist</button>
                        </a>
                    </div>

                 </form>
               </div>';

        return $html;
    }

    private function generateViewBody($changedItemStatesSuccessMessage) {
        $html = '<div class="main-content">
                  <form method="post">';

        $html .= $this->generateChecklistItemLists();
        $html .= $this->generateActionButtons($changedItemStatesSuccessMessage);

        $html .= '
                </form>
               </div>';

        return $html;
    }
    private function generateChecklistItemLists() {
        $html = '';

        if (count($this->checklist->getChecklistItems())) {
            $ulsClassTag = 'list-unstyled ui-sortable task-list border-top-first';

            $showArchived = $this->navigationView->checklistPageShowArchived();
            $archivedCount = $this->checklist->getArchivedCount();
            $firstItem = true;
            $anyArchived = false;
            $anyNotArchived = false;

            //loop the checklist items
            foreach ($this->checklist->getChecklistItems() as $item) {

                $id = $item->getId();
                $stateType = $item->getCurrentStateType();
                $archived = ($stateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED);
                $checked = ($archived || $stateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED);
                $hiddenName = str_replace('[]', '[' . $id . ']', self::$checklistItemCheckboxEarlierStates);
                $checkboxName = str_replace('[]', '[' . $id . ']', self::$checklistItemCheckboxStates);

                if ($archived && !$anyArchived) {
                    $html .= '<span class="list-title">
                                ' . ($showArchived ? '
                                    Archived items:
                                    <a href="' . $this->getUrl(false) . '">Hide</a>
                                ' : '
                                    <a href="' . $this->getUrl(true) . '">Show ' . $archivedCount . ' archived item' . ($archivedCount != 1 ? 's' : '') . '</a>
                                ') . '
                             </span>';
                }

                if ($firstItem) {
                    $html .= '<ul class="' . $ulsClassTag . '">';
                    $firstItem = false;
                }

                //if this is the first not archived item, and there exists archived items, make a new ul list
                if (!$anyNotArchived && $anyArchived && !$archived) {

                    $html .= '</ul>';
                    if ($showArchived) {
                        $html .= '<span class="list-title">Items:</span>';
                    }
                    $html .= '<ul class="' . $ulsClassTag . '">';

                    $anyNotArchived = true;
                }
                if ($archived) {
                    $anyArchived = true;
                }

                //generate the actual checklist item row (li element)
                $createdByMyself = ($item->getUserId() == $this->access->getUserId());
                $html .= '<li class="' . ($item->getImportant() ? ' task-important ' : '') . ($archived ? ' task-archived ' . (!$showArchived ? 'hidden ' : '') : '') . ($checked ? ' task-done ' : '') . '">
                              ' . $this->generateUserWithDateInfoIcon($item->getUserId(), $item->getUserName(), $createdByMyself, $item->getCreatedAt()) . '
                              <div class="task-checkbox">
                                    <input type="hidden" name="' . $hiddenName . '" value="' . ($checked ? '1' : '0') . '">
                                    <input type="checkbox" name="' . $checkboxName . '" value="1" ' . ($checked ? 'checked="checked" ' : '') . ($archived || !$this->access->canWrite() ? 'disabled="disabled" ' : '') . '>
                              </div>
                              <div class="task-title">
                                  <span class="task-title-sp">' . $item->getTitle() . '</span>
                                  <span class="task-description">
                                  ' . $item->getDescription() . '
                                  </span>
                                  ';

                if ($this->access->canWrite()) {
                    $html .= '    <div class="pull-right task-buttons">';
                    if ($archived) {
                        $html .= $this->generateChecklistItemButton($id, 3); //de-archive button
                    } else if ($checked) {
                        $html .= $this->generateChecklistItemButton($id, 2); //archive button
                        $html .= $this->generateChecklistItemButton($id, 1); //delete button
                    }
                    $html .= '    </div>';
                }

                $html .= '
                          </div>
                        </li>';
            }

            $html .= '</ul>';
        }
        else {
            $html .= '<span class="no-entries">No items yet</span>';
        }

        return $html;
    }

    private function generateChecklistItemButton($id, $delete1_Archive2_DeArchive3, $disabled = false) {
        assert($delete1_Archive2_DeArchive3 === 1 || $delete1_Archive2_DeArchive3 === 2 || $delete1_Archive2_DeArchive3 === 3);

        return '<button type="submit"
                        name="' . ($delete1_Archive2_DeArchive3 === 1 ? self::$deleteItem : ($delete1_Archive2_DeArchive3 === 2 ? self::$archiveItem : self::$deArchiveItem)) . '"
                        value="'. $id . '"
                        ' . ($disabled ? 'disabled="disabled"' : '') . '
                        class="btn ' . (!$disabled && $delete1_Archive2_DeArchive3 === 1 ? 'btn-danger' : (!$disabled ? 'btn-success' : 'btn-default')) . ' btn-xs"
                        title="' . ($delete1_Archive2_DeArchive3 === 1 ? 'Delete' : ($delete1_Archive2_DeArchive3 === 2 ? 'Archive' : 'Restore (re-activate)')) . ' item">
                    <span class="glyphicon ' . ($delete1_Archive2_DeArchive3 === 1 ? 'glyphicon-trash' : ($delete1_Archive2_DeArchive3 === 2 ? 'glyphicon-hdd' : 'glyphicon-folder-open')) . '"></span>
                </button>';
    }


    private function generateActionButtons($changedItemStatesSuccessMessage = '') {
        $html = '';

        if ($this->access->canWrite() &&
            count($this->checklist->getChecklistItems())) {

            $nonArchivedCount = $this->checklist->getNonArchivedCount();

            //save state changes button
            $html .= '<div class="form-group form-inline save-panel pull-left">
                        <button type="submit"
                                name="' . self::$saveChecklistItemStates . '"
                                class="btn btn-success form-control"
                                title="Saves any changed checkbox states">
                            Save checkbox changes
                        </button>
                        <label class="text-danger control-label">' . $this->checklistItemsErrorMessage . '</label>
                        <span class="text-success">' . $changedItemStatesSuccessMessage . '</span>
                    </div>';

            if ($nonArchivedCount >= 3) { //only if there are at least 3 non-archived items
                $uncheckedCount = $this->checklist->getUncheckedCount();
                $checkedCount = $this->checklist->getCheckedCount();

                $html .= '<div class="action-buttons">';
                $html .= '<button type="submit" name="' . self::$checkAllItems . '" ' . ($uncheckedCount <= 0 ? 'disabled="disabled"' : '') . ' class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-ok"></span> Check all items</button>';
                $html .= '<button type="submit" name="' . self::$unCheckAllItems . '" ' . ($checkedCount <= 0 ? 'disabled="disabled"' : '') . ' class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-unchecked"></span> Un-check all items</button>';
                $html .= '<button type="submit" name="' . self::$archiveAllCheckedItems . '" ' . ($checkedCount <= 0 ? 'disabled="disabled"' : '') . ' class="btn btn-success btn-sm"><span class="glyphicon glyphicon-hdd"></span> Archive all checked items</button>';
                $html .= '</div>';
            }
        }

        return $html;
    }

    private function generateStatusPanel($calculateIncludingArchived = false) {
        $html = '';

        //generate this panel only if there are any items
        if (count($this->checklist->getChecklistItems()) > 1) {

            $doneCount = $this->checklist->getDoneCount($calculateIncludingArchived);
            $totalCount = $this->checklist->getTotalCount($calculateIncludingArchived);
            $undoneCount = ($totalCount - $doneCount);
            $percentDone = $this->getPercent($totalCount, $doneCount);

            $html .= '<div class="panel-status">
                          <div class="bottom-info">
                              ' . ($undoneCount > 0 ? '
                                <strong><span class="count-checklist-unchecked">' . $percentDone . '%</span></strong> ' . ($doneCount > 0 ? '(' . $doneCount . ' of ' . $totalCount . ' item' . ($totalCount != 1 ? 's' : '') . ')' : '') . ' done
                              ' : '
                                <span>All items done!</span>
                              ');
            $html .= '
                          </div>
                          ' . $this->generateProgressBar($percentDone) . '
                     </div>';
        }

        return $html;
    }

    private function generateAddPanel() {
        if ($this->access->canWrite()) {
            return '<div class="panel-add bg-success">
                      <form method="post" class="row">
                        ' . ($this->isAnyAddError() ? '
                        <div class="has-error col-sm-12">
                            <label class="control-label">' . $this->addErrorMessage . '</label>
                        </div>' : '') . '

                        <div class="col-sm-4 col-md-4 form-group' . ($this->addErrorElement == self::$addTitle ? ' has-error' : '') . '">
                            <label class="sr-only" for="' . self::$addTitle . '">Add to list (title)</label>
                            <input type="text" class="form-control" placeholder="Add to list (title)" name="' . self::$addTitle . '">
                        </div>
                        <div class="col-sm-3 col-md-4 form-group' . ($this->addErrorElement == self::$addDescription ? ' has-error' : '') . '">
                            <label class="sr-only" for="' . self::$addDescription . '">Description (optional)</label>
                            <textarea class="form-control" rows="1" placeholder="Description (optional)" name="' . self::$addDescription . '"></textarea>
                        </div>
                        <div class="col-sm-3 col-md-2 form-group' . ($this->addErrorElement == self::$addImportant ? ' has-error' : '') . ' checkbox">
                                <label class="control-label">
                                    <input type="checkbox" name="' . self::$addImportant . '">
                                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                                    Important
                                </label>
                            </label>
                        </div>
                        <div class="col-sm-2 col-md-2">
                            <button type="submit" class="btn btn-success form-control" name="' . self::$addItem . '">
                                Add
                            </button>
                        </div>

                      </form>
                      <div class="clearfix visible-xs"></div>
                    </div>';
        }
        else {
            return '';
        }
    }

    private function getUrl($showArchived = false, $editDetails = false) {
        return $this->navigationView->getURLToChecklistPage($this->checklist, $showArchived, $editDetails);
    }

}