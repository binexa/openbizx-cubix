<?PHP

/**
 * Openbiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: ClientProxy.php 5190 2013-01-19 15:57:13Z hellojixian@gmail.com $
 */

namespace Openbiz\web;

use Openbiz\Openbiz;
use Openbiz\Helpers\DeviceUtil;
use Openbiz\I18n\I18n;

/**
 * ClientProxy class
 *
 * A class that is treated as the bi-direction proxy of client. Through this class,
 * others can get client form inputs, redraw client form or call client javascript functions.
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class ClientProxy
{

    protected $requestArguments;
    protected $formInputArray = null;
    protected $isRpc = false;

    /**
     * Associate array of HTML content to be printed
     *
     * @var array
     */
    private $_formsOutput = array(); // key-value array
    private $_otherOutput = array(); // index array

    /**
     * Associate array of Javascript and Javascript references
     *
     * @var array
     */
    private $_extraScripts = array();

    /**
     * Associate array of CSS and CSS references
     *
     * @var array
     */
    private $_extraStyles = array();

    /**
     * Check has form rendered?
     *
     * Form has rendered if _formsOutput[$formName] exist.
     * See {@link ReDrawForm} method.
     *
     * @param string $formName
     * @return boolean true if form rendered (formName exist on _formsOutput)
     */
    public function hasFormRerendered($formName)
    {
        return isset($this->_formsOutput[$formName]);
    }

    /**
     * Get form output.
     *
     * @param string $formName
     * @return string HTML output of form
     */
    public function getFormOutput($formName)
    {
        return $this->_formsOutput[$formName];
    }

    /**
     * Check has other output?
     * NOTE: NYU - not yet used
     *
     * @return boolean true if has other output
     */
    public function hasOtherOutput()
    {
        return count($this->_otherOutput) > 0;
    }

    /**
     * Check has output (other output and/or forms output)
     * 
     * @return boolean true if has output
     */
    public function hasOutput()
    {
        return count($this->_otherOutput) + count($this->_formsOutput) > 0;
    }

    /**
     * Print output.
     *
     * @return void
     */
    public function printOutput()
    {
        if ($this->isRpc == true) {
            return $this->printJSONOuput();
        }
        foreach ($this->_otherOutput as $output) {
            print $output;
        }
        foreach ($this->_formsOutput as $output) {
            print $output;
        }
    }

    protected function printJSONOuput()
    {
        $outs = array();
        // output JSON
        foreach ($this->_otherOutput as $output) {
            $outs[] = $output;
        }
        foreach ($this->_formsOutput as $output) {
            $outs[] = $output;
        }
        if (function_exists("json_encode")) {
            echo json_encode($outs);
        } else {
            require_once 'Zend/Json.php';
            echo \Zend_Json::encode($outs);
        }
    }

    /**
     * Set RPC flag.
     *
     * @param boolean $flag
     * @return void
     */
    public function setRpcFlag($flag)
    {
        $this->isRpc = $flag;
    }

    /**
     * Get request parameter,
     * get the client form data passed by GET or POST
     * NOTE: NYU - not yet used
     *
     * @param string $name name of parameter
     * @return string value of paramater
     */
    public function getRequestParam($name)
    {
        $val = (isset($_REQUEST[$name]) ? $_REQUEST[$name] : "");
        return $val;
    }

    /**
     * Get form all inputs or one input if controlName is given
     *
     * @param string $controlName
     * @param boolean $toString - Convert array oriented form controls to string?
     * @return array|string
     */
    public function getFormInputs($controlName = null, $toString = TRUE)
    {
        if ($controlName) {
            if (isset($_GET[$controlName])) {
                $_POST[$controlName] = $_GET[$controlName];
            }
            if (isset($_POST[$controlName])) {
                if (is_array($_POST[$controlName]) and $toString == TRUE) {
                    $array_string = '';
                    foreach ($_POST[$controlName] as $rec) {
                        $array_string .= $rec . ",";
                    }
                    $result = substr($array_string, 0, strlen($array_string) - 1);
                } else {
                    $result = $_POST[$controlName];
                }
                if (get_magic_quotes_gpc() == 1) {
                    if (!is_array($result)) {
                        $result = stripslashes($result);
                    }
                }
                return $result;
            } else {
                if (isset($_FILES[$controlName])) {
                    $result = true;
                    return $result;
                } else {
                    return null;
                }
            }
        } else {
            return $_POST;
        }
    }

    /**
     * Update the form elements/controls on the client UI
     * Return to client browser : array or string
     *
     * @param string $formName - name of the html form on client
     * @param array $recArr - name/value pairs
     * @return void
     */
    public function updateFormElements($formName, $recArr, $rawData = false)
    {
        if ($this->isRpc) {
            if ($rawData) {
                $fieldsOutput = $recArr;
                $this->_otherOutput[] = $this->buildTargetContent($formName, $fieldsOutput);
            } else {
                foreach ($recArr as $fld => $val) {
                    $fieldsOutput[] = $this->buildTargetContent($fld, $val);
                }
                $this->_otherOutput[] = $this->buildTargetContent($formName, $fieldsOutput);
            }
        }
    }

    /**
     * Replace the form content with the provided html text,
     * encoded html string returns to browser,
     * it'll be processed by client javascript.
     *
     * @param string $formName - name of the html form on client
     * @param string $sHTML - html text to redraw the form
     * @return void
     */
    public function redrawForm($formName, $sHTML)
    {
        if ($this->isRpc) {
            $this->_formsOutput[$formName] = $this->buildTargetContent($formName, $sHTML);
        } else {
            $this->_formsOutput[$formName] = $sHTML;
        }
    }

    /**
     * Show popup an alert window on the client browser.
     * Encoded html string returns to browser, it'll be processed by client javascript.
     *
     * @param string $alertText
     * @return void
     */
    public function showClientAlert($alertText)
    {        
        if ($this->isRpc) {
            $msg = addslashes($alertText);
            $this->_otherOutput[] = $this->callClientFunction("alert('" . $msg . "')");
        }
    }

    /**
     * Show error message.
     *
     * If its a remote call, it uses javascript
     * If it is not a remote call, it outputs to the page's html
     * 
     * @todo Hard code for error form : "common.form.ErrorPopupForm"
     *       change to flexible ErrorPopupForm, save in Class variable and maybe also in configuration
     *
     * @param char $errMsg
     * @return void
     * 
     */    
    public function showErrorMessage($errMsg)
    {
        if (!$errMsg) {
            return;
        }
        if ($this->isRpc) {
            $_GET['ob_err_msg'] = $errMsg;
            ob_end_clean();
            $form = "common.form.ErrorPopupForm";
            $html = Openbiz::getObject($form)->render();
            $html = $this->buildTargetContent("DIALOG", $html);
            $this->_formsOutput[] = $html;
        } else {
            $this->_errorOutput($errMsg);
        }
    }

    /**
     * Show popup window
     * 
     * @param string $baseForm
     * @param string $popupForm
     * @param string $ctrlName 
     * @return void
     */
    public function showPopup($baseForm, $popupForm, $ctrlName = "")
    {
        if ($this->isRpc) {
            $function = $baseForm . ".ShowPopup(" . $popupForm . "," . $ctrlName . ")";
            $this->_otherOutput[] = $this->callClientFunction("CallFunction('$function','Popup')");
        }
    }

    /*
      public function runClientScript($script, $type = 'Form')
      {
      if ($this->isRPC) {
      if ($type == 'Form') {
      $this->formsOutput[] = $this->callClientFunction($script);
      } elseif ($type == 'Other') {
      $this->otherOutput[] = $this->callClientFunction($script);
      }
      } else {
      echo $script;
      }
      }
     */

    /**
     * Show HTML error message
     * This method call by {@link ShowErrorMessage} if RPC flag false.
     *
     * @param string $errMsg error message
     * @return void
     */
    private function _errorOutput($errMsg)
    {
        //ob_clean();
        //Openbiz::$app->isInitialized = false;
        if (defined('OPENBIZ_INTERNAL_ERROR_VIEW') && Openbiz::$app->isInitialized) {
            //render the view
            $_GET['ob_err_msg'] = $errMsg;
            ob_end_clean();
            Openbiz::getObject(OPENBIZ_INTERNAL_ERROR_VIEW)->render();
            exit;
        } else {
            echo $errMsg;
            echo "<input type='button' NAME='btn_back' VALUE='Go back' onClick='history.go(-1);return true;'>";
        }
    }

    /**
     * Close popup window
     *
     * @return void
     */
    public function closePopup()
    {
        if ($this->isRpc) {
            $this->_formsOutput[] = $this->callClientFunction("Openbiz.Window.closePopup()");
            $this->_otherOutput[] = $this->callClientFunction("Openbiz.Window.closePopup()");
        }
    }

    /**
     * Show popup window
     *
     * @param string $content
     * @param number $w width of window
     * @param number $h height of window
     * @return void
     */
    public function showPopupWindow($content, $w, $h)
    {
        if ($this->isRpc) {
            $this->_formsOutput[] = $this->callClientFunction("popupWindow(\"$content\", $w, $h)");
        }
    }

    /**
     * Update client element
     *
     * @param string $elementId
     * @param string $content
     * @return void
     */
    public function updateClientElement($elementId, $content)
    {
        $scriptStr = "<script>$('" . $elementId . "').innerHTML='" . $content . "';</script>";
        $this->runClientScript($scriptStr);
    }

    /**
     * Run client script
     *
     * @param string $scriptStr
     * @return void
     */
    public function runClientScript($scriptStr)
    {
        if ($this->isRpc) {
            $this->_otherOutput[] = $this->buildTargetContent("SCRIPT", $scriptStr);
        } else {
            echo $scriptStr;
        }
    }

    public function runClientFunction($scriptStr)
    {
        if ($this->isRpc) {
            $this->_otherOutput[] = $this->callClientFunction($scriptStr);
        }
    }

    /**
     * Call client function
     *
     * @param string $funcStr
     * @return string target content
     */
    private function callClientFunction($funcStr)
    {
        if ($this->isRpc) {
            return $this->buildTargetContent("FUNCTION", $funcStr);
        }
    }

    /**
     * Build target-content array with target string and content string as inputs.
     * After RPC call, this function is usually called to set the HTML text to an UI element.
     *
     * @param string $target the HTML element id, i.e. the form id
     * @param string $content the HTML text to be set as the content of the HTML element referred with the id
     * @return array
     * */
    private function buildTargetContent($target, &$content)
    {
        return array(
            'target' => $target,
            'content' => $content
        );
    }

    /**
     * Redirect page to the given url.
     * Encoded html string returns to browser, it'll be processed by client javascript.
     * NOTE: NYU - not yet used
     *
     * @param string $pageURL
     * @return void
     */
    public function redirectPage($pageURL)
    {
        if (!$this->isRpc) {
            $func = (isset($_REQUEST['F']) ? $_REQUEST['F'] : "");
            if ($func == "RPCInvoke") {
                $this->isRpc = true;
            }
        }
        if (!$this->isRpc) {
            ob_clean();
            header("Location: $pageURL");
            return;
        }
        if ($pageURL == "#back") {
            $this->_otherOutput[] = $this->callClientFunction("history.go(-1)");
        } else {
            $pageURL = str_replace("&", "&amp;", $pageURL);
            $this->_otherOutput[] = $this->callClientFunction("Openbiz.Net.redirectPage('$pageURL')");
        }
    }

    /**
     * Redirect page to the given view.
     * Encoded html string returns to browser, it'll be processed by client javascript.
     *
     * @param string $view full name of view (with package)
     * @param string $rule
     * @return void
     */
    public function redirectView($view, $rule = null)
    {
        // get the view url form view name
        $viewParts = explode('.', $view);
        $viewMod = $viewParts[0];
        $viewName = $viewParts[count($viewParts) - 1];
        $viewName = strtolower(str_replace("View", "", $viewName));
        $url = OPENBIZ_APP_INDEX_URL . "/$viewMod/$viewName";
        //echo "$view page url is $url. $this->isRPC";
        $this->redirectPage($url);
        $this->printOutput();
        //Openbiz::$app->getClientProxy()->printOutput();
    }

    /**
     * Append more scripts - js include, js code
     *
     * @param mixed $scriptKey
     * @param string $scripts
     * @param boolean $isFile
     * @return void
     */
    public function appendScripts($scriptKey, $scripts, $isFile = true)
    {
        // if has the script key already, ignore
        if (isset($this->_extraScripts[$scriptKey])) {
            return;
        }
        // add the scripts
        if ($isFile) {
            $_scripts = "<script type='text/javascript' src=\"" . Openbiz::$app->getJsUrl() . "/$scripts\"></script>";
            $this->_extraScripts[$scriptKey] = $_scripts;
        } else {
            $this->_extraScripts[$scriptKey] = $scripts;
        }
    }

    /**
     * Get append scripts
     *
     * @return string
     */
    public function getAppendedScripts()
    {
        $currentView = Openbiz::$app->getCurrentViewName();
        $initScripts = "<script>var APP_URL='" . OPENBIZ_APP_URL . "'; var APP_CONTROLLER='" . OPENBIZ_APP_URL . "/bin/controller.php';</script>\n";
        $initScripts .= "<script>var APP_VIEWNAME='" . $currentView . "';</script>\n";
        $extraScripts = implode("", $this->_extraScripts);
        $extraScript_array = explode("</script>", $extraScripts);
        /* if (defined("OPENBIZ_RESOURCE_PHP")) {
          $js_scripts = OPENBIZ_RESOURCE_PHP."?f=";
          foreach ($extraScript_array as $script)
          {
          // extract src part from each line
          if (preg_match('/.+src="([^"]+)".+/', $script, $matches) > 0 && !empty($matches[1])) {
          if (substr($js_scripts, -2)=='f=') $js_scripts .= $matches[1];
          else $js_scripts .= ','.$matches[1];
          }
          }
          return $initScripts."<script type=\"text/javascript\" src=\"".$js_scripts."\"></script>";
          } */
        $cleanScript_array = array();
        foreach ($extraScript_array as $script) {
            if (in_array($script . "</script>", $cleanScript_array) == FALSE and strlen($script) != 0) {
                $cleanScript_array[] = $script . "</script>";
            }
        }
        return $initScripts . implode("\n", $cleanScript_array);
    }

    /**
     * Append more styles - include and sections
     *
     * @param <type> $scriptKey
     * @param string $styles
     * @param boolean $isFile
     * @return void
     */
    public function appendStyles($scriptKey, $styles, $isFile = true)
    {
        // if has the script key already, ignore
        if (isset($this->_extraStyles[$scriptKey])) {
            return;
        }
        // add the styles
        $css = Openbiz::$app->getCssUrl();
        if ($isFile) {
            $_styles = "<link rel=\"stylesheet\" href=\"$css/" . $styles . "\" type=\"text/css\">";
            $this->_extraStyles[$scriptKey] = $_styles;
        } else {
            $this->_extraStyles[$scriptKey] = $styles;
        }
    }

    /**
     * Get appended styles
     *
     * @return string
     */
    public function getAppendedStyles($comb = 0)
    {
        $extraStyles = implode("", $this->_extraStyles);
        $extraStyle_array = explode("type=\"text/css\">", $extraStyles);
        if (defined("OPENBIZ_RESOURCE_PHP") && $comb) {
            $css_scripts = OPENBIZ_RESOURCE_PHP . "?f=";
            $matches = array();
            foreach ($extraStyle_array as $style) {
                // extract href part from each line
                if (preg_match('/.+href="([^"]+)".+/', $style, $matches) > 0 && !empty($matches[1])) {
                    if (substr($css_scripts, -2) == 'f=') {
                        $css_scripts .= $matches[1];
                    } else {
                        $css_scripts .= ',' . $matches[1];
                    }
                }
            }
            return "<link rel=\"stylesheet\" href=\"" . $css_scripts . "\" type=\"text/css\"/>";
        }
        $cleanStyle_array = array();
        foreach ($extraStyle_array as $style) {
            if (in_array($style . "type=\"text/css\">", $cleanStyle_array) == FALSE and strlen($style) != 0) {
                $cleanStyle_array[] = $style . "type=\"text/css\">";
            }
        }
        $lang = I18n::getCurrentLangCode();
        $localization_css_file = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR 
                . "languages" . DIRECTORY_SEPARATOR 
                . $lang . DIRECTORY_SEPARATOR . "localization.css";
        if (is_file($localization_css_file)) {
            $cleanStyle_array[] = "<link rel=\"stylesheet\" href=\"" . OPENBIZ_APP_URL . "/languages/$lang/localization.css\" type=\"text/css\">";
        }
        return implode("\n", $cleanStyle_array);
    }

    /**
     * Include base client library scripts
     *
     * @return void
     */
    public function includeBaseClientScripts()
    {
        if (defined('OPENBIZ_JSLIB_BASE') && OPENBIZ_JSLIB_BASE == 'JQUERY') {
            Openbiz::$app->getClientProxy()->appendScripts("jquery", "jquery.js");
            Openbiz::$app->getClientProxy()->appendScripts("jquery_class", "jquery.class.js");
            Openbiz::$app->getClientProxy()->appendScripts("jquery_dollarj", "<script>try{var \$j=\$;}catch(e){}</script>", false);
            Openbiz::$app->getClientProxy()->appendStyles("default", "openbiz.css");
            if (DeviceUtil::$PHONE_TOUCH) {
                Openbiz::$app->getClientProxy()->appendScripts("openbiz", "openbiz.mobile.js");
                Openbiz::$app->getClientProxy()->appendScripts("jquery_mobile", "jqm/jquery.mobile-1.0.js");
                $style = "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getJsUrl() . "/jqm/jquery.mobile-1.0.css\" type=\"text/css\">";
                Openbiz::$app->getClientProxy()->appendStyles("jquery_mobile_css", $style, false);
            } else {
                Openbiz::$app->getClientProxy()->appendScripts("openbiz", "openbiz.js");
                Openbiz::$app->getClientProxy()->appendScripts("jquery_ui", "jquery-ui-1.8.16.custom.min.js");
                $style = "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getJsUrl() . "/jquery-ui/ui-lightness/jquery-ui-1.8.16.custom.css\" type=\"text/css\">";
                $style .= "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getJsUrl() . "/jquery-ui/ui-openbiz/jquery.css\" type=\"text/css\">";
                Openbiz::$app->getClientProxy()->appendStyles("jquery_ui_css", $style, false);
            }
            return;
        }
        // prototype is still the default js lib
        Openbiz::$app->getClientProxy()->appendScripts("prototype", "prototype.js");
        Openbiz::$app->getClientProxy()->appendScripts("scriptaculous", "scriptaculous.js");
        Openbiz::$app->getClientProxy()->appendScripts("effects", "effects.js");
        Openbiz::$app->getClientProxy()->appendScripts("controls", "controls.js");
        Openbiz::$app->getClientProxy()->appendScripts("cookies", "cookies.js");
        Openbiz::$app->getClientProxy()->appendScripts("openbiz", "openbiz.js");
        Openbiz::$app->getClientProxy()->appendStyles("default", "openbiz.css");
        // window lib
        Openbiz::$app->getClientProxy()->includePropWindowScripts();
        // validator lib
        //Openbiz::$app->getClientProxy()->includeValidatorScripts();
    }

    /**
     * Include calendar scripts
     *
     * @return void
     */
    public function includeCalendarScripts()
    {
        if (isset($this->_extraScripts['calendar'])) {
            return;
        }
        $style = "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getJsUrl() . "/jscalendar/calendar-system.css\" type=\"text/css\">";
        $script = "<script type='text/javascript' src='" . Openbiz::$app->getJsUrl() . "/jscalendar/calendar.js'></script>";
        $script .= "<script type='text/javascript' src='" . Openbiz::$app->getJsUrl() . "/jscalendar/lang/calendar-en.js'></script>";
        $script .= "<script type='text/javascript' src='" . Openbiz::$app->getJsUrl() . "/jscalendar/calendar-setup.js'></script>";
        $script .= "<script type='text/javascript' src='" . Openbiz::$app->getJsUrl() . "/calendar.js'></script>";
        $this->appendStyles("calendar", $style, false);
        $this->appendScripts("calendar", $script, false);
    }

    /**
     * Include calendar scripts
     *
     * @return void
     */
    public function includeColorPickerScripts()
    {
        if (isset($this->_extraScripts['colorpicker'])) {
            return;
        }
        $style = "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getJsUrl() . "/colorpicker/css/colorpicker.css\" type=\"text/css\">";
        if (OPENBIZ_JSLIB_BASE != 'JQUERY') {
            $script = "<script type=\"text/javascript\" src=\"" . Openbiz::$app->getJsUrl() . "/jquery.js\"></script>";
        }
        $script .= "<script type=\"text/javascript\" src=\"" . Openbiz::$app->getJsUrl() . "/colorpicker/js/eye.js\"></script>";
        $script .= "<script type=\"text/javascript\" src=\"" . Openbiz::$app->getJsUrl() . "/colorpicker/js/utils.js\"></script>";
        $script .= "<script type=\"text/javascript\" src=\"" . Openbiz::$app->getJsUrl() . "/colorpicker/js/colorpicker.js\"></script>";
        $this->appendStyles("colorpicker", $style, false);
        $this->appendScripts("colorpicker", $script, false);
    }

    /**
     * Include RTE scripts
     *
     * @return void
     */
    public function includeRTEScripts()
    {
        if (isset($this->_extraScripts['rte'])) {
            return;
        }
        $script = "<script type=\"text/javascript\" src=\"" . Openbiz::$app->getJsUrl() . "/richtext.js\"></script>";
        $script .= "<script language=\"JavaScript\">initRTE('" . Openbiz::$app->getImageUrl() . "/rte/', '../pages/rte/', '', false);</script>";
        $this->appendScripts("rte", $script, false);
    }

    /**
     * Include CKEditor scripts
     *
     * @return void
     */
    public function includeCKEditorScripts()
    {
        if (isset($this->_extraScripts['ckeditor'])) {
            return;
        }

        $script = "<script type=\"text/javascript\" src=\"" . Openbiz::$app->getJsUrl() . "/ckeditor/ckeditor.js\"></script>";

        $this->appendScripts("ckeditor", $script, false);
    }

    /**
     * Include PropWindow scripts
     *
     * @return void
     */
    public function includePropWindowScripts()
    {
        $this->appendScripts("scriptaculous", "scriptaculous.js");
        $this->appendScripts("prop_window", "window.js");
        $style = "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getJsUrl() . "/window/default.css\" type=\"text/css\">";
        //$style .= "<link rel=\"stylesheet\" href=\"".Openbiz::$app->getJsUrl()."/window/spread.css\" type=\"text/css\">";
        //$style .= "<link rel=\"stylesheet\" href=\"".Openbiz::$app->getJsUrl()."/window/lighting.css\" type=\"text/css\">";
        $this->appendStyles("prop_window", $style, false);
    }

    /**
     * Include validator scripts
     *
     * @return void
     */
    public function includeValidatorScripts()
    {
        $this->appendScripts("yav", "yav/yav.js");
        $this->appendScripts("yav-cfg", "yav/yav-config.js");
        //$this->appendScripts("validator", "validator.js");
        $style = "<link rel=\"stylesheet\" href=\"" . Openbiz::$app->getCssUrl() . "/validator.css\" type=\"text/css\">";
        $this->appendStyles("yav", $style, false);
    }

}
