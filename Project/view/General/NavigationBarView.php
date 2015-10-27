<?php

namespace view;

/**
 * Class NavigationBarView
 * handles output of the navigation bar on top of the page
 * @package view
 */
class NavigationBarView {

    private $loginView;
    private $navigationView;
    private $isLoggedIn;
    private $loggedInUser;

    public function __construct(LoginView $loginView, $isLoggedIn, \model\User $loggedInUser = null) {
        $this->loginView = $loginView;
        $this->navigationView = new NavigationView();
        $this->isLoggedIn = $isLoggedIn;
        $this->loggedInUser = $loggedInUser;
    }

    public function response() {
        $isOnTabHome = false;
        $isOnTabChecklists = false;
        $isOnTabUserGroups = false;
        $isOnTabTests = false;
        if ($this->navigationView->onHomePage()) {
            $isOnTabHome = true;
        }
        else if ($this->navigationView->onChecklistPage() || $this->navigationView->onChecklistsPage()) {
            $isOnTabChecklists = true;
        }
        else if ($this->navigationView->onUserGroupsPage() || $this->navigationView->onUserGroupPage() ||
            $this->navigationView->onUserPage()) {
            $isOnTabUserGroups = true;
        }
        else if ($this->navigationView->onTestsPage()) {
            $isOnTabTests = true;
        }

        //TODO: better mobile menu when javascript is allowed to use..
        $html = '
        <nav class="navbar navbar-default">
          <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="?">Project</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                <ul class="nav navbar-nav navbar-left">
                  <li' . ($isOnTabHome ? ' class="active"' : '') . '><a href="' . $this->navigationView->getURLToHomePage() . '"><span class="glyphicon glyphicon-home"></span> Home</a></li>';

        if ($this->isLoggedIn) {
            $html .= '<li' . ($isOnTabChecklists ? ' class="active"' : '') . '><a href="' . $this->navigationView->getURLToChecklists() . '"><span class="glyphicon glyphicon-edit"></span> Checklists</a></li>
                      <li' . ($isOnTabUserGroups ? ' class="active"' : '') . '><a href="' . $this->navigationView->getURLToUserGroups() . '"><span class="glyphicon glyphicon-link"></span> User groups</a></li>';
        }
        if (\Settings::DEBUG_MODE && $this->isLoggedIn) {
            $html .= '    <li' . ($isOnTabTests ? ' class="active"' : '') . '><a href="' . $this->navigationView->getURLToTestsPage() . '"><span class="glyphicon glyphicon-flash"></span> Tests (debug mode)</a></li>';
        }
        $html .= '
                </ul>
            ' . $this->loginView->generateOutputForNavigationBar($this->loggedInUser) . '
            </div><!-- /.navbar-collapse -->
          </div><!-- /.container-fluid -->
        </nav>';

        return $html;
    }

}
