<?php

namespace view;

/**
 * Class EntryAccessView
 * used by all entry type's (like checklist's) view to show entry access info
 * @package view
 */
class EntryAccessView {

    private static $addUserGroupAccessType = \Settings::ACCESS_TYPE_WRITE;
    private static $addUserGroupAccess = 'EntryAccessView::AddUserGroupAccess';
    private static $addUserGroupAccessWhich = 'EntryAccessView::AddUserGroupAccessWhich';
    private static $removeUserGroupAccess = 'EntryAccessView::RemoveUserGroupAccess';

    private $addRemoveUserGroupAccessSuccess = false;
    private $addRemoveUserGroupAccessFailure = false;
    private $addRemoveUserGroupAccessErrorMessage = '';

    private $model;

    public function __construct(\model\EntryAccess $access = null) {
        $this->updateAccess($access);
    }
    public function updateAccess(\model\EntryAccess $entryAccess = null) {
        $this->model = $entryAccess;
    }

    public function wantToAddUserGroupAccess() {
        return isset($_POST[self::$addUserGroupAccess]);
    }
    public function wantToRemoveUserGroupAccess() {
        return isset($_POST[self::$removeUserGroupAccess]);
    }

    private function getAddUserGroupAccessWhich() {
        if (isset($_POST[self::$addUserGroupAccessWhich])) {
            return ($_POST[self::$addUserGroupAccessWhich]);
        }
        return null;
    }
    private function getRemoveUserGroupAccessUserGroupId() {
        if (isset($_POST[self::$removeUserGroupAccess])) {
            return $_POST[self::$removeUserGroupAccess];
        }
        return "";
    }

    public function getUserGroupAccessAddRemoveCredentials() {
        assert($this->wantToAddUserGroupAccess() || $this->wantToRemoveUserGroupAccess());

        $addNotRemove = ($this->wantToAddUserGroupAccess());
        if ($addNotRemove) {
            return $this->getUserGroupAccessAddCredentials();
        }
        else {
            return $this->getUserGroupAccessRemoveCredentials();
        }
    }
    private function getUserGroupAccessAddCredentials() {
        try {
            $userGroupId = $this->getAddUserGroupAccessWhich();
            if ($userGroupId == null || $userGroupId == '') {
                $this->setAddUserGroupAccessFailure('Select a user group to share with');
                return null;
            }
            $userGroup = $this->model->findUserGroupWithoutAccess($userGroupId);
            if ($userGroup != null) {
                return new \model\EntryUserGroupAccessAddCredentials($userGroup,
                                                                        $this->model,
                                                                        self::$addUserGroupAccessType);
            }
        }
        catch (\Exception $e) { }

        $this->setAddUserGroupAccessFailure();
        return null;
    }
    private function getUserGroupAccessRemoveCredentials()
    {
        try {
            $userGroupId = $this->getRemoveUserGroupAccessUserGroupId();
            if ($userGroupId != null && $userGroupId != '') {

                $userGroup = $this->model->findUserGroupWithAccess($userGroupId);
                if ($userGroup != null) {
                    return new \model\EntryUserGroupAccessRemoveCredentials($userGroup,
                                                                            $this->model);
                }
            }
        }
        catch (\Exception $e) { }

        $this->setRemoveUserGroupAccessFailure();
        return null;
    }

    public function setAddRemoveUserGroupAccessSuccess() {
        $this->addRemoveUserGroupAccessSuccess = true;
    }
    public function setAddUserGroupAccessFailure($message = '') {
        $this->addRemoveUserGroupAccessFailure = true;
        $this->addRemoveUserGroupAccessErrorMessage = ($message != '' ? $message : 'Could not add user group access');
    }
    public function setRemoveUserGroupAccessFailure($message = '') {
        $this->addRemoveUserGroupAccessFailure = true;
        $this->addRemoveUserGroupAccessErrorMessage = ($message != '' ? $message : 'Could not remove user group access');
    }


    public function response($includeForm = true) {
        $navigationView = new NavigationView();

        if ($this->model != null) {
            $userGroupsAccess = $this->model->getUserGroupsAccess();

            $html = '<div class="entry-shared-with form-inline">
                      ' . ($includeForm ? '<form method="post">' : '') . '
                        <span class="glyphicon glyphicon-share"></span>';

            if ($userGroupsAccess != null && count($userGroupsAccess)) {
                $html .= ' Shared with:
                            <ul>';
                foreach ($userGroupsAccess as $item) {
                    $html .= '<li>
                                <div class="label-group">
                                    <span class="label label-info" title="' . $item->getUserGroupName() . ' has ' . strtolower($item->getAccess()) . ' access to this entry">
                                        <a href="' . $navigationView->getURLToUserGroupPage($item->getUserGroup()) . '">
                                            <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
                                            ' . $item->getUserGroupName() . '
                                        </a>
                                    </span>
                                    <span class="label label-danger label-with-button" title="Remove access for user group ' . $item->getUserGroupName() . '">
                                        <button type="submit"
                                                value="' . $item->getUserGroupId() . '"
                                                name="' . self::$removeUserGroupAccess . '"
                                                class="btn btn-xs btn-danger">
                                            <span class="glyphicon glyphicon-remove"></span>
                                        </button>
                                    </span>
                                </div>
                            </li>';
                }
                $html .= '</ul>';
            } else {
                $html .= ' Not shared';
            }

            if ($this->model->canWrite()) {

                $userGroupsWithoutAccess = $this->model->getUserGroupsCurrentlyWithoutAccess();
                if ($userGroupsWithoutAccess != null && count($userGroupsWithoutAccess)) {
                    $html .= '<div class="form-inline">
                                <select name="' . self::$addUserGroupAccessWhich . '" class="form-control btn btn-mini">
                                    <option></option>';
                    foreach ($userGroupsWithoutAccess as $group) {
                        $html .= '<option value="' . $group->getId() . '">' .
                                    $group->getName() .
                                 '</option>';
                    }
                    $html .= '
                                </select>
                                <input type="submit" name="' . self::$addUserGroupAccess . '" class="btn btn-xs btn-info" value="Add">
                                <label class="text-danger control-label">' . $this->addRemoveUserGroupAccessErrorMessage . '</label>
                            </div>';
                }
            }

            $html .= '
                    ' . ($includeForm ? '</form>' : '') . '
                </div>';

            if ($this->model->canRead() &&
                !$this->model->canWrite()) {

                $html .= '<div class="alert alert-warning small" role="alert">
                              <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                              <span class="sr-only">Please note:</span>
                              You have read-only access to this item. Please ask the creator if you wish to get access to edit as well.
                            </div>';
            }

            return $html;
        }
        return "";
    }

    public function needToRedirect() {
        return ($this->addRemoveUserGroupAccessSuccess);
    }

}
