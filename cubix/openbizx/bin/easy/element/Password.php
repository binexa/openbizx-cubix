<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: Password.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("InputElement.php");

/**
 * Password class is element for input password
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class Password extends InputElement
{
	public function readMetaData(&$xmlArr){
		parent::readMetaData($xmlArr);
		$this->cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_text";		
		$this->cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->cssClass."_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->cssClass."_focus";
	}
    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $formobj = $this->GetFormObj();
		if (OPENBIZ_CLIENT_DEVICE != 'mobile') { 
			if($formobj->m_Errors[$this->objectName]){
				$func .= "onchange=\"this.className='$this->cssClass'\"";
			}else{
				$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->cssClass'\"";
			} 
		}
        $sHTML = "<INPUT TYPE=\"PASSWORD\" NAME='$this->objectName' ID=\"" . $this->objectName ."\" VALUE='$this->value' $disabledStr $this->m_HTMLAttr $style $func />";
    	if($this->m_Hint){
        	$sHTML.="<script>        	
        	\$j('#" . $this->objectName . "').tbHinter({
				text: '".$this->m_Hint."'
			});
        	</script>";
        }
        return $sHTML;
    }

}

?>
