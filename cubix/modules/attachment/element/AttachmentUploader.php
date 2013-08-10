<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.attachment.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: AttachmentUploader.php 3350 2012-05-31 05:29:38Z rockyswen@gmail.com $
 */

class AttachmentUploader extends InputText
{
 public function render() {      
    	$this->m_cssClass=null;
    	$this->m_cssErrorClass = null;
    	$this->m_cssHoverClass = null;   
    	
        $sHTML = "
        <INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\"  />
        
        ";
        
        $formObj = $this->getFormObj();
        $formName = $formObj->m_Name;
        
        $sHTML .= "
        <script>
		  \$j('#".$this->m_Name."').uploadify({
		    'uploader'  : '".JS_URL."/uploadify/uploadify.swf',		    
		    'cancelImg' : '".JS_URL."/uploadify/cancel.png',
		    'script'    : '".APP_URL."/bin/controller.php',
		    'scriptData': { 'F':'RPCInvoke',
 							'P0':'[attachment.widget.AttachmentNewForm]',
 							'P1':'[uploadFile]',
 							'__this':'btn_upload_file:upload_onclick',
 							'_selectedId':'',
 							'session_id':'".session_id()."',
 							'cubi_sess_id':'".session_id()."'
 							},
		    'folder'    : '".JS_URL."/uploadify/test',
		    'displayData' : true,
		    'multi'      : true,
		   
		    'auto'      : false,
		    'onComplete' : function(event, ID, fileObj, response, data){							
							Openbiz.CallFunction('$formName.fileUploadComplete('+response+')');
 						},
 			'onAllComplete': function(event,data){ 			 							
 							Openbiz.CallFunction('$formName.allUploadComplete()');
 						}
		  });
		        
        </script>       
        ";
        return $sHTML;
    }	
}
?>