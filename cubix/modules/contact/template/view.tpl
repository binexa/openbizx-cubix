{php}
$header_background_image='/contact/images/top_logo_banner.png';
$this->assign('header_background_image', $header_background_image);

$js_url = $this->_tpl_vars['js_url'];
$theme_js_url = $this->_tpl_vars['theme_js_url'];
$css_url = $this->_tpl_vars['css_url'];

Openbiz\Openbiz::$app->getClientProxy()->includeColorPickerScripts();
Openbiz\Openbiz::$app->getClientProxy()->includeCKEditorScripts();
Openbiz\Openbiz::$app->getClientProxy()->includeCalendarScripts();

$includedScripts = Openbiz\Openbiz::$app->getClientProxy()->getAppendedScripts();
$includedScripts .= "
<script type='text/javascript' src='//maps.googleapis.com/maps/api/js?sensor=false'></script>
<script type='text/javascript' src='$js_url/cookies.js'></script>
<script type='text/javascript' src='$js_url/grouping.js'></script>
<script type='text/javascript' src='$js_url/general_ui.js'></script>
<script type='text/javascript' src='$js_url/uploadify/swfobject.js'></script>
<script type='text/javascript' src='$js_url/uploadify/jquery.uploadify.v2.1.4.js'></script>
";
if (OPENBIZ_JSLIB_BASE!='JQUERY') :
$includedScripts.="
<script type='text/javascript' src='$js_url/jquery-ui-1.8.12.custom.min.js'></script>
<script>try{var \$j=jQuery.noConflict();}catch(e){}</script>
";
endif;
$includedScripts.="
<script type='text/javascript' src='$js_url/uploadify/swfobject.js'></script>
<script type='text/javascript' src='$js_url/uploadify/jquery.uploadify.v2.1.4.js'></script>
";
$this->_tpl_vars['scripts'] = $includedScripts;

$left_menu = "contact.widget.ContactLeftMenu";
$this->assign('left_menu', $left_menu);

$appendStyle = Openbiz\Openbiz::$app->getClientProxy()->getAppendedStyles();
$appendStyle .= "
<link rel=\"stylesheet\" href=\"$css_url/general.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_backend.css\" type=\"text/css\" />
<link rel='stylesheet' href='".OPENBIZ_RESOURCE_URL."/contact/css/contact.css' type='text/css'>
<style>
.action_panel{
width:292px;
}
.search_panel{
width:398px;
}
</style>
";
$this->_tpl_vars['style_sheets'] = $appendStyle;
$this->assign('template_file', 'system_view.tpl.html');
{/php}
{include file=$template_file}
{literal}
<script>
function updatePreviewPic(){
	if(Prototype.Browser.IE){
		$('photo_placeholder').src=$('fld_photo').value;
	}else{		
		$('photo_placeholder').src="{/literal}{$image_url}{literal}/profile_photo_icon_ok.gif";		
	}
}
</script>
{/literal}