<?php

namespace view;

/**
 * Class NavigationView
 * contains general navigation functions to make routing to different pages easier
 * @package view
 */
class NavigationView {

    private static $urlIdRegister = 'register';
    private static $urlIdLogin = 'login';
    private static $urlIdLogout = 'logout';
    private static $urlIdUser = 'user';
    private static $urlIdChecklists = 'checklists';
    private static $urlIdChecklist = 'checklist';
    private static $urlIdUserGroup = 'usergroup';
    private static $urlIdUserGroups = 'usergroups';

    private static $urlIdTests = 'tests';

    private static $urlIdChecklistShowArchived = 'archived';
    private static $urlIdEntryEditDetails = 'edit';
    private static $urlIdEntryAdd = 'add';

    public function onHomePage() {
        return !count($_GET);
    }
    public function onRegisterPage() {
        return isset($_GET[self::$urlIdRegister]);
    }
    public function onLoginPage() {
        return isset($_GET[self::$urlIdLogin]);
    }
    public function onLogoutPage() {
        return isset($_GET[self::$urlIdLogout]);
    }
    public function onUserPage() {
        return isset($_GET[self::$urlIdUser]);
    }
    public function onUserGroupPage() {
        return isset($_GET[self::$urlIdUserGroup]);
    }
    public function onUserGroupsPage() {
        return isset($_GET[self::$urlIdUserGroups]);
    }
    public function onChecklistsPage() {
        return isset($_GET[self::$urlIdChecklists]);
    }
    public function onChecklistPage() {
        return isset($_GET[self::$urlIdChecklist]);
    }

    public function onTestsPage() {
        return isset($_GET[self::$urlIdTests]);
    }

    public function checklistPageShowArchived() {
        return isset($_GET[self::$urlIdChecklistShowArchived]);
    }
    public function checklistPageEditDetails() {
        return isset($_GET[self::$urlIdEntryEditDetails]);
    }
    public function checklistPageAdd() {
        return isset($_GET[self::$urlIdEntryAdd]);
    }

    public function userGroupPageEditDetails() {
        return isset($_GET[self::$urlIdEntryEditDetails]);
    }
    public function userGroupPageAdd() {
        return isset($_GET[self::$urlIdEntryAdd]);
    }

    public function getChecklistId() {
        if ($this->onChecklistPage()) {
            $value = $_GET[self::$urlIdChecklist];
            if (is_numeric($value)) {
                return $value;
            }
        }
        return null;
    }
    public function getUserGroupId() {
        if ($this->onUserGroupPage()) {
            $value = $_GET[self::$urlIdUserGroup];
            if (is_numeric($value)) {
                return $value;
            }
        }
        return null;
    }
    public function getUserId() {
        if ($this->onUserPage()) {
            $value = $_GET[self::$urlIdUser];
            if (is_numeric($value)) {
                return $value;
            }
        }
        return null;
    }

    public function getURLToHomePage() {
        return '?';
    }
    public function getURLToTestsPage() {
        return '?' . self::$urlIdTests;
    }
    public function getURLToChecklistPage(\model\Checklist $checklist = null, $showArchivedItems = false, $editDetails = false, $createNew = false) {
        return '?' . self::$urlIdChecklist . ($checklist != null ? '=' . $checklist->getId() : '') . ($showArchivedItems ? '&' . self::$urlIdChecklistShowArchived : '') . ($editDetails ? '&' . self::$urlIdEntryEditDetails : '') . ($createNew ? '&' . self::$urlIdEntryAdd : '');
    }
    public function getURLToChecklists() {
        return '?' . self::$urlIdChecklists;
    }
    public function getURLToLogin() {
        return '?' . self::$urlIdLogin;
    }
    public function getURLToLogout() {
        return '?' . self::$urlIdLogout;
    }
    public function getURLToRegister() {
        return '?' . self::$urlIdRegister;
    }
    public function getURLToUser(\model\User $user) {
        return $this->getURLToUserWithId($user->getId());
    }
    public function getURLToUserWithId($userId) {
        return '?' . self::$urlIdUser . '=' . $userId;
    }
    public function getURLToUserGroupPage(\model\UserGroup $userGroup = null, $editDetails = false, $createNew = false) {
        return '?' . self::$urlIdUserGroup . ($userGroup != null ? '=' . $userGroup->getId() : '') . ($editDetails ? '&' . self::$urlIdEntryEditDetails : '') . ($createNew ? '&' . self::$urlIdEntryAdd : '');
    }
    public function getURLToUserGroups() {
        return '?' . self::$urlIdUserGroups;
    }

    public function redirectToLoginPage() {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . self::$urlIdLogin);
    }
}