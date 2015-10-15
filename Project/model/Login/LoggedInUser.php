<?php

namespace model;

class LoggedInUser {

    private $userName;
    private $client;

    public function __construct(LoginCredentials $loginCredentials) {
        $this->userName = $loginCredentials->getUserName();
        $this->client = $loginCredentials->getClient();
    }

    public function getUserName() {
        return $this->userName;
    }

    /**
     * Checks if the user client matches
     * @param UserClient $userClient
     * @return bool
     */
    public function isSameAsLastTime(UserClient $userClient) {
        return $userClient->isSame($this->client);
    }


}