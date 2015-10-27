<?php

namespace view;

/**
 * Class BreadcrumbItem
 * used by view to output breadcrumb items in the top of the page
 * @package view
 */
class BreadcrumbItem {

    private $name;
    private $url;
    private $isActive;

    public function __construct($name, $url, $isActive) {
        assert(is_string($name) && is_string($url) && is_bool($isActive));

        $this->name = $name;
        $this->url = $url;
        $this->isActive = $isActive;
    }

    public function response() {
        return '<li' . ($this->isActive ? ' class="active"' : '') . '>
                    ' . (!$this->isActive ? '<a href="' . $this->url . '">' . $this->name . '</a>' : $this->name) . '
                </li>';
    }
}