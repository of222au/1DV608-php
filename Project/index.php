<?php

require_once('Settings.php');
require_once('controller/MasterController.php');
require_once('view/General/LayoutView.php');

if (\Settings::DEBUG_MODE) {
    //show errors
    error_reporting(-1);
    ini_set('display_errors', 'ON');
}

//make sure session is started
session_start();

//to solve problem with special characters (like åäö) not showing correctly
header('Content-Type: text/html; charset=ISO-8859-1');


//setlocale(LC_ALL, \Settings::LOCALE);

//handle input
$c = new \controller\MasterController();
$c->handleInput();

//generate view
echo $c->generateOutput();

