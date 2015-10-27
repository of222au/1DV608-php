<?php

namespace controller;

require_once("model/Tests/TestResult.php");
require_once("model/Tests/TestsModel.php");
require_once("view/TestsView.php");

/**
 * Class TestsController
 * a controller for the automatic tests
 * @package controller
 */
class TestsController implements SubController {

    private $view;
    private $model;

    public function __construct(\mysqli $database, $isLoggedIn) {
        $this->model = new \model\TestsModel($database);
        $this->view = new \view\TestsView($this->model, $isLoggedIn);
    }

    public function doControl() {

        if ($this->view->wantToRunTests()) {
            $this->model->runTests();
        }
    }

    public function getView() {
        return $this->view;
    }

}
