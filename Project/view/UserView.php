<?php

namespace view;

/**
 * Class UserView
 * view for a user
 * @package view
 */
class UserView extends GeneralView implements PageView {

    private $user; // \model\User | null

    private $navigationView;

    public function __construct(\model\User $user = null) {
        $this->user = $user;

        $this->navigationView = new NavigationView();
    }

    public function response() {

        if ($this->user != null) {
            return $this->generateViewHtml();
        }
        else { //unknown
            return $this->generateUnknownHtml('user');
        }
    }

    private function generateViewHtml() {
        $html = $this->generateHeader();
        $html .= $this->generateBody();

        return $html;
    }

    private function generateHeader() {

        $html = '<header class="panel-heading">
                    <h1><span class="glyphicon glyphicon-user"></span> ' . $this->user->getUsername() . '</h1>
                      ' . $this->generateEntryInfo();
        $html .= '</header>';
        return $html;
    }

    private function generateEntryInfo() {
        return '<p>
                    Created on ' . $this->formatDateTimeToReadableDate($this->user->getCreatedAt()) . '
                </p>';
    }

    private function generateBody() {
        $html = '<div class="panel-body">';
        $html .= $this->generateViewBody();
        $html .= '</div>';

        return $html;
    }

    private function generateViewBody() {
        $html = '<div class="main-content">';
        $html .= '</div>';

        return $html;
    }

    public function responseBreadcrumbSubItems() {
        $breadcrumbItems = array();
        $breadcrumbItems[] = new BreadcrumbItem('User groups', $this->navigationView->getURLToUserGroups(), false);
        $breadcrumbItems[] = new BreadcrumbItem($this->user->getUsername(), '', true);
        return $breadcrumbItems;
    }

}