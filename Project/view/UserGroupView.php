<?php

namespace view;

/**
 * Class UserGroupView
 * user group view
 * @package view
 */
class UserGroupView extends GeneralView implements PageView {

    private static $searchMember = 'UserGroupView::SearchMember';
    private static $searchMemberUsername = 'UserGroupView::SearchMemberUsername';
    private static $skipFoundMember = 'UserGroupView::SkipFoundMember';
    private static $addMember = 'UserGroupView::AddMember';
    private static $removeMember = 'UserGroupView::RemoveMember';
    private static $addUserGroup = 'UserGroupView::AddUserGroup';
    private static $editUserGroup = 'UserGroupView::EditUserGroup';
    private static $editAddTitle = 'UserGroupView::EditTitle';

    private static $sessionFoundUser = 'UserGroupView::FoundMember';

    private $searchMemberErrorMessage = "";
    private $searchMemberSuccess = false;

    private $addMemberErrorMessage = "";
    private $addMemberSuccess = false;

    private $deleteErrorMessage = "";
    private $deleteError = false;
    private $deleteSuccess = false;

    private $editAddErrorMessage = "";
    private $editAddErrorElement = "";
    private $editAddSuccess = false;


    private $userGroup; // \model\UserGroup | null
    private $access;    // \model\UserGroupUserAccess | null

    private $navigationView;

    public function __construct(\model\UserGroup $userGroup = null, \model\UserGroupUserAccess $access = null) {
        $this->userGroup = $userGroup;
        $this->access = $access;

        $this->navigationView = new NavigationView();
    }

    public function response() {

        if ($this->userGroup != null &&
            $this->access != null &&
            $this->access->isCreatorOrMember()) {

            if ($this->addMemberSuccess ||
                $this->deleteSuccess) {

                $this->redirect();
            }
            else if ($this->editAddSuccess) {

                //redirect back to view page for user group
                $url = $this->navigationView->getURLToUserGroupPage($this->userGroup);
                $this->redirect($url);
            }

            //if on edit page
            if ($this->navigationView->userGroupPageEditDetails()) {

                if ($this->access->isCreatorOrMember()) {
                    return $this->generateEditAddHtml(true);
                }
            }
            else { //view page
                return $this->generateViewHtml();
            }
        }
        else if ($this->navigationView->userGroupPageAdd()) { // add page
            return $this->generateEditAddHtml(false);
        }
        else if ($this->userGroup == null) { //unknown
            return $this->generateUnknownHtml('user group');
        }

        //if no return has been done, it means user has no access to this user group
        return $this->generateNoAccessHtml('user group');
    }


    public function wantToSearchMember() {
        return isset($_POST[self::$searchMember]);
    }
    public function wantToSkipFoundMember() {
        return isset($_POST[self::$skipFoundMember]);
    }
    public function wantToAddMember() {
        return isset($_POST[self::$addMember]);
    }
    public function wantToRemoveMember() {
        return isset($_POST[self::$removeMember]);
    }
    public function wantToEditUserGroup() {
        return isset($_POST[self::$editUserGroup]);
    }
    public function wantToAddUserGroup() {
        return isset($_POST[self::$addUserGroup]);
    }

    public function setSearchMemberSuccess(\model\User $foundUser) {
        $this->searchMemberSuccess = true;
        $_SESSION[self::$sessionFoundUser] = $foundUser;
    }
    public function setSearchMemberFailureGeneral() {
        $this->searchMemberErrorMessage = "Something went wrong when searching user";
    }
    public function setSearchMemberFailureUserNotFound() {
        $this->searchMemberErrorMessage = "User could not be found";
    }
    public function setSearchMemberFailureAlreadyMember() {
        $this->searchMemberErrorMessage = "User is already a member of this user group";
    }
    public function setSearchMemberFailureIsTheCreator() {
        $this->searchMemberErrorMessage = "User is the creator of the group and therefore already is a member";
    }
    public function removeFoundMember() {
        unset($_SESSION[self::$sessionFoundUser]);
        $this->searchMemberSuccess = false;
    }
    public function setAddMemberSuccess() {
        $this->removeFoundMember();
        $this->addMemberSuccess = true;
    }
    public function setAddMemberFailure() {
        $this->addMemberErrorMessage = "Something went wrong when adding user";
    }

    public function setAddUserGroupFailed() {
        $this->addMemberErrorMessage = "Something went wrong when saving.";
    }
    public function setAddUserGroupSucceeded() {
        $this->addMemberSuccess = true;
    }

    public function setEditAddDetailsFailed() {
        $this->editAddErrorMessage = "Something went wrong when saving user group details.";
        $this->editAddErrorElement = "Unknown";
    }
    public function setEditUserGroupDetailsSucceeded() {
        $this->editAddSuccess = true;
    }
    public function setAddUserGroupDetailsSucceeded(\model\UserGroup $createdUserGroup, \model\UserGroupUserAccess $accessForCreatedUserGroup) {
        assert($createdUserGroup != null);
        assert($accessForCreatedUserGroup != null);

        $this->userGroup = $createdUserGroup;
        $this->access = $accessForCreatedUserGroup;
        $this->editAddSuccess = true;
    }

    public function setRemoveMemberFailed() {
        $this->deleteErrorMessage = "Something went wrong when removing member.";
        $this->deleteError = true;
    }
    public function setRemoveMemberSucceeded() {
        $this->deleteSuccess = true;
    }

    private function getEditAddTitle() {
        if (isset($_POST[self::$editAddTitle])) {
            return $_POST[self::$editAddTitle];
        }
        return null;
    }

    public function getEditAddCredentials() {
        assert($this->wantToAddUserGroup() || $this->wantToEditUserGroup());

        $editNotAdd = ($this->wantToEditUserGroup());
        try {
            if ($editNotAdd) {
                return new \model\UserGroupEditCredentials($this->userGroup,
                            $this->getEditAddTitle());
            }
            else {
                return new \model\UserGroupAddCredentials($this->getEditAddTitle());
            }
        }
        catch (\model\UserGroupNameException $e) {
            $this->editAddErrorMessage = 'Title needs to be filled in correctly';
            $this->editAddErrorElement = self::$editAddTitle;
        }
        catch (\Exception $e) {
           $this->editAddErrorMessage = 'Some details was not filled in correctly';
            $this->editAddErrorElement = 'Unknown';
        }
        return null;
    }

    public function getSearchMember() {
        $value = $this->getSearchMemberUsername();
        if ($value == null || $value == "") {
            $this->searchMemberErrorMessage = "Fill in a username to search for";
        }
        return $value;
    }
    private function getSearchMemberUsername() {
        if (isset($_POST[self::$searchMemberUsername])) {
            return $_POST[self::$searchMemberUsername];
        }
        return null;
    }

    private function getFoundUser() {
        if (isset($_SESSION[self::$sessionFoundUser])) {
            return $_SESSION[self::$sessionFoundUser];
        }
        return null;
    }
    public function getAddMember() {
        assert($this->wantToAddMember());

        return $this->getFoundUser();
    }

    private function getRemoveMemberId() {
        if (isset($_POST[self::$removeMember])) {
            return $_POST[self::$removeMember];
        }
        return null;
    }
    public function getRemoveMemberUser() {
        assert($this->wantToRemoveMember());

        $userId = $this->getRemoveMemberId();
        $member = $this->userGroup->findMember($userId);

        if ($member == null) {
            $this->setRemoveMemberFailed();
        }
        return $member;
    }


    private function generateViewHtml() {
        $edit1_add2_view3 = 3; //view
        $html = $this->generateHeader($edit1_add2_view3);
        $html .= $this->generateBody($edit1_add2_view3);
        $html .= $this->generateAddPanel();
        $html .= $this->generateStatusPanel();

        return $html;
    }

    private function generateEditAddHtml($editNotAdd) {
        $edit1_add2_view3 = ($editNotAdd ? 1 : 2);
        $html = $this->generateHeader($edit1_add2_view3);
        $html .= $this->generateBody($edit1_add2_view3);

        return $html;
    }

    private function generateHeader($edit1_add2_view3) {
        assert($edit1_add2_view3 == 1 || $edit1_add2_view3 == 2 || $edit1_add2_view3 == 3);

        $html = '<header class="panel-heading">';

        if ($edit1_add2_view3 == 1 ||
            $edit1_add2_view3 == 2) { //edit or add page

            $editNotAdd = ($edit1_add2_view3 == 1);
            if ($editNotAdd) {
                $title = 'Edit user group "' . $this->userGroup->getName() . '"';
            }
            else {
                $title = 'Create new user group';
            }
            $html .= '<h1>' . $title . '</h1>';
        }
        else if ($edit1_add2_view3 == 3) { //view page
            if ($this->access->isCreatorOrMember()) {
                $html .= '<a href="' . $this->getUrl(true) . '" class="pull-top-right">
                                <button type="button" class="btn btn-info btn-sm" title="Edit user group details">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </button>
                            </a>';
            }

            $html .= '<h1><span class="glyphicon glyphicon-link"></span> ' . $this->userGroup->getName() . '</h1>
                      ' . $this->generateEntryInfo();
        }

        $html .= '</header>';
        return $html;
    }

    private function generateEntryInfo($withHr = false) {
        return '    ' . ($withHr ? '<hr>' : '') . '
                    <p>
                        Created by
                        <a href="' . $this->navigationView->getURLToUserWithId($this->userGroup->getUserId()) . '">' . $this->userGroup->getUser()->getUserName() . '</a>
                        on ' . $this->formatDateTimeToReadableDate($this->userGroup->getCreatedAt()) . '
                    </p>';
    }

    private function generateBody($edit1_add2_view3) {
        assert($edit1_add2_view3 == 1 || $edit1_add2_view3 == 2 || $edit1_add2_view3 == 3);

        $html = '<div class="panel-body">';

        if ($edit1_add2_view3 == 1 ||
            $edit1_add2_view3 == 2) { //edit or add page

            $editNotAdd = ($edit1_add2_view3 == 1);
            $html .= $this->generateEditAddBody($editNotAdd);
        }
        else if ($edit1_add2_view3 == 3) { //view page
            $html .= $this->generateViewBody();
        }

        $html .= '</div>';
        return $html;
    }

    private function generateEditAddBody($editNotAdd) {

        $title = $this->getEditAddTitle();
        if ($title === null && $editNotAdd) {
            $title = $this->userGroup->getName();
        }
        if ($editNotAdd) {
            $backUrl = $this->getUrl(false);
        }
        else {
            $backUrl = $this->navigationView->getURLToHomePage();
        }

        $html = '<div class="main-content">
                  <form method="post">

                    <div class="content-main">
                        <label class="text-danger control-label">' . $this->editAddErrorMessage . '</label>
                        <div class="form-group' . ($this->editAddErrorElement == self::$editAddTitle ? ' has-error' : '') . '">
                            <label>Title:</label>
                            <input type="text" name="' . self::$editAddTitle . '" class="form-control" placeholder="Title" value="' . $title . '">
                        </div>
                        ' . ($editNotAdd ?
                            $this->generateEntryInfo(true)
                        : '') . '
                    </div>

                    <div class="form-group form-inline save-panel">
                        <input type="submit" class="btn btn-success form-control"
                                value="' . ($editNotAdd ? 'Save changes' : 'Add user group') . '"
                                name="' . ($editNotAdd ? self::$editUserGroup : self::$addUserGroup) . '">
                        <a href="' . $backUrl . '">
                            <button type="button" class="btn btn-link">Back</button>
                        </a>
                    </div>

                 </form>
               </div>';

        return $html;
    }

    private function generateViewBody() {
        $html = '<div class="main-content">
                  <form method="post">';

        $html .= $this->generateMemberList();

        if ($this->deleteErrorMessage != "") {
            $html .= '<div class="form-group form-inline">
                        <label class="text-danger control-label">' . $this->deleteErrorMessage . '</label>
                      </div>';
        }

        $html .= '
                </form>
               </div>';

        return $html;
    }
    private function generateMemberList() {
        $html = '';

        if (count($this->userGroup->getMembers())) {

            $html .= '<ul class="list-unstyled ui-sortable task-list">';
            foreach ($this->userGroup->getMembers() as $member) {

                $isMyself = ($member->getId() == $this->access->getUserId());
                $html .= '<li>
                            ' . $this->generateUserWithDateInfoIcon($member->getId(), $member->getUsername(), $isMyself, null, !$isMyself, '') . '
                              <div class="task-title">
                                  <span class="task-title-sp">' . $member->getUsername() . '</span>
                                  <span class="task-description">
                                    <span title="' . $this->formatDateTimeToReadableDate($member->getAddedToGroupAt()) . '">
                                        added ' . $this->getDateReadableDayString($member->getAddedToGroupAt()) . '
                                    </span>
                                  </span>
                                  ';

                if ($this->access->isCreatorOrMember()) {
                    $html .= '    <div class="pull-right task-buttons">
                                    <button type="submit"
                                            name="' . self::$removeMember . '"
                                            value="' . $member->getId() . '"
                                            class="btn btn-danger btn-xs"
                                            title="Remove member from group">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </button>
                                  </div>';
                }

                $html .= '
                          </div>
                        </li>';
            }
            $html .= '</ul>';
        }
        else {
            $html .= '<span class="no-entries">No members yet</span>';
        }

        return $html;
    }

    private function generateAddPanel() {
        $html = '';

        if ($this->access->isCreatorOrMember()) {
            $html = '<div class="panel-add bg-success">
                      <form method="post">
                        <div  class="row">';

            $foundUser = $this->getFoundUser();
            if ($this->searchMemberSuccess != true || $foundUser == null) {
                $html .= '
                        ' . ($this->searchMemberErrorMessage != "" || $this->addMemberErrorMessage != "" ? '
                        <div class="has-error col-sm-12">
                            <label class="control-label">' . ($this->searchMemberErrorMessage != "" ? $this->searchMemberErrorMessage : $this->addMemberErrorMessage) . '</label>
                        </div>
                      </div>
                      <div class="row">' : '') . '
                        <div class="col-sm-5 col-md-4 form-group' . ($this->searchMemberErrorMessage != "" ? ' has-error' : '') . '">
                            <label class="sr-only" for="' . self::$searchMemberUsername . '">Add a member (searh by username)</label>
                            <input type="text" class="form-control" placeholder="Add a member (search by username)" name="' . self::$searchMemberUsername . '">
                        </div>
                        <div class="col-sm-3 col-md-2">
                            <button type="submit" class="btn btn-success form-control" name="' . self::$searchMember . '">
                                Search
                            </button>
                        </div>';
            }
            else {
                $html .= '
                        ' . ($this->addMemberErrorMessage != "" ? '
                        <div class="has-error col-sm-12">
                            <label class="control-label">' . $this->addMemberErrorMessage . '</label>
                        </div>
                      </div>
                      <div class="row">' : '') . '
                        <div class="col-sm-12 form-group">
                            <div class="form-inline">
                                ' . $this->generateUserWithDateInfoIcon($foundUser->getId(), $foundUser->getUsername(), false, null, false, '') . '
                                <span>' . $foundUser->getUsername() . '</span>

                                <button type="submit" class="btn btn-success form-control" name="' . self::$addMember . '">
                                    Add user
                                </button>

                                <button type="submit" class="btn btn-default form-control" name="' . self::$skipFoundMember . '">
                                    Skip
                                </button>
                            </div>
                        </div>';
            }

            $html .= '</div>
                     </form>
                     <div class="clearfix visible-xs"></div>
                    </div>';
        }

        return $html;
    }
    private function generateStatusPanel() {
        $html = '';

        //generate this panel only if there are any members
        $memberCount = count($this->userGroup->getMembers());
        if ($memberCount > 0) {

            $html .= '<div class="panel-status">
                          <div class="bottom-info">
                               <strong>' . $memberCount . '</strong> member' . ($memberCount != 1 ? 's' : '') . '
                          </div>
                     </div>';
        }

        return $html;
    }


    public function responseBreadcrumbSubItems() {
        $breadcrumbItems = array();
        $breadcrumbItems[] = new BreadcrumbItem('User groups', $this->navigationView->getURLToUserGroups(), false);
        $userGroupName = ($this->userGroup != null && $this->access != null && $this->access->isCreatorOrMember() ? $this->userGroup->getName() : ($this->navigationView->userGroupPageAdd() ? 'Add user group' : ($this->userGroup == null ? 'Unknown page' : 'Unknown user group')));
        $breadcrumbItems[] = new BreadcrumbItem($userGroupName, '', true);

        return $breadcrumbItems;
    }

    private function getUrl($editDetails = false) {
        return $this->navigationView->getURLToUserGroupPage($this->userGroup, $editDetails);
    }
}