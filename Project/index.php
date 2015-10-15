<?php

require_once('Settings.php');
require_once('controller/MasterController.php');
require_once('view/General/DateTimeView.php');
require_once('view/General/LayoutView.php');

if (Settings::DISPLAY_ERRORS) {
    //show errors
    error_reporting(-1);
    ini_set('display_errors', 'ON');
}

//make sure session is started
session_start();

//handle input
$c = new \controller\MasterController();
$c->handleInput();

//generate view
$v = $c->generateOutput();

$user = $c->getLoggedInUser();

//render output
$dtv = new \view\DateTimeView();
$lyv = new \view\LayoutView();
$lyv->render($c->isLoggedIn(), $v->response(), $dtv, $user);
