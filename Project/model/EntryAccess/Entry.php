<?php

namespace model;

interface Entry {

    public function getEntryType();
    public function getId();
    public function getUserId();
}