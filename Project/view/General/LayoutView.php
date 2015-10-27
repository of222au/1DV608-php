<?php

namespace view;

require_once('view/General/NavigationBarView.php');
require_once('view/General/NavigationView.php');
require_once('view/General/CookieStorage.php');

/**
 * Class LayoutView
 * the main view that handles the basic surrounding output (like html and body tags)
 * @package view
 */
class LayoutView {

  private $navigationBarView;

  public function __construct(NavigationBarView $navigationBarView) {
    $this->navigationBarView = $navigationBarView;
  }

  public function render($pageViewResponse, $pageViewBreadcrumbItems) {
    $navigationView = new \view\NavigationView();

    echo '<!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">

          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
          <link rel="stylesheet" href="styles.css">

          <title>Project - Checklists</title>
        </head>
        <body>
          ' . $this->navigationBarView->response() . '
          ' . $this->generateMainBodySurroundingBeginningTags() . '

            <ol class="breadcrumb">
                <li><a href="'. $navigationView->getURLToHomePage() . '">Home</a></li>
                ' . $pageViewBreadcrumbItems . '
            </ol>';

    echo $pageViewResponse;

    echo '

          ' . $this->generateMainBodySurroundingEndingTags() . '
         </body>
      </html>
    ';
  }

  private function generateMainBodySurroundingBeginningTags() {
    return '<div class="tab-content">
              <div class="container">
                <div class="row">
                    <div class="col-md-12">
                     <section class="panel">';
  }
  private function generateMainBodySurroundingEndingTags() {
    return '    </section>
               </div>
              </div>
             </div>
            </div>';
  }

}
