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
 * @version   $Id: InputPassword.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("Password.php");

/**
 * InputPassword class is element for input password
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class InputPassword extends Password
{
 
    /**
     * Mask character for hidden original/real value
     * @var string
     */
    public $m_MaskChar='*';

    /**
     * length of mask character will be displayed
     * @var number
     */
    public $m_MaskLength=6;

    /**
     * Real value of password
     * @var string
     */
    protected $value_Real;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    public function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_text";        
        $this->cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->cssClass."_error";
        $this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->cssClass."_focus";

        $this->m_MaskChar = isset($xmlArr["ATTRIBUTES"]["MASKCHAR"]) ? $xmlArr["ATTRIBUTES"]["MASKCHAR"] : $this->m_MaskChar;
        $this->m_MaskLength = isset($xmlArr["ATTRIBUTES"]["MASKLENGTH"]) ? $xmlArr["ATTRIBUTES"]["MASKLENGTH"] : $this->m_MaskLength;
        $this->m_PasswordMask = str_repeat($this->m_MaskChar, $this->m_MaskLength);
    }

    /**
     * Render / draw the element according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $value = $this->value;
        
        	$this->value_Real = $this->value;
            $value = $this->m_PasswordMask;
           
        
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();

        $func = $this->getEnabled() == 'N' ? "" : $this->getFunction();		
        $formobj = $this->GetFormObj();
        if($formobj->m_Errors[$this->objectName]){
			$func .= "onchange=\"this.className='$this->cssClass'\"";
		}else{
			$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->cssClass'\"";
		} 
        $sHTML = "<INPUT TYPE=\"PASSWORD\" NAME='$this->objectName' ID=\"" . $this->objectName ."\" VALUE='$value' $disabledStr $this->m_HTMLAttr $style $func />";
    	if($this->m_Hint){
        	$sHTML.="<script>        	
        	\$j('#" . $this->objectName . "').tbHinter({
				text: '".$this->m_Hint."',
			});
        	</script>";
        }
        return $sHTML;

    }

    /**
     * Get value of element
     *
     * @return string
     */
    public function getValue()
    {    	
    	if($this->value==null){
    		$this->value = BizSystem::clientProxy()->getFormInputs($this->objectName);
    	}
        if($this->value==$this->m_PasswordMask)
        {       	
    		$rawDataArr = $this->getFormObj()->getActiveRecord();
    		$this->value_Real = $rawDataArr[$this->m_FieldName];
    		$this->value = $rawDataArr[$this->m_FieldName];
            return $this->value_Real;
        }
        else
        {
            return $this->value;
        }
    }

    /**
     * Set value of element
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        if($value==$this->m_PasswordMask)
        {
            $this->value = $this->value_Real;
        }
        else
        {
            $this->value = $value;
        }
    }


}

?>