<?php

namespace model;

class UserGroupNameException extends \Exception {};

class UserGroupBase {

    private $name;

    public function __construct($name) {
        assert(is_string($name));
        if (strlen($name) < 1) {
            throw new UserGroupNameException();
        }

        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
