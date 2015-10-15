<?php

namespace view;

use model\EntryAccess;

class ChecklistView {

    private $checklist;
    private $access;

    private $entryAccessView;

    public function __construct(\model\Checklist $model = null, \model\EntryAccess $access = null) {
        $this->checklist = $model;
        $this->access = $access;

        $this->entryAccessView = new EntryAccessView($access);
    }

    public function response() {

        if ($this->checklist != null && $this->access != null &&
            $this->access->canRead()) {

            return $this->generateChecklistHtml();
        }
        else if ($this->checklist == null) {
            return $this->generateUnknownChecklistHtml();
        }
        else {
            return $this->generateNoAccessHtml();
        }
    }

    private function generateNoAccessHtml() {
        return '<p>You don\'t have access to this checklist.</p>';
    }
    private function generateUnknownChecklistHtml() {
        return '<p>Unknown checklist</p>';
    }
    private function generateChecklistHtml() {

        $html = '<div class="checklist-container col-md-6">
                    <h2>' . $this->checklist->getTitle() . ':</h2>
                    <p>' . $this->checklist->getDescription() . '</p>
                    ' . $this->entryAccessView->reponse() . '
                     <form method="post" class="form-inline" name="add-item">

                        <input type="text" class="form-control" placeholder="Add to list">
                        <button class="btn btn-success">Add</button>

                      </form>
                    <hr>
                    <ul class="list-unstyled ui-sortable">';

        foreach ($this->checklist->getChecklistItems() as $item) {
            $stateType = $item->getCurrentStateType();
            $checked = ($stateType == \Settings::CHECKLIST_ITEM_STATE_CHECKED);

            $html .= '<li class="' . ($checked ? 'ui-state-done' : 'ui-state-default') . '">
                            <div class="checkbox">
                            ' . ($checked ? '<button class="remove-item btn btn-default btn-xs pull-right"><span class="glyphicon glyphicon-remove"></span></button>' : '') . '
                                <label><input type="checkbox" value="" ' . ($checked ? 'checked="checked" ' : '') . '>
                                ' . ($checked ? '<s>' : ($item->getImportant() ? '<strong>' : '')) . $item->getTitle() . ($checked ? '</s>' : ($item->getImportant() ? '</strong>' : '')) . '</label><br>
                                <small>' . $item->getDescription() . '</small>' . '
                            </div>
                </li>';
        }
        $uncheckedCount = $this->checklist->getUncheckedCount();

        $html .= '</ul>
                  <div class="checklist-footer row">
                    <div class="col-sm-6 bottom-info">
                          <strong><span class="count-checklist-unchecked">' . $uncheckedCount . '</span></strong> Item' . ($uncheckedCount != 1 ? 's' : '') . ' Left
                      </div>
                    <div class="col-sm-6">
                      <form method="post" class="form-inline pull-right" name="mark-all-items">
                            <button class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Mark all as done</button>
                          </form>
                    </div>
                </div>';

        return $html;
    }
}