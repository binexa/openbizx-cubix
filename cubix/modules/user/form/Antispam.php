<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: Antispam.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

include_once (OPENBIZ_BIN."/easy/element/InputElement.php");
class Antispam extends InputElement{

	public $m_AntiSpamImage ;
	public $m_Length;
	public $m_SpamLevel;
	
    public function Validate()
    {
    	if($this->getRequired()=='N'){
    		return true;
    	}
    	$formobj = $this->GetFormObj();       	     
        BizSystem::sessionContext()->getObjVar($formobj->objectName, $this->objectName,$orgValue);
        $inputValue = strtoupper($this->getValue());
		if($inputValue==$orgValue) {
			return true;
		}else{
			return false;
		}
    }	
	
	protected function readMetaData(&$xmlArr){
		parent::readMetaData($xmlArr);
		$this->m_AntiSpamImage = isset($xmlArr["ATTRIBUTES"]["ANTISPAMIMAGE"]) ? $xmlArr["ATTRIBUTES"]["ANTISPAMIMAGE"] : "{@home:base_url}/bin/antispam_image.php";
		$this->m_Length = isset($xmlArr["ATTRIBUTES"]["LENGTH"]) ? $xmlArr["ATTRIBUTES"]["LENGTH"] : 6;
		$this->m_SpamLevel = isset($xmlArr["ATTRIBUTES"]["SPAMLEVEL"]) ? $xmlArr["ATTRIBUTES"]["SPAMLEVEL"] : 1;
		$this->cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_text_s";
		$this->cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : "input_text_s_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : "input_text_s_focus";        
	}

	public function render()
    {
        $value = $this->value ? $this->value : $this->text;

        $disabledStr = ($this->getEnabled() == "N") ? "READONLY=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        
        $formobj = $this->GetFormObj();
    	if($formobj->m_Errors[$this->objectName]){
			$func .= "onchange=\"this.className='$this->cssClass'\"";
		}else{
			$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->cssClass'\"";
		}        
        $formObj = $this->getFormObj();       
        $this->m_AntiSpamImage = Expression::evaluateExpression($this->m_AntiSpamImage, $formObj);
        
        $sHTML = "<INPUT maxlength='".$this->m_Lenght."' NAME=\"" . $this->objectName . "\" ID=\"" . $this->objectName ."\" VALUE=\"" . $value . "\" $disabledStr $this->m_HTMLAttr $style $func>";
        $sHTML .= "<IMG style='margin-left:10px;' BRODER='0' SRC=\"". $this->m_AntiSpamImage."?form=".$formobj->objectName."&name=".$this->objectName."&length=".$this->m_Length."&level=".$this->m_SpamLevel."&rand=".rand()."\" />"; 
        return $sHTML;
    }
}
?>