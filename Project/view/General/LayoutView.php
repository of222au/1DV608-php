<?php

namespace view;

require_once('view/General/NavigationBarView.php');
require_once('view/General/NavigationView.php');
require_once('view/General/DateTimeView.php');
require_once('view/General/CookieStorage.php');

class LayoutView {

  public function render($isLoggedIn, $viewResponse, DateTimeView $dtv, \model\User $loggedInUser = null) {

    $navigationView = new \view\NavigationView();
    $navigationBarView = new \view\NavigationBarView();

    $navigationLink = '';
    if ($navigationView->onRegisterPage()) { //register page
      $navigationLink = $navigationView->getLinkToLogin();
    }
    else { //login page
      $navigationLink = $navigationView->getLinkToRegister();
    }

    //to solve problem with special characters (like åäö) not showing correctly
    header('Content-Type: text/html; charset=ISO-8859-1');

    echo '<!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">

          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
          <link rel="stylesheet" href="styles.css">

          <title>Login Example</title>
        </head>
        <body>
          ' . $navigationBarView->response($isLoggedIn, $loggedInUser);
    /*
          <h1>Assignment 2</h1>
          ' . $navigationLink . '
          ' . $this->renderIsLoggedIn($isLoggedIn) . '
        */
    echo '<div class="container">
              ' . $viewResponse;

    /*
              ' . $dtv->show() . '
    */
    echo '</div>
         </body>
      </html>
    ';
  }

  private function renderIsLoggedIn($isLoggedIn) {
    if ($isLoggedIn) {
      return '<h2>Logged in</h2>';
    }
    else {
      return '<h2>Not logged in</h2>';
    }
  }
}
