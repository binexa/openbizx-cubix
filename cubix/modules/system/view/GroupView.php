<?php

use Openbiz\Openbiz;
use Openbiz\Easy\WebPage;

class GroupView extends WebPage
{

    const MIN_GROUP_COUNT = 3;

    protected $groupDO = "system.do.GroupDO";

    protected function isNeedInitialize()
    {
        if ($this->objectName == 'system.view.InitializeGroupView') {
            return false;
        }
        $group_init_lock = OPENBIZ_APP_FILE_PATH . DIRECTORY_SEPARATOR . 'initialize_group.lock';
        if (is_file($group_init_lock)) {
            return false;
        }
        $do = Openbiz::getObject($this->groupDO);
        $groupList = $do->directFetch();
        if ($groupList->count() > self::MIN_GROUP_COUNT) {
            return false;
        }
        return true;
    }

    public function allowAccess($access = null)
    {
        if ($this->isNeedInitialize()) {
            Openbiz::$app->getSessionContext()->setVar("_GROUP_INITIALIZE_LASTVIEW", $_SERVER['REQUEST_URI']);
            Openbiz::$app->getClientProxy()->redirectPage(OPENBIZ_APP_INDEX_URL . '/system/initialize_group');
        }
        return parent::allowAccess($access);
    }

    public function getLastViewURL()
    {
        return Openbiz::$app->getSessionContext()->getVar("_GROUP_INITIALIZE_LASTVIEW");
    }

}
