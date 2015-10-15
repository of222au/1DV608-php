<?php

namespace view;

class EntryAccessView {

    private $model;

    public function __construct(\model\EntryAccess $access) {
        $this->model = $access;
    }

    public function reponse() {

        $userGroupsAccess = $this->model->getUserGroupsAccess();

        $html = '<div class="entry-shared-with small">
                    Shared with: ';

        if (count($userGroupsAccess)) {
            $html .= '<ul>';
            foreach($userGroupsAccess as $item) {
                $html .= '<li>' .
                                '<button type="button" class="btn btn-info btn-sm">
                                  <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                  ' . $item->getUserGroupName() . '
                                </button>' .
                        '</li>';
            }
            $html .= '</ul>';
        }
        else {
            $html .= 'None';
        }
        $html .= '
                </div>';

        return $html;
    }

}
