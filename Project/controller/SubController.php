<?php

namespace controller;

/**
 * Interface SubController
 * is used by all sub-controllers
 * @package controller
 */
interface SubController {

    public function doControl();

    public function getView();

}