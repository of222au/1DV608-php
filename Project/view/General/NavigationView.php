<?php

namespace view;

class NavigationView {

    private static $urlIdRegisterUrlId = 'register';
    private static $urlIdLogin = 'login';
    private static $urlIdGalleries = 'galleries';
    private static $urlIdGallery = 'gallery';
    private static $urlIdChecklist = 'checklist';

    public function onHomePage() {
        return !count($_GET);
    }
    public function onRegisterPage() {
        return isset($_GET[self::$urlIdRegisterUrlId]);
    }
    public function onLoginPage() {
        return isset($_GET[self::$urlIdLogin]);
    }
    public function onGalleriesPage() {
        return isset($_GET[self::$urlIdGalleries]);
    }
    public function onGalleryPage() {
        return isset($_GET[self::$urlIdGallery]);
    }
    public function onChecklistPage() {
        return isset($_GET[self::$urlIdChecklist]);
    }

    public function getGalleryId() {
        if ($this->onGalleryPage()) {
            return $_GET[self::$urlIdGallery];
        }
        else {
            return null;
        }
    }
    public function getChecklistId() {
        if ($this->onChecklistPage()) {
            return $_GET[self::$urlIdChecklist];
        }
        else {
            return null;
        }
    }

    public function getLinkToLogin() {
        return "<a href='?" . self::$urlIdLogin . "'>Back to login</a>";
    }
    public function getLinkToRegister() {
        return "<a href='?" . self::$urlIdRegisterUrlId . "'>Register a new user</a>";
    }


    public function redirectToLoginPage() {

        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . self::$urlIdLogin);
    }
}