<?php

namespace view;

/**
 * Class GeneralView
 * some general view functions used by other views (extending this class)
 * @package view
 */
class GeneralView {

    private static $progressPercentWarningMin = 32;
    private static $progressPercentSuccessMin = 100;


    private function generateBasicPage($title, $bodyContents) {
        return '
                <header class="panel-heading">
                    <h1>' . $title . '</h1>
                </header>
                <div class="panel-body">
                    <div class="main-content">
                        ' . $bodyContents . '
                    </div>
                </div>';
    }

    protected function generateNoAccessHtml($typeString) {
        return $this->generateBasicPage('<span class="glyphicon glyphicon-warning-sign"></span> No access',
                                        '<p>You don\'t have access to this ' . $typeString . '</p>');
    }
    protected function generateUnknownHtml($typeString) {
        return $this->generateBasicPage('<span class="glyphicon glyphicon-search"></span> Not sure what you were looking for here...',
                                        '<p>Unknown ' . $typeString . '</p>');
    }

    protected function generateProgressBar($percentDone) {
        return '<div class="progress">
                  <div class="progress-bar ' . ($percentDone >= self::$progressPercentSuccessMin ? 'progress-bar-success' : ($percentDone >= self::$progressPercentWarningMin ? 'progress-bar-warning' : 'progress-bar-danger')) . '" role="progressbar" aria-valuenow="' . $percentDone . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percentDone . '%"
                        title="' . $percentDone . '% of all items completed">
                    <span class="sr-only">' . $percentDone . '% of all items completed</span>
                  </div>
                </div>';
    }

    protected function generateUserWithDateInfoIcon($userId, $userName, $createdByMyself, $date = null, $useLink = true, $titlePreString = 'Created by') {
        $navigationView = new NavigationView();

        $isToday = ($date != null ? $this->checkIfDateIsToday($date) : false);
        if ($date != null) {
            $dateInTitle = $this->getDateLabelString($date, 'today', 'on');
        }
        else {
            $dateInTitle = '';
        }
        $html = '
                    <div class="user-date-label">
                        <span class="user-label label ' . ($isToday ? 'label-warning' : 'label-info') . ' ' . ($createdByMyself ? 'is-myself' : 'is-someone-else') . '"
                              title="' . $titlePreString . ' ' . $userName . ' ' . $dateInTitle . '">
                            ' . ($useLink ? '<a href="' . $navigationView->getURLToUserWithId($userId) . '">' : '') . '
                                <span class="glyphicon glyphicon-user"></span>
                            ' . ($useLink ? '</a>' : '') . '
                        </span>
                    </div>';

        return $html;
    }

    protected function generateDateLabel($date, $showDateString = false) {
        $readableDateString = $this->getDateReadableDayString($date);
        $isThisWeek = $this->checkIfDateIsThisWeek($date);
        $isToday = $this->checkIfDateIsToday($date);
        $dateInTitle = $this->getDateLabelString($date, 'Created today', 'Created on');

        return '<span class="date-label label ' . ($isToday ? 'label-warning label-today' : 'label-default') . ($isThisWeek ? ' label-this-week' : '') . '"
                      title="' . $dateInTitle . '">
                      <span class="glyphicon glyphicon-calendar"></span> ' . ($showDateString ? $readableDateString : '') . '
                </span>';
    }
    private function getDateLabelString($date, $preStringIfToday = 'today', $preStringIfNotToday = 'on') {
        $isToday = $this->checkIfDateIsToday($date);
        if ($isToday) {
            return $preStringIfToday . ' ' . $this->formatDateTimeToReadableTime($date);
        }
        else {
            return $preStringIfNotToday . ' ' . $this->formatDateTimeToReadableDate($date);
        }
    }

    protected function formatDateTimeToReadableDate($datetime, $includeTime = true) {
        $format = "%B %e, %Y at %I:%M %p"; //ex: August 1, 2014 at 9:00 PM
        $format = $this->fixEParameterIfWindows($format);
        return strftime($format, strtotime($datetime));
    }
    protected function formatDateTimeToReadableTime($datetime) {
        $format = "at %I:%M %p"; //ex: at 9:00 PM
        return strftime($format, strtotime($datetime));
    }
    private function fixEParameterIfWindows($format) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }
        return $format;
    }

    protected function getDateReadableDayString($date, $includeYesterday = false, $includeWeekAndMonth = false) {
        if ($this->checkIfDateIsToday($date)) {
            return 'Today';
        }
        else if ($includeYesterday && $this->checkIfDateIsYesterday($date)) {
            return 'Yesterday';
        }
        else
            if ($includeWeekAndMonth) {
                if ($this->checkIfDateIsThisWeek($date)) {
                    return 'This week';
                } else if ($this->checkIfDateIsLastWeek($date)) {
                    return 'Last week';
                } else if ($this->checkIfDateIsThisMonth($date)) {
                    return 'This month';
                } else if ($this->checkIfDateIsLastMonth($date)) {
                    return 'Last month';
                } else {
                    return '';
                }
            }
            else {
                return date('M jS', strtotime($date));
            }
    }
    protected function checkIfDateIsToday($date) {
        return (date('Ymd') == date('Ymd', strtotime($date)));
    }
    protected function checkIfDateIsYesterday($date) {
        return (date('Ymd', (time() - (24 * 60 * 60))) == date('Ymd', strtotime($date)));
    }
    protected function checkIfDateIsThisWeek($date) {
        return $this->checkIfDateIsWithinTwoNamedDates($date, 'monday this week', 'sunday this week');
    }
    protected function checkIfDateIsLastWeek($date) {
        return $this->checkIfDateIsWithinTwoNamedDates($date, 'monday last week', 'sunday last week');
    }
    protected function checkIfDateIsThisMonth($date) {
        return $this->checkIfDateIsWithinTwoNamedDates($date, 'first day of this month', 'last day of this month');
    }
    protected function checkIfDateIsLastMonth($date) {
        return $this->checkIfDateIsWithinTwoNamedDates($date, 'first day of previous month', 'last day of previous month');
    }
    private function checkIfDateIsWithinTwoNamedDates($date, $firstDateString, $lastDateString) {
        $firstDay = date("Ymd", strtotime($firstDateString));
        $lastDay = date("Ymd", strtotime($lastDateString));
        $date = date('Ymd', strtotime($date));
        return ($date >= $firstDay && $date <= $lastDay);
    }

    protected function getPercent($totalCount, $doneCount) {
        if ($totalCount > 0) {
            return round(($doneCount / $totalCount), 2) * 100;
        }

        return $percentDone = 0;
    }

    protected function redirect($url = "") {
        if ($url == "") {
            $url = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; //$_SERVER['REQUEST_URI']);
        }
        header('Location: ' . $url);

        //TODO: fix this problem on mobile devices (at least on iPhone this doesn't work when requesting same url)
    }
}