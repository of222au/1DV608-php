<?php

namespace view;

class LoginStatePersistor
{
    private static $sessionMessage = 'Session::Message';
    private static $sessionUserName = 'Session::UserName';

    /**
     * Save the username
     * @param $username, String
     */
    public function saveUsername($username) {
        $_SESSION[self::$sessionUserName] = $username;
    }
    /**
     * Save the message to session
     * @param $message, String
     */
    public function saveMessage($message) {
        $_SESSION[self::$sessionMessage] = $message;
    }

    /**
     * Retrieves the username
     * @return string
     */
    public function getUserName() {
        $username = '';
        if (isset($_SESSION[self::$sessionUserName])) {
            $username = $_SESSION[self::$sessionUserName];
            unset($_SESSION[self::$sessionUserName]);
        }
        return $username;
    }
    /**
     * Retrieves the message
     * @return string
     */
    public function getMessage() {

        $message = '';
        if (isset($_SESSION[self::$sessionMessage])) {
            $message = $_SESSION[self::$sessionMessage];
            unset($_SESSION[self::$sessionMessage]);
        }
        return $message;
    }

    /**
     * Clears the username
     */
    public function clearUserName() {
        unset($_SESSION[self::$sessionUserName]);
    }





}