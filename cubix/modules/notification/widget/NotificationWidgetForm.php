<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.notification.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: NotificationWidgetForm.php 3366 2012-05-31 06:09:02Z rockyswen@gmail.com $
 */

class NotificationWidgetForm extends EasyForm
{
	protected $readAccess;
	
	public function render()
	{
		$this->triggerCheckers();
		
		$result = parent::render();
		if(!$this->totalRecords)
		{
			return "";
		}
		if($this->readAccess){
			if(BizSystem::allowUserAccess($this->readAccess))
			{
				return $result;
			}			
		}else{
			return $result;
		}
	}
	
	public function triggerCheckers()
	{
		$svc = BizSystem::getService("notification.lib.checkerService");
		$svc->checkNotification();
	}
	
	public function fetchDataSet()
	{
		$resultSet = parent::fetchDataSet();
		foreach($resultSet as $key => $record)
		{
			$record['release_date'] = date("Y-m-d",strtotime($record['create_time']));
			$resultSet[$key] = $record;
		}
		$this->readAccess = $resultSet[0]['read_access'];
		return $resultSet;
	}
	
	public function MarkRead($id)
	{
		$rec = $this->getDataObj()->fetchById($id);
		$type		= $rec['type'];
		$goto_url 	= $rec['goto_url'];
		
		if($rec['update_access'])
		{
			if(BizSystem::allowUserAccess($rec['update_access']))
			{				
				$this->getDataObj()->deleteRecords("[type]='$type'");
			}
		}else{
			$this->getDataObj()->deleteRecords("[type]='$type'");
		}
		
		if($goto_url)
		{
			$goto_url = OPENBIZ_APP_INDEX_URL.$goto_url;
			BizSystem::clientProxy()->redirectPage($goto_url);
		}else{
			BizSystem::clientProxy()->updateClientElement($this->objectName,"" );
		}
	}
}
?>