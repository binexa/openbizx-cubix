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
 * @version   $Id: MyDashboardForm.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */

use Openbiz\Openbiz;
use Openbiz\Easy\EasyForm;

class MyDashboardForm extends EasyForm
{
	public function ConfigWidget($id=null){
		if(!$id)
		{
			if ($id==null || $id=='')
            $id = Openbiz::$app->getClientProxy()->getFormInputs('_selectedId');
		}
		$dataRec = $this->getDataObj()->fetchById($id);
		$widget = $dataRec['widget'];
		
		$widgetObj = Openbiz::getObject($widget);
		if($widgetObj->configable)
		{
			$configForm = $widgetObj->configForm;
			$this->switchForm($configForm);	
		}		
	}
	
}
