<?php

namespace view;

/**
 * Class TestsView
 * used to output automatic tests results
 * @package view
 */
class TestsView extends GeneralView implements PageView {

    private static $runTests = 'TestsView::RunTests';

    private $model;
    private $isLoggedIn;

    public function __construct(\model\TestsModel $model, $isLoggedIn) {
        $this->model = $model;
        $this->isLoggedIn = $isLoggedIn;
    }

    public function response() {
        if ($this->isLoggedIn) {
            $html = $this->generateHeader();
            $html .= $this->generateBody();
            return $html;
        }
        else {
            return $this->generateNoAccessHtml('page');
        }
    }

    public function responseBreadcrumbSubItems() {
        return array(new BreadcrumbItem('Tests', '', true));
    }

    public function wantToRunTests() {
        return isset($_POST[self::$runTests]);
    }

    private function generateHeader() {
        return '<header class="panel-heading">
                    <h1>Automatic testing</h1>
                </header>';
    }

    private function generateBody() {
        $html = '<div class="panel-body">
                  <div>
                    <form method="post">
                        <button type="submit" class="btn btn-success"
                                name="' . self::$runTests . '">
                            Run tests
                        </button>
                    </form>
                </div>';

        if ($this->model != null && $this->model->hasTestResults()) {
            $html .= '<h3>Test results:</h3>
                      <p>Total time elapsed: ' . $this->model->getTotalTimeElapsed() . ' seconds</p>';

            foreach ($this->model->getTestResults() as $testResult) {
                $testName = $testResult->getTestName();
                $wasSuccess = $testResult->getWasSuccess();
                $timeElapsed = $testResult->getTimeElapsed();
                $errorMessage = $testResult->getErrorMessage();

                $html .= '<div class="alert ' . ($wasSuccess ? 'alert-success' : 'alert-danger') . '" role="alert">
                                <span class="glyphicon ' . ($wasSuccess ? 'alert-success' : 'alert-danger') . '"></span>
                                <strong>' . $testName . '</strong> ' . $timeElapsed . ' seconds
                                ' . (!$wasSuccess ? '
                                <div class="">
                                ' . ($errorMessage != null && $errorMessage != '' ? $errorMessage : '(no error message..)') . '
                                </div>
                                ' : '') . '
                            </div>';
            }
        }
        $html .= '</div>';

        return $html;
    }


}