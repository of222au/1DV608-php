<?php

namespace model;

class UserClient {

    private $remoteAddr;
    private $userAgent;

    public function __construct($remoteAddress, $userAgent) {
        $this->remoteAddr = $remoteAddress;
        $this->userAgent = $userAgent;
    }

    /**
     * checks if supplied UserClient is the same as this one
     * @param UserClient $uc
     * @return bool
     */
    public function isSame(UserClient $uc) {
        return ($uc->getRemoteAddr() == $this->remoteAddr && $uc->getUserAgent() == $this->userAgent);
    }

    public function getRemoteAddr() {
        return $this->remoteAddr;
    }
    public function getUserAgent() {
        return $this->userAgent;
    }
}
