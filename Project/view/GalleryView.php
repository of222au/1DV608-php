<?php

namespace view;

class GalleryView {

    private $gallery;
    private $access;

    public function __construct(\model\Gallery $model = null, \model\EntryAccess $access = null) {
        $this->gallery = $model;
        $this->access = $access;
    }

    public function response() {

        if ($this->gallery != null && $this->access != null &&
            $this->access->canRead()) {

            return $this->generateGalleryHtml();
        }
        else if ($this->gallery == null) {
            return $this->generateUnknownGalleryHtml();
        }
        else {
            return $this->generateNoAccessHtml();
        }
    }

    private function generateNoAccessHtml() {
        return '<p>You don\'t have access to this gallery.</p>';
    }
    private function generateUnknownGalleryHtml() {
        return '<p>Unknown gallery</p>';
    }
    private function generateGalleryHtml() {

        return '<h2>Gallery "' . $this->gallery->getTitle() . '"</h2>
                <p>' . $this->gallery->getDescription() . '</p>
                <p>Can write:' . $this->access->canWrite() . '<br>
                   Can read:' . $this->access->canRead() . '</p>';


    }
}