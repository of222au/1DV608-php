<?php

namespace view;

class CookieStorage {

    /**
     * Saves cookie
     * @param $name, String
     * @param $value
     */
    public static function save($name, $value, $expiresInSeconds) {
        setcookie($name, $value, $expiresInSeconds); //set cookie to last 30 days
        $_COOKIE[$name] = $value; //make sure it is put to the cookie array directly as well
    }

    /**
     * Retrieves cookie
     * @param $name, String
     * @return string|null
     */
    public static function load($name) {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }

        return null;
    }

    /**
     * Deletes cookie
     * @param $name, String
     */
    public static function delete($name) {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            setcookie($name, '', -1); //set empty value
        }
    }

}
