<?php

namespace view;

/**
 * Class UnknownView
 * simple view to use when an unknown page
 * @package view
 */
class UnknownView extends GeneralView implements PageView {

    public function response() {
        return $this->generateUnknownHtml('page');
    }

    public function responseBreadcrumbSubItems() {
        return array(new BreadcrumbItem('Unknown page', '', true));
    }

}
