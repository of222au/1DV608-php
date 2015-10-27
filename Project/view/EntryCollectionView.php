<?php

namespace view;

/**
 * Class EntryCollectionView
 * handles output for entry collections, i.e. lists of different entries (like checklists). Can handle lists of multiple entry types (and user groups etc)
 * @package view
 */
class EntryCollectionView extends GeneralView implements PageView {

    private $userGroups;  // array of \model\UserGroup | null
    private $checklists;  // array of \model\Checklist | null

    private $showUserGroups = false;      //  bool
    private $showEntryTypesArray = null; //  array of \Settings::\ENTRY_TYPE_...

    private $loggedInUser; // \model\User
    private $navigationView;
    private $glyphIcon;
    private $title;

    public function __construct(\model\User $loggedInUser, $glyphIcon, $title) {
        $this->loggedInUser = $loggedInUser;
        $this->navigationView = new NavigationView();
        $this->glyphIcon = $glyphIcon;
        $this->title = $title;
    }

    public function showUserGroups($userGroups) {
        $this->showUserGroups = true;
        $this->userGroups = $userGroups;
    }

    /**
     * @param $entryType    //any of the \Settings::ENTRY_TYPE_...
     * @param $entriesArray //array of the specific entries
     * @throws \Exception
     */
    public function showEntryType($entryType, $entriesArray) {
        assert($entriesArray == null || is_array($entriesArray));

        if ($this->showEntryTypesArray == null) { $this->showEntryTypesArray = array(); }

        //add entry type if not already in list
        if (!$this->wantToShowEntryType($entryType)) {
            $this->showEntryTypesArray[] = $entryType;
        }

        //set entries
        if ($entryType == \Settings::ENTRY_TYPE_CHECKLIST) {
            $this->checklists = $entriesArray;
        }
        else {
            throw new \Exception("Entry type not defined!");
        }
    }

    public function response() {
        $html = $this->generateHeader();
        $html .= $this->generateBody();

        return $html;
    }

    public function responseBreadcrumbSubItems() {
        return array(new BreadcrumbItem(($this->title != null && $this->title != "" ? $this->title : ""), '', true));
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

    private function generateHeader() {
        return '<header class="panel-heading">
                    <h1>
                        ' . ($this->glyphIcon != null && $this->glyphIcon != "" ? '<span class="glyphicon ' . $this->glyphIcon . '"></span> ' : '') . '
                        ' . ($this->title != null && $this->title != "" ? $this->title : "Entries") . '
                    </h1>
                </header>';
    }

    private function generateBody() {
        $html = '<div class="panel-body">';

        if ($this->showUserGroups) {
            $html .= $this->generateUserGroupsHtml();
        }

        //all entry types below
        if ($this->wantToShowEntryType(\Settings::ENTRY_TYPE_CHECKLIST)) {
            $html .= $this->generateChecklistsHtml();
        }

        return $html;
    }

    private function generateUserGroupsHtml() {

        $html = '<div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <a href="' . $this->navigationView->getURLToUserGroups() . '">
                                User groups
                            </a>
                        </h3>
                    </div>

                    <div class="panel-body">';

        if ($this->userGroups != null && count($this->userGroups)) {

            $html .= '
                        <div><p>You currently belong to these user groups:</p></div>
                        <ul class="list-unstyled ui-sortable task-list task-list-small">';

            foreach ($this->userGroups as $userGroup) {
                $memberCount = $userGroup->getMemberCount();
                $description = ($userGroup->getUser() != null ? 'Created by ' . $userGroup->getUser()->getUsername() : 'Unknown creator') . ' (';
                if ($memberCount > 0) {
                    $description .= $memberCount . ' member' . ($memberCount != 1 ? 's' : '');
                }
                else {
                    $description .= 'no members yet';
                }
                $description .= ')';
                $html .= '
                        <li>
                            <div class="task-title">
                              <a href="' . $this->navigationView->getURLToUserGroupPage($userGroup) . '">
                                  <span class="task-title-sp">
                                    <span class="glyphicon glyphicon-link"></span> ' . $userGroup->getName() . '
                                  </span>
                              </a>
                              <span class="task-description">
                              ' . $description . '
                              </span>
                            </div>
                        </li>';
            }
            $html .= '
                        </ul>';
        }
        else {
            $html .= '
                        <p class="no-entries">No user groups</p>';
        }

        $html .= '
                      <div class="div-with-button">
                        <a href="' . $this->navigationView->getURLToUserGroupPage(null, false, true) . '">
                            <button type="button" class="btn btn-success">
                                <span class="glyphicon glyphicon-plus"></span>
                                Create new user group
                            </button>
                        </a>
                      </div>';

        $html .= '
                    </div>
                 </div>';


        return $html;
    }

    private function generateChecklistsHtml() {
        $html = '';

        if ($this->checklists != null && count($this->checklists)) {
            foreach ($this->checklists as $checklist) {
                $html .= $this->generateChecklistSmallHtml($checklist);
            }
        }
        else {
            $html .= '<p class="no-entries">No checklists yet</p>';
        }

        $html .= '
                  <div class="div-with-button">
                    <a href="' . $this->navigationView->getURLToChecklistPage(null, false, false, true) . '">
                        <button type="button" class="btn btn-success">
                            <span class="glyphicon glyphicon-plus"></span>
                            Create new checklist
                        </button>
                    </a>
                  </div>';

        return $html;
    }
    private function generateChecklistSmallHtml(\model\Checklist $checklist) {

        $html = '<div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <a class="pull-left" href="' . $this->navigationView->getURLToChecklistPage($checklist) . '">
                                <span class="glyphicon glyphicon-edit"></span> ' . $checklist->getTitle() . '</a>

                            <a class="pull-right" href="' . $this->navigationView->getURLToChecklistPage($checklist, false, true) . '">
                                <button type="button" class="btn btn-info btn-sm">
                                    <span class="glyphicon glyphicon-pencil"></span> Edit
                                </button>
                            </a>
                            <div class="clearfix"></div>
                        </h3>
                    </div>

                    <div class="panel-body">

                        <div><p>' . $checklist->getDescription() . '</p></div>
                        <div>
                            <p>
                                <span class="glyphicon glyphicon-user"></span> Created by
                                <a href="' . $this->navigationView->getURLToUser($checklist->getUser()) . '">' . $checklist->getUser()->getUserName() . '</a>
                                on ' . $this->formatDateTimeToReadableDate($checklist->getCreatedAt()) . '
                            </p>
                        </div>';

        if (count($checklist->getChecklistItems())) {

            $html .= '<ul class="list-unstyled ui-sortable task-list task-list-small">';

            foreach ($checklist->getChecklistItems() as $item) {
                $stateType = $item->getCurrentStateType();
                $archived = ($stateType == \Settings::CHECKLIST_ITEM_STATE_ARCHIVED);
                if (!$archived) {
                    $checked = ($stateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED);
                    $createdByMyself = ($item->getUserId() == $this->loggedInUser->getId());
                    $html .= '
                                <li>
                                    <div class="task-checkbox">
                                      ' . $this->generateUserWithDateInfoIcon($item->getUserId(), $item->getUserName(), $createdByMyself, $item->getCreatedAt()) . '
                                      <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' disabled="disabled"></span>
                                    </div>
                                    <div class="task-title">
                                      <span class="task-title-sp">' . $item->getTitle() . '</span>
                                      <span class="task-description">
                                      ' . $item->getDescription() . '
                                      </span>
                                    </div>
                                </li>';
                }
            }
            $html .= '</ul>';
        }
        else {
            $html .= '<p class="no-entries">No items yet</p>';
        }

        $totalCount = $checklist->getTotalCount();
        $doneCount = $checklist->getDoneCount();
        $percentDone = $this->getPercent($totalCount, $doneCount);
        if ($percentDone > 0) {
            $html .= $this->generateProgressBar($percentDone);
        }

        $html .= '<a href="' . $this->navigationView->getURLToChecklistPage($checklist) . '">
                        <button type="button" class="btn btn-success btn-sm">
                            View/edit
                        </button>
                    </a>';
        $html .= '</div>
                 </div>';

        return $html;
    }

}
