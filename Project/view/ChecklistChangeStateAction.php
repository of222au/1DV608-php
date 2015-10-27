<?php

namespace view;

abstract class ChecklistChangeStateAction {

    const ArchiveItem = 1;
    const DeArchiveItem = 2;
    const ChangedItems = 3;
    const CheckAllItems = 10;
    const UnCheckAllItems = 11;
    const ArchiveAllCheckedItems = 12;

}
