<?php

use Openbiz\Openbiz;

class PaymentService
{
	protected $providerDO = "payment.provider.do.ProviderDO";
	
	public function goPayment($orderId, $amount, $type, $title=null,$customData=null)
	{		
		$amount = round($amount,2);
		$providerObj = $this->getProviderObj($type);
		$url = $providerObj->GetPaymentURL($orderId, $amount,  $title , $customData);
		if($url)
		{			
			Openbiz::$app->getClientProxy()->redirectPage($url);		
			return true;
		}
		return false;
	}
	
	public function getPaymentLink($orderId, $amount, $type, $title=null,$customData=null)
	{
		$amount = round($amount,2);
		$providerObj = $this->getProviderObj($type);
		$url = $providerObj->GetPaymentURL($orderId, $amount,  $title , $customData);
		return $url;
	}
	
	protected function getProviderObj($type)
	{
		$providerRec = Openbiz::getObject($this->providerDO)->fetchOne("[type]='$type'");
		$driver = $providerRec['driver'];
		$driverFile = Openbiz::$app->getModulePath().'/'.str_replace(".", "/", $driver).'.php';
		$driverName = explode(".", $driver);
		$driverName = $driverName[count($driverName)-1];
		
		if(!is_file($driverFile)){return false;}
		require_once $driverFile;
		$obj = new $driverName;
		return $obj;		
	}
	
	public function getReturnData($type)
	{
		return $this->getProviderObj($type)->getReturnData();
	}
	
	public function ValidateNotification($type,$txn_id=null)
	{
		return $this->getProviderObj($type)->ValidateNotification($txn_id);
	}
	
}
