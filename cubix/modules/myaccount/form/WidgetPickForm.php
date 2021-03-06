<?php

/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: WidgetPickForm.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */
use Openbiz\Openbiz;
use Openbiz\Easy\PickerForm;

class WidgetPickForm extends PickerForm
{

    public function PicktoParent()
    {
        if ($id == null || $id == '')
            $id = Openbiz::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbiz::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;

        foreach ($selIds as $id) {
            $rec = $this->getDataObj()->fetchById($id);
            $parentForm = Openbiz::getObject($this->parentFormName);
            $parentDo = $parentForm->getDataObj();
            $user_id = Openbiz::$app->getUserProfile("Id");

            $newRec = array(
                "user_id" => $user_id,
                "widget" => $rec['name'],
                "ordering" => 10,
                "status" => 1
            );
            $parentDo->insertRecord($newRec);
        }



        $this->close();
        $parentForm->rerender();
    }

}
