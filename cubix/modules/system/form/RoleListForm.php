<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: RoleListForm.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

use Openbiz\Openbiz;
use Openbiz\Easy\EasyForm;

class RoleListForm extends  EasyForm
{
	public function SetPermission()
	{		
        $id = Openbiz::$app->getClientProxy()->getFormInputs('_selectedId');
		$redirectPage = OPENBIZ_APP_INDEX_URL."/system/role_detail/".$id;
		Openbiz::$app->getClientProxy()->ReDirectPage($redirectPage);
	}
	
}
