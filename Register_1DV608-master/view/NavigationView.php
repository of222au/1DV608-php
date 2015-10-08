<?php

namespace view;

class NavigationView {

    private static $registerUrlId = 'register';

    public function onRegisterPage() {
        return isset($_GET[self::$registerUrlId]);
    }

    public function getLinkToLogin() {
        return "<a href='?'>Back to login</a>";
    }
    public function getLinkToRegister() {
        return "<a href='?" . self::$registerUrlId . "'>Register a new user</a>";
    }
}