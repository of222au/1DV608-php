<?php

namespace view;

require_once('view/NavigationView.php');
require_once('view/DateTimeView.php');

class LayoutView {

  public function render($isLoggedIn, $viewResponse, DateTimeView $dtv) {

    $navigationView = new \view\NavigationView();

    $navigationLink = '';
    if ($navigationView->onRegisterPage()) { //register page
      $navigationLink = $navigationView->getLinkToLogin();
    }
    else { //login page
      $navigationLink = $navigationView->getLinkToRegister();
    }

    echo '<!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>Login Example</title>
        </head>
        <body>
          <h1>Assignment 2</h1>
          ' . $navigationLink . '
          ' . $this->renderIsLoggedIn($isLoggedIn) . '
          
          <div class="container">
              ' . $viewResponse . '
              
              ' . $dtv->show() . '
          </div>
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
