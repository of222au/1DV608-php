<?php

namespace model;

class TempCredentials {

    private $tempPassword;
    private $expire;

    public function __construct() {

        //generate a new random temp password
        $this->tempPassword = sha1(\Settings::SALT . rand() . time());

        //set expire date/time
        $this->expire = time() + \Settings::TEMP_CREDENTIALS_REMEMBER_TIME;
    }

    /**
     * @param $tempPassword
     * @return bool
     */
    public function isValid($tempPassword) {
        return $this->expire > time() && $this->tempPassword === $tempPassword;
    }

    public function getTempPassword() {
        return $this->tempPassword;
    }
    public function getExpire() {
        return $this->expire;
    }

}
