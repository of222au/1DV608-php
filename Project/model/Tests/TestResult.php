<?php

namespace model;

class TestResult {

    private $testName;
    private $result;
    private $timeElapsed;
    private $errorMessage;

    public function __construct($testName, $result, $timeElapsed, $errorMessage = '') {
        assert(is_string($testName) && strlen($testName) > 0);
        assert(is_bool($result));
        assert(is_numeric($timeElapsed));
        assert(is_string($errorMessage));

        $this->testName = $testName;
        $this->result = $result;
        $this->timeElapsed = $timeElapsed;
        $this->errorMessage = $errorMessage;
    }

    public function getTestName() {
        return $this->testName;
    }
    public function getTimeElapsed() {
        return $this->timeElapsed;
    }
    public function getErrorMessage() {
        return $this->errorMessage;
    }
    public function getWasSuccess() {
        return ($this->result == true);
    }
}
