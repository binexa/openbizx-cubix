<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: UserLoadHandler.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

use Openbiz\Openbiz;

include_once (Openbiz::$app->getModulePath()."/system/lib/ModuleLoadHandler.php");

class UserLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    	
    	$roleRec = Openbiz::getObject("system.do.RoleDO")->fetchOne("[name]='Cubi Member'");
    	$roleId = $roleRec['Id'];
    	
    	$actionRec = Openbiz::getObject("system.do.AclActionDO")->fetchOne("[module]='user' AND [resource]='UserAccount' AND [action]='Edit_Own_Account'");
    	$actionId = $actionRec["Id"];
    	
    	$aclRecord = array(
    		"role_id" =>  $roleId,
    		"action_id" => $actionId,
    		"access_level" => 1
    	);
    	Openbiz::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
    }
}

