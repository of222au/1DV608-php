<?php

namespace model;

/**
 * Interface Entry
 * the general interface the different entries (like checklist) implements
 * @package model
 */
interface Entry {

    public function getEntryType();
    public function getId();
    public function getUserId();
}