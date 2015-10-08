<?php

class Settings {

    /**
     * The app session name allows different apps on the same server to share a virtual session
     */
    const APP_SESSION_NAME = "MyLoginApp";

    /**
     * Path to folder writable by www-data but not accessable by webserver
     */
    const DATA_PATH = "../../data/";

    /**
     * Salt for creating temporary passwords
     * Should be a random string like "feje3-#GS"
     */
    const SALT = "fgdfla";

    /**
     * The time in seconds to accept login by temp credentials (cookies)
     */
    const TEMP_CREDENTIALS_REMEMBER_TIME = 2592000; // 60*60*24*30 = 2592000 seconds = 30 days;

    /**
     * Show errors
     * boolean true | false
     */
    const DISPLAY_ERRORS = true;

    /**
     * Username and password minimum character length
     */
    const USERNAME_MIN_LENGTH = 3;
    const PASSWORD_MIN_LENGTH = 6;

    /**
     * Database credentials
     */
    const DATABASE_SERVER = "mysql513.loopia.se";
    const DATABASE_USER = "php@s132052";
    const DATABASE_PASSWORD = "php_kursen_1";
    const DATABASE_NAME = "subleem_se";


}