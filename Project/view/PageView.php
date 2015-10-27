<?php

namespace view;

/**
 * Interface PageView
 * @package view
 */
interface PageView {

    /**
     * @return string
     */
    public function response();

    /**
     * @return array of BreadcrumbItem
     */
    public function responseBreadcrumbSubItems();

}