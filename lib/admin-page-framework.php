<?php 
/**
 * @package Admin Page Framework
 * @version  v3.5.1.1
 * @author  Michael Uno 
 * @link   texthttp://en.michaeluno.jp/admin-page-framework>
 * @license Copyright (c) 2013-2015, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> 
*/
abstract class Legull_AdminPageFramework_Registry_Base {
    const Version = '3.5.1.1';
    const Name = 'Admin Page Framework';
    const Description = 'Facilitates WordPress plugin and theme development.';
    const URI = 'http://en.michaeluno.jp/admin-page-framework';
    const Author = 'Michael Uno';
    const AuthorURI = 'http://en.michaeluno.jp/';
    const Copyright = 'Copyright (c) 2013-2015, Michael Uno';
    const License = 'MIT <http://opensource.org/licenses/MIT>';
    const Contributors = '';
}
final class Legull_AdminPageFramework_Registry extends Legull_AdminPageFramework_Registry_Base {
    const TextDomain = 'admin-page-framework';
    const TextDomainPath = '/language';
    static public $bIsMinifiedVersion = true;
    static public $sAutoLoaderPath;
    static public $sFilePath = '';
    static public $sDirPath = '';
    static public $sFileURI = '';
    static function setUp($sFilePath = null) {
        self::$sFilePath = $sFilePath ? $sFilePath : __FILE__;
        self::$sDirPath = dirname(self::$sFilePath);
        self::$sFileURI = plugins_url('', self::$sFilePath);
        self::$sAutoLoaderPath = self::$sDirPath . '/factory/Legull_AdminPageFramework_Factory/utility/Legull_AdminPageFramework_RegisterClasses.php';
        self::$bIsMinifiedVersion = class_exists('Legull_AdminPageFramework_MinifiedVersionHeader');
    }
    static function getVersion() {
        if (!isset(self::$sAutoLoaderPath)) {
            trigger_error('Admin Page Framework: ' . ' : ' . sprintf(__('The method is called too early. Perform <code>%2$s</code> earlier.', 'admin-page-framework'), __METHOD__, 'setUp()'), E_USER_WARNING);
            return self::Version;
        }
        return self::Version . (self::$bIsMinifiedVersion ? '.min' : '');
    }
    static public function getInfo() {
        $_oReflection = new ReflectionClass(__CLASS__);
        return $_oReflection->getConstants() + $_oReflection->getStaticProperties();
    }
}
final class Legull_AdminPageFramework_Bootstrap {
    function __construct($sLibraryPath) {
        if (isset(self::$sAutoLoaderPath)) {
            return;
        }
        if (!defined('ABSPATH')) {
            return;
        }
        Legull_AdminPageFramework_Registry::setUp($sLibraryPath);
        if (Legull_AdminPageFramework_Registry::$bIsMinifiedVersion) {
            return;
        }
        include (Legull_AdminPageFramework_Registry::$sAutoLoaderPath);
        include (Legull_AdminPageFramework_Registry::$sDirPath . '/admin-page-framework-include-class-list.php');
        new Legull_AdminPageFramework_RegisterClasses(isset($aClassFiles) ? '' : Legull_AdminPageFramework_Registry::$sDirPath, array('exclude_class_names' => 'Legull_AdminPageFramework_MinifiedVersionHeader',), isset($aClassFiles) ? $aClassFiles : array());
    }
}
new Legull_AdminPageFramework_Bootstrap(__FILE__);
abstract class Legull_AdminPageFramework_Factory_Router {
    public $oProp;
    public $oDebug;
    public $oUtil;
    public $oMsg;
    protected $oForm;
    protected $oPageLoadInfo;
    protected $oResource;
    protected $oHeadTag;
    protected $oHelpPane;
    protected $oLink;
    function __construct($oProp) {
        unset($this->oDebug, $this->oUtil, $this->oMsg, $this->oForm, $this->oPageLoadInfo, $this->oResource, $this->oHelpPane, $this->oLink);
        $this->oProp = $oProp;
        if ($this->oProp->bIsAdmin && !$this->oProp->bIsAdminAjax) {
            add_action('current_screen', array($this, '_replyToLoadComponents'));
        }
        $this->start();
    }
    public function _replyToLoadComponents() {
        if ('plugins.php' === $this->oProp->sPageNow) {
            $this->oLink = isset($this->oLink) ? $this->oLink : $this->_getLinkInstancce($this->oProp, $this->oMsg);
        }
        if (!$this->_isInThePage()) {
            return;
        }
        if (in_array($this->oProp->_sPropertyType, array('widget')) && 'customize.php' !== $this->oProp->sPageNow) {
            return;
        }
        $this->oResource = isset($this->oResource) ? $this->oResource : $this->_getResourceInstance($this->oProp);
        $this->oHeadTag = $this->oResource;
        $this->oLink = isset($this->oLink) ? $this->oLink : $this->_getLinkInstancce($this->oProp, $this->oMsg);
        $this->oPageLoadInfo = isset($this->oPageLoadInfo) ? $this->oPageLoadInfo : $this->_getPageLoadInfoInstance($this->oProp, $this->oMsg);
    }
    protected function _isInstantiatable() {
        return true;
    }
    public function _isInThePage() {
        return true;
    }
    protected function _getFormInstance($oProp) {
        switch ($oProp->sFieldsType) {
            case 'page':
            case 'network_admin_page':
                if ($oProp->bIsAdminAjax) {
                    return null;
                }
                return new Legull_AdminPageFramework_FormElement_Page($oProp->sFieldsType, $oProp->sCapability, $this);
            case 'post_meta_box':
            case 'page_meta_box':
            case 'post_type':
                if ($oProp->bIsAdminAjax) {
                    return null;
                }
                return new Legull_AdminPageFramework_FormElement($oProp->sFieldsType, $oProp->sCapability, $this);
            case 'taxonomy':
            case 'widget':
            case 'user_meta':
                return new Legull_AdminPageFramework_FormElement($oProp->sFieldsType, $oProp->sCapability, $this);
        }
    }
    protected function _getResourceInstance($oProp) {
        switch ($oProp->sFieldsType) {
            case 'page':
            case 'network_admin_page':
                return new Legull_AdminPageFramework_Resource_Page($oProp);
            case 'post_meta_box':
                return new Legull_AdminPageFramework_Resource_MetaBox($oProp);
            case 'page_meta_box':
                return new Legull_AdminPageFramework_Resource_MetaBox_Page($oProp);
            case 'post_type':
                return new Legull_AdminPageFramework_Resource_PostType($oProp);
            case 'taxonomy':
                return new Legull_AdminPageFramework_Resource_TaxonomyField($oProp);
            case 'widget':
                return new Legull_AdminPageFramework_Resource_Widget($oProp);
            case 'user_meta':
                return new Legull_AdminPageFramework_Resource_UserMeta($oProp);
        }
    }
    protected function _getHelpPaneInstance($oProp) {
        switch ($oProp->sFieldsType) {
            case 'page':
            case 'network_admin_page':
                return new Legull_AdminPageFramework_HelpPane_Page($oProp);
            case 'post_meta_box':
                return new Legull_AdminPageFramework_HelpPane_MetaBox($oProp);
            case 'page_meta_box':
                return new Legull_AdminPageFramework_HelpPane_MetaBox_Page($oProp);
            case 'post_type':
                return null;
            case 'taxonomy':
                return new Legull_AdminPageFramework_HelpPane_TaxonomyField($oProp);
            case 'widget':
                return new Legull_AdminPageFramework_HelpPane_Widget($oProp);
            case 'user_meta':
                return new Legull_AdminPageFramework_HelpPane_UserMeta($oProp);
        }
    }
    protected function _getLinkInstancce($oProp, $oMsg) {
        switch ($oProp->sFieldsType) {
            case 'page':
                return new Legull_AdminPageFramework_Link_Page($oProp, $oMsg);
            case 'network_admin_page':
                return new Legull_AdminPageFramework_Link_NetworkAdmin($oProp, $oMsg);
            case 'post_meta_box':
                return null;
            case 'page_meta_box':
                return null;
            case 'post_type':
                return new Legull_AdminPageFramework_Link_PostType($oProp, $oMsg);
            case 'taxonomy':
            case 'widget':
            case 'user_meta':
            default:
                return null;
        }
    }
    protected function _getPageLoadInfoInstance($oProp, $oMsg) {
        switch ($oProp->sFieldsType) {
            case 'page':
                return Legull_AdminPageFramework_PageLoadInfo_Page::instantiate($oProp, $oMsg);
            case 'network_admin_page':
                return Legull_AdminPageFramework_PageLoadInfo_NetworkAdminPage::instantiate($oProp, $oMsg);
            case 'post_meta_box':
                return null;
            case 'page_meta_box':
                return null;
            case 'post_type':
                return Legull_AdminPageFramework_PageLoadInfo_PostType::instantiate($oProp, $oMsg);
            case 'taxonomy':
            case 'widget':
            default:
                return null;
        }
    }
    function __get($sPropertyName) {
        switch ($sPropertyName) {
            case 'oUtil':
                $this->oUtil = new Legull_AdminPageFramework_WPUtility;
                return $this->oUtil;
            case 'oDebug':
                $this->oDebug = new Legull_AdminPageFramework_Debug;
                return $this->oDebug;
            case 'oMsg':
                $this->oMsg = Legull_AdminPageFramework_Message::getInstance($this->oProp->sTextDomain);
                return $this->oMsg;
            case 'oForm':
                $this->oForm = $this->_getFormInstance($this->oProp);
                return $this->oForm;
            case 'oResource':
                $this->oResource = $this->_getResourceInstance($this->oProp);
                return $this->oResource;
            case 'oHeadTag':
                return $this->oResource;
            case 'oHelpPane':
                $this->oHelpPane = $this->_getHelpPaneInstance($this->oProp);
                return $this->oHelpPane;
            case 'oLink':
                $this->oLink = $this->_getLinkInstancce($this->oProp, $this->oMsg);
                return $this->oLink;
            case 'oPageLoadInfo':
                $this->oPageLoadInfo = $this->_getPageLoadInfoInstance($this->oProp, $this->oMsg);
                return $this->oPageLoadInfo;
        }
    }
    function __call($sMethodName, $aArgs = null) {
        if (has_filter($sMethodName)) {
            return isset($aArgs[0]) ? $aArgs[0] : null;
        }
        trigger_error('Admin Page Framework: ' . ' : ' . sprintf(__('The method is not defined: %1$s', $this->oProp->sTextDomain), $sMethodName), E_USER_WARNING);
    }
    public function __toString() {
        $_iCount = count(get_object_vars($this));
        $_sClassName = get_class($this);
        return '(object) ' . $_sClassName . ': ' . $_iCount . ' properties.';
    }
}
abstract class Legull_AdminPageFramework_Factory_Model extends Legull_AdminPageFramework_Factory_Router {
    protected function _setUp() {
        $this->setUp();
    }
    static private $_aFieldTypeDefinitions = array();
    protected function _loadFieldTypeDefinitions() {
        if (empty(self::$_aFieldTypeDefinitions)) {
            self::$_aFieldTypeDefinitions = Legull_AdminPageFramework_FieldTypeRegistration::register(array(), $this->oProp->sClassName, $this->oMsg);
        }
        $this->oProp->aFieldTypeDefinitions = $this->oUtil->addAndApplyFilters($this, array('field_types_admin_page_framework', "field_types_{$this->oProp->sClassName}",), self::$_aFieldTypeDefinitions);
    }
    protected function _registerFields(array $aFields) {
        foreach ($aFields as $_sSecitonID => $_aFields) {
            $_bIsSubSectionLoaded = false;
            foreach ($_aFields as $_iSubSectionIndexOrFieldID => $_aSubSectionOrField) {
                if (is_numeric($_iSubSectionIndexOrFieldID) && is_int($_iSubSectionIndexOrFieldID + 0)) {
                    if ($_bIsSubSectionLoaded) {
                        continue;
                    }
                    $_bIsSubSectionLoaded = true;
                    foreach ($_aSubSectionOrField as $_aField) {
                        $this->_registerField($_aField);
                    }
                    continue;
                }
                $_aField = $_aSubSectionOrField;
                $this->_registerField($_aField);
            }
        }
    }
    protected function _registerField(array $aField) {
        Legull_AdminPageFramework_FieldTypeRegistration::_setFieldResources($aField, $this->oProp, $this->oResource);
        if ($aField['help']) {
            $this->oHelpPane->_addHelpTextForFormFields($aField['title'], $aField['help'], $aField['help_aside']);
        }
        if (isset($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfDoOnRegistration']) && is_callable($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfDoOnRegistration'])) {
            call_user_func_array($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfDoOnRegistration'], array($aField));
        }
    }
    public function getSavedOptions() {
        return $this->oProp->aOptions;
    }
    public function getFieldErrors() {
        return $this->_getFieldErrors();
    }
    protected function _getFieldErrors($sID = 'deprecated', $bDelete = true) {
        static $_aFieldErrors;
        $_sTransientKey = "apf_field_erros_" . get_current_user_id();
        $_sID = md5($this->oProp->sClassName);
        $_aFieldErrors = isset($_aFieldErrors) ? $_aFieldErrors : $this->oUtil->getTransient($_sTransientKey);
        if ($bDelete) {
            add_action('shutdown', array($this, '_replyToDeleteFieldErrors'));
        }
        return isset($_aFieldErrors[$_sID]) ? $_aFieldErrors[$_sID] : array();
    }
    protected function _isValidationErrors() {
        if (isset($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors']) && $GLOBALS['aLegull_AdminPageFramework']['aFieldErrors']) {
            return true;
        }
        return $this->oUtil->getTransient("apf_field_erros_" . get_current_user_id());
    }
    public function _replyToDeleteFieldErrors() {
        $this->oUtil->deleteTransient("apf_field_erros_" . get_current_user_id());
    }
    public function _replyToSaveFieldErrors() {
        if (!isset($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'])) {
            return;
        }
        $this->oUtil->setTransient("apf_field_erros_" . get_current_user_id(), $GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'], 300);
    }
    public function _replyToSaveNotices() {
        if (!isset($GLOBALS['aLegull_AdminPageFramework']['aNotices'])) {
            return;
        }
        if (empty($GLOBALS['aLegull_AdminPageFramework']['aNotices'])) {
            return;
        }
        $this->oUtil->setTransient('apf_notices_' . get_current_user_id(), $GLOBALS['aLegull_AdminPageFramework']['aNotices']);
    }
    public function validate($aInput, $aOldInput, $oFactory) {
        return $aInput;
    }
    public function _setLastInput(array $aLastInput) {
        return $this->oUtil->setTransient('apf_tfd' . md5('temporary_form_data_' . $this->oProp->sClassName . get_current_user_id()), $aLastInput, 60 * 60);
    }
}
abstract class Legull_AdminPageFramework_Factory_View extends Legull_AdminPageFramework_Factory_Model {
    function __construct($oProp) {
        parent::__construct($oProp);
        if ($this->_isInThePage() && !$this->oProp->bIsAdminAjax) {
            if (is_network_admin()) {
                add_action('network_admin_notices', array($this, '_replyToPrintSettingNotice'));
            } else {
                add_action('admin_notices', array($this, '_replyToPrintSettingNotice'));
            }
        }
    }
    static private $_bSettingNoticeLoaded = false;
    public function _replyToPrintSettingNotice() {
        if (!$this->_isInThePage()) {
            return;
        }
        if (self::$_bSettingNoticeLoaded) {
            return;
        }
        self::$_bSettingNoticeLoaded = true;
        $_iUserID = get_current_user_id();
        $_aNotices = $this->oUtil->getTransient("apf_notices_{$_iUserID}");
        if (false === $_aNotices) {
            return;
        }
        $this->oUtil->deleteTransient("apf_notices_{$_iUserID}");
        if (isset($_GET['settings-notice']) && !$_GET['settings-notice']) {
            return;
        }
        $_aPeventDuplicates = array();
        foreach (( array )$_aNotices as $__aNotice) {
            if (!isset($__aNotice['aAttributes'], $__aNotice['sMessage']) || !is_array($__aNotice)) {
                continue;
            }
            $_sNotificationKey = md5(serialize($__aNotice));
            if (isset($_aPeventDuplicates[$_sNotificationKey])) {
                continue;
            }
            $_aPeventDuplicates[$_sNotificationKey] = true;
            $__aNotice['aAttributes']['class'] = isset($__aNotice['aAttributes']['class']) ? $__aNotice['aAttributes']['class'] . ' admin-page-framework-settings-notice-container' : 'admin-page-framework-settings-notice-container';
            echo "<div " . $this->oUtil->generateAttributes($__aNotice['aAttributes']) . ">" . "<p class='admin-page-framework-settings-notice-message'>" . $__aNotice['sMessage'] . "</p>" . "</div>";
        }
    }
    public function _replyToGetFieldOutput($aField) {
        $_oField = new Legull_AdminPageFramework_FormField($aField, $this->oProp->aOptions, $this->_getFieldErrors(), $this->oProp->aFieldTypeDefinitions, $this->oMsg, $this->oProp->aFieldCallbacks);
        $_sOutput = $this->oUtil->addAndApplyFilters($this, array('field_' . $this->oProp->sClassName . '_' . $aField['field_id']), $_oField->_getFieldOutput(), $aField);
        return $_sOutput;
    }
}
abstract class Legull_AdminPageFramework_Factory_Controller extends Legull_AdminPageFramework_Factory_View {
    public function start() {
    }
    public function setUp() {
    }
    public function enqueueStyles($aSRCs, $_vArg2 = null) {
    }
    public function enqueueStyle($sSRC, $_vArg2 = null) {
    }
    public function enqueueScripts($aSRCs, $_vArg2 = null) {
    }
    public function enqueueScript($sSRC, $_vArg2 = null) {
    }
    public function addHelpText($sHTMLContent, $sHTMLSidebarContent = "") {
        if (method_exists($this->oHelpPane, '_addHelpText')) {
            $this->oHelpPane->_addHelpText($sHTMLContent, $sHTMLSidebarContent);
        }
    }
    public function addSettingSections($aSection1, $aSection2 = null, $_and_more = null) {
        foreach (func_get_args() as $asSection) {
            $this->addSettingSection($asSection);
        }
        $this->_sTargetSectionTabSlug = null;
    }
    public function addSettingSection($aSection) {
        if (!is_array($aSection)) {
            return;
        }
        $this->_sTargetSectionTabSlug = isset($aSection['section_tab_slug']) ? $this->oUtil->sanitizeSlug($aSection['section_tab_slug']) : $this->_sTargetSectionTabSlug;
        $aSection['section_tab_slug'] = $this->_sTargetSectionTabSlug ? $this->_sTargetSectionTabSlug : null;
        $this->oForm->addSection($aSection);
    }
    public function addSettingFields($aField1, $aField2 = null, $_and_more = null) {
        foreach (func_get_args() as $aField) $this->addSettingField($aField);
    }
    public function addSettingField($asField) {
        if (method_exists($this->oForm, 'addField')) {
            $this->oForm->addField($asField);
        }
    }
    public function setFieldErrors($aErrors) {
        $GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'] = isset($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors']) ? $GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'] : array();
        if (empty($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'])) {
            add_action('shutdown', array($this, '_replyToSaveFieldErrors'));
        }
        $_sID = md5($this->oProp->sClassName);
        $GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'][$_sID] = isset($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'][$_sID]) ? $this->oUtil->uniteArrays($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'][$_sID], $aErrors) : $aErrors;
    }
    public function hasFieldError() {
        return isset($GLOBALS['aLegull_AdminPageFramework']['aFieldErrors'][md5($this->oProp->sClassName) ]);
    }
    public function setSettingNotice($sMessage, $sType = 'error', $asAttributes = array(), $bOverride = true) {
        $GLOBALS['aLegull_AdminPageFramework']['aNotices'] = isset($GLOBALS['aLegull_AdminPageFramework']['aNotices']) ? $GLOBALS['aLegull_AdminPageFramework']['aNotices'] : array();
        if (empty($GLOBALS['aLegull_AdminPageFramework']['aNotices'])) {
            add_action('shutdown', array($this, '_replyToSaveNotices'));
        }
        $_sID = md5(trim($sMessage));
        if ($bOverride || !isset($GLOBALS['aLegull_AdminPageFramework']['aNotices'][$_sID])) {
            $_aAttributes = is_array($asAttributes) ? $asAttributes : array();
            if (is_string($asAttributes) && !empty($asAttributes)) {
                $_aAttributes['id'] = $asAttributes;
            }
            $GLOBALS['aLegull_AdminPageFramework']['aNotices'][$_sID] = array('sMessage' => $sMessage, 'aAttributes' => $_aAttributes + array('class' => $sType, 'id' => $this->oProp->sClassName . '_' . $_sID,),);
        }
    }
    public function hasSettingNotice($sType = '') {
        $_aNotices = isset($GLOBALS['aLegull_AdminPageFramework']['aNotices']) ? $GLOBALS['aLegull_AdminPageFramework']['aNotices'] : array();
        if (!$sType) {
            return count($_aNotices) ? true : false;
        }
        foreach ($_aNotices as $aNotice) {
            if (!isset($aNotice['aAttributes']['class'])) {
                continue;
            }
            if ($aNotice['aAttributes']['class'] == $sType) {
                return true;
            }
        }
        return false;
    }
}
abstract class Legull_AdminPageFramework_Factory extends Legull_AdminPageFramework_Factory_Controller {
}
abstract class Legull_AdminPageFramework_Router extends Legull_AdminPageFramework_Factory {
    protected static $_aHookPrefixes = array('start_' => 'start_', 'set_up_' => 'set_up_', 'load_' => 'load_', 'load_after_' => 'load_after_', 'do_before_' => 'do_before_', 'do_after_' => 'do_after_', 'do_form_' => 'do_form_', 'do_' => 'do_', 'submit_' => 'submit_', 'content_top_' => 'content_top_', 'content_bottom_' => 'content_bottom_', 'content_' => 'content_', 'validation_' => 'validation_', 'validation_saved_options_' => 'validation_saved_options_', 'export_name' => 'export_name', 'export_format' => 'export_format', 'export_' => 'export_', 'import_name' => 'import_name', 'import_format' => 'import_format', 'import_' => 'import_', 'style_common_ie_' => 'style_common_ie_', 'style_common_' => 'style_common_', 'style_ie_' => 'style_ie_', 'style_' => 'style_', 'script_' => 'script_', 'field_' => 'field_', 'section_head_' => 'section_head_', 'fields_' => 'fields_', 'sections_' => 'sections_', 'pages_' => 'pages_', 'tabs_' => 'tabs_', 'field_types_' => 'field_types_', 'field_definition_' => 'field_definition_', 'options_' => 'options_',);
    function __construct($sOptionKey = null, $sCallerPath = null, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        $this->oProp = isset($this->oProp) ? $this->oProp : new Legull_AdminPageFramework_Property_Page($this, $sCallerPath, get_class($this), $sOptionKey, $sCapability, $sTextDomain);
        parent::__construct($this->oProp);
        if ($this->oProp->bIsAdminAjax) {
            return;
        }
        if ($this->oProp->bIsAdmin) {
            add_action('wp_loaded', array($this, 'setup_pre'));
        }
    }
    public function __call($sMethodName, $aArgs = null) {
        $sPageSlug = isset($_GET['page']) ? $_GET['page'] : null;
        $sTabSlug = isset($_GET['tab']) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab($sPageSlug);
        if ('setup_pre' === $sMethodName) {
            $this->_setUp();
            $this->oUtil->addAndDoAction($this, "set_up_{$this->oProp->sClassName}", $this);
            $this->oProp->_bSetupLoaded = true;
            return;
        }
        if (substr($sMethodName, 0, strlen('section_pre_')) == 'section_pre_') return $this->_renderSectionDescription($sMethodName);
        if (substr($sMethodName, 0, strlen('field_pre_')) == 'field_pre_') return $this->_renderSettingField($aArgs[0], $sPageSlug);
        if (substr($sMethodName, 0, strlen('load_pre_')) == 'load_pre_') {
            return substr($sMethodName, strlen('load_pre_')) === $sPageSlug ? $this->_doPageLoadCall($sPageSlug, $sTabSlug, $aArgs[0]) : null;
        }
        if ($sMethodName == $this->oProp->sClassHash . '_page_' . $sPageSlug) {
            return $this->_renderPage($sPageSlug, $sTabSlug);
        }
        if (has_filter($sMethodName)) {
            return isset($aArgs[0]) ? $aArgs[0] : null;
        }
        trigger_error('Admin Page Framework: ' . ' : ' . sprintf(__('The method is not defined: %1$s', $this->oProp->sTextDomain), $sMethodName), E_USER_WARNING);
    }
    protected function _doPageLoadCall($sPageSlug, $sTabSlug, $oScreen) {
        if (!isset($this->oProp->aPageHooks[$sPageSlug]) || $oScreen->id !== $this->oProp->aPageHooks[$sPageSlug]) {
            return;
        }
        $this->oForm->aSections['_default']['page_slug'] = $sPageSlug ? $sPageSlug : null;
        $this->oForm->aSections['_default']['tab_slug'] = $sTabSlug ? $sTabSlug : null;
        $this->oUtil->addAndDoActions($this, array("load_{$this->oProp->sClassName}", "load_{$sPageSlug}",), $this);
        $this->_finalizeInPageTabs();
        $this->oUtil->addAndDoActions($this, array("load_{$sPageSlug}_" . $this->oProp->getCurrentTab($sPageSlug)), $this);
        $this->oUtil->addAndDoActions($this, array("load_after_{$this->oProp->sClassName}"), $this);
    }
    public function _sortByOrder($a, $b) {
        return isset($a['order'], $b['order']) ? $a['order'] - $b['order'] : 1;
    }
    protected function _isInstantiatable() {
        if (isset($GLOBALS['pagenow']) && 'admin-ajax.php' === $GLOBALS['pagenow']) {
            return false;
        }
        return !is_network_admin();
    }
    public function _isInThePage($aPageSlugs = array()) {
        if (!isset($this->oProp)) {
            return true;
        }
        if (!$this->oProp->_bSetupLoaded) {
            return true;
        }
        if (!isset($_GET['page'])) {
            return false;
        }
        $_oScreen = get_current_screen();
        if (is_object($_oScreen)) {
            return in_array($_oScreen->id, $this->oProp->aPageHooks);
        }
        if (empty($aPageSlugs)) {
            return $this->oProp->isPageAdded();
        }
        return in_array($_GET['page'], $aPageSlugs);
    }
}
abstract class Legull_AdminPageFramework_Form_Model_Port extends Legull_AdminPageFramework_Router {
    protected function _importOptions($aStoredOptions, $sPageSlug, $sTabSlug) {
        $oImport = new Legull_AdminPageFramework_ImportOptions($_FILES['__import'], $_POST['__import']);
        $sSectionID = $oImport->getSiblingValue('section_id');
        $sPressedFieldID = $oImport->getSiblingValue('field_id');
        $sPressedInputID = $oImport->getSiblingValue('input_id');
        $bMerge = $oImport->getSiblingValue('is_merge');
        if ($oImport->getError() > 0) {
            $this->setSettingNotice($this->oMsg->get('import_error'));
            return $aStoredOptions;
        }
        $aMIMEType = $this->oUtil->addAndApplyFilters($this, array("import_mime_types_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_mime_types_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_mime_types_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_mime_types_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_mime_types_{$sPageSlug}_{$sTabSlug}" : null, "import_mime_types_{$sPageSlug}", "import_mime_types_{$this->oProp->sClassName}"), array('text/plain', 'application/octet-stream'), $sPressedFieldID, $sPressedInputID, $this);
        $_sType = $oImport->getType();
        if (!in_array($oImport->getType(), $aMIMEType)) {
            $this->setSettingNotice(sprintf($this->oMsg->get('uploaded_file_type_not_supported'), $_sType));
            return $aStoredOptions;
        }
        $vData = $oImport->getImportData();
        if ($vData === false) {
            $this->setSettingNotice($this->oMsg->get('could_not_load_importing_data'));
            return $aStoredOptions;
        }
        $sFormatType = $this->oUtil->addAndApplyFilters($this, array("import_format_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_format_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_format_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_format_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_format_{$sPageSlug}_{$sTabSlug}" : null, "import_format_{$sPageSlug}", "import_format_{$this->oProp->sClassName}"), $oImport->getFormatType(), $sPressedFieldID, $sPressedInputID, $this);
        $oImport->formatImportData($vData, $sFormatType);
        $sImportOptionKey = $this->oUtil->addAndApplyFilters($this, array("import_option_key_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_option_key_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_option_key_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_option_key_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_option_key_{$sPageSlug}_{$sTabSlug}" : null, "import_option_key_{$sPageSlug}", "import_option_key_{$this->oProp->sClassName}"), $oImport->getSiblingValue('option_key'), $sPressedFieldID, $sPressedInputID, $this);
        $vData = $this->oUtil->addAndApplyFilters($this, array("import_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_{$sPageSlug}_{$sTabSlug}" : null, "import_{$sPageSlug}", "import_{$this->oProp->sClassName}"), $vData, $aStoredOptions, $sPressedFieldID, $sPressedInputID, $sFormatType, $sImportOptionKey, $bMerge . $this);
        $bEmpty = empty($vData);
        $this->setSettingNotice($bEmpty ? $this->oMsg->get('not_imported_data') : $this->oMsg->get('imported_data'), $bEmpty ? 'error' : 'updated', $this->oProp->sOptionKey, false);
        if ($sImportOptionKey != $this->oProp->sOptionKey) {
            update_option($sImportOptionKey, $vData);
            return $aStoredOptions;
        }
        return $bMerge ? $this->oUtil->unitArrays($vData, $aStoredOptions) : $vData;
    }
    protected function _exportOptions($vData, $sPageSlug, $sTabSlug) {
        $oExport = new Legull_AdminPageFramework_ExportOptions($_POST['__export'], $this->oProp->sClassName);
        $sSectionID = $oExport->getSiblingValue('section_id');
        $sPressedFieldID = $oExport->getSiblingValue('field_id');
        $sPressedInputID = $oExport->getSiblingValue('input_id');
        $vData = $oExport->getTransientIfSet($vData);
        $vData = $this->oUtil->addAndApplyFilters($this, array("export_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "export_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "export_{$this->oProp->sClassName}_{$sPressedFieldID}", $sTabSlug ? "export_{$sPageSlug}_{$sTabSlug}" : null, "export_{$sPageSlug}", "export_{$this->oProp->sClassName}"), $vData, $sPressedFieldID, $sPressedInputID, $this);
        $sFileName = $this->oUtil->addAndApplyFilters($this, array("export_name_{$this->oProp->sClassName}_{$sPressedInputID}", "export_name_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "export_name_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "export_name_{$this->oProp->sClassName}_{$sPressedFieldID}", $sTabSlug ? "export_name_{$sPageSlug}_{$sTabSlug}" : null, "export_name_{$sPageSlug}", "export_name_{$this->oProp->sClassName}"), $oExport->getFileName(), $sPressedFieldID, $sPressedInputID, $vData, $this);
        $sFormatType = $this->oUtil->addAndApplyFilters($this, array("export_format_{$this->oProp->sClassName}_{$sPressedInputID}", "export_format_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "export_format_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "export_format_{$this->oProp->sClassName}_{$sPressedFieldID}", $sTabSlug ? "export_format_{$sPageSlug}_{$sTabSlug}" : null, "export_format_{$sPageSlug}", "export_format_{$this->oProp->sClassName}"), $oExport->getFormat(), $sPressedFieldID, $sPressedInputID, $this);
        $oExport->doExport($vData, $sFileName, $sFormatType);
        exit;
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Router extends Legull_AdminPageFramework_Factory {
    function __construct($sMetaBoxID, $sTitle, $asPostTypeOrScreenID = array('post'), $sContext = 'normal', $sPriority = 'default', $sCapability = 'edit_posts', $sTextDomain = 'admin-page-framework') {
        if (empty($asPostTypeOrScreenID)) {
            return;
        }
        $_sClassName = get_class($this);
        parent::__construct(isset($this->oProp) ? $this->oProp : new Legull_AdminPageFramework_Property_MetaBox($this, $_sClassName, $sCapability));
        $this->oProp->sMetaBoxID = $sMetaBoxID ? $this->oUtil->sanitizeSlug($sMetaBoxID) : strtolower($_sClassName);
        $this->oProp->sTitle = $sTitle;
        $this->oProp->sContext = $sContext;
        $this->oProp->sPriority = $sPriority;
        if ($this->oProp->bIsAdmin) {
            add_action('current_screen', array($this, '_replyToDetermineToLoad'));
        }
    }
    public function _isInThePage() {
        if (!in_array($this->oProp->sPageNow, array('post.php', 'post-new.php'))) {
            return false;
        }
        if (!in_array($this->oUtil->getCurrentPostType(), $this->oProp->aPostTypes)) {
            return false;
        }
        return true;
    }
    protected function _isInstantiatable() {
        if (isset($GLOBALS['pagenow']) && 'admin-ajax.php' === $GLOBALS['pagenow']) {
            return false;
        }
        return true;
    }
    public function _replyToDetermineToLoad($oScreen) {
        if (!$this->_isInThePage()) {
            return;
        }
        $this->_setUp();
        $this->oUtil->addAndDoAction($this, "set_up_{$this->oProp->sClassName}", $this);
        $this->oProp->_bSetupLoaded = true;
        $this->_registerFormElements($oScreen);
        add_action('add_meta_boxes', array($this, '_replyToAddMetaBox'));
        $this->_setUpValidationHooks($oScreen);
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Model extends Legull_AdminPageFramework_MetaBox_Router {
    private $_bIsNewPost = false;
    protected function _setUpValidationHooks($oScreen) {
        if ('attachment' === $oScreen->post_type && in_array('attachment', $this->oProp->aPostTypes)) {
            add_filter('wp_insert_attachment_data', array($this, '_replyToFilterSavingData'), 10, 2);
        } else {
            add_filter('wp_insert_post_data', array($this, '_replyToFilterSavingData'), 10, 2);
        }
    }
    public function _replyToAddMetaBox() {
        foreach ($this->oProp->aPostTypes as $sPostType) {
            add_meta_box($this->oProp->sMetaBoxID, $this->oProp->sTitle, array($this, '_replyToPrintMetaBoxContents'), $sPostType, $this->oProp->sContext, $this->oProp->sPriority, null);
        }
    }
    protected function _registerFormElements($oScreen) {
        if (!$this->oUtil->isPostDefinitionPage($this->oProp->aPostTypes)) {
            return;
        }
        $this->_loadFieldTypeDefinitions();
        $this->oForm->format();
        $this->oForm->applyConditions();
        $this->oForm->applyFiltersToFields($this, $this->oProp->sClassName);
        $this->_setOptionArray($this->_getPostID(), $this->oForm->aConditionedFields);
        $this->oForm->setDynamicElements($this->oProp->aOptions);
        $this->_registerFields($this->oForm->aConditionedFields);
    }
    private function _getPostID() {
        if (isset($GLOBALS['post']->ID)) {
            return $GLOBALS['post']->ID;
        }
        if (isset($_GET['post'])) {
            return $_GET['post'];
        }
        if (isset($_POST['post_ID'])) {
            return $_POST['post_ID'];
        }
        return null;
    }
    protected function _getInputArray(array $aFieldDefinitionArrays, array $aSectionDefinitionArrays) {
        $_aInput = array();
        foreach ($aFieldDefinitionArrays as $_sSectionID => $_aSubSectionsOrFields) {
            if ('_default' == $_sSectionID) {
                $_aFields = $_aSubSectionsOrFields;
                foreach ($_aFields as $_aField) {
                    $_aInput[$_aField['field_id']] = isset($_POST[$_aField['field_id']]) ? $_POST[$_aField['field_id']] : null;
                }
                continue;
            }
            $_aInput[$_sSectionID] = isset($_aInput[$_sSectionID]) ? $_aInput[$_sSectionID] : array();
            if (!count($this->oUtil->getIntegerElements($_aSubSectionsOrFields))) {
                $_aFields = $_aSubSectionsOrFields;
                foreach ($_aFields as $_aField) {
                    $_aInput[$_sSectionID][$_aField['field_id']] = isset($_POST[$_sSectionID][$_aField['field_id']]) ? $_POST[$_sSectionID][$_aField['field_id']] : null;
                }
                continue;
            }
            foreach ($_POST[$_sSectionID] as $_iIndex => $_aFields) {
                $_aInput[$_sSectionID][$_iIndex] = isset($_POST[$_sSectionID][$_iIndex]) ? $_POST[$_sSectionID][$_iIndex] : null;
            }
        }
        return $_aInput;
    }
    protected function _getSavedMetaArray($iPostID, $aInputStructure) {
        $_aSavedMeta = array();
        foreach ($aInputStructure as $_sSectionORFieldID => $_v) {
            $_aSavedMeta[$_sSectionORFieldID] = get_post_meta($iPostID, $_sSectionORFieldID, true);
        }
        return $_aSavedMeta;
    }
    protected function _setOptionArray($iPostID, $aFields) {
        if (!is_array($aFields)) {
            return;
        }
        if (!is_numeric($iPostID) || !is_int($iPostID + 0)) {
            return;
        }
        $this->oProp->aOptions = is_array($this->oProp->aOptions) ? $this->oProp->aOptions : array();
        foreach ($aFields as $_sSectionID => $_aFields) {
            if ('_default' == $_sSectionID) {
                foreach ($_aFields as $_aField) {
                    $this->oProp->aOptions[$_aField['field_id']] = get_post_meta($iPostID, $_aField['field_id'], true);
                }
            }
            $this->oProp->aOptions[$_sSectionID] = get_post_meta($iPostID, $_sSectionID, true);
        }
        $this->oProp->aOptions = Legull_AdminPageFramework_WPUtility::addAndApplyFilter($this, 'options_' . $this->oProp->sClassName, $this->oProp->aOptions);
        $_aLastInput = isset($_GET['field_errors']) && $_GET['field_errors'] ? $this->oProp->aLastInput : array();
        $this->oProp->aOptions = empty($this->oProp->aOptions) ? array() : Legull_AdminPageFramework_WPUtility::getAsArray($this->oProp->aOptions);
        $this->oProp->aOptions = $_aLastInput + $this->oProp->aOptions;
    }
    public function _replyToGetSectionHeaderOutput($sSectionDescription, $aSection) {
        return $this->oUtil->addAndApplyFilters($this, array('section_head_' . $this->oProp->sClassName . '_' . $aSection['section_id']), $sSectionDescription);
    }
    public function _replyToFilterSavingData($aPostData, $aUnmodified) {
        if ('auto-draft' === $aUnmodified['post_status']) {
            return $aPostData;
        }
        if (!$this->_validateCall()) {
            return $aPostData;
        }
        if (!in_array($aUnmodified['post_type'], $this->oProp->aPostTypes)) {
            return $aPostData;
        }
        $_iPostID = $aUnmodified['ID'];
        if (!current_user_can($this->oProp->sCapability, $_iPostID)) {
            return $aPostData;
        }
        $_aInput = $this->_getInputArray($this->oForm->aConditionedFields, $this->oForm->aConditionedSections);
        $_aInputRaw = $_aInput;
        $_aSavedMeta = $_iPostID ? $this->oUtil->getSavedMetaArray($_iPostID, array_keys($_aInput)) : array();
        $_aInput = $this->oUtil->addAndApplyFilters($this, "validation_{$this->oProp->sClassName}", $this->validate($_aInput, $_aSavedMeta, $this), $_aSavedMeta, $this);
        if ($this->hasFieldError()) {
            $this->_setLastInput($_aInputRaw);
            $aPostData['post_status'] = 'pending';
            add_filter('redirect_post_location', array($this, '_replyToModifyRedirectPostLocation'));
        }
        $this->_updatePostMeta($_iPostID, $_aInput, $this->oForm->dropRepeatableElements($_aSavedMeta));
        return $aPostData;
    }
    public function _replyToModifyRedirectPostLocation($sLocation) {
        remove_filter('redirect_post_location', array($this, __FUNCTION__));
        return add_query_arg(array('message' => 'apf_field_error', 'field_errors' => true), $sLocation);
    }
    private function _updatePostMeta($iPostID, array $aInput, array $aSavedMeta) {
        if (!$iPostID) {
            return;
        }
        foreach ($aInput as $_sSectionOrFieldID => $_vValue) {
            if (is_null($_vValue)) {
                continue;
            }
            $_vSavedValue = isset($aSavedMeta[$_sSectionOrFieldID]) ? $aSavedMeta[$_sSectionOrFieldID] : null;
            if ($_vValue == $_vSavedValue) {
                continue;
            }
            update_post_meta($iPostID, $_sSectionOrFieldID, $_vValue);
        }
    }
    private function _validateCall() {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        if (!isset($_POST[$this->oProp->sMetaBoxID]) || !wp_verify_nonce($_POST[$this->oProp->sMetaBoxID], $this->oProp->sMetaBoxID)) {
            return false;
        }
        return true;
    }
}
abstract class Legull_AdminPageFramework_PostType_Router extends Legull_AdminPageFramework_Factory {
    public function _isInThePage() {
        if (!$this->oProp->bIsAdmin) {
            return false;
        }
        if (!in_array($this->oProp->sPageNow, array('edit.php', 'edit-tags.php', 'post.php', 'post-new.php'))) {
            return false;
        }
        return ($this->oUtil->getCurrentPostType() == $this->oProp->sPostType);
    }
    public function __call($sMethodName, $aArgs = null) {
        if ('setup_pre' == $sMethodName) {
            $this->_setUp();
            $this->oUtil->addAndDoAction($this, "set_up_{$this->oProp->sClassName}", $this);
            $this->oProp->_bSetupLoaded = true;
            return;
        }
        if (has_filter($sMethodName)) {
            return isset($aArgs[0]) ? $aArgs[0] : null;
        }
        parent::__call($sMethodName, $aArgs);
    }
}
abstract class Legull_AdminPageFramework_PostType_Model extends Legull_AdminPageFramework_PostType_Router {
    function __construct($oProp) {
        parent::__construct($oProp);
        add_action("set_up_{$this->oProp->sClassName}", array($this, '_replyToRegisterPostType'), 999);
        $this->oProp->aColumnHeaders = array('cb' => '<input type="checkbox" />', 'title' => $this->oMsg->get('title'), 'author' => $this->oMsg->get('author'), 'comments' => '<div class="comment-grey-bubble"></div>', 'date' => $this->oMsg->get('date'),);
        if ($this->_isInThePage()):
            add_filter("manage_{$this->oProp->sPostType}_posts_columns", array($this, '_replyToSetColumnHeader'));
            add_filter("manage_edit-{$this->oProp->sPostType}_sortable_columns", array($this, '_replyToSetSortableColumns'));
            add_action("manage_{$this->oProp->sPostType}_posts_custom_column", array($this, '_replyToPrintColumnCell'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, '_replyToDisableAutoSave'));
        endif;
    }
    public function _replyToSetSortableColumns($aColumns) {
        return $this->oUtil->addAndApplyFilter($this, "sortable_columns_{$this->oProp->sPostType}", $aColumns);
    }
    public function _replyToSetColumnHeader($aHeaderColumns) {
        return $this->oUtil->addAndApplyFilter($this, "columns_{$this->oProp->sPostType}", $aHeaderColumns);
    }
    public function _replyToPrintColumnCell($sColumnTitle, $iPostID) {
        echo $this->oUtil->addAndApplyFilter($this, "cell_{$this->oProp->sPostType}_{$sColumnTitle}", $sCell = '', $iPostID);
    }
    public function _replyToDisableAutoSave() {
        if ($this->oProp->bEnableAutoSave) {
            return;
        }
        if ($this->oProp->sPostType != get_post_type()) {
            return;
        }
        wp_dequeue_script('autosave');
    }
    public function _replyToRegisterPostType() {
        register_post_type($this->oProp->sPostType, $this->oProp->aPostTypeArgs);
    }
    public function _replyToRegisterTaxonomies() {
        foreach ($this->oProp->aTaxonomies as $_sTaxonomySlug => $_aArgs) {
            $this->_registerTaxonomy($_sTaxonomySlug, is_array($this->oProp->aTaxonomyObjectTypes[$_sTaxonomySlug]) ? $this->oProp->aTaxonomyObjectTypes[$_sTaxonomySlug] : array(), $_aArgs);
        }
    }
    public function _registerTaxonomy($sTaxonomySlug, array $aObjectTypes, array $aArguments) {
        if (!in_array($this->oProp->sPostType, $aObjectTypes)) {
            $aObjectTypes[] = $this->oProp->sPostType;
        }
        register_taxonomy($sTaxonomySlug, array_unique($aObjectTypes), $aArguments);
    }
    public function _replyToRemoveTexonomySubmenuPages() {
        foreach ($this->oProp->aTaxonomyRemoveSubmenuPages as $sSubmenuPageSlug => $sTopLevelPageSlug) {
            remove_submenu_page($sTopLevelPageSlug, $sSubmenuPageSlug);
            unset($this->oProp->aTaxonomyRemoveSubmenuPages[$sSubmenuPageSlug]);
        }
    }
}
abstract class Legull_AdminPageFramework_TaxonomyField_Router extends Legull_AdminPageFramework_Factory {
    public function __construct($oProp) {
        parent::__construct($oProp);
        if ($this->oProp->bIsAdmin) {
            add_action('wp_loaded', array($this, '_replyToDetermineToLoad'));
        }
    }
    public function _isInThePage() {
        if ('admin-ajax.php' == $this->oProp->sPageNow) {
            return true;
        }
        if ('edit-tags.php' != $this->oProp->sPageNow) {
            return false;
        }
        if (isset($_GET['taxonomy']) && !in_array($_GET['taxonomy'], $this->oProp->aTaxonomySlugs)) {
            return false;
        }
        return true;
    }
    public function _replyToDetermineToLoad($oScreen) {
        if (!$this->_isInThePage()) {
            return;
        }
        $this->_setUp();
        $this->oUtil->addAndDoAction($this, "set_up_{$this->oProp->sClassName}", $this);
        $this->oProp->_bSetupLoaded = true;
        add_action('current_screen', array($this, '_replyToRegisterFormElements'), 20);
        foreach ($this->oProp->aTaxonomySlugs as $__sTaxonomySlug) {
            add_action("created_{$__sTaxonomySlug}", array($this, '_replyToValidateOptions'), 10, 2);
            add_action("edited_{$__sTaxonomySlug}", array($this, '_replyToValidateOptions'), 10, 2);
            add_action("{$__sTaxonomySlug}_add_form_fields", array($this, '_replyToPrintFieldsWOTableRows'));
            add_action("{$__sTaxonomySlug}_edit_form_fields", array($this, '_replyToPrintFieldsWithTableRows'));
            add_filter("manage_edit-{$__sTaxonomySlug}_columns", array($this, '_replyToManageColumns'), 10, 1);
            add_filter("manage_edit-{$__sTaxonomySlug}_sortable_columns", array($this, '_replyToSetSortableColumns'));
            add_action("manage_{$__sTaxonomySlug}_custom_column", array($this, '_replyToPrintColumnCell'), 10, 3);
        }
    }
}
abstract class Legull_AdminPageFramework_TaxonomyField_Model extends Legull_AdminPageFramework_TaxonomyField_Router {
    public function _replyToManageColumns($aColumns) {
        if (isset($_GET['taxonomy']) && $_GET['taxonomy']) {
            $aColumns = $this->oUtil->addAndApplyFilter($this, "columns_{$_GET['taxonomy']}", $aColumns);
        }
        $aColumns = $this->oUtil->addAndApplyFilter($this, "columns_{$this->oProp->sClassName}", $aColumns);
        return $aColumns;
    }
    public function _replyToSetSortableColumns($aSortableColumns) {
        if (isset($_GET['taxonomy']) && $_GET['taxonomy']) {
            $aSortableColumns = $this->oUtil->addAndApplyFilter($this, "sortable_columns_{$_GET['taxonomy']}", $aSortableColumns);
        }
        $aSortableColumns = $this->oUtil->addAndApplyFilter($this, "sortable_columns_{$this->oProp->sClassName}", $aSortableColumns);
        return $aSortableColumns;
    }
    public function _replyToRegisterFormElements($oScreen) {
        $this->_loadFieldTypeDefinitions();
        $this->oForm->format();
        $this->oForm->applyConditions();
        $this->_registerFields($this->oForm->aConditionedFields);
    }
    protected function _setOptionArray($iTermID = null, $sOptionKey) {
        $aOptions = get_option($sOptionKey, array());
        $this->oProp->aOptions = isset($iTermID, $aOptions[$iTermID]) ? $aOptions[$iTermID] : array();
    }
    public function _replyToValidateOptions($iTermID) {
        if (!isset($_POST[$this->oProp->sClassHash])) {
            return;
        }
        if (!wp_verify_nonce($_POST[$this->oProp->sClassHash], $this->oProp->sClassHash)) {
            return;
        }
        $aTaxonomyFieldOptions = get_option($this->oProp->sOptionKey, array());
        $aOldOptions = isset($aTaxonomyFieldOptions[$iTermID]) ? $aTaxonomyFieldOptions[$iTermID] : array();
        $aSubmittedOptions = array();
        foreach ($this->oForm->aFields as $_sSectionID => $_aFields) {
            foreach ($_aFields as $_sFieldID => $_aField) {
                if (isset($_POST[$_sFieldID])) {
                    $aSubmittedOptions[$_sFieldID] = $_POST[$_sFieldID];
                }
            }
        }
        $aSubmittedOptions = $this->oUtil->addAndApplyFilters($this, 'validation_' . $this->oProp->sClassName, $aSubmittedOptions, $aOldOptions, $this);
        $aTaxonomyFieldOptions[$iTermID] = $this->oUtil->uniteArrays($aSubmittedOptions, $aOldOptions);
        update_option($this->oProp->sOptionKey, $aTaxonomyFieldOptions);
    }
}
abstract class Legull_AdminPageFramework_UserMeta_Router extends Legull_AdminPageFramework_Factory {
    public function __construct($oProp) {
        parent::__construct($oProp);
        if ($this->oProp->bIsAdmin) {
            add_action('wp_loaded', array($this, '_replyToDetermineToLoad'));
        }
    }
    public function _isInThePage() {
        if (!$this->oProp->bIsAdmin) {
            return false;
        }
        return in_array($this->oProp->sPageNow, array('user-new.php', 'user-edit.php', 'profile.php'));
    }
    public function _replyToDetermineToLoad($oScreen) {
        if (!$this->_isInThePage()) {
            return;
        }
        $this->_setUp();
        $this->oUtil->addAndDoAction($this, "set_up_{$this->oProp->sClassName}", $this);
        $this->oProp->_bSetupLoaded = true;
        add_action('current_screen', array($this, '_replyToRegisterFormElements'), 20);
        add_action('show_user_profile', array($this, '_replyToPrintFields'));
        add_action('edit_user_profile', array($this, '_replyToPrintFields'));
        add_action('user_new_form', array($this, '_replyToPrintFields'));
        add_action('personal_options_update', array($this, '_replyToSaveFieldValues'));
        add_action('edit_user_profile_update', array($this, '_replyToSaveFieldValues'));
        add_action('user_register', array($this, '_replyToSaveFieldValues'));
    }
}
abstract class Legull_AdminPageFramework_UserMeta_Model extends Legull_AdminPageFramework_UserMeta_Router {
    public function _replyToRegisterFormElements($oScreen) {
        $this->_loadFieldTypeDefinitions();
        $this->oForm->format();
        $this->oForm->applyConditions();
        $this->_registerFields($this->oForm->aConditionedFields);
    }
    protected function _setOptionArray($iUserID) {
        if (!$iUserID) {
            return;
        }
        $_aOptions = array();
        foreach ($this->oForm->aConditionedFields as $_sSectionID => $_aFields) {
            if ('_default' == $_sSectionID) {
                foreach ($_aFields as $_aField) {
                    $_aOptions[$_aField['field_id']] = get_user_meta($iUserID, $_aField['field_id'], true);
                }
            }
            $_aOptions[$_sSectionID] = get_user_meta($iUserID, $_sSectionID, true);
        }
        $_aOptions = Legull_AdminPageFramework_WPUtility::addAndApplyFilter($this, 'options_' . $this->oProp->sClassName, $_aOptions);
        $_aLastInput = isset($_GET['field_errors']) && $_GET['field_errors'] ? $this->oProp->aLastInput : array();
        $_aOptions = empty($_aOptions) ? array() : Legull_AdminPageFramework_WPUtility::getAsArray($_aOptions);
        $_aOptions = $_aLastInput + $_aOptions;
        $this->oProp->aOptions = $_aOptions;
    }
    public function _replyToSaveFieldValues($iUserID) {
        if (!current_user_can('edit_user', $iUserID)) {
            return;
        }
        $_aInput = $this->_getInputArray($this->oForm->aConditionedFields, $this->oForm->aConditionedSections);
        $_aInputRaw = $_aInput;
        $_aSavedMeta = $iUserID ? $this->_getSavedMetaArray($iUserID, array_keys($_aInput)) : array();
        $_aInput = $this->oUtil->addAndApplyFilters($this, "validation_{$this->oProp->sClassName}", $this->validate($_aInput, $_aSavedMeta, $this), $_aSavedMeta, $this);
        if ($this->hasFieldError()) {
            $this->_setLastInput($_aInputRaw);
        }
        $this->_updatePostMeta($iUserID, $_aInput, $this->oForm->dropRepeatableElements($_aSavedMeta));
    }
    private function _getSavedMetaArray($iUserID, array $aKeys) {
        $_aSavedMeta = array();
        foreach ($aKeys as $_sKey) {
            $_aSavedMeta[$_sKey] = get_post_meta($iUserID, $_sKey, true);
        }
        return $_aSavedMeta;
    }
    private function _updatePostMeta($iUserID, array $aInput, array $aSavedMeta) {
        if (!$iUserID) {
            return;
        }
        foreach ($aInput as $_sSectionOrFieldID => $_vValue) {
            if (is_null($_vValue)) {
                continue;
            }
            $_vSavedValue = isset($aSavedMeta[$_sSectionOrFieldID]) ? $aSavedMeta[$_sSectionOrFieldID] : null;
            if ($_vValue == $_vSavedValue) {
                continue;
            }
            update_user_meta($iUserID, $_sSectionOrFieldID, $_vValue);
        }
    }
    protected function _getInputArray(array $aFieldDefinitionArrays, array $aSectionDefinitionArrays) {
        $_aInput = array();
        foreach ($aFieldDefinitionArrays as $_sSectionID => $_aSubSectionsOrFields) {
            if ('_default' == $_sSectionID) {
                $_aFields = $_aSubSectionsOrFields;
                foreach ($_aFields as $_aField) {
                    $_aInput[$_aField['field_id']] = isset($_POST[$_aField['field_id']]) ? $_POST[$_aField['field_id']] : null;
                }
                continue;
            }
            $_aInput[$_sSectionID] = isset($_aInput[$_sSectionID]) ? $_aInput[$_sSectionID] : array();
            if (!count($this->oUtil->getIntegerElements($_aSubSectionsOrFields))) {
                $_aFields = $_aSubSectionsOrFields;
                foreach ($_aFields as $_aField) {
                    $_aInput[$_sSectionID][$_aField['field_id']] = isset($_POST[$_sSectionID][$_aField['field_id']]) ? $_POST[$_sSectionID][$_aField['field_id']] : null;
                }
                continue;
            }
            foreach ($_POST[$_sSectionID] as $_iIndex => $_aFields) {
                $_aInput[$_sSectionID][$_iIndex] = isset($_POST[$_sSectionID][$_iIndex]) ? $_POST[$_sSectionID][$_iIndex] : null;
            }
        }
        return $_aInput;
    }
}
abstract class Legull_AdminPageFramework_Widget_Router extends Legull_AdminPageFramework_Factory {
    public function __call($sMethodName, $aArgs = null) {
        if ('setup_pre' === $sMethodName) {
            $this->_setUp();
            $this->oUtil->addAndDoAction($this, "set_up_{$this->oProp->sClassName}", $this);
            $this->oProp->_bSetupLoaded = true;
            return;
        }
        if (has_filter($sMethodName)) {
            return isset($aArgs[0]) ? $aArgs[0] : null;
        }
        parent::__call($sMethodName, $aArgs);
    }
}
abstract class Legull_AdminPageFramework_Widget_Model extends Legull_AdminPageFramework_Widget_Router {
    function __construct($oProp) {
        parent::__construct($oProp);
        if (did_action('widgets_init')) {
            add_action("set_up_{$this->oProp->sClassName}", array($this, '_replyToRegisterWidget'), 20);
        } else {
            add_action('widgets_init', array($this, '_replyToRegisterWidget'), 20);
        }
    }
    public function validate($aSubmit, $aStored, $oAdminWidget) {
        return $aSubmit;
    }
    public function _isInThePage() {
        return true;
    }
    public function _replyToRegisterWidget() {
        global $wp_widget_factory;
        if (!is_object($wp_widget_factory)) {
            return;
        }
        $wp_widget_factory->widgets[$this->oProp->sClassName] = new Legull_AdminPageFramework_Widget_Factory($this, $this->oProp->sWidgetTitle, is_array($this->oProp->aWidgetArguments) ? $this->oProp->aWidgetArguments : array());
    }
    public function _registerFormElements($aOptions) {
        $this->_loadFieldTypeDefinitions();
        $this->oProp->aOptions = $aOptions;
        $this->oForm->format();
        $this->oForm->applyConditions();
        $this->oForm->setDynamicElements($this->oProp->aOptions);
        $this->_registerFields($this->oForm->aConditionedFields);
    }
}
abstract class Legull_AdminPageFramework_Form_Model_Validation extends Legull_AdminPageFramework_Form_Model_Port {
    protected function _handleSubmittedData() {
        if (!$this->_verifyFormSubmit()) {
            return;
        }
        $_aDefaultOptions = $this->oProp->getDefaultOptions($this->oForm->aFields);
        $_aOptions = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_{$this->oProp->sClassName}", $this->oUtil->uniteArrays($this->oProp->aOptions, $_aDefaultOptions), $this);
        $_aInput = isset($_POST[$this->oProp->sOptionKey]) ? stripslashes_deep($_POST[$this->oProp->sOptionKey]) : array();
        $_aInputRaw = $_aInput;
        $_sTabSlug = isset($_POST['tab_slug']) ? $_POST['tab_slug'] : '';
        $_sPageSlug = isset($_POST['page_slug']) ? $_POST['page_slug'] : '';
        $_aInput = $this->oUtil->uniteArrays($_aInput, $this->oUtil->castArrayContents($_aInput, $this->_removePageElements($_aDefaultOptions, $_sPageSlug, $_sTabSlug)));
        $_aSubmit = isset($_POST['__submit']) ? $_POST['__submit'] : array();
        $_sPressedFieldID = $this->_getPressedSubmitButtonData($_aSubmit, 'field_id');
        $_sPressedInputID = $this->_getPressedSubmitButtonData($_aSubmit, 'input_id');
        $_sSubmitSectionID = $this->_getPressedSubmitButtonData($_aSubmit, 'section_id');
        if (has_action("submit_{$this->oProp->sClassName}_{$_sPressedInputID}")) {
            trigger_error('Admin Page Framework: ' . ' : ' . sprintf(__('The hook <code>%1$s</code>is deprecated. Use <code>%2$s</code> instead.', $this->oProp->sTextDomain), "submit_{instantiated class name}_{pressed input id}", "submit_{instantiated class name}_{pressed field id}"), E_USER_WARNING);
        }
        $this->oUtil->addAndDoActions($this, array("submit_{$this->oProp->sClassName}_{$_sPressedInputID}", $_sSubmitSectionID ? "submit_{$this->oProp->sClassName}_{$_sSubmitSectionID}_{$_sPressedFieldID}" : "submit_{$this->oProp->sClassName}_{$_sPressedFieldID}", $_sSubmitSectionID ? "submit_{$this->oProp->sClassName}_{$_sSubmitSectionID}" : null, isset($_POST['tab_slug']) ? "submit_{$this->oProp->sClassName}_{$_sPageSlug}_{$_sTabSlug}" : null, "submit_{$this->oProp->sClassName}_{$_sPageSlug}", "submit_{$this->oProp->sClassName}",), $_aInput, $_aOptions, $this);
        $_aStatus = array('settings-updated' => true);
        $_aInput = $this->_validateSubmittedData($_aInput, $_aInputRaw, $_aOptions, $_aStatus);
        $_bUpdated = false;
        if (!$this->oProp->_bDisableSavingOptions) {
            $_bUpdated = $this->oProp->updateOption($_aInput);
        }
        $this->oUtil->addAndDoActions($this, array($_sSubmitSectionID ? "submit_after_{$this->oProp->sClassName}_{$_sSubmitSectionID}_{$_sPressedFieldID}" : "submit_after_{$this->oProp->sClassName}_{$_sPressedFieldID}", $_sSubmitSectionID ? "submit_after_{$this->oProp->sClassName}_{$_sSubmitSectionID}" : null, isset($_POST['tab_slug']) ? "submit_after_{$this->oProp->sClassName}_{$_sPageSlug}_{$_sTabSlug}" : null, "submit_after_{$this->oProp->sClassName}_{$_sPageSlug}", "submit_after_{$this->oProp->sClassName}",), $_bUpdated ? $_aInput : array(), $_aOptions, $this);
        exit(wp_redirect($this->_getSettingUpdateURL($_aStatus, $_sPageSlug, $_sTabSlug)));
    }
    private function _getSettingUpdateURL(array $aStatus, $sPageSlug, $sTabSlug) {
        $aStatus = $this->oUtil->addAndApplyFilters($this, array("options_update_status_{$sPageSlug}_{$sTabSlug}", "options_update_status_{$sPageSlug}", "options_update_status_{$this->oProp->sClassName}",), $aStatus);
        $_aRemoveQueries = array();
        if (!isset($aStatus['field_errors']) || !$aStatus['field_errors']) {
            unset($aStatus['field_errors']);
            $_aRemoveQueries[] = 'field_errors';
        }
        return $this->oUtil->addAndApplyFilters($this, array("setting_update_url_{$this->oProp->sClassName}",), $this->oUtil->getQueryURL($aStatus, $_aRemoveQueries, $_SERVER['REQUEST_URI']));
    }
    private function _verifyFormSubmit() {
        if (!isset($_POST['_is_admin_page_framework'], $_POST['page_slug'], $_POST['tab_slug'], $_POST['_wp_http_referer'])) {
            return false;
        }
        $_sRequestURI = remove_query_arg(array('settings-updated', 'confirmation', 'field_errors'), wp_unslash($_SERVER['REQUEST_URI']));
        $_sReffererURI = remove_query_arg(array('settings-updated', 'confirmation', 'field_errors'), $_POST['_wp_http_referer']);
        if ($_sRequestURI != $_sReffererURI) {
            return false;
        }
        $_sNonceTransientKey = 'form_' . md5($this->oProp->sClassName . get_current_user_id());
        if ($_POST['_is_admin_page_framework'] !== $this->oUtil->getTransient($_sNonceTransientKey)) {
            $this->setAdminNotice($this->oMsg->get('nonce_verification_failed'));
            return false;
        }
        return true;
    }
    protected function _validateSubmittedData($aInput, $aInputRaw, $aOptions, &$aStatus) {
        $_sTabSlug = isset($_POST['tab_slug']) ? $_POST['tab_slug'] : '';
        $_sPageSlug = isset($_POST['page_slug']) ? $_POST['page_slug'] : '';
        $_aSubmit = isset($_POST['__submit']) ? $_POST['__submit'] : array();
        $_sPressedInputName = $this->_getPressedSubmitButtonData($_aSubmit, 'name');
        $_bIsReset = $this->_getPressedSubmitButtonData($_aSubmit, 'is_reset');
        $_sKeyToReset = $this->_getPressedSubmitButtonData($_aSubmit, 'reset_key');
        $_sSubmitSectionID = $this->_getPressedSubmitButtonData($_aSubmit, 'section_id');
        $_bConfirmingToSendEmail = $this->_getPressedSubmitButtonData($_aSubmit, 'confirming_sending_email');
        $_bConfirmedToSendEmail = $this->_getPressedSubmitButtonData($_aSubmit, 'confirmed_sending_email');
        $_aSubmitInformation = array('page_slug' => $_sPageSlug, 'tab_slug' => $_sTabSlug, 'input_id' => $this->_getPressedSubmitButtonData($_aSubmit, 'input_id'), 'section_id' => $_sSubmitSectionID, 'field_id' => $this->_getPressedSubmitButtonData($_aSubmit, 'field_id'),);
        if ($_bConfirmedToSendEmail) {
            $this->_sendEmailInBackground($aInputRaw, $_sPressedInputName, $_sSubmitSectionID);
            $this->oProp->_bDisableSavingOptions = true;
            $this->oUtil->deleteTransient('apf_tfd' . md5('temporary_form_data_' . $this->oProp->sClassName . get_current_user_id()));
            add_action("setting_update_url_{$this->oProp->sClassName}", array($this, '_replyToRemoveConfirmationQueryKey'));
            return $aInputRaw;
        }
        if ($_bIsReset) {
            $aStatus = $aStatus + array('confirmation' => 'reset');
            return $this->_confirmSubmitButtonAction($_sPressedInputName, $_sSubmitSectionID, 'reset');
        }
        if ($_sLinkURL = $this->_getPressedSubmitButtonData($_aSubmit, 'link_url')) {
            exit(wp_redirect($_sLinkURL));
        }
        if ($_sRedirectURL = $this->_getPressedSubmitButtonData($_aSubmit, 'redirect_url')) {
            $aStatus = $aStatus + array('confirmation' => 'redirect');
            $this->_setRedirectTransients($_sRedirectURL, $_sPageSlug);
        }
        $aInput = $this->_getFilteredOptions($aInput, $aInputRaw, $aOptions, $_aSubmitInformation);
        $_bHasFieldErrors = $this->hasFieldError();
        if ($_bHasFieldErrors) {
            $this->_setLastInput($aInputRaw);
            $aStatus = $aStatus + array('field_errors' => $_bHasFieldErrors);
        }
        if (!$_bHasFieldErrors && isset($_POST['__import']['submit'], $_FILES['__import'])) {
            return $this->_importOptions($this->oProp->aOptions, $_sPageSlug, $_sTabSlug);
        }
        if (!$_bHasFieldErrors && isset($_POST['__export']['submit'])) {
            exit($this->_exportOptions($this->oProp->aOptions, $_sPageSlug, $_sTabSlug));
        }
        if ($_sKeyToReset) {
            $aInput = $this->_resetOptions($_sKeyToReset, $aInput);
        }
        if (!$_bHasFieldErrors && $_bConfirmingToSendEmail) {
            $this->_setLastInput($aInput);
            $this->oProp->_bDisableSavingOptions = true;
            $aStatus = $aStatus + array('confirmation' => 'email');
            return $this->_confirmSubmitButtonAction($_sPressedInputName, $_sSubmitSectionID, 'email');
        }
        if (!$this->hasSettingNotice()) {
            $_bEmpty = empty($aInput);
            $this->setSettingNotice($_bEmpty ? $this->oMsg->get('option_cleared') : $this->oMsg->get('option_updated'), $_bEmpty ? 'error' : 'updated', $this->oProp->sOptionKey, false);
        }
        return $aInput;
    }
    public function _replyToRemoveConfirmationQueryKey($sSettingUpdateURL) {
        return remove_query_arg(array('confirmation',), $sSettingUpdateURL);
    }
    private function _sendEmailInBackground($aInput, $sPressedInputNameFlat, $sSubmitSectionID) {
        $_sTranskentKey = 'apf_em_' . md5($sPressedInputNameFlat . get_current_user_id());
        $_aEmailOptions = $this->oUtil->getTransient($_sTranskentKey);
        $this->oUtil->deleteTransient($_sTranskentKey);
        $_aEmailOptions = $this->oUtil->getAsArray($_aEmailOptions) + array('to' => '', 'subject' => '', 'message' => '', 'headers' => '', 'attachments' => '', 'is_html' => false, 'from' => '', 'name' => '',);
        $_sTransientKey = 'apf_emd_' . md5($sPressedInputNameFlat . get_current_user_id());
        $_aFormEmailData = array('email_options' => $_aEmailOptions, 'input' => $aInput, 'section_id' => $sSubmitSectionID,);
        $_bIsSet = $this->oUtil->setTransient($_sTransientKey, $_aFormEmailData, 100);
        wp_remote_get(add_query_arg(array('apf_action' => 'email', 'transient' => $_sTransientKey,), admin_url($GLOBALS['pagenow'])), array('timeout' => 0.01, 'sslverify' => false,));
        $_bSent = $_bIsSet;
        $this->setSettingNotice($this->oMsg->get($_bSent ? 'email_scheduled' : 'email_could_not_send'), $_bSent ? 'updated' : 'error');
    }
    private function _confirmSubmitButtonAction($sPressedInputName, $sSectionID, $sType = 'reset') {
        switch ($sType) {
            default:
            case 'reset':
                $_sFieldErrorMessage = $this->oMsg->get('reset_options');
                $_sTransientKey = 'apf_rc_' . md5($sPressedInputName . get_current_user_id());
            break;
            case 'email':
                $_sFieldErrorMessage = $this->oMsg->get('send_email');
                $_sTransientKey = 'apf_ec_' . md5($sPressedInputName . get_current_user_id());
            break;
        }
        $_aNameKeys = explode('|', $sPressedInputName);
        $_sFieldID = $sSectionID ? $_aNameKeys[2] : $_aNameKeys[1];
        $_aErrors = array();
        if ($sSectionID) {
            $_aErrors[$sSectionID][$_sFieldID] = $_sFieldErrorMessage;
        } else {
            $_aErrors[$_sFieldID] = $_sFieldErrorMessage;
        }
        $this->setFieldErrors($_aErrors);
        $this->oUtil->setTransient($_sTransientKey, $sPressedInputName, 60 * 2);
        $this->setSettingNotice($this->oMsg->get('confirm_perform_task'), 'error confirmation');
        return $this->oProp->aOptions;
    }
    private function _resetOptions($sKeyToReset, $aInput) {
        if (!$this->oProp->sOptionKey) {
            return array();
        }
        if (1 == $sKeyToReset || true === $sKeyToReset) {
            delete_option($this->oProp->sOptionKey);
            return array();
        }
        unset($this->oProp->aOptions[trim($sKeyToReset) ], $aInput[trim($sKeyToReset) ]);
        update_option($this->oProp->sOptionKey, $this->oProp->aOptions);
        $this->setSettingNotice($this->oMsg->get('specified_option_been_deleted'));
        return $aInput;
    }
    private function _setRedirectTransients($sURL, $sPageSlug) {
        if (empty($sURL)) {
            return;
        }
        $_sTransient = 'apf_rurl' . md5(trim("redirect_{$this->oProp->sClassName}_{$sPageSlug}"));
        return $this->oUtil->setTransient($_sTransient, $sURL, 60 * 2);
    }
    private function _getPressedSubmitButtonData($aPostElements, $sTargetKey = 'field_id') {
        foreach ($aPostElements as $_sInputID => $_aSubElements) {
            $_aNameKeys = explode('|', $_aSubElements['name']);
            if (count($_aNameKeys) == 2 && isset($_POST[$_aNameKeys[0]][$_aNameKeys[1]])) {
                return isset($_aSubElements[$sTargetKey]) ? $_aSubElements[$sTargetKey] : null;
            }
            if (count($_aNameKeys) == 3 && isset($_POST[$_aNameKeys[0]][$_aNameKeys[1]][$_aNameKeys[2]])) {
                return isset($_aSubElements[$sTargetKey]) ? $_aSubElements[$sTargetKey] : null;
            }
            if (count($_aNameKeys) == 4 && isset($_POST[$_aNameKeys[0]][$_aNameKeys[1]][$_aNameKeys[2]][$_aNameKeys[3]])) {
                return isset($_aSubElements[$sTargetKey]) ? $_aSubElements[$sTargetKey] : null;
            }
        }
        return null;
    }
    private function _getFilteredOptions($aInput, $aInputRaw, $aStoredData, $aSubmitInformation) {
        $_aData = array('sPageSlug' => $aSubmitInformation['page_slug'], 'sTabSlug' => $aSubmitInformation['tab_slug'], 'aInput' => $this->oUtil->getAsArray($aInput), 'aStoredData' => $aStoredData, 'aStoredTabData' => array(), 'aStoredDataWODynamicElements' => $this->oUtil->addAndApplyFilter($this, "validation_saved_options_without_dynamic_elements_{$this->oProp->sClassName}", $this->oForm->dropRepeatableElements($aStoredData), $this), 'aStoredTabDataWODynamicElements' => array(), 'aEmbeddedDataWODynamicElements' => array(), 'aSubmitInformation' => $aSubmitInformation,);
        $_aData = $this->_validateEachField($_aData, $aInputRaw);
        $_aData = $this->_validateTabFields($_aData);
        $_aData = $this->_validatePageFields($_aData);
        return $this->_getValidatedData("validation_{$this->oProp->sClassName}", $_aData['aInput'], $_aData['aStoredData'], $_aData['aSubmitInformation']);
    }
    private function _validateEachField(array $aData, array $aInputToParse) {
        foreach ($aInputToParse as $_sID => $_aSectionOrFields) {
            if ($this->oForm->isSection($_sID)) {
                if (!$this->_isValidSection($_sID, $aData['sPageSlug'], $aData['sTabSlug'])) {
                    continue;
                }
                foreach ($_aSectionOrFields as $_sFieldID => $_aFields) {
                    $aData['aInput'][$_sID][$_sFieldID] = $this->_getValidatedData("validation_{$this->oProp->sClassName}_{$_sID}_{$_sFieldID}", $aData['aInput'][$_sID][$_sFieldID], isset($aData['aStoredData'][$_sID][$_sFieldID]) ? $aData['aStoredData'][$_sID][$_sFieldID] : null, $aData['aSubmitInformation']);
                }
                $_aSectionInput = is_array($aData['aInput'][$_sID]) ? $aData['aInput'][$_sID] : array();
                $_aSectionInput = $_aSectionInput + (isset($aData['aStoredDataWODynamicElements'][$_sID]) && is_array($aData['aStoredDataWODynamicElements'][$_sID]) ? $aData['aStoredDataWODynamicElements'][$_sID] : array());
                $aData['aInput'][$_sID] = $this->_getValidatedData("validation_{$this->oProp->sClassName}_{$_sID}", $_aSectionInput, isset($aData['aStoredData'][$_sID]) ? $aData['aStoredData'][$_sID] : null, $aData['aSubmitInformation']);
                continue;
            }
            if (!$this->_isValidSection('_default', $aData['sPageSlug'], $aData['sTabSlug'])) {
                continue;
            }
            $aData['aInput'][$_sID] = $this->_getValidatedData("validation_{$this->oProp->sClassName}_{$_sID}", $aData['aInput'][$_sID], isset($aData['aStoredData'][$_sID]) ? $aData['aStoredData'][$_sID] : null, $aData['aSubmitInformation']);
        }
        return $aData;
    }
    private function _isValidSection($sSectionID, $sPageSlug, $sTabSlug) {
        if ($sPageSlug && isset($this->oForm->aSections[$sSectionID]['page_slug']) && $sPageSlug !== $this->oForm->aSections[$sSectionID]['page_slug']) {
            return false;
        }
        if ($sTabSlug && isset($this->oForm->aSections[$sSectionID]['tab_slug']) && $sTabSlug !== $this->oForm->aSections[$sSectionID]['tab_slug']) {
            return false;
        }
        return true;
    }
    private function _validateTabFields(array $aData) {
        if (!$aData['sTabSlug'] || !$aData['sPageSlug']) {
            return $aData;
        }
        $aData['aStoredTabData'] = $this->oForm->getTabOptions($aData['aStoredData'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aStoredTabData'] = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_{$aData['sPageSlug']}_{$aData['sTabSlug']}", $aData['aStoredTabData'], $this);
        $_aOtherTabOptions = $this->oForm->getOtherTabOptions($aData['aStoredData'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aStoredTabDataWODynamicElements'] = $this->oForm->getTabOptions($aData['aStoredDataWODynamicElements'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aStoredTabDataWODynamicElements'] = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_without_dynamic_elements_{$aData['sPageSlug']}_{$aData['sTabSlug']}", $aData['aStoredTabDataWODynamicElements'], $this);
        $aData['aStoredDataWODynamicElements'] = $aData['aStoredTabDataWODynamicElements'] + $aData['aStoredDataWODynamicElements'];
        $_aTabOnlyOptionsWODynamicElements = $this->oForm->getTabOnlyOptions($aData['aStoredTabDataWODynamicElements'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aInput'] = $aData['aInput'] + $_aTabOnlyOptionsWODynamicElements;
        $aData['aInput'] = $this->_getValidatedData("validation_{$aData['sPageSlug']}_{$aData['sTabSlug']}", $aData['aInput'], $aData['aStoredTabData'], $aData['aSubmitInformation']);
        $aData['aEmbeddedDataWODynamicElements'] = $this->_getEmbeddedOptions($aData['aInput'], $aData['aStoredTabDataWODynamicElements'], $_aTabOnlyOptionsWODynamicElements);
        $aData['aInput'] = $aData['aInput'] + $_aOtherTabOptions;
        return $aData;
    }
    private function _validatePageFields(array $aData) {
        if (!$aData['sPageSlug']) {
            return $aData['aInput'];
        }
        $_aPageOptions = $this->oForm->getPageOptions($aData['aStoredData'], $aData['sPageSlug']);
        $_aPageOptions = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_{$aData['sPageSlug']}", $_aPageOptions, $this);
        $_aOtherPageOptions = $this->oUtil->invertCastArrayContents($this->oForm->getOtherPageOptions($aData['aStoredData'], $aData['sPageSlug']), $_aPageOptions);
        $_aPageOptionsWODynamicElements = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_without_dynamic_elements_{$aData['sPageSlug']}", $this->oForm->getPageOptions($aData['aStoredDataWODynamicElements'], $aData['sPageSlug']), $this);
        $_aPageOnlyOptionsWODynamicElements = $this->oForm->getPageOnlyOptions($_aPageOptionsWODynamicElements, $aData['sPageSlug']);
        $aData['aInput'] = $aData['aInput'] + $_aPageOnlyOptionsWODynamicElements;
        $aData['aInput'] = $this->_getValidatedData("validation_{$aData['sPageSlug']}", $aData['aInput'], $_aPageOptions, $aData['aSubmitInformation']);
        $_aPageOptions = $aData['sTabSlug'] && !empty($aData['aStoredTabData']) ? $this->oUtil->invertCastArrayContents($_aPageOptions, $aData['aStoredTabData']) : (!$aData['sTabSlug'] ? array() : $_aPageOptions);
        $_aEmbeddedOptionsWODynamicElements = $aData['aEmbeddedDataWODynamicElements'] + $this->_getEmbeddedOptions($aData['aInput'], $_aPageOptionsWODynamicElements, $_aPageOnlyOptionsWODynamicElements);
        $aData['aInput'] = $aData['aInput'] + $this->oUtil->uniteArrays($_aPageOptions, $_aOtherPageOptions, $_aEmbeddedOptionsWODynamicElements);
        return $aData;
    }
    private function _getEmbeddedOptions(array $aInput, array $aOptions, array $aPageSpecificOptions) {
        $_aEmbeddedData = $this->oUtil->invertCastArrayContents($aOptions, $aPageSpecificOptions);
        return $this->oUtil->invertCastArrayContents($_aEmbeddedData, $aInput);
    }
    private function _getValidatedData($sFilterName, $aInput, $aStoredData, $aSubmitInfo = array()) {
        return $this->oUtil->addAndApplyFilter($this, $sFilterName, $aInput, $aStoredData, $this, $aSubmitInfo);
    }
    private function _removePageElements($aOptions, $sPageSlug, $sTabSlug) {
        if (!$sPageSlug && !$sTabSlug) {
            return $aOptions;
        }
        if ($sTabSlug && $sPageSlug) {
            return $this->oForm->getOtherTabOptions($aOptions, $sPageSlug, $sTabSlug);
        }
        return $this->oForm->getOtherPageOptions($aOptions, $sPageSlug);
    }
}
abstract class Legull_AdminPageFramework_Form_Model extends Legull_AdminPageFramework_Form_Model_Validation {
    protected $aFieldErrors;
    static protected $_sFieldsType = 'page';
    protected $_sTargetPageSlug = null;
    protected $_sTargetTabSlug = null;
    protected $_sTargetSectionTabSlug = null;
    function __construct($sOptionKey = null, $sCallerPath = null, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        parent::__construct($sOptionKey, $sCallerPath, $sCapability, $sTextDomain);
        if ($this->oProp->bIsAdminAjax) {
            return;
        }
        if (!$this->oProp->bIsAdmin) {
            return;
        }
        add_action("load_after_{$this->oProp->sClassName}", array($this, '_replyToRegisterSettings'), 20);
        add_action("load_after_{$this->oProp->sClassName}", array($this, '_replyToCheckRedirects'), 21);
        if (isset($_GET['apf_action'], $_GET['transient']) && 'email' === $_GET['apf_action']) {
            ignore_user_abort(true);
            $this->oUtil->registerAction('plugins_loaded', array($this, '_replyToSendFormEmail'));
        }
        if (isset($_REQUEST['apf_remote_request_test']) && '_testing' === $_REQUEST['apf_remote_request_test']) {
            exit('OK');
        }
    }
    static public $_bDoneEmail = false;
    public function _replyToSendFormEmail() {
        if (self::$_bDoneEmail) {
            return;
        }
        self::$_bDoneEmail = true;
        $_sTransient = isset($_GET['transient']) ? $_GET['transient'] : '';
        if (!$_sTransient) {
            return;
        }
        $_aFormEmail = $this->oUtil->getTransient($_sTransient);
        $this->oUtil->deleteTransient($_sTransient);
        if (!is_array($_aFormEmail)) {
            return;
        }
        $_oEmail = new Legull_AdminPageFramework_FormEmail($_aFormEmail['email_options'], $_aFormEmail['input'], $_aFormEmail['section_id']);
        $_bSent = $_oEmail->send();
        exit;
    }
    public function _replyToCheckRedirects() {
        if (!$this->_isInThePage()) {
            return;
        }
        if (!(isset($_GET['settings-updated']) && !empty($_GET['settings-updated']))) {
            return;
        }
        if (!isset($_GET['confirmation']) || 'redirect' !== $_GET['confirmation']) {
            return;
        }
        $_sTransient = 'apf_rurl' . md5(trim("redirect_{$this->oProp->sClassName}_{$_GET['page']}"));
        $_aError = $this->_getFieldErrors($_GET['page'], false);
        if (!empty($_aError)) {
            $this->oUtil->deleteTransient($_sTransient);
            return;
        }
        $_sURL = $this->oUtil->getTransient($_sTransient);
        if (false === $_sURL) {
            return;
        }
        $this->oUtil->deleteTransient($_sTransient);
        exit(wp_redirect($_sURL));
    }
    public function _replyToRegisterSettings() {
        if (!$this->_isInThePage()) {
            return;
        }
        $this->oForm->aSections = $this->oUtil->addAndApplyFilter($this, "sections_{$this->oProp->sClassName}", $this->oForm->aSections);
        foreach ($this->oForm->aFields as $_sSectionID => & $_aFields) {
            $_aFields = $this->oUtil->addAndApplyFilter($this, "fields_{$this->oProp->sClassName}_{$_sSectionID}", $_aFields);
            unset($_aFields);
        }
        $this->oForm->aFields = $this->oUtil->addAndApplyFilter($this, "fields_{$this->oProp->sClassName}", $this->oForm->aFields);
        $this->oForm->setDefaultPageSlug($this->oProp->sDefaultPageSlug);
        $this->oForm->setOptionKey($this->oProp->sOptionKey);
        $this->oForm->setCallerClassName($this->oProp->sClassName);
        $this->oForm->format();
        $_sCurrentPageSlug = isset($_GET['page']) && $_GET['page'] ? $_GET['page'] : '';
        $this->oForm->setCurrentPageSlug($_sCurrentPageSlug);
        $this->oForm->setCurrentTabSlug($this->oProp->getCurrentTab($_sCurrentPageSlug));
        $this->oForm->applyConditions();
        $this->oForm->applyFiltersToFields($this, $this->oProp->sClassName);
        $this->oForm->setDynamicElements($this->oProp->aOptions);
        $this->_loadFieldTypeDefinitions();
        foreach ($this->oForm->aConditionedSections as $_aSection) {
            if (empty($_aSection['help'])) {
                continue;
            }
            $this->addHelpTab(array('page_slug' => $_aSection['page_slug'], 'page_tab_slug' => $_aSection['tab_slug'], 'help_tab_title' => $_aSection['title'], 'help_tab_id' => $_aSection['section_id'], 'help_tab_content' => $_aSection['help'], 'help_tab_sidebar_content' => $_aSection['help_aside'] ? $_aSection['help_aside'] : "",));
        }
        $this->_registerFields($this->oForm->aConditionedFields);
        $this->oProp->bEnableForm = true;
        $this->_handleSubmittedData();
    }
    protected function _registerField(array $aField) {
        Legull_AdminPageFramework_FieldTypeRegistration::_setFieldResources($aField, $this->oProp, $this->oResource);
        if ($aField['help']) {
            $this->addHelpTab(array('page_slug' => $aField['page_slug'], 'page_tab_slug' => $aField['tab_slug'], 'help_tab_title' => $aField['section_title'], 'help_tab_id' => $aField['section_id'], 'help_tab_content' => "<span class='contextual-help-tab-title'>" . $aField['title'] . "</span> - " . PHP_EOL . $aField['help'], 'help_tab_sidebar_content' => $aField['help_aside'] ? $aField['help_aside'] : "",));
        }
        if (isset($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfDoOnRegistration']) && is_callable($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfDoOnRegistration'])) {
            call_user_func_array($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfDoOnRegistration'], array($aField));
        }
    }
    public function getSavedOptions() {
        $_bHasConfirmation = isset($_GET['confirmation']);
        $_bHasFieldErrors = isset($_GET['field_errors']) && $_GET['field_errors'];
        $_aLastInput = $_bHasConfirmation || $_bHasFieldErrors ? $this->oProp->aLastInput : array();
        return $_aLastInput + $this->oProp->aOptions;
    }
}
abstract class Legull_AdminPageFramework_MetaBox_View extends Legull_AdminPageFramework_MetaBox_Model {
    public function _replyToPrintMetaBoxContents($oPost, $vArgs) {
        $_aOutput = array();
        $_aOutput[] = wp_nonce_field($this->oProp->sMetaBoxID, $this->oProp->sMetaBoxID, true, false);
        $_oFieldsTable = new Legull_AdminPageFramework_FormTable($this->oProp->aFieldTypeDefinitions, $this->_getFieldErrors(), $this->oMsg);
        $_aOutput[] = $_oFieldsTable->getFormTables($this->oForm->aConditionedSections, $this->oForm->aConditionedFields, array($this, '_replyToGetSectionHeaderOutput'), array($this, '_replyToGetFieldOutput'));
        $this->oUtil->addAndDoActions($this, 'do_' . $this->oProp->sClassName, $this);
        echo $this->oUtil->addAndApplyFilters($this, "content_{$this->oProp->sClassName}", $this->content(implode(PHP_EOL, $_aOutput)));
    }
    public function content($sContent) {
        return $sContent;
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Controller extends Legull_AdminPageFramework_MetaBox_View {
    public function setUp() {
    }
    public function enqueueStyles($aSRCs, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueStyles($aSRCs, $aPostTypes, $aCustomArgs);
    }
    public function enqueueStyle($sSRC, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueStyle($sSRC, $aPostTypes, $aCustomArgs);
    }
    public function enqueueScripts($aSRCs, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueScripts($aSRCs, $aPostTypes, $aCustomArgs);
    }
    public function enqueueScript($sSRC, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueScript($sSRC, $aPostTypes, $aCustomArgs);
    }
}
abstract class Legull_AdminPageFramework_MetaBox extends Legull_AdminPageFramework_MetaBox_Controller {
    static protected $_sFieldsType = 'post_meta_box';
    function __construct($sMetaBoxID, $sTitle, $asPostTypeOrScreenID = array('post'), $sContext = 'normal', $sPriority = 'default', $sCapability = 'edit_posts', $sTextDomain = 'admin-page-framework') {
        if (!$this->_isInstantiatable()) {
            return;
        }
        $this->oProp = new Legull_AdminPageFramework_Property_MetaBox($this, get_class($this), $sCapability, $sTextDomain, self::$_sFieldsType);
        $this->oProp->aPostTypes = is_string($asPostTypeOrScreenID) ? array($asPostTypeOrScreenID) : $asPostTypeOrScreenID;
        parent::__construct($sMetaBoxID, $sTitle, $asPostTypeOrScreenID, $sContext, $sPriority, $sCapability, $sTextDomain);
        $this->oUtil->addAndDoAction($this, "start_{$this->oProp->sClassName}", $this);
    }
}
abstract class Legull_AdminPageFramework_PostType_View extends Legull_AdminPageFramework_PostType_Model {
    function __construct($oProp) {
        parent::__construct($oProp);
        if ($this->_isInThePage()) {
            add_action('restrict_manage_posts', array($this, '_replyToAddAuthorTableFilter'));
            add_action('restrict_manage_posts', array($this, '_replyToAddTaxonomyTableFilter'));
            add_filter('parse_query', array($this, '_replyToGetTableFilterQueryForTaxonomies'));
            add_action('admin_head', array($this, '_replyToPrintStyle'));
        }
        add_action('the_content', array($this, '_replyToFilterPostTypeContent'));
    }
    public function _replyToAddAuthorTableFilter() {
        if (!$this->oProp->bEnableAuthorTableFileter) {
            return;
        }
        if (!(isset($_GET['post_type']) && post_type_exists($_GET['post_type']) && in_array(strtolower($_GET['post_type']), array($this->oProp->sPostType)))) {
            return;
        }
        wp_dropdown_users(array('show_option_all' => 'Show all Authors', 'show_option_none' => false, 'name' => 'author', 'selected' => !empty($_GET['author']) ? $_GET['author'] : 0, 'include_selected' => false));
    }
    public function _replyToAddTaxonomyTableFilter() {
        if ($GLOBALS['typenow'] != $this->oProp->sPostType) {
            return;
        }
        $oPostCount = wp_count_posts($this->oProp->sPostType);
        if ($oPostCount->publish + $oPostCount->future + $oPostCount->draft + $oPostCount->pending + $oPostCount->private + $oPostCount->trash == 0) {
            return;
        }
        foreach (get_object_taxonomies($GLOBALS['typenow']) as $sTaxonomySulg) {
            if (!in_array($sTaxonomySulg, $this->oProp->aTaxonomyTableFilters)) continue;
            $oTaxonomy = get_taxonomy($sTaxonomySulg);
            if (wp_count_terms($oTaxonomy->name) == 0) continue;
            wp_dropdown_categories(array('show_option_all' => $this->oMsg->get('show_all') . ' ' . $oTaxonomy->label, 'taxonomy' => $sTaxonomySulg, 'name' => $oTaxonomy->name, 'orderby' => 'name', 'selected' => intval(isset($_GET[$sTaxonomySulg])), 'hierarchical' => $oTaxonomy->hierarchical, 'show_count' => true, 'hide_empty' => false, 'hide_if_empty' => false, 'echo' => true,));
        }
    }
    public function _replyToGetTableFilterQueryForTaxonomies($oQuery = null) {
        if ('edit.php' != $this->oProp->sPageNow) {
            return $oQuery;
        }
        if (!isset($GLOBALS['typenow'])) {
            return $oQuery;
        }
        foreach (get_object_taxonomies($GLOBALS['typenow']) as $sTaxonomySlug) {
            if (!in_array($sTaxonomySlug, $this->oProp->aTaxonomyTableFilters)) {
                continue;
            }
            $sVar = & $oQuery->query_vars[$sTaxonomySlug];
            if (!isset($sVar)) {
                continue;
            }
            $oTerm = get_term_by('id', $sVar, $sTaxonomySlug);
            if (is_object($oTerm)) {
                $sVar = $oTerm->slug;
            }
        }
        return $oQuery;
    }
    public function _replyToPrintStyle() {
        if ($this->oUtil->getCurrentPostType() !== $this->oProp->sPostType) {
            return;
        }
        if (isset($this->oProp->aPostTypeArgs['screen_icon']) && $this->oProp->aPostTypeArgs['screen_icon']) {
            $this->oProp->sStyle.= $this->_getStylesForPostTypeScreenIcon($this->oProp->aPostTypeArgs['screen_icon']);
        }
        $this->oProp->sStyle = $this->oUtil->addAndApplyFilters($this, "style_{$this->oProp->sClassName}", $this->oProp->sStyle);
        if (!empty($this->oProp->sStyle)) {
            echo "<style type='text/css' id='admin-page-framework-style-post-type'>" . $this->oProp->sStyle . "</style>";
        }
    }
    private function _getStylesForPostTypeScreenIcon($sSRC) {
        $sNone = 'none';
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        return "#post-body-content {margin-bottom: 10px;}#edit-slug-box {display: {$sNone};}#icon-edit.icon32.icon32-posts-{$this->oProp->sPostType} {background: url('{$sSRC}') no-repeat;background-size: 32px 32px;} ";
    }
    public function content($sContent) {
        return $sContent;
    }
    public function _replyToFilterPostTypeContent($sContent) {
        if (!is_singular()) {
            return $sContent;
        }
        if (!is_main_query()) {
            return $sContent;
        }
        global $post;
        if ($this->oProp->sPostType !== $post->post_type) {
            return $sContent;
        }
        return $this->oUtil->addAndApplyFilters($this, "content_{$this->oProp->sClassName}", $this->content($sContent));
    }
}
abstract class Legull_AdminPageFramework_PostType_Controller extends Legull_AdminPageFramework_PostType_View {
    function __construct($oProp) {
        parent::__construct($oProp);
        if (did_action('init')) {
            $this->setup_pre();
        } {
            add_action('init', array($this, 'setup_pre'));
        }
    }
    public function setUp() {
    }
    public function enqueueStyles($aSRCs, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyles')) {
            return $this->oResource->_enqueueStyles($aSRCs, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    public function enqueueStyle($sSRC, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyle')) {
            return $this->oResource->_enqueueStyle($sSRC, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    public function enqueueScripts($aSRCs, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScripts')) {
            return $this->oResource->_enqueueScripts($aSRCs, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    public function enqueueScript($sSRC, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScript')) {
            return $this->oResource->_enqueueScript($sSRC, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    protected function setAutoSave($bEnableAutoSave = True) {
        $this->oProp->bEnableAutoSave = $bEnableAutoSave;
    }
    protected function addTaxonomy($sTaxonomySlug, array $aArgs, array $aAdditionalObjectTypes = array()) {
        Legull_AdminPageFramework_Debug::log($this->oUtil);
        $sTaxonomySlug = $this->oUtil->sanitizeSlug($sTaxonomySlug);
        $this->oProp->aTaxonomies[$sTaxonomySlug] = $aArgs;
        if (isset($aArgs['show_table_filter']) && $aArgs['show_table_filter']) {
            $this->oProp->aTaxonomyTableFilters[] = $sTaxonomySlug;
        }
        if (isset($aArgs['show_in_sidebar_menus']) && !$aArgs['show_in_sidebar_menus']) {
            $this->oProp->aTaxonomyRemoveSubmenuPages["edit-tags.php?taxonomy={$sTaxonomySlug}&amp;post_type={$this->oProp->sPostType}"] = "edit.php?post_type={$this->oProp->sPostType}";
        }
        $_aExistingObjectTypes = isset($this->oProp->aTaxonomyObjectTypes[$sTaxonomySlug]) && is_array($this->oProp->aTaxonomyObjectTypes[$sTaxonomySlug]) ? $this->oProp->aTaxonomyObjectTypes[$sTaxonomySlug] : array();
        $aAdditionalObjectTypes = array_merge($_aExistingObjectTypes, $aAdditionalObjectTypes);
        $this->oProp->aTaxonomyObjectTypes[$sTaxonomySlug] = array_unique($aAdditionalObjectTypes);
        if (did_action('init')) {
            $this->_registerTaxonomy($sTaxonomySlug, $aAdditionalObjectTypes, $aArgs);
        } else {
            if (1 == count($this->oProp->aTaxonomies)) {
                add_action('init', array($this, '_replyToRegisterTaxonomies'));
            }
        }
        if (did_action('admin_menu')) {
            $this->_replyToRemoveTexonomySubmenuPages();
        } else {
            if (1 == count($this->oProp->aTaxonomyRemoveSubmenuPages)) {
                add_action('admin_menu', array($this, '_replyToRemoveTexonomySubmenuPages'), 999);
            }
        }
    }
    protected function setAuthorTableFilter($bEnableAuthorTableFileter = false) {
        $this->oProp->bEnableAuthorTableFileter = $bEnableAuthorTableFileter;
    }
    protected function setPostTypeArgs($aArgs) {
        $this->setArguments(( array )$aArgs);
    }
    protected function setArguments(array $aArguments = array()) {
        $this->oProp->aPostTypeArgs = $aArguments;
    }
    protected function setFooterInfoLeft($sHTML, $bAppend = true) {
        if (isset($this->oLink)) $this->oLink->aFooterInfo['sLeft'] = $bAppend ? $this->oLink->aFooterInfo['sLeft'] . $sHTML : $sHTML;
    }
    protected function setFooterInfoRight($sHTML, $bAppend = true) {
        if (isset($this->oLink)) $this->oLink->aFooterInfo['sRight'] = $bAppend ? $this->oLink->aFooterInfo['sRight'] . $sHTML : $sHTML;
    }
}
abstract class Legull_AdminPageFramework_PostType extends Legull_AdminPageFramework_PostType_Controller {
    public function __construct($sPostType, $aArgs = array(), $sCallerPath = null, $sTextDomain = 'admin-page-framework') {
        if (empty($sPostType)) {
            return;
        }
        $this->oProp = new Legull_AdminPageFramework_Property_PostType($this, $sCallerPath ? trim($sCallerPath) : ((is_admin() && isset($GLOBALS['pagenow']) && in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php', 'plugins.php', 'tags.php', 'edit-tags.php',))) ? Legull_AdminPageFramework_Utility::getCallerScriptPath(__FILE__) : null), get_class($this), 'publish_posts', $sTextDomain, 'post_type');
        $this->oProp->sPostType = Legull_AdminPageFramework_WPUtility::sanitizeSlug($sPostType);
        $this->oProp->aPostTypeArgs = $aArgs;
        parent::__construct($this->oProp);
        $this->oUtil->addAndDoAction($this, "start_{$this->oProp->sClassName}", $this);
    }
}
abstract class Legull_AdminPageFramework_TaxonomyField_View extends Legull_AdminPageFramework_TaxonomyField_Model {
    public function _replyToPrintFieldsWOTableRows($oTerm) {
        echo $this->_getFieldsOutput(isset($oTerm->term_id) ? $oTerm->term_id : null, false);
    }
    public function _replyToPrintFieldsWithTableRows($oTerm) {
        echo $this->_getFieldsOutput(isset($oTerm->term_id) ? $oTerm->term_id : null, true);
    }
    private function _getFieldsOutput($iTermID, $bRenderTableRow) {
        $_aOutput = array();
        $_aOutput[] = wp_nonce_field($this->oProp->sClassHash, $this->oProp->sClassHash, true, false);
        $this->_setOptionArray($iTermID, $this->oProp->sOptionKey);
        $this->oForm->format();
        $_oFieldsTable = new Legull_AdminPageFramework_FormTable($this->oProp->aFieldTypeDefinitions, $this->_getFieldErrors(), $this->oMsg);
        $_aOutput[] = $bRenderTableRow ? $_oFieldsTable->getFieldRows($this->oForm->aFields['_default'], array($this, '_replyToGetFieldOutput')) : $_oFieldsTable->getFields($this->oForm->aFields['_default'], array($this, '_replyToGetFieldOutput'));
        $_sOutput = $this->oUtil->addAndApplyFilters($this, 'content_' . $this->oProp->sClassName, implode(PHP_EOL, $_aOutput));
        $this->oUtil->addAndDoActions($this, 'do_' . $this->oProp->sClassName, $this);
        return $_sOutput;
    }
    public function _replyToPrintColumnCell($vValue, $sColumnSlug, $sTermID) {
        $_sCellHTML = '';
        if (isset($_GET['taxonomy']) && $_GET['taxonomy']) {
            $_sCellHTML = $this->oUtil->addAndApplyFilter($this, "cell_{$_GET['taxonomy']}", $vValue, $sColumnSlug, $sTermID);
        }
        $_sCellHTML = $this->oUtil->addAndApplyFilter($this, "cell_{$this->oProp->sClassName}", $_sCellHTML, $sColumnSlug, $sTermID);
        $_sCellHTML = $this->oUtil->addAndApplyFilter($this, "cell_{$this->oProp->sClassName}_{$sColumnSlug}", $_sCellHTML, $sTermID);
        echo $_sCellHTML;
    }
}
abstract class Legull_AdminPageFramework_TaxonomyField_Controller extends Legull_AdminPageFramework_TaxonomyField_View {
    public function setUp() {
    }
}
abstract class Legull_AdminPageFramework_TaxonomyField extends Legull_AdminPageFramework_TaxonomyField_Controller {
    static protected $_sFieldsType = 'taxonomy';
    function __construct($asTaxonomySlug, $sOptionKey = '', $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        if (empty($asTaxonomySlug)) {
            return;
        }
        $this->oProp = new Legull_AdminPageFramework_Property_TaxonomyField($this, get_class($this), $sCapability, $sTextDomain, self::$_sFieldsType);
        $this->oProp->aTaxonomySlugs = ( array )$asTaxonomySlug;
        $this->oProp->sOptionKey = $sOptionKey ? $sOptionKey : $this->oProp->sClassName;
        parent::__construct($this->oProp);
        $this->oUtil->addAndDoAction($this, "start_{$this->oProp->sClassName}");
    }
}
abstract class Legull_AdminPageFramework_UserMeta_View extends Legull_AdminPageFramework_UserMeta_Model {
    public function content($sContent) {
        return $sContent;
    }
    public function _replyToPrintFields($oUser) {
        $this->_setOptionArray($oUser->ID);
        echo $this->_getFieldsOutput($oUser->ID);
    }
    private function _getFieldsOutput($iUserID) {
        $_aOutput = array();
        $_oFieldsTable = new Legull_AdminPageFramework_FormTable($this->oProp->aFieldTypeDefinitions, $this->_getFieldErrors(), $this->oMsg);
        $_aOutput[] = $_oFieldsTable->getFormTables($this->oForm->aConditionedSections, $this->oForm->aConditionedFields, array($this, '_replyToGetSectionHeaderOutput'), array($this, '_replyToGetFieldOutput'));
        $_sOutput = $this->oUtil->addAndApplyFilters($this, 'content_' . $this->oProp->sClassName, $this->content(implode(PHP_EOL, $_aOutput)));
        $this->oUtil->addAndDoActions($this, 'do_' . $this->oProp->sClassName, $this);
        return $_sOutput;
    }
    public function _replyToGetSectionHeaderOutput($sSectionDescription, $aSection) {
        return $this->oUtil->addAndApplyFilters($this, array('section_head_' . $this->oProp->sClassName . '_' . $aSection['section_id']), $sSectionDescription);
    }
}
abstract class Legull_AdminPageFramework_UserMeta_Controller extends Legull_AdminPageFramework_UserMeta_View {
    public function setUp() {
    }
    public function enqueueStyles($aSRCs, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueStyles($aSRCs, $aPostTypes, $aCustomArgs);
    }
    public function enqueueStyle($sSRC, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueStyle($sSRC, $aPostTypes, $aCustomArgs);
    }
    public function enqueueScripts($aSRCs, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueScripts($aSRCs, $aPostTypes, $aCustomArgs);
    }
    public function enqueueScript($sSRC, $aPostTypes = array(), $aCustomArgs = array()) {
        return $this->oResource->_enqueueScript($sSRC, $aPostTypes, $aCustomArgs);
    }
}
abstract class Legull_AdminPageFramework_UserMeta extends Legull_AdminPageFramework_UserMeta_Controller {
    static protected $_sFieldsType = 'user_meta';
    public function __construct($sCapability = 'edit_user', $sTextDomain = 'admin-page-framework') {
        $this->oProp = new Legull_AdminPageFramework_Property_UserMeta($this, get_class($this), $sCapability, $sTextDomain, self::$_sFieldsType);
        parent::__construct($this->oProp);
        $this->oUtil->addAndDoAction($this, "start_{$this->oProp->sClassName}");
    }
}
abstract class Legull_AdminPageFramework_Widget_View extends Legull_AdminPageFramework_Widget_Model {
    public function content($sContent, $aArguments, $aFormData) {
        return $sContent;
    }
    public function _printWidgetForm() {
        $_oFieldsTable = new Legull_AdminPageFramework_FormTable($this->oProp->aFieldTypeDefinitions, $this->_getFieldErrors(), $this->oMsg);
        $_aOutput[] = $_oFieldsTable->getFormTables($this->oForm->aConditionedSections, $this->oForm->aConditionedFields, array($this, '_replyToGetSectionHeaderOutput'), array($this, '_replyToGetFieldOutput'));
        echo implode(PHP_EOL, $_aOutput);
    }
    public function _replyToGetSectionHeaderOutput($sSectionDescription, $aSection) {
        return $this->oUtil->addAndApplyFilters($this, array('section_head_' . $this->oProp->sClassName . '_' . $aSection['section_id']), $sSectionDescription);
    }
}
abstract class Legull_AdminPageFramework_Widget_Controller extends Legull_AdminPageFramework_Widget_View {
    function __construct($oProp) {
        parent::__construct($oProp);
        if ($this->_isInThePage()):
            if (did_action('widgets_init')) {
                $this->setup_pre();
            } {
                add_action('widgets_init', array($this, 'setup_pre'));
            }
        endif;
    }
    public function setUp() {
    }
    public function load($oAdminWidget) {
    }
    public function enqueueStyles($aSRCs, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyles')) {
            return $this->oResource->_enqueueStyles($aSRCs, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    public function enqueueStyle($sSRC, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyle')) {
            return $this->oResource->_enqueueStyle($sSRC, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    public function enqueueScripts($aSRCs, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScripts')) {
            return $this->oResource->_enqueueScripts($aSRCs, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    public function enqueueScript($sSRC, $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScript')) {
            return $this->oResource->_enqueueScript($sSRC, array($this->oProp->sPostType), $aCustomArgs);
        }
    }
    protected function setArguments(array $aArguments = array()) {
        $this->oProp->aWidgetArguments = $aArguments;
    }
}
abstract class Legull_AdminPageFramework_Widget extends Legull_AdminPageFramework_Widget_Controller {
    static protected $_sFieldsType = 'widget';
    public function __construct($sWidgetTitle, $aWidgetArguments = array(), $sCapability = 'edit_theme_options', $sTextDomain = 'admin-page-framework') {
        if (empty($sWidgetTitle)) {
            return;
        }
        $this->oProp = new Legull_AdminPageFramework_Property_Widget($this, null, get_class($this), $sCapability, $sTextDomain, self::$_sFieldsType);
        $this->oProp->sWidgetTitle = $sWidgetTitle;
        $this->oProp->aWidgetArguments = $aWidgetArguments;
        parent::__construct($this->oProp);
        $this->oUtil->addAndDoAction($this, "start_{$this->oProp->sClassName}", $this);
    }
}
abstract class Legull_AdminPageFramework_Form_View extends Legull_AdminPageFramework_Form_Model {
    public function _replyToGetSectionHeaderOutput($sSectionDescription, $aSection) {
        return $this->oUtil->addAndApplyFilters($this, array('section_head_' . $this->oProp->sClassName . '_' . $aSection['section_id']), $sSectionDescription);
    }
    public function _replyToGetFieldOutput($aField) {
        $_sCurrentPageSlug = isset($_GET['page']) ? $_GET['page'] : null;
        $_sSectionID = isset($aField['section_id']) ? $aField['section_id'] : '_default';
        $_sFieldID = $aField['field_id'];
        if ($aField['page_slug'] != $_sCurrentPageSlug) {
            return '';
        }
        $this->aFieldErrors = isset($this->aFieldErrors) ? $this->aFieldErrors : $this->_getFieldErrors($_sCurrentPageSlug);
        $sFieldType = isset($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfRenderField']) && is_callable($this->oProp->aFieldTypeDefinitions[$aField['type']]['hfRenderField']) ? $aField['type'] : 'default';
        $_aTemp = $this->getSavedOptions();
        $_oField = new Legull_AdminPageFramework_FormField($aField, $_aTemp, $this->aFieldErrors, $this->oProp->aFieldTypeDefinitions, $this->oMsg);
        $_sFieldOutput = $_oField->_getFieldOutput();
        unset($_oField);
        return $this->oUtil->addAndApplyFilters($this, array(isset($aField['section_id']) && $aField['section_id'] != '_default' ? 'field_' . $this->oProp->sClassName . '_' . $aField['section_id'] . '_' . $_sFieldID : 'field_' . $this->oProp->sClassName . '_' . $_sFieldID,), $_sFieldOutput, $aField);
    }
}
abstract class Legull_AdminPageFramework_Form_Controller extends Legull_AdminPageFramework_Form_View {
    public function addSettingSections($aSection1, $aSection2 = null, $_and_more = null) {
        foreach (func_get_args() as $asSection) {
            $this->addSettingSection($asSection);
        }
        $this->_sTargetTabSlug = null;
        $this->_sTargetSectionTabSlug = null;
    }
    public function addSettingSection($asSection) {
        if (!is_array($asSection)) {
            $this->_sTargetPageSlug = is_string($asSection) ? $asSection : $this->_sTargetPageSlug;
            return;
        }
        $aSection = $asSection;
        $this->_sTargetPageSlug = isset($aSection['page_slug']) ? $aSection['page_slug'] : $this->_sTargetPageSlug;
        $this->_sTargetTabSlug = isset($aSection['tab_slug']) ? $aSection['tab_slug'] : $this->_sTargetTabSlug;
        $this->_sTargetSectionTabSlug = isset($aSection['section_tab_slug']) ? $aSection['section_tab_slug'] : $this->_sTargetSectionTabSlug;
        $aSection = $this->oUtil->uniteArrays($aSection, array('page_slug' => $this->_sTargetPageSlug ? $this->_sTargetPageSlug : null, 'tab_slug' => $this->_sTargetTabSlug ? $this->_sTargetTabSlug : null, 'section_tab_slug' => $this->_sTargetSectionTabSlug ? $this->_sTargetSectionTabSlug : null,));
        $aSection['page_slug'] = $aSection['page_slug'] ? $this->oUtil->sanitizeSlug($aSection['page_slug']) : ($this->oProp->sDefaultPageSlug ? $this->oProp->sDefaultPageSlug : null);
        $aSection['tab_slug'] = $this->oUtil->sanitizeSlug($aSection['tab_slug']);
        $aSection['section_tab_slug'] = $this->oUtil->sanitizeSlug($aSection['section_tab_slug']);
        if (!$aSection['page_slug']) {
            return;
        }
        $this->oForm->addSection($aSection);
    }
    public function removeSettingSections($sSectionID1 = null, $sSectionID2 = null, $_and_more = null) {
        foreach (func_get_args() as $_sSectionID) {
            $this->oForm->removeSection($_sSectionID);
        }
    }
    public function addSettingFields($aField1, $aField2 = null, $_and_more = null) {
        foreach (func_get_args() as $aField) {
            $this->addSettingField($aField);
        }
    }
    public function addSettingField($asField) {
        $this->oForm->addField($asField);
    }
    public function removeSettingFields($sFieldID1, $sFieldID2 = null, $_and_more) {
        foreach (func_get_args() as $_sFieldID) {
            $this->oForm->removeField($_sFieldID);
        }
    }
    public function getValue() {
        $_aParams = func_get_args();
        return Legull_AdminPageFramework_WPUtility::getOption($this->oProp->sOptionKey, $_aParams, null, $this->getSavedOptions() + $this->oProp->getDefaultOptions($this->oForm->aFields));
    }
    public function getFieldValue($sFieldID, $sSectionID = '') {
        trigger_error('Admin Page Framework: ' . ' : ' . sprintf(__('The method is deprecated: %1$s. Use %2$s instead.', $this->oProp->sTextDomain), __METHOD__, 'getValue()'), E_USER_WARNING);
        $_aOptions = $this->oUtil->uniteArrays($this->oProp->aOptions, $this->oProp->getDefaultOptions($this->oForm->aFields));
        if (!$sSectionID) {
            if (array_key_exists($sFieldID, $_aOptions)) {
                return $_aOptions[$sFieldID];
            }
            foreach ($_aOptions as $aOptions) {
                if (array_key_exists($sFieldID, $aOptions)) {
                    return $aOptions[$sFieldID];
                }
            }
        }
        if ($sSectionID) {
            if (array_key_exists($sSectionID, $_aOptions) && array_key_exists($sFieldID, $_aOptions[$sSectionID])) {
                return $_aOptions[$sSectionID][$sFieldID];
            }
        }
        return null;
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Page_Router extends Legull_AdminPageFramework_MetaBox_View {
    function __construct($sMetaBoxID, $sTitle, $asPageSlugs = array(), $sContext = 'normal', $sPriority = 'default', $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        parent::__construct($sMetaBoxID, $sTitle, $asPageSlugs, $sContext, $sPriority, $sCapability, $sTextDomain);
        $this->oUtil->addAndDoAction($this, "start_{$this->oProp->sClassName}", $this);
    }
    protected function _isInstantiatable() {
        if (isset($GLOBALS['pagenow']) && 'admin-ajax.php' === $GLOBALS['pagenow']) {
            return false;
        }
        return true;
    }
    public function _isInThePage() {
        if (!$this->oProp->bIsAdmin) {
            return false;
        }
        if (!isset($_GET['page'])) {
            return false;
        }
        if (array_key_exists($_GET['page'], $this->oProp->aPageSlugs)) {
            return true;
        }
        return in_array($_GET['page'], $this->oProp->aPageSlugs);
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Page_Model extends Legull_AdminPageFramework_MetaBox_Page_Router {
    static protected $_sFieldsType = 'page_meta_box';
    function __construct($sMetaBoxID, $sTitle, $asPageSlugs = array(), $sContext = 'normal', $sPriority = 'default', $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        $this->oProp = new Legull_AdminPageFramework_Property_MetaBox_Page($this, get_class($this), $sCapability, $sTextDomain, self::$_sFieldsType);
        $this->oProp->aPageSlugs = is_string($asPageSlugs) ? array($asPageSlugs) : $asPageSlugs;
        parent::__construct($sMetaBoxID, $sTitle, $asPageSlugs, $sContext, $sPriority, $sCapability, $sTextDomain);
    }
    protected function _setUpValidationHooks($oScreen) {
        foreach ($this->oProp->aPageSlugs as $_sIndexOrPageSlug => $_asTabArrayOrPageSlug) {
            if (is_string($_asTabArrayOrPageSlug)) {
                $_sPageSlug = $_asTabArrayOrPageSlug;
                add_filter("validation_saved_options_without_dynamic_elements_{$_sPageSlug}", array($this, '_replyToFilterPageOptionsWODynamicElements'), 10, 2);
                add_filter("validation_{$_sPageSlug}", array($this, '_replyToValidateOptions'), 10, 3);
                add_filter("options_update_status_{$_sPageSlug}", array($this, '_replyToModifyOptionsUpdateStatus'));
                continue;
            }
            $_sPageSlug = $_sIndexOrPageSlug;
            $_aTabs = $_asTabArrayOrPageSlug;
            foreach ($_aTabs as $_sTabSlug) {
                add_filter("validation_{$_sPageSlug}_{$_sTabSlug}", array($this, '_replyToValidateOptions'), 10, 3);
                add_filter("validation_saved_options_without_dynamic_elements_{$_sPageSlug}_{$_sTabSlug}", array($this, '_replyToFilterPageOptionsWODynamicElements'), 10, 2);
                add_filter("options_update_status_{$_sPageSlug}_{$_sTabSlug}", array($this, '_replyToModifyOptionsUpdateStatus'));
            }
        }
    }
    protected function getFieldOutput($aField) {
        $_sOptionKey = $this->_getOptionKey();
        $aField['option_key'] = $_sOptionKey ? $_sOptionKey : null;
        $aField['page_slug'] = isset($_GET['page']) ? $_GET['page'] : '';
        return parent::getFieldOutput($aField);
    }
    private function _getOptionkey() {
        return isset($_GET['page']) ? $this->oProp->getOptionKey($_GET['page']) : null;
    }
    public function _replyToAddMetaBox($sPageHook = '') {
        foreach ($this->oProp->aPageSlugs as $sKey => $asPage) {
            if (is_string($asPage)) {
                $this->_addMetaBox($asPage);
                continue;
            }
            if (!is_array($asPage)) {
                continue;
            }
            $_sPageSlug = $sKey;
            foreach ($asPage as $_sTabSlug) {
                if (!$this->oProp->isCurrentTab($_sTabSlug)) {
                    continue;
                }
                $this->_addMetaBox($_sPageSlug);
            }
        }
    }
    private function _addMetaBox($sPageSlug) {
        add_meta_box($this->oProp->sMetaBoxID, $this->oProp->sTitle, array($this, '_replyToPrintMetaBoxContents'), $this->oProp->_getScreenIDOfPage($sPageSlug), $this->oProp->sContext, $this->oProp->sPriority, null);
    }
    public function _replyToFilterPageOptions($aPageOptions) {
        return $aPageOptions;
    }
    public function _replyToFilterPageOptionsWODynamicElements($aOptionsWODynamicElements, $oFactory) {
        return $this->oForm->dropRepeatableElements($aOptionsWODynamicElements);
    }
    public function _replyToValidateOptions($aNewPageOptions, $aOldPageOptions) {
        $_aFieldsModel = $this->oForm->getFieldsModel();
        $_aNewMetaBoxInput = $this->oUtil->castArrayContents($_aFieldsModel, $_POST);
        $_aOldMetaBoxInput = $this->oUtil->castArrayContents($_aFieldsModel, $aOldPageOptions);
        $_aNewMetaBoxInput = stripslashes_deep($_aNewMetaBoxInput);
        $_aNewMetaBoxInputRaw = $_aNewMetaBoxInput;
        $_aNewMetaBoxInput = $this->validate($_aNewMetaBoxInput, $_aOldMetaBoxInput, $this);
        $_aNewMetaBoxInput = $this->oUtil->addAndApplyFilters($this, "validation_{$this->oProp->sClassName}", $_aNewMetaBoxInput, $_aOldMetaBoxInput, $this);
        if ($this->hasFieldError()) {
            $this->_setLastInput($_aNewMetaBoxInputRaw);
        }
        return $this->oUtil->uniteArrays($_aNewMetaBoxInput, $aNewPageOptions);
    }
    public function _replyToModifyOptionsUpdateStatus($aStatus) {
        if (!$this->hasFieldError()) {
            return $aStatus;
        }
        return array('field_errors' => true) + $this->oUtil->getAsArray($aStatus);
    }
    public function _registerFormElements($oScreen) {
        if (!$this->_isInThePage()) {
            return;
        }
        $this->_loadFieldTypeDefinitions();
        $this->oForm->format();
        $this->oForm->applyConditions();
        $this->oForm->applyFiltersToFields($this, $this->oProp->sClassName);
        $this->_setOptionArray($_GET['page'], $this->oForm->aConditionedFields);
        $this->oForm->setDynamicElements($this->oProp->aOptions);
        $this->_registerFields($this->oForm->aConditionedFields);
    }
    protected function _setOptionArray($sPageSlug, $aFields) {
        $_aOptions = array();
        foreach ($aFields as $_sSectionID => $_aFields) {
            if ('_default' == $_sSectionID) {
                foreach ($_aFields as $_aField) {
                    if (array_key_exists($_aField['field_id'], $this->oProp->aOptions)) {
                        $_aOptions[$_aField['field_id']] = $this->oProp->aOptions[$_aField['field_id']];
                    }
                }
            }
            if (array_key_exists($_sSectionID, $this->oProp->aOptions)) {
                $_aOptions = $this->oProp->aOptions[$_sSectionID];
            }
        }
        $this->oProp->aOptions = $this->oUtil->addAndApplyFilter($this, 'options_' . $this->oProp->sClassName, $_aOptions);
        $_aLastInput = isset($_GET['field_errors']) && $_GET['field_errors'] ? $this->oProp->aLastInput : array();
        $this->oProp->aOptions = empty($this->oProp->aOptions) ? array() : $this->oUtil->getAsArray($this->oProp->aOptions);
        $this->oProp->aOptions = $_aLastInput + $this->oProp->aOptions;
    }
}
abstract class Legull_AdminPageFramework_Page_Model extends Legull_AdminPageFramework_Form_Controller {
    static protected $_aScreenIconIDs = array('edit', 'post', 'index', 'media', 'upload', 'link-manager', 'link', 'link-category', 'edit-pages', 'page', 'edit-comments', 'themes', 'plugins', 'users', 'profile', 'user-edit', 'tools', 'admin', 'options-general', 'ms-admin', 'generic',);
    static protected $_aStructure_InPageTabElements = array('page_slug' => null, 'tab_slug' => null, 'title' => null, 'order' => null, 'show_in_page_tab' => true, 'parent_tab_slug' => null, 'url' => null,);
    protected function _finalizeInPageTabs() {
        if (!$this->oProp->isPageAdded()) {
            return;
        }
        foreach ($this->oProp->aPages as $sPageSlug => $aPage) {
            if (!isset($this->oProp->aInPageTabs[$sPageSlug])) {
                continue;
            }
            $this->oProp->aInPageTabs[$sPageSlug] = $this->oUtil->addAndApplyFilter($this, "tabs_{$this->oProp->sClassName}_{$sPageSlug}", $this->oProp->aInPageTabs[$sPageSlug]);
            foreach ($this->oProp->aInPageTabs[$sPageSlug] as & $aInPageTab) {
                $aInPageTab = $this->_formatInPageTab($aInPageTab);
            }
            uasort($this->oProp->aInPageTabs[$sPageSlug], array($this, '_sortByOrder'));
            foreach ($this->oProp->aInPageTabs[$sPageSlug] as $sTabSlug => & $aInPageTab) {
                if (!isset($aInPageTab['tab_slug'])) {
                    continue;
                }
                $this->oProp->aDefaultInPageTabs[$sPageSlug] = $aInPageTab['tab_slug'];
                break;
            }
        }
    }
    private function _formatInPageTab(array $aInPageTab) {
        $aInPageTab = $aInPageTab + self::$_aStructure_InPageTabElements;
        $aInPageTab['order'] = is_null($aInPageTab['order']) ? 10 : $aInPageTab['order'];
        return $aInPageTab;
    }
    public function _replyToFinalizeInPageTabs() {
        $this->_finalizeInPageTabs();
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Page_View extends Legull_AdminPageFramework_MetaBox_Page_Model {
}
abstract class Legull_AdminPageFramework_MetaBox_Page_Controller extends Legull_AdminPageFramework_MetaBox_Page_View {
    public function enqueueStyles($aSRCs, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyles')) {
            return $this->oResource->_enqueueStyles($aSRCs, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function enqueueStyle($sSRC, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyle')) {
            return $this->oResource->_enqueueStyle($sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function enqueueScripts($aSRCs, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScripts')) {
            return $this->oResource->_enqueueScripts($sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function enqueueScript($sSRC, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScript')) {
            return $this->oResource->_enqueueScript($sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
}
abstract class Legull_AdminPageFramework_MetaBox_Page extends Legull_AdminPageFramework_MetaBox_Page_Controller {
    function __construct($sMetaBoxID, $sTitle, $asPageSlugs = array(), $sContext = 'normal', $sPriority = 'default', $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        if (empty($asPageSlugs)) {
            return;
        }
        if (!$this->_isInstantiatable()) {
            return;
        }
        parent::__construct($sMetaBoxID, $sTitle, $asPageSlugs, $sContext, $sPriority, $sCapability, $sTextDomain);
    }
}
abstract class Legull_AdminPageFramework_Page_View_MetaBox extends Legull_AdminPageFramework_Page_Model {
    function __construct($sOptionKey = null, $sCallerPath = null, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        parent::__construct($sOptionKey, $sCallerPath, $sCapability, $sTextDomain);
        if ($this->oProp->bIsAdminAjax) {
            return;
        }
        add_action('admin_head', array($this, '_replyToEnableMetaBox'));
        add_filter('screen_layout_columns', array($this, '_replyToSetNumberOfScreenLayoutColumns'), 10, 2);
    }
    protected function _printMetaBox($sContext, $iContainerID) {
        $_sCurrentScreenID = $this->oUtil->getCurrentScreenID();
        if (!isset($GLOBALS['wp_meta_boxes'][$_sCurrentScreenID][$sContext])) {
            return;
        }
        if (count($GLOBALS['wp_meta_boxes'][$_sCurrentScreenID][$sContext]) <= 0) {
            return;
        }
        echo "<div id='postbox-container-{$iContainerID}' class='postbox-container'>";
        do_meta_boxes('', $sContext, null);
        echo "</div>";
    }
    protected function _getNumberOfColumns() {
        $_sCurrentScreenID = $this->oUtil->getCurrentScreenID();
        if (isset($GLOBALS['wp_meta_boxes'][$_sCurrentScreenID]['side']) && count($GLOBALS['wp_meta_boxes'][$_sCurrentScreenID]['side']) > 0) return 2;
        return 1;
        return 1 == get_current_screen()->get_columns() ? '1' : '2';
    }
    public function _replyToSetNumberOfScreenLayoutColumns($aColumns, $sScreenID) {
        if (!isset($GLOBALS['page_hook'])) {
            return;
        }
        if (!$this->_isMetaBoxAdded()) {
            return;
        }
        if (!$this->oProp->isPageAdded()) {
            return;
        }
        $_sCurrentScreenID = $this->oUtil->getCurrentScreenID();
        add_filter('get_user_option_' . 'screen_layout_' . $_sCurrentScreenID, array($this, '_replyToReturnDefaultNumberOfScreenColumns'), 10, 3);
        if ($sScreenID == $_sCurrentScreenID) {
            $aColumns[$_sCurrentScreenID] = 2;
        }
        return $aColumns;
    }
    private function _isMetaBoxAdded($sPageSlug = '') {
        if (!isset($GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses']) || !is_array($GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses'])) {
            return false;
        }
        $sPageSlug = $sPageSlug ? $sPageSlug : (isset($_GET['page']) ? $_GET['page'] : '');
        if (!$sPageSlug) {
            return false;
        }
        foreach ($GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses'] as $sClassName => $oMetaBox) {
            if ($this->_isPageOfMetaBox($sPageSlug, $oMetaBox)) return true;
        }
        return false;
    }
    private function _isPageOfMetaBox($sPageSlug, $oMetaBox) {
        if (in_array($sPageSlug, $oMetaBox->oProp->aPageSlugs)) {
            return true;
        }
        if (!array_key_exists($sPageSlug, $oMetaBox->oProp->aPageSlugs)) {
            return false;
        }
        $aTabs = $oMetaBox->oProp->aPageSlugs[$sPageSlug];
        $sCurrentTabSlug = isset($_GET['tab']) ? $_GET['tab'] : (isset($_GET['page']) ? $this->oProp->getDefaultInPageTab($_GET['page']) : '');
        if ($sCurrentTabSlug && in_array($sCurrentTabSlug, $aTabs)) return true;
        return false;
    }
    public function _replyToReturnDefaultNumberOfScreenColumns($vStoredData, $sOptionKey, $oUser) {
        $_sCurrentScreenID = $this->oUtil->getCurrentScreenID();
        if ($sOptionKey != 'screen_layout_' . $_sCurrentScreenID) return $vStoredData;
        return ($vStoredData) ? $vStoredData : $this->_getNumberOfColumns();
    }
    public function _replyToEnableMetaBox() {
        if (!$this->oProp->isPageAdded()) {
            return;
        }
        if (!$this->_isMetaBoxAdded()) {
            return;
        }
        $_sCurrentScreenID = $this->oUtil->getCurrentScreenID();
        do_action("add_meta_boxes_{$_sCurrentScreenID}", null);
        do_action('add_meta_boxes', $_sCurrentScreenID, null);
        wp_enqueue_script('postbox');
        if (isset($GLOBALS['page_hook'])) {
            add_action("admin_footer-{$GLOBALS['page_hook']}", array($this, '_replyToAddMetaboxScript'));
        }
    }
    public function _replyToAddMetaboxScript() {
        if (isset($GLOBALS['aLegull_AdminPageFramework']['bAddedMetaBoxScript']) && $GLOBALS['aLegull_AdminPageFramework']['bAddedMetaBoxScript']) {
            return;
        }
        $GLOBALS['aLegull_AdminPageFramework']['bAddedMetaBoxScript'] = true;
        $_sScript = "jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow) });";
        echo '<script class="admin-page-framework-insert-metabox-script">' . $_sScript . '</script>';
    }
}
abstract class Legull_AdminPageFramework_Page_View extends Legull_AdminPageFramework_Page_View_MetaBox {
    protected function _renderPage($sPageSlug, $sTabSlug = null) {
        $this->oUtil->addAndDoActions($this, $this->oUtil->getFilterArrayByPrefix('do_before_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true), $this); ?>
        <div class="<?php echo esc_attr($this->oProp->sWrapperClassAttribute); ?>">
            <?php
        $sContentTop = $this->_getScreenIcon($sPageSlug);
        $sContentTop.= $this->_getPageHeadingTabs($sPageSlug, $this->oProp->sPageHeadingTabTag);
        $sContentTop.= $this->_getInPageTabs($sPageSlug, $this->oProp->sInPageTabTag);
        echo $this->oUtil->addAndApplyFilters($this, $this->oUtil->getFilterArrayByPrefix('content_top_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false), $sContentTop); ?>
            <div class="admin-page-framework-container">    
                <?php
        $this->oUtil->addAndDoActions($this, $this->oUtil->getFilterArrayByPrefix('do_form_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true), $this);
        $this->_printFormOpeningTag($this->oProp->bEnableForm); ?>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-<?php echo $this->_getNumberOfColumns(); ?>">
                    <?php
        $this->_printMainContent($sPageSlug, $sTabSlug);
        $this->_printMetaBox('side', 1);
        $this->_printMetaBox('normal', 2);
        $this->_printMetaBox('advanced', 3); ?>     
                    </div><!-- #post-body -->    
                </div><!-- #poststuff -->
                
            <?php echo $this->_printFormClosingTag($sPageSlug, $sTabSlug, $this->oProp->bEnableForm); ?>
            </div><!-- .admin-page-framework-container -->
                
            <?php echo $this->oUtil->addAndApplyFilters($this, $this->oUtil->getFilterArrayByPrefix('content_bottom_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false), ''); ?>
        </div><!-- .wrap -->
        <?php
        $this->oUtil->addAndDoActions($this, $this->oUtil->getFilterArrayByPrefix('do_after_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true), $this);
    }
    private function _printMainContent($sPageSlug, $sTabSlug) {
        $_bIsSideMetaboxExist = (isset($GLOBALS['wp_meta_boxes'][$GLOBALS['page_hook']]['side']) && count($GLOBALS['wp_meta_boxes'][$GLOBALS['page_hook']]['side']) > 0);
        echo "<!-- main admin page content -->";
        echo "<div class='admin-page-framework-content'>";
        if ($_bIsSideMetaboxExist) {
            echo "<div id='post-body-content'>";
        }
        ob_start();
        if ($this->oProp->bEnableForm && $this->oForm->isPageAdded($sPageSlug)) {
            $this->aFieldErrors = isset($this->aFieldErrors) ? $this->aFieldErrors : $this->_getFieldErrors($sPageSlug);
            $_oFieldsTable = new Legull_AdminPageFramework_FormTable($this->oProp->aFieldTypeDefinitions, $this->aFieldErrors, $this->oMsg);
            echo $_oFieldsTable->getFormTables($this->oForm->aConditionedSections, $this->oForm->aConditionedFields, array($this, '_replyToGetSectionHeaderOutput'), array($this, '_replyToGetFieldOutput'));
        }
        $_sContent = ob_get_contents();
        ob_end_clean();
        echo $this->oUtil->addAndApplyFilters($this, $this->oUtil->getFilterArrayByPrefix('content_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false), $_sContent);
        $this->oUtil->addAndDoActions($this, $this->oUtil->getFilterArrayByPrefix('do_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true), $this);
        if ($_bIsSideMetaboxExist) {
            echo "</div><!-- #post-body-content -->";
        }
        echo "</div><!-- .admin-page-framework-content -->";
    }
    private function _printFormOpeningTag($fEnableForm = true) {
        if (!$fEnableForm) {
            return;
        }
        echo "<form " . $this->oUtil->generateAttributes(array('method' => 'post', 'enctype' => $this->oProp->sFormEncType, 'id' => 'admin-page-framework-form', 'action' => wp_unslash(remove_query_arg('settings-updated', $this->oProp->sTargetFormPage)),)) . " >";
        settings_fields($this->oProp->sOptionKey);
    }
    private function _printFormClosingTag($sPageSlug, $sTabSlug, $fEnableForm = true) {
        if (!$fEnableForm) {
            return;
        }
        $_sNonceTransientKey = 'form_' . md5($this->oProp->sClassName . get_current_user_id());
        $_sNonce = $this->oUtil->getTransient($_sNonceTransientKey, '_admin_page_framework_form_nonce_' . uniqid());
        $this->oUtil->setTransient($_sNonceTransientKey, $_sNonce, 60 * 60);
        echo "<input type='hidden' name='page_slug' value='{$sPageSlug}' />" . PHP_EOL . "<input type='hidden' name='tab_slug' value='{$sTabSlug}' />" . PHP_EOL . "<input type='hidden' name='_is_admin_page_framework' value='{$_sNonce}' />" . PHP_EOL . "</form><!-- End Form -->" . PHP_EOL;
    }
    private function _getScreenIcon($sPageSlug) {
        if (isset($this->oProp->aPages[$sPageSlug]['href_icon_32x32'])) {
            return "<div " . $this->oUtil->generateAttributes(array('class' => 'icon32', 'style' => $this->oUtil->generateInlineCSS(array('background-image' => "url('" . esc_url($this->oProp->aPages[$sPageSlug]['href_icon_32x32']) . "')")))) . ">" . "<br />" . "</div>";
        }
        if (isset($this->oProp->aPages[$sPageSlug]['screen_icon_id'])) {
            return "<div " . $this->oUtil->generateAttributes(array('class' => 'icon32', 'id' => "icon-" . $this->oProp->aPages[$sPageSlug]['screen_icon_id'],)) . ">" . "<br />" . "</div>";
        }
        $_oScreen = get_current_screen();
        $_sIconIDAttribute = $this->_getScreenIDAttribute($_oScreen);
        $_sClass = 'icon32';
        if (empty($_sIconIDAttribute) && $_oScreen->post_type) {
            $_sClass.= ' ' . sanitize_html_class('icon32-posts-' . $_oScreen->post_type);
        }
        if (empty($_sIconIDAttribute) || $_sIconIDAttribute == $this->oProp->sClassName) {
            $_sIconIDAttribute = 'generic';
        }
        return "<div " . $this->oUtil->generateAttributes(array('class' => $_sClass, 'id' => "icon-" . $_sIconIDAttribute,)) . ">" . "<br />" . "</div>";
    }
    private function _getScreenIDAttribute($oScreen) {
        if (!empty($oScreen->parent_base)) {
            return $oScreen->parent_base;
        }
        if ('page' == $oScreen->post_type) {
            return 'edit-pages';
        }
        return esc_attr($oScreen->base);
    }
    private function _getPageHeadingTabs($sCurrentPageSlug, $sTag = 'h2', $aOutput = array()) {
        if (!$this->oProp->aPages[$sCurrentPageSlug]['show_page_title']) {
            return "";
        }
        $sTag = $this->oProp->aPages[$sCurrentPageSlug]['page_heading_tab_tag'] ? $this->oProp->aPages[$sCurrentPageSlug]['page_heading_tab_tag'] : $sTag;
        $sTag = tag_escape($sTag);
        if (!$this->oProp->aPages[$sCurrentPageSlug]['show_page_heading_tabs'] || count($this->oProp->aPages) == 1) {
            return "<{$sTag}>" . $this->oProp->aPages[$sCurrentPageSlug]['title'] . "</{$sTag}>";
        }
        foreach ($this->oProp->aPages as $aSubPage) {
            if (isset($aSubPage['page_slug']) && $aSubPage['show_page_heading_tab']) {
                $aOutput[] = "<a " . $this->oUtil->generateAttributes(array('class' => $this->oUtil->generateClassAttribute('nav-tab', $sCurrentPageSlug === $aSubPage['page_slug'] ? 'nav-tab-active' : ''), 'href' => esc_url($this->oUtil->getQueryAdminURL(array('page' => $aSubPage['page_slug'], 'tab' => false), $this->oProp->aDisallowedQueryKeys)),)) . ">" . $aSubPage['title'] . "</a>";
            }
            if (isset($aSubPage['href']) && 'link' === $aSubPage['type'] && $aSubPage['show_page_heading_tab']) {
                $aOutput[] = "<a " . $this->oUtil->generateAttributes(array('class' => 'nav-tab link', 'href' => esc_url($aSubPage['href']),)) . ">" . $aSubPage['title'] . "</a>";
            }
        }
        return "<div class='admin-page-framework-page-heading-tab'>" . "<{$sTag} class='nav-tab-wrapper'>" . implode('', $aOutput) . "</{$sTag}>" . "</div>";
    }
    private function _getInPageTabs($sCurrentPageSlug, $sTag = 'h3') {
        $aOutput = array();
        if (empty($this->oProp->aInPageTabs[$sCurrentPageSlug])) {
            return '';
        }
        $_sCurrentTabSlug = isset($_GET['tab']) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab($sCurrentPageSlug);
        $_sCurrentTabSlug = $this->_getParentTabSlug($sCurrentPageSlug, $_sCurrentTabSlug);
        $sTag = $this->oProp->aPages[$sCurrentPageSlug]['in_page_tab_tag'] ? $this->oProp->aPages[$sCurrentPageSlug]['in_page_tab_tag'] : $sTag;
        $sTag = tag_escape($sTag);
        if (!$this->oProp->aPages[$sCurrentPageSlug]['show_in_page_tabs']) {
            return isset($this->oProp->aInPageTabs[$sCurrentPageSlug][$_sCurrentTabSlug]['title']) ? "<{$sTag}>{$this->oProp->aInPageTabs[$sCurrentPageSlug][$_sCurrentTabSlug]['title']}</{$sTag}>" : "";
        }
        foreach ($this->oProp->aInPageTabs[$sCurrentPageSlug] as $_sTabSlug => $_aInPageTab) {
            if (!$_aInPageTab['show_in_page_tab'] && !isset($_aInPageTab['parent_tab_slug'])) {
                continue;
            }
            $_sInPageTabSlug = isset($_aInPageTab['parent_tab_slug'], $this->oProp->aInPageTabs[$sCurrentPageSlug][$_aInPageTab['parent_tab_slug']]) ? $_aInPageTab['parent_tab_slug'] : $_aInPageTab['tab_slug'];
            $aOutput[$_sInPageTabSlug] = "<a " . $this->oUtil->generateAttributes(array('class' => $this->oUtil->generateClassAttribute('nav-tab', $_sCurrentTabSlug == $_sInPageTabSlug ? "nav-tab-active" : ""), 'href' => esc_url(isset($_aInPageTab['url']) ? $_aInPageTab['url'] : $this->oUtil->getQueryAdminURL(array('page' => $sCurrentPageSlug, 'tab' => $_sInPageTabSlug), $this->oProp->aDisallowedQueryKeys)),)) . ">" . $this->oProp->aInPageTabs[$sCurrentPageSlug][$_sInPageTabSlug]['title'] . "</a>";
        }
        return empty($aOutput) ? "" : "<div class='admin-page-framework-in-page-tab'>" . "<{$sTag} class='nav-tab-wrapper in-page-tab'>" . implode('', $aOutput) . "</{$sTag}>" . "</div>";
    }
    private function _getParentTabSlug($sPageSlug, $sTabSlug) {
        $sParentTabSlug = isset($this->oProp->aInPageTabs[$sPageSlug][$sTabSlug]['parent_tab_slug']) ? $this->oProp->aInPageTabs[$sPageSlug][$sTabSlug]['parent_tab_slug'] : $sTabSlug;
        return isset($this->oProp->aInPageTabs[$sPageSlug][$sParentTabSlug]['show_in_page_tab']) && $this->oProp->aInPageTabs[$sPageSlug][$sParentTabSlug]['show_in_page_tab'] ? $sParentTabSlug : '';
    }
}
abstract class Legull_AdminPageFramework_Page_Controller extends Legull_AdminPageFramework_Page_View {
    public function addInPageTabs() {
        foreach (func_get_args() as $asTab) {
            $this->addInPageTab($asTab);
        }
    }
    public function addInPageTab($asInPageTab) {
        static $__sTargetPageSlug;
        if (!is_array($asInPageTab)) {
            $__sTargetPageSlug = is_string($asInPageTab) ? $asInPageTab : $__sTargetPageSlug;
            return;
        }
        $aInPageTab = $this->oUtil->uniteArrays($asInPageTab, self::$_aStructure_InPageTabElements, array('page_slug' => $__sTargetPageSlug));
        $__sTargetPageSlug = $aInPageTab['page_slug'];
        if (!isset($aInPageTab['page_slug'], $aInPageTab['tab_slug'])) return;
        $iCountElement = isset($this->oProp->aInPageTabs[$aInPageTab['page_slug']]) ? count($this->oProp->aInPageTabs[$aInPageTab['page_slug']]) : 0;
        $aInPageTab = array('page_slug' => $this->oUtil->sanitizeSlug($aInPageTab['page_slug']), 'tab_slug' => $this->oUtil->sanitizeSlug($aInPageTab['tab_slug']), 'order' => is_numeric($aInPageTab['order']) ? $aInPageTab['order'] : $iCountElement + 10,) + $aInPageTab;
        $this->oProp->aInPageTabs[$aInPageTab['page_slug']][$aInPageTab['tab_slug']] = $aInPageTab;
    }
    public function setPageTitleVisibility($bShow = true, $sPageSlug = '') {
        $sPageSlug = $this->oUtil->sanitizeSlug($sPageSlug);
        if ($sPageSlug) {
            $this->oProp->aPages[$sPageSlug]['show_page_title'] = $bShow;
            return;
        }
        $this->oProp->bShowPageTitle = $bShow;
        foreach ($this->oProp->aPages as & $aPage) {
            $aPage['show_page_title'] = $bShow;
        }
    }
    public function setPageHeadingTabsVisibility($bShow = true, $sPageSlug = '') {
        $sPageSlug = $this->oUtil->sanitizeSlug($sPageSlug);
        if ($sPageSlug) {
            $this->oProp->aPages[$sPageSlug]['show_page_heading_tabs'] = $bShow;
            return;
        }
        $this->oProp->bShowPageHeadingTabs = $bShow;
        foreach ($this->oProp->aPages as & $aPage) {
            $aPage['show_page_heading_tabs'] = $bShow;
        }
    }
    public function setInPageTabsVisibility($bShow = true, $sPageSlug = '') {
        $sPageSlug = $this->oUtil->sanitizeSlug($sPageSlug);
        if ($sPageSlug) {
            $this->oProp->aPages[$sPageSlug]['show_in_page_tabs'] = $bShow;
            return;
        }
        $this->oProp->bShowInPageTabs = $bShow;
        foreach ($this->oProp->aPages as & $aPage) {
            $aPage['show_in_page_tabs'] = $bShow;
        }
    }
    public function setInPageTabTag($sTag = 'h3', $sPageSlug = '') {
        $sPageSlug = $this->oUtil->sanitizeSlug($sPageSlug);
        if ($sPageSlug) {
            $this->oProp->aPages[$sPageSlug]['in_page_tab_tag'] = $sTag;
            return;
        }
        $this->oProp->sInPageTabTag = $sTag;
        foreach ($this->oProp->aPages as & $aPage) {
            $aPage['in_page_tab_tag'] = $sTag;
        }
    }
    public function setPageHeadingTabTag($sTag = 'h2', $sPageSlug = '') {
        $sPageSlug = $this->oUtil->sanitizeSlug($sPageSlug);
        if ($sPageSlug) {
            $this->oProp->aPages[$sPageSlug]['page_heading_tab_tag'] = $sTag;
            return;
        }
        $this->oProp->sPageHeadingTabTag = $sTag;
        foreach ($this->oProp->aPages as & $aPage) {
            $aPage[$sPageSlug]['page_heading_tab_tag'] = $sTag;
        }
    }
}
abstract class Legull_AdminPageFramework_Menu_Model extends Legull_AdminPageFramework_Page_Controller {
    protected $_aBuiltInRootMenuSlugs = array('dashboard' => 'index.php', 'posts' => 'edit.php', 'media' => 'upload.php', 'links' => 'link-manager.php', 'pages' => 'edit.php?post_type=page', 'comments' => 'edit-comments.php', 'appearance' => 'themes.php', 'plugins' => 'plugins.php', 'users' => 'users.php', 'tools' => 'tools.php', 'settings' => 'options-general.php', 'network admin' => "network_admin_menu",);
    protected static $_aStructure_SubMenuLinkForUser = array('type' => 'link', 'title' => null, 'href' => null, 'capability' => null, 'order' => null, 'show_page_heading_tab' => true, 'show_in_menu' => true,);
    protected static $_aStructure_SubMenuPageForUser = array('type' => 'page', 'title' => null, 'page_title' => null, 'menu_title' => null, 'page_slug' => null, 'screen_icon' => null, 'capability' => null, 'order' => null, 'show_page_heading_tab' => true, 'show_in_menu' => true, 'href_icon_32x32' => null, 'screen_icon_id' => null, 'show_page_title' => null, 'show_page_heading_tabs' => null, 'show_in_page_tabs' => null, 'in_page_tab_tag' => null, 'page_heading_tab_tag' => null,);
    public function _replyToBuildMenu() {
        if ($this->oProp->aRootMenu['fCreateRoot']) {
            $this->_registerRootMenuPage();
        }
        $this->oProp->aPages = $this->oUtil->addAndApplyFilter($this, "pages_{$this->oProp->sClassName}", $this->oProp->aPages);
        uasort($this->oProp->aPages, array($this, '_sortByOrder'));
        foreach ($this->oProp->aPages as $aPage) {
            if (!isset($aPage['page_slug'])) {
                continue;
            }
            $this->oProp->sDefaultPageSlug = $aPage['page_slug'];
            break;
        }
        foreach ($this->oProp->aPages as & $aSubMenuItem) {
            $aSubMenuItem = $this->_formatSubMenuItemArray($aSubMenuItem);
            $aSubMenuItem['_page_hook'] = $this->_registerSubMenuItem($aSubMenuItem);
        }
        if ($this->oProp->aRootMenu['fCreateRoot']) {
            remove_submenu_page($this->oProp->aRootMenu['sPageSlug'], $this->oProp->aRootMenu['sPageSlug']);
        }
        $this->oProp->_bBuiltMenu = true;
    }
    private function _registerRootMenuPage() {
        $this->oProp->aRootMenu['_page_hook'] = add_menu_page($this->oProp->sClassName, $this->oProp->aRootMenu['sTitle'], $this->oProp->sCapability, $this->oProp->aRootMenu['sPageSlug'], '', $this->oProp->aRootMenu['sIcon16x16'], isset($this->oProp->aRootMenu['iPosition']) ? $this->oProp->aRootMenu['iPosition'] : null);
    }
    private function _formatSubMenuItemArray($aSubMenuItem) {
        if (isset($aSubMenuItem['page_slug'])) {
            return $this->_formatSubMenuPageArray($aSubMenuItem);
        }
        if (isset($aSubMenuItem['href'])) {
            return $this->_formatSubmenuLinkArray($aSubMenuItem);
        }
        return array();
    }
    protected function _formatSubmenuLinkArray($aSubMenuLink) {
        if (!filter_var($aSubMenuLink['href'], FILTER_VALIDATE_URL)) {
            return array();
        }
        return $this->oUtil->uniteArrays(array('capability' => isset($aSubMenuLink['capability']) ? $aSubMenuLink['capability'] : $this->oProp->sCapability, 'order' => isset($aSubMenuLink['order']) && is_numeric($aSubMenuLink['order']) ? $aSubMenuLink['order'] : count($this->oProp->aPages) + 10,), $aSubMenuLink + self::$_aStructure_SubMenuLinkForUser);
    }
    protected function _formatSubMenuPageArray($aSubMenuPage) {
        $aSubMenuPage = $aSubMenuPage + self::$_aStructure_SubMenuPageForUser;
        $aSubMenuPage['screen_icon_id'] = trim($aSubMenuPage['screen_icon_id']);
        return $this->oUtil->uniteArrays(array('href_icon_32x32' => $this->oUtil->resolveSRC($aSubMenuPage['screen_icon'], true), 'screen_icon_id' => in_array($aSubMenuPage['screen_icon'], self::$_aScreenIconIDs) ? $aSubMenuPage['screen_icon'] : 'generic', 'capability' => isset($aSubMenuPage['capability']) ? $aSubMenuPage['capability'] : $this->oProp->sCapability, 'order' => is_numeric($aSubMenuPage['order']) ? $aSubMenuPage['order'] : count($this->oProp->aPages) + 10,), $aSubMenuPage, array('show_page_title' => $this->oProp->bShowPageTitle, 'show_page_heading_tabs' => $this->oProp->bShowPageHeadingTabs, 'show_in_page_tabs' => $this->oProp->bShowInPageTabs, 'in_page_tab_tag' => $this->oProp->sInPageTabTag, 'page_heading_tab_tag' => $this->oProp->sPageHeadingTabTag,));
    }
    private function _registerSubMenuItem($aArgs) {
        if (!isset($aArgs['type'])) {
            return '';
        }
        $_sCapability = isset($aArgs['capability']) ? $aArgs['capability'] : $this->oProp->sCapability;
        if (!current_user_can($_sCapability)) {
            return '';
        }
        $_sRootPageSlug = $this->oProp->aRootMenu['sPageSlug'];
        $_sMenuSlug = plugin_basename($_sRootPageSlug);
        switch ($aArgs['type']) {
            case 'page':
                return isset($aArgs['page_slug']) ? $this->_addPageSubmenuItem($_sRootPageSlug, $_sMenuSlug, $aArgs['page_slug'], isset($aArgs['page_title']) ? $aArgs['page_title'] : $aArgs['title'], isset($aArgs['menu_title']) ? $aArgs['menu_title'] : $aArgs['title'], $_sCapability, $aArgs['show_in_menu']) : '';
            case 'link':
                return $aArgs['show_in_menu'] ? $this->_addLinkSubmenuItem($_sMenuSlug, $aArgs['title'], $_sCapability, $aArgs['href']) : '';
        }
        return '';
    }
    private function _addPageSubmenuItem($sRootPageSlug, $sMenuSlug, $sPageSlug, $sPageTitle, $sMenuTitle, $sCapability, $bShowInMenu) {
        $_sPageHook = add_submenu_page($sRootPageSlug, $sPageTitle, $sMenuTitle, $sCapability, $sPageSlug, array($this, $this->oProp->sClassHash . '_page_' . $sPageSlug));
        if (!isset($this->oProp->aPageHooks[$_sPageHook])) {
            add_action('current_screen', array($this, "load_pre_" . $sPageSlug), 20);
        }
        $this->oProp->aPageHooks[$sPageSlug] = is_network_admin() ? $_sPageHook . '-network' : $_sPageHook;
        if ($bShowInMenu) {
            return $_sPageHook;
        }
        $this->_removePageSubmenuItem($sMenuSlug, $sMenuTitle, $sPageTitle, $sPageSlug);
        return $_sPageHook;
    }
    private function _removePageSubmenuItem($sMenuSlug, $sMenuTitle, $sPageTitle, $sPageSlug) {
        foreach (( array )$GLOBALS['submenu'][$sMenuSlug] as $_iIndex => $_aSubMenu) {
            if (!isset($_aSubMenu[3])) {
                continue;
            }
            if ($_aSubMenu[0] == $sMenuTitle && $_aSubMenu[3] == $sPageTitle && $_aSubMenu[2] == $sPageSlug) {
                if (is_network_admin()) {
                    unset($GLOBALS['submenu'][$sMenuSlug][$_iIndex]);
                } else if (!isset($_GET['page']) || isset($_GET['page']) && $sPageSlug != $_GET['page']) {
                    unset($GLOBALS['submenu'][$sMenuSlug][$_iIndex]);
                }
                $this->oProp->aHiddenPages[$sPageSlug] = $sMenuTitle;
                add_filter('admin_title', array($this, '_replyToFixPageTitleForHiddenPages'), 10, 2);
                break;
            }
        }
    }
    private function _addLinkSubmenuItem($sMenuSlug, $sTitle, $sCapability, $sHref) {
        if (!isset($GLOBALS['submenu'][$sMenuSlug])) {
            $GLOBALS['submenu'][$sMenuSlug] = array();
        }
        $GLOBALS['submenu'][$sMenuSlug][] = array($sTitle, $sCapability, $sHref,);
    }
    public function _replyToFixPageTitleForHiddenPages($sAdminTitle, $sPageTitle) {
        if (isset($_GET['page'], $this->oProp->aHiddenPages[$_GET['page']])) {
            return $this->oProp->aHiddenPages[$_GET['page']] . $sAdminTitle;
        }
        return $sAdminTitle;
    }
}
abstract class Legull_AdminPageFramework_Menu_View extends Legull_AdminPageFramework_Menu_Model {
}
abstract class Legull_AdminPageFramework_Menu_Controller extends Legull_AdminPageFramework_Menu_View {
    function __construct($sOptionKey = null, $sCallerPath = null, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        parent::__construct($sOptionKey, $sCallerPath, $sCapability, $sTextDomain);
        if ($this->oProp->bIsAdminAjax) {
            return;
        }
        add_action('admin_menu', array($this, '_replyToBuildMenu'), 98);
    }
    public function setRootMenuPage($sRootMenuLabel, $sIcon16x16 = null, $iMenuPosition = null) {
        $sRootMenuLabel = trim($sRootMenuLabel);
        $_sSlug = $this->_isBuiltInMenuItem($sRootMenuLabel);
        $this->oProp->aRootMenu = array('sTitle' => $sRootMenuLabel, 'sPageSlug' => $_sSlug ? $_sSlug : $this->oProp->sClassName, 'sIcon16x16' => $this->oUtil->resolveSRC($sIcon16x16), 'iPosition' => $iMenuPosition, 'fCreateRoot' => $_sSlug ? false : true,);
    }
    private function _isBuiltInMenuItem($sMenuLabel) {
        $_sMenuLabelLower = strtolower($sMenuLabel);
        if (array_key_exists($_sMenuLabelLower, $this->_aBuiltInRootMenuSlugs)) return $this->_aBuiltInRootMenuSlugs[$_sMenuLabelLower];
    }
    public function setRootMenuPageBySlug($sRootMenuSlug) {
        $this->oProp->aRootMenu['sPageSlug'] = $sRootMenuSlug;
        $this->oProp->aRootMenu['fCreateRoot'] = false;
    }
    public function addSubMenuItems($aSubMenuItem1, $aSubMenuItem2 = null, $_and_more = null) {
        foreach (func_get_args() as $aSubMenuItem) {
            $this->addSubMenuItem($aSubMenuItem);
        }
    }
    public function addSubMenuItem(array $aSubMenuItem) {
        if (isset($aSubMenuItem['href'])) {
            $this->addSubMenuLink($aSubMenuItem);
        } else {
            $this->addSubMenuPage($aSubMenuItem);
        }
    }
    public function addSubMenuLink(array $aSubMenuLink) {
        if (!isset($aSubMenuLink['href'], $aSubMenuLink['title'])) {
            return;
        }
        if (!filter_var($aSubMenuLink['href'], FILTER_VALIDATE_URL)) {
            return;
        }
        $this->oProp->aPages[$aSubMenuLink['href']] = $this->_formatSubmenuLinkArray($aSubMenuLink);
    }
    public function addSubMenuPages() {
        foreach (func_get_args() as $aSubMenuPage) {
            $this->addSubMenuPage($aSubMenuPage);
        }
    }
    public function addSubMenuPage(array $aSubMenuPage) {
        if (!isset($aSubMenuPage['page_slug'])) {
            return;
        }
        $aSubMenuPage['page_slug'] = $this->oUtil->sanitizeSlug($aSubMenuPage['page_slug']);
        $this->oProp->aPages[$aSubMenuPage['page_slug']] = $this->_formatSubMenuPageArray($aSubMenuPage);
    }
}
abstract class Legull_AdminPageFramework_Model extends Legull_AdminPageFramework_Menu_Controller {
}
abstract class Legull_AdminPageFramework_View extends Legull_AdminPageFramework_Model {
    public function _replyToPrintAdminNotices() {
        if (!$this->_isInThePage()) {
            return;
        }
        foreach ($this->oProp->aAdminNotices as $aAdminNotice) {
            echo "<div class='{$aAdminNotice['sClassSelector']}' id='{$aAdminNotice['sID']}'>" . "<p>" . $aAdminNotice['sMessage'] . "</p>" . "</div>";
        }
    }
    public function content($sContent) {
        return $sContent;
    }
}
abstract class Legull_AdminPageFramework_Controller extends Legull_AdminPageFramework_View {
    public function setUp() {
    }
    public function addHelpTab($aHelpTab) {
        if (method_exists($this->oHelpPane, '_addHelpTab')) {
            $this->oHelpPane->_addHelpTab($aHelpTab);
        }
    }
    public function enqueueStyles($aSRCs, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyles')) {
            return $this->oResource->_enqueueStyles($aSRCs, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function enqueueStyle($sSRC, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueStyle')) {
            return $this->oResource->_enqueueStyle($sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function enqueueScripts($aSRCs, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScripts')) {
            return $this->oResource->_enqueueScripts($aSRCs, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function enqueueScript($sSRC, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        if (method_exists($this->oResource, '_enqueueScript')) {
            return $this->oResource->_enqueueScript($sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
    }
    public function addLinkToPluginDescription($sTaggedLinkHTML1, $sTaggedLinkHTML2 = null, $_and_more = null) {
        if (method_exists($this->oLink, '_addLinkToPluginDescription')) {
            $this->oLink->_addLinkToPluginDescription(func_get_args());
        }
    }
    public function addLinkToPluginTitle($sTaggedLinkHTML1, $sTaggedLinkHTML2 = null, $_and_more = null) {
        if (method_exists($this->oLink, '_addLinkToPluginTitle')) {
            $this->oLink->_addLinkToPluginTitle(func_get_args());
        }
    }
    public function setPluginSettingsLinkLabel($sLabel) {
        $this->oProp->sLabelPluginSettingsLink = $sLabel;
    }
    public function setCapability($sCapability) {
        $this->oProp->sCapability = $sCapability;
        if (isset($this->oForm)) {
            $this->oForm->sCapability = $sCapability;
        }
    }
    public function setFooterInfoLeft($sHTML, $bAppend = true) {
        $this->oProp->aFooterInfo['sLeft'] = $bAppend ? $this->oProp->aFooterInfo['sLeft'] . PHP_EOL . $sHTML : $sHTML;
    }
    public function setFooterInfoRight($sHTML, $bAppend = true) {
        $this->oProp->aFooterInfo['sRight'] = $bAppend ? $this->oProp->aFooterInfo['sRight'] . PHP_EOL . $sHTML : $sHTML;
    }
    public function setAdminNotice($sMessage, $sClassSelector = 'error', $sID = '') {
        $sID = $sID ? $sID : md5($sMessage);
        $this->oProp->aAdminNotices[md5($sMessage) ] = array('sMessage' => $sMessage, 'sClassSelector' => $sClassSelector, 'sID' => $sID,);
        if (is_network_admin()) {
            add_action('network_admin_notices', array($this, '_replyToPrintAdminNotices'));
        } else {
            add_action('admin_notices', array($this, '_replyToPrintAdminNotices'));
        }
    }
    public function setDisallowedQueryKeys($asQueryKeys, $bAppend = true) {
        if (!$bAppend) {
            $this->oProp->aDisallowedQueryKeys = ( array )$asQueryKeys;
            return;
        }
        $aNewQueryKeys = array_merge(( array )$asQueryKeys, $this->oProp->aDisallowedQueryKeys);
        $aNewQueryKeys = array_filter($aNewQueryKeys);
        $aNewQueryKeys = array_unique($aNewQueryKeys);
        $this->oProp->aDisallowedQueryKeys = $aNewQueryKeys;
    }
    static public function getOption($sOptionKey, $asKey = null, $vDefault = null) {
        return Legull_AdminPageFramework_WPUtility::getOption($sOptionKey, $asKey, $vDefault);
    }
}
abstract class Legull_AdminPageFramework extends Legull_AdminPageFramework_Controller {
    public function __construct($sOptionKey = null, $sCallerPath = null, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        if (!$this->_isInstantiatable()) {
            return;
        }
        parent::__construct($sOptionKey, $sCallerPath ? trim($sCallerPath) : $sCallerPath = (is_admin() && (isset($GLOBALS['pagenow']) && in_array($GLOBALS['pagenow'], array('plugins.php',)) || isset($_GET['page'])) ? Legull_AdminPageFramework_Utility::getCallerScriptPath(__FILE__) : null), $sCapability, $sTextDomain);
        $this->oUtil->addAndDoAction($this, 'start_' . $this->oProp->sClassName, $this);
    }
}
abstract class Legull_AdminPageFramework_NetworkAdmin extends Legull_AdminPageFramework {
    protected $_aBuiltInRootMenuSlugs = array('dashboard' => 'index.php', 'sites' => 'sites.php', 'themes' => 'themes.php', 'plugins' => 'plugins.php', 'users' => 'users.php', 'settings' => 'settings.php', 'updates' => 'update-core.php',);
    public function __construct($sOptionKey = null, $sCallerPath = null, $sCapability = 'manage_network', $sTextDomain = 'admin-page-framework') {
        if (!$this->_isInstantiatable()) {
            return;
        }
        add_action('network_admin_menu', array($this, '_replyToBuildMenu'), 98);
        $sCallerPath = $sCallerPath ? $sCallerPath : Legull_AdminPageFramework_Utility::getCallerScriptPath(__FILE__);
        $this->oProp = new Legull_AdminPageFramework_Property_NetworkAdmin($this, $sCallerPath, get_class($this), $sOptionKey, $sCapability, $sTextDomain);
        parent::__construct($sOptionKey, $sCallerPath, $sCapability, $sTextDomain);
    }
    protected function _isInstantiatable() {
        if (isset($GLOBALS['pagenow']) && 'admin-ajax.php' === $GLOBALS['pagenow']) {
            return false;
        }
        if (is_network_admin()) {
            return true;
        }
        return false;
    }
    static public function getOption($sOptionKey, $asKey = null, $vDefault = null) {
        return Legull_AdminPageFramework_WPUtility::getSiteOption($sOptionKey, $asKey, $vDefault);
    }
}
abstract class Legull_AdminPageFramework_Resource_Base {
    protected static $_aStructure_EnqueuingResources = array('sSRC' => null, 'aPostTypes' => array(), 'sPageSlug' => null, 'sTabSlug' => null, 'sType' => null, 'handle_id' => null, 'dependencies' => array(), 'version' => false, 'translation' => array(), 'in_footer' => false, 'media' => 'all', 'attributes' => array(),);
    protected $_sClassSelector_Style = 'admin-page-framework-style';
    protected $_sClassSelector_Script = 'admin-page-framework-script';
    protected $_aHandleIDs = array();
    function __construct($oProp) {
        $this->oProp = $oProp;
        $this->oUtil = new Legull_AdminPageFramework_WPUtility;
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        add_action('admin_enqueue_scripts', array($this, '_replyToEnqueueScripts'));
        add_action('admin_enqueue_scripts', array($this, '_replyToEnqueueStyles'));
        add_action(did_action('admin_print_styles') ? 'admin_print_footer_scripts' : 'admin_print_styles', array($this, '_replyToAddStyle'), 999);
        add_action(did_action('admin_print_scripts') ? 'admin_print_footer_scripts' : 'admin_print_scripts', array($this, '_replyToAddScript'), 999);
        add_action('customize_controls_print_footer_scripts', array($this, '_replyToEnqueueScripts'));
        add_action('customize_controls_print_footer_scripts', array($this, '_replyToEnqueueStyles'));
        add_action('admin_footer', array($this, '_replyToEnqueueScripts'));
        add_action('admin_footer', array($this, '_replyToEnqueueStyles'));
        add_action('admin_print_footer_scripts', array($this, '_replyToAddStyle'), 999);
        add_action('admin_print_footer_scripts', array($this, '_replyToAddScript'), 999);
        add_filter('script_loader_src', array($this, '_replyToSetupArgumentCallback'), 1, 2);
        add_filter('style_loader_src', array($this, '_replyToSetupArgumentCallback'), 1, 2);
    }
    public function _forceToEnqueueStyle($sSRC, $aCustomArgs = array()) {
    }
    public function _forceToEnqueueScript($sSRC, $aCustomArgs = array()) {
    }
    protected function _enqueueSRCByConditoin($aEnqueueItem) {
        return $this->_enqueueSRC($aEnqueueItem);
    }
    public function _replyToSetupArgumentCallback($sSRC, $sHandleID) {
        if (isset($this->oProp->aResourceAttributes[$sHandleID])) {
            $this->_aHandleIDs[$sSRC] = $sHandleID;
            add_filter('clean_url', array($this, '_replyToModifyEnqueuedAttrbutes'), 1, 3);
            remove_filter(current_filter(), array($this, '_replyToSetupArgumentCallback'), 1, 2);
        }
        return $sSRC;
    }
    public function _replyToModifyEnqueuedAttrbutes($sSanitizedURL, $sOriginalURL, $sContext) {
        if ('display' !== $sContext) {
            return $sSanitizedURL;
        }
        if (isset($this->_aHandleIDs[$sOriginalURL])) {
            $_sHandleID = $this->_aHandleIDs[$sOriginalURL];
            $_aAttributes = $this->oProp->aResourceAttributes[$_sHandleID];
            if (empty($_aAttributes)) {
                return $sSanitizedURL;
            }
            $_sAttributes = $this->oUtil->generateAttributes($_aAttributes);
            $_sModifiedURL = $sSanitizedURL . "' " . rtrim($_sAttributes, "'\"");
            return $_sModifiedURL;
        }
        return $sSanitizedURL;
    }
    static private $_bCommonStyleLoaded = false;
    protected function _printCommonStyles($sIDPrefix, $sClassName) {
        if (self::$_bCommonStyleLoaded) {
            return;
        }
        self::$_bCommonStyleLoaded = true;
        $_oCaller = $this->oProp->_getCallerObject();
        $_sStyle = $this->oUtil->addAndApplyFilters($_oCaller, array("style_common_admin_page_framework", "style_common_{$this->oProp->sClassName}",), Legull_AdminPageFramework_CSS::getDefaultCSS());
        $_sStyle = $this->oUtil->minifyCSS($_sStyle);
        if ($_sStyle) {
            echo "<style type='text/css' id='" . esc_attr($sIDPrefix) . "'>" . $_sStyle . "</style>";
        }
        $_sStyleIE = $this->oUtil->addAndApplyFilters($_oCaller, array("style_ie_common_admin_page_framework", "style_ie_common_{$this->oProp->sClassName}",), Legull_AdminPageFramework_CSS::getDefaultCSSIE());
        $_sStyleIE = $this->oUtil->minifyCSS($_sStyleIE);
        if ($_sStyleIE) {
            echo "<!--[if IE]><style type='text/css' id='" . esc_attr($sIDPrefix . "-ie") . "'>" . $_sStyleIE . "</style><![endif]-->";
        }
    }
    static private $_bCommonScriptLoaded = false;
    protected function _printCommonScripts($sIDPrefix, $sClassName) {
        if (self::$_bCommonScriptLoaded) {
            return;
        }
        self::$_bCommonScriptLoaded = true;
        $_sScript = $this->oUtil->addAndApplyFilters($this->oProp->_getCallerObject(), array("script_common_admin_page_framework", "script_common_{$this->oProp->sClassName}",), Legull_AdminPageFramework_Property_Base::$_sDefaultScript);
        if ($_sScript) {
            echo "<script type='text/javascript' id='" . esc_attr($sIDPrefix) . "'>" . $_sScript . "</script>";
        }
    }
    protected function _printClassSpecificStyles($sIDPrefix) {
        static $_iCallCount = 1;
        static $_iCallCountIE = 1;
        $_oCaller = $this->oProp->_getCallerObject();
        $sStyle = $this->oUtil->addAndApplyFilters($_oCaller, "style_{$this->oProp->sClassName}", $this->oProp->sStyle);
        $sStyle = $this->oUtil->minifyCSS($sStyle);
        if ($sStyle) {
            echo "<style type='text/css' id='" . esc_attr("{$sIDPrefix}-{$this->oProp->sClassName}_{$_iCallCount}") . "'>" . $sStyle . "</style>";
            $_iCallCount++;
        }
        $sStyleIE = $this->oUtil->addAndApplyFilters($_oCaller, "style_ie_{$this->oProp->sClassName}", $this->oProp->sStyleIE);
        $sStyleIE = $this->oUtil->minifyCSS($sStyleIE);
        if ($sStyleIE) {
            echo "<!--[if IE]><style type='text/css' id='" . esc_attr("{$sIDPrefix}-ie-{$this->oProp->sClassName}_{$_iCallCountIE}") . "'>" . $sStyleIE . "</style><![endif]-->";
            $_iCallCountIE++;
        }
        $this->oProp->sStyle = '';
        $this->oProp->sStyleIE = '';
    }
    protected function _printClassSpecificScripts($sIDPrefix) {
        static $_iCallCount = 1;
        $_sScript = $this->oUtil->addAndApplyFilters($this->oProp->_getCallerObject(), array("script_{$this->oProp->sClassName}",), $this->oProp->sScript);
        if ($_sScript) {
            echo "<script type='text/javascript' id='" . esc_attr("{$sIDPrefix}-{$this->oProp->sClassName}_{$_iCallCount}") . "'>" . $_sScript . "</script>";
            $_iCallCount++;
        }
        $this->oProp->sScript = '';
    }
    public function _replyToAddStyle() {
        $_oCaller = $this->oProp->_getCallerObject();
        if (!$_oCaller->_isInThePage()) {
            return;
        }
        $this->_printCommonStyles('admin-page-framework-style-common', get_class());
        $this->_printClassSpecificStyles($this->_sClassSelector_Style . '-' . $this->oProp->sFieldsType);
    }
    public function _replyToAddScript() {
        $_oCaller = $this->oProp->_getCallerObject();
        if (!$_oCaller->_isInThePage()) {
            return;
        }
        $this->_printCommonScripts('admin-page-framework-script-common', get_class());
        $this->_printClassSpecificScripts($this->_sClassSelector_Script . '-' . $this->oProp->sFieldsType);
    }
    protected function _enqueueSRC($aEnqueueItem) {
        if ('style' === $aEnqueueItem['sType']) {
            wp_enqueue_style($aEnqueueItem['handle_id'], $aEnqueueItem['sSRC'], $aEnqueueItem['dependencies'], $aEnqueueItem['version'], $aEnqueueItem['media']);
            return;
        }
        wp_enqueue_script($aEnqueueItem['handle_id'], $aEnqueueItem['sSRC'], $aEnqueueItem['dependencies'], $aEnqueueItem['version'], did_action('admin_body_class') ? true : $aEnqueueItem['in_footer']);
        if ($aEnqueueItem['translation']) {
            wp_localize_script($aEnqueueItem['handle_id'], $aEnqueueItem['handle_id'], $aEnqueueItem['translation']);
        }
    }
    public function _replyToEnqueueStyles() {
        foreach ($this->oProp->aEnqueuingStyles as $_sKey => $_aEnqueuingStyle) {
            $this->_enqueueSRCByConditoin($_aEnqueuingStyle);
            unset($this->oProp->aEnqueuingStyles[$_sKey]);
        }
    }
    public function _replyToEnqueueScripts() {
        foreach ($this->oProp->aEnqueuingScripts as $_sKey => $_aEnqueuingScript) {
            $this->_enqueueSRCByConditoin($_aEnqueuingScript);
            unset($this->oProp->aEnqueuingScripts[$_sKey]);
        }
    }
}
class Legull_AdminPageFramework_Resource_Page extends Legull_AdminPageFramework_Resource_Base {
    protected function _printClassSpecificStyles($sIDPrefix) {
        static $_bLoaded = false;
        if ($_bLoaded) {
            parent::_printClassSpecificStyles($sIDPrefix);
            return;
        }
        $_bLoaded = true;
        $_oCaller = $this->oProp->_getCallerObject();
        $_sPageSlug = isset($_GET['page']) ? $_GET['page'] : '';
        $_sPageSlug = $this->oProp->isPageAdded($_sPageSlug) ? $_sPageSlug : '';
        $_sTabSlug = $this->oProp->getCurrentTab($_sPageSlug);
        $_sTabSlug = isset($this->oProp->aInPageTabs[$_sPageSlug][$_sTabSlug]) ? $_sTabSlug : '';
        if ($_sPageSlug && $_sTabSlug) {
            $this->oProp->sStyle = $this->oUtil->addAndApplyFilters($_oCaller, "style_{$_sPageSlug}_{$_sTabSlug}", $this->oProp->sStyle);
        }
        if ($_sPageSlug) {
            $this->oProp->sStyle = $this->oUtil->addAndApplyFilters($_oCaller, "style_{$_sPageSlug}", $this->oProp->sStyle);
        }
        parent::_printClassSpecificStyles($sIDPrefix);
    }
    protected function _printClassSpecificScripts($sIDPrefix) {
        static $_bLoaded = false;
        if ($_bLoaded) {
            parent::_printClassSpecificScripts($sIDPrefix);
            return;
        }
        $_bLoaded = true;
        $_oCaller = $this->oProp->_getCallerObject();
        $_sPageSlug = isset($_GET['page']) ? $_GET['page'] : '';
        $_sTabSlug = $this->oProp->getCurrentTab($_sPageSlug);
        if ($_sPageSlug && $_sTabSlug) {
            $this->oProp->sScript = $this->oUtil->addAndApplyFilters($_oCaller, "script_{$_sPageSlug}_{$_sTabSlug}", $this->oProp->sScript);
        }
        if ($_sPageSlug) {
            $this->oProp->sScript = $this->oUtil->addAndApplyFilters($_oCaller, "script_{$_sPageSlug}", $this->oProp->sScript);
        }
        parent::_printClassSpecificScripts($sIDPrefix);
    }
    public function _enqueueStyles($aSRCs, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueStyle($_sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueStyle($sSRC, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingStyles[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingStyles[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'sPageSlug' => $sPageSlug, 'sTabSlug' => $sTabSlug, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedStyleIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingStyles[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id'];
    }
    public function _enqueueScripts($aSRCs, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueScript($_sSRC, $sPageSlug, $sTabSlug, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueScript($sSRC, $sPageSlug = '', $sTabSlug = '', $aCustomArgs = array()) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingScripts[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingScripts[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sPageSlug' => $sPageSlug, 'sTabSlug' => $sTabSlug, 'sSRC' => $sSRC, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedScriptIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingScripts[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id'];
    }
    public function _forceToEnqueueStyle($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueStyle($sSRC, '', '', $aCustomArgs);
    }
    public function _forceToEnqueueScript($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueScript($sSRC, '', '', $aCustomArgs);
    }
    protected function _enqueueSRCByConditoin($aEnqueueItem) {
        $sCurrentPageSlug = isset($_GET['page']) ? $_GET['page'] : '';
        $sCurrentTabSlug = isset($_GET['tab']) ? $_GET['tab'] : $this->oProp->getCurrentTab($sCurrentPageSlug);
        $sPageSlug = $aEnqueueItem['sPageSlug'];
        $sTabSlug = $aEnqueueItem['sTabSlug'];
        if (!$sPageSlug && $this->oProp->isPageAdded($sCurrentPageSlug)) {
            return $this->_enqueueSRC($aEnqueueItem);
        }
        if (($sPageSlug && $sCurrentPageSlug == $sPageSlug) && ($sTabSlug && $sCurrentTabSlug == $sTabSlug)) {
            return $this->_enqueueSRC($aEnqueueItem);
        }
        if (($sPageSlug && !$sTabSlug) && ($sCurrentPageSlug == $sPageSlug)) {
            return $this->_enqueueSRC($aEnqueueItem);
        }
    }
}
class Legull_AdminPageFramework_Resource_MetaBox extends Legull_AdminPageFramework_Resource_Base {
    public function _enqueueStyles($aSRCs, $aPostTypes = array(), $aCustomArgs = array()) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueStyle($_sSRC, $aPostTypes, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueStyle($sSRC, $aPostTypes = array(), $aCustomArgs = array()) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingStyles[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingStyles[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'aPostTypes' => empty($aPostTypes) ? $this->oProp->aPostTypes : $aPostTypes, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedStyleIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingStyles[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id'];
    }
    public function _enqueueScripts($aSRCs, $aPostTypes = array(), $aCustomArgs = array()) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueScript($_sSRC, $aPostTypes, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueScript($sSRC, $aPostTypes = array(), $aCustomArgs = array()) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingScripts[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingScripts[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'aPostTypes' => empty($aPostTypes) ? $this->oProp->aPostTypes : $aPostTypes, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedScriptIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingScripts[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id'];
    }
    public function _forceToEnqueueStyle($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueStyle($sSRC, array(), $aCustomArgs);
    }
    public function _forceToEnqueueScript($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueScript($sSRC, array(), $aCustomArgs);
    }
    protected function _enqueueSRCByConditoin($aEnqueueItem) {
        $_sCurrentPostType = isset($_GET['post_type']) ? $_GET['post_type'] : (isset($GLOBALS['typenow']) ? $GLOBALS['typenow'] : null);
        if (in_array($_sCurrentPostType, $aEnqueueItem['aPostTypes'])) {
            return $this->_enqueueSRC($aEnqueueItem);
        }
    }
}
class Legull_AdminPageFramework_Resource_Widget extends Legull_AdminPageFramework_Resource_Base {
    public function _enqueueStyles($aSRCs, $aCustomArgs = array()) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueStyle($_sSRC, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueStyle($sSRC, $aCustomArgs = array()) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingStyles[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingStyles[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedStyleIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingStyles[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id'];
    }
    public function _enqueueScripts($aSRCs, $aCustomArgs = array()) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueScript($_sSRC, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueScript($sSRC, $aCustomArgs = array()) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingScripts[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingScripts[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedScriptIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingScripts[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id'];
    }
    public function _forceToEnqueueStyle($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueStyle($sSRC, $aCustomArgs);
    }
    public function _forceToEnqueueScript($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueScript($sSRC, $aCustomArgs);
    }
}
class Legull_AdminPageFramework_Resource_MetaBox_Page extends Legull_AdminPageFramework_Resource_Page {
}
class Legull_AdminPageFramework_Resource_PostType extends Legull_AdminPageFramework_Resource_MetaBox {
}
class Legull_AdminPageFramework_Resource_TaxonomyField extends Legull_AdminPageFramework_Resource_MetaBox {
    public function _enqueueStyles($aSRCs, $aCustomArgs = array(), $_deprecated = null) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueStyle($_sSRC, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueStyle($sSRC, $aCustomArgs = array(), $_deprecated = null) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingStyles[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingStyles[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedStyleIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingStyles[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingStyles[$_sSRCHash]['handle_id'];
    }
    public function _enqueueScripts($aSRCs, $aCustomArgs = array(), $_deprecated = null) {
        $_aHandleIDs = array();
        foreach (( array )$aSRCs as $_sSRC) {
            $_aHandleIDs[] = $this->_enqueueScript($_sSRC, $aCustomArgs);
        }
        return $_aHandleIDs;
    }
    public function _enqueueScript($sSRC, $aCustomArgs = array(), $_deprecated = null) {
        $sSRC = trim($sSRC);
        if (empty($sSRC)) {
            return '';
        }
        $sSRC = $this->oUtil->resolveSRC($sSRC);
        $_sSRCHash = md5($sSRC);
        if (isset($this->oProp->aEnqueuingScripts[$_sSRCHash])) {
            return '';
        }
        $this->oProp->aEnqueuingScripts[$_sSRCHash] = $this->oUtil->uniteArrays(( array )$aCustomArgs, array('sSRC' => $sSRC, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . (++$this->oProp->iEnqueuedScriptIndex),), self::$_aStructure_EnqueuingResources);
        $this->oProp->aResourceAttributes[$this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id']] = $this->oProp->aEnqueuingScripts[$_sSRCHash]['attributes'];
        return $this->oProp->aEnqueuingScripts[$_sSRCHash]['handle_id'];
    }
    public function _forceToEnqueueStyle($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueStyle($sSRC, $aCustomArgs);
    }
    public function _forceToEnqueueScript($sSRC, $aCustomArgs = array()) {
        return $this->_enqueueScript($sSRC, $aCustomArgs);
    }
    protected function _enqueueSRCByConditoin($aEnqueueItem) {
        return $this->_enqueueSRC($aEnqueueItem);
    }
}
class Legull_AdminPageFramework_Resource_UserMeta extends Legull_AdminPageFramework_Resource_MetaBox {
}
class Legull_AdminPageFramework_CSS {
    static public function getDefaultCSS() {
        $_sCSS = ".wrap div.updated.admin-page-framework-settings-notice-container, .wrap div.error.admin-page-framework-settings-notice-container, .media-upload-form div.error.admin-page-framework-settings-notice-container{clear: both;margin-top: 16px;}.wrap div.error.confirmation.admin-page-framework-settings-notice-container {border-color: #368ADD;}.contextual-help-description {clear: left;display: block;margin: 1em 0;}.contextual-help-tab-title {font-weight: bold;}.admin-page-framework-content {margin-bottom: 1.48em; width: 100%;display: block; }.admin-page-framework-container #poststuff .admin-page-framework-content h3 {font-weight: bold;font-size: 1.3em;margin: 1em 0;padding: 0;font-family: 'Open Sans', sans-serif;}.admin-page-framework-in-page-tab .nav-tab.nav-tab-active {border-bottom-width: 2px;}.wrap .admin-page-framework-in-page-tab div.error, .wrap .admin-page-framework-in-page-tab div.updated {margin-top: 15px;}.admin-page-framework-info {font-size: 0.8em;font-weight: lighter;text-align: right;}pre.dump-array {border: 1px solid #ededed;margin: 24px 2em;margin: 1.714285714rem 2em;padding: 24px;padding: 1.714285714rem;overflow-x: auto; white-space: pre-wrap;background-color: #FFF;margin-bottom: 2em;width: auto;}";
        return $_sCSS . PHP_EOL . self::_getFormSectionRules() . PHP_EOL . self::_getFormFieldRules() . PHP_EOL . self::_getCollapsibleSectionsRules() . PHP_EOL . self::_getFieldErrorRules() . PHP_EOL . self::_getMetaBoxFormRules() . PHP_EOL . self::_getWidgetFormRules() . PHP_EOL . self::_getPageLoadStatsRules() . PHP_EOL . self::_getVersionSpecificRules($GLOBALS['wp_version']);
    }
    static private function _getFormSectionRules() {
        return ".admin-page-framework-section {margin-bottom: 1em; }.admin-page-framework-sectionset {margin-bottom: 1em; }";
    }
    static private function _getFormFieldRules() {
        return "td.admin-page-framework-field-td-no-title {padding-left: 0;padding-right: 0;}.admin-page-framework-section .form-table {margin-top: 0;}.admin-page-framework-section .form-table td label { display: inline;}.admin-page-framework-section-tabs-contents {margin-top: 1em;}.admin-page-framework-section-tabs { margin: 0;}.admin-page-framework-tab-content { padding: 0.5em 2em 1.5em 2em;margin: 0;border-style: solid;border-width: 1px;border-color: #dfdfdf;background-color: #fdfdfd; }.admin-page-framework-section-tab {background-color: transparent;vertical-align: bottom; }.admin-page-framework-section-tab.active {background-color: #fdfdfd; }.admin-page-framework-section-tab h4 {margin: 0;padding: 8px 14px 10px;font-size: 1.2em;}.admin-page-framework-section-tab.nav-tab {padding: 0;}.admin-page-framework-section-tab.nav-tab a {text-decoration: none;color: #464646;vertical-align: inherit; outline: 0; }.admin-page-framework-section-tab.nav-tab a:focus { box-shadow: none;}.admin-page-framework-section-tab.nav-tab.active a {color: #000;}.admin-page-framework-repeatable-section-buttons {float: right;clear: right;margin-top: 1em;}.admin-page-framework-section-caption {text-align: left;margin: 0;}.admin-page-framework-section .admin-page-framework-section-title {}.admin-page-framework-fields {display: table; width: 100%;table-layout: fixed;}.admin-page-framework-field input[type='number'] {text-align: right;} .admin-page-framework-fields .disabled,.admin-page-framework-fields .disabled input,.admin-page-framework-fields .disabled textarea,.admin-page-framework-fields .disabled select,.admin-page-framework-fields .disabled option {color: #BBB;}.admin-page-framework-fields hr {border: 0; height: 0;border-top: 1px solid #dfdfdf; }.admin-page-framework-fields .delimiter {display: inline;}.admin-page-framework-fields-description {margin-bottom: 0;}.admin-page-framework-field {float: left;clear: both;display: inline-block;margin: 1px 0;}.admin-page-framework-field label{display: inline-block; width: 100%;}.admin-page-framework-field .admin-page-framework-input-label-container {margin-bottom: 0.25em;}@media only screen and ( max-width: 780px ) { .admin-page-framework-field .admin-page-framework-input-label-container {margin-bottom: 0.5em;}} .admin-page-framework-field .admin-page-framework-input-label-string {padding-right: 1em; vertical-align: middle; display: inline-block; }.admin-page-framework-field .admin-page-framework-input-button-container {padding-right: 1em; }.admin-page-framework-field .admin-page-framework-input-container {display: inline-block;vertical-align: middle;}.admin-page-framework-field-image .admin-page-framework-input-label-container { vertical-align: middle;}.admin-page-framework-field .admin-page-framework-input-label-container {display: inline-block; vertical-align: middle; } .repeatable .admin-page-framework-field {clear: both;display: block;}.admin-page-framework-repeatable-field-buttons {float: right; margin: 0.1em 0 0.5em 0.3em;vertical-align: middle;}.admin-page-framework-repeatable-field-buttons .repeatable-field-button {margin: 0 0.1em;font-weight: normal;vertical-align: middle;text-align: center;}@media only screen and (max-width: 960px) {.admin-page-framework-repeatable-field-buttons {margin-top: 0;}}.sortable .admin-page-framework-field {clear: both;float: left;display: inline-block;padding: 1em 1.2em 0.78em;margin: 1px 0 0 0;border-top-width: 1px;border-bottom-width: 1px;border-bottom-style: solid;-webkit-user-select: none;-moz-user-select: none;user-select: none; text-shadow: #fff 0 1px 0;-webkit-box-shadow: 0 1px 0 #fff;box-shadow: 0 1px 0 #fff;-webkit-box-shadow: inset 0 1px 0 #fff;box-shadow: inset 0 1px 0 #fff;-webkit-border-radius: 3px;border-radius: 3px;background: #f1f1f1;background-image: -webkit-gradient(linear, left bottom, left top, from(#ececec), to(#f9f9f9));background-image: -webkit-linear-gradient(bottom, #ececec, #f9f9f9);background-image: -moz-linear-gradient(bottom, #ececec, #f9f9f9);background-image: -o-linear-gradient(bottom, #ececec, #f9f9f9);background-image: linear-gradient(to top, #ececec, #f9f9f9);border: 1px solid #CCC;background: #F6F6F6;} .admin-page-framework-fields.sortable {margin-bottom: 1.2em; } .admin-page-framework-field .button.button-small {width: auto;} .font-lighter {font-weight: lighter;} .admin-page-framework-field .button.button-small.dashicons {font-size: 1.2em;padding-left: 0.2em;padding-right: 0.22em;}";
    }
    static private function _getCollapsibleSectionsRules() {
        $_sCSSRules = ".admin-page-framework-collapsible-sections-title, .admin-page-framework-collapsible-section-title{font-size:13px;background-color: #fff;padding: 15px 18px;margin-top: 1em; border-top: 1px solid #eee;border-bottom: 1px solid #eee;}.admin-page-framework-collapsible-sections-title.collapsed.admin-page-framework-collapsible-section-title.collapsed {border-bottom: 1px solid #dfdfdf;margin-bottom: 1em; }.admin-page-framework-collapsible-section-title {margin-top: 0;}.admin-page-framework-collapsible-section-title.collapsed {margin-bottom: 0;}#poststuff .metabox-holder .admin-page-framework-collapsible-sections-title.admin-page-framework-section-title h3,#poststuff .metabox-holder .admin-page-framework-collapsible-section-title.admin-page-framework-section-title h3{font-size: 1em;margin: 0;}.admin-page-framework-collapsible-sections-title.accordion-section-title:after,.admin-page-framework-collapsible-section-title.accordion-section-title:after {top: 12px;right: 15px;}.admin-page-framework-collapsible-sections-title.accordion-section-title:after,.admin-page-framework-collapsible-section-title.accordion-section-title:after {content: '\\f142';}.admin-page-framework-collapsible-sections-title.accordion-section-title.collapsed:after,.admin-page-framework-collapsible-section-title.accordion-section-title.collapsed:after {content: '\\f140';}.admin-page-framework-collapsible-sections-content, .admin-page-framework-collapsible-section-content{border: 1px solid #dfdfdf;border-top: 0;background-color: #fff;}tbody.admin-page-framework-collapsible-content {display: table-caption; padding: 10px 20px 15px 20px;}tbody.admin-page-framework-collapsible-content.table-caption {display: table-caption; }.admin-page-framework-collapsible-toggle-all-button-container {margin-top: 1em;margin-bottom: 1em;width: 100%;display: table; }.admin-page-framework-collapsible-toggle-all-button.button {height: 36px;line-height: 34px;padding: 0 16px 6px;font-size: 20px;width: auto;}.admin-page-framework-collapsible-section-title .admin-page-framework-repeatable-section-buttons {margin: 0;margin-right: 2em; margin-top: -0.32em;}.admin-page-framework-collapsible-section-title .admin-page-framework-repeatable-section-buttons.section_title_field_sibling {margin-top: 0;}.admin-page-framework-collapsible-section-title .repeatable-section-button {background: none; }";
        if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
            $_sCSSRules.= ".admin-page-framework-collapsible-sections-title.accordion-section-title:after,.admin-page-framework-collapsible-section-title.accordion-section-title:after {content: '';top: 18px;}.admin-page-framework-collapsible-sections-title.accordion-section-title.collapsed:after,.admin-page-framework-collapsible-section-title.accordion-section-title.collapsed:after {content: '';} .admin-page-framework-collapsible-toggle-all-button.button {font-size: 1em;}.admin-page-framework-collapsible-section-title .admin-page-framework-repeatable-section-buttons {top: -8px;}";
        }
        return $_sCSSRules;
    }
    static private function _getMetaBoxFormRules() {
        return ".postbox .title-colon {margin-left: 0.2em;}.postbox .admin-page-framework-section .form-table > tbody > tr > td,.postbox .admin-page-framework-section .form-table > tbody > tr > th{display: inline-block;width: 100%;padding: 0;float: right;clear: right; }.postbox .admin-page-framework-field {width: 96%; }.postbox .sortable .admin-page-framework-field {width: auto;} .postbox .admin-page-framework-section .form-table > tbody > tr > th {font-size: 13px;line-height: 1.5;margin: 1em 0px;font-weight: 700;}#poststuff .metabox-holder .admin-page-framework-section-title h3 {border: none;font-weight: bold;font-size: 1.3em;margin: 1em 0;padding: 0;font-family: 'Open Sans', sans-serif; cursor: inherit; -webkit-user-select: inherit;-moz-user-select: inherit;user-select: inherit;text-shadow: none;-webkit-box-shadow: none;box-shadow: none;background: none;} ";
    }
    static private function _getWidgetFormRules() {
        return ".widget .admin-page-framework-section .form-table > tbody > tr > td,.widget .admin-page-framework-section .form-table > tbody > tr > th{display: inline-block;width: 100%;padding: 0;float: right;clear: right; }.widget .admin-page-framework-field,.widget .admin-page-framework-input-label-container{width: 100%;}.widget .sortable .admin-page-framework-field {padding: 4% 4.4% 3.2% 4.4%;width: 91.2%;}.widget .admin-page-framework-field input {margin-bottom: 0.1em;margin-top: 0.1em;}.widget .admin-page-framework-field input[type=text],.widget .admin-page-framework-field textarea {width: 100%;} @media screen and ( max-width: 782px ) {.widget .admin-page-framework-fields {width: 99.2%;}.widget .admin-page-framework-field input[type='checkbox'], .widget .admin-page-framework-field input[type='radio'] {margin-top: 0;}}";
    }
    static private function _getFieldErrorRules() {
        return ".field-error, .section-error{color: red;float: left;clear: both;margin-bottom: 0.5em;}.repeatable-section-error,.repeatable-field-error {float: right;clear: both;color: red;margin-left: 1em;}";
    }
    static private function _getPageLoadStatsRules() {
        return "#admin-page-framework-page-load-stats {clear: both;display: inline-block;width: 100%}#admin-page-framework-page-load-stats li{display: inline;margin-right: 1em;} #wpbody-content {padding-bottom: 140px;}";
    }
    static private function _getVersionSpecificRules($sWPVersion) {
        $_sCSSRules = '';
        if (version_compare($sWPVersion, '3.8', '<')) {
            $_sCSSRules.= ".admin-page-framework-field .remove_value.button.button-small {line-height: 1.5em; }.widget .admin-page-framework-section table.mceLayout {table-layout: fixed;}";
        }
        if (version_compare($sWPVersion, '3.8', '>=')) {
            $_sCSSRules.= ".widget .admin-page-framework-section .form-table th{font-size: 13px;font-weight: normal;margin-bottom: 0.2em;}.widget .admin-page-framework-section .form-table {margin-top: 1em;}.admin-page-framework-repeatable-field-buttons {margin: 2px 0 0 0.3em;} @media screen and ( max-width: 782px ) {.admin-page-framework-fieldset {overflow-x: hidden;}}";
        }
        return $_sCSSRules;
    }
    static public function getDefaultCSSIE() {
        return "tbody.admin-page-framework-collapsible-content > tr > th,tbody.admin-page-framework-collapsible-content > tr > td{padding-right: 20px;padding-left: 20px;}";
    }
}
class Legull_AdminPageFramework_Message {
    public $aMessages = array();
    protected $_sTextDomain = 'admin-page-framework';
    static private $_aInstancesByTextDomain = array();
    public static function getInstance($sTextDomain = 'admin-page-framework') {
        $_oInstance = isset(self::$_aInstancesByTextDomain[$sTextDomain]) && (self::$_aInstancesByTextDomain[$sTextDomain] instanceof Legull_AdminPageFramework_Message) ? self::$_aInstancesByTextDomain[$sTextDomain] : new Legull_AdminPageFramework_Message($sTextDomain);
        self::$_aInstancesByTextDomain[$sTextDomain] = $_oInstance;
        return self::$_aInstancesByTextDomain[$sTextDomain];
    }
    public static function instantiate($sTextDomain = 'admin-page-framework') {
        return self::getInstance($sTextDomain);
    }
    public function __construct($sTextDomain = 'admin-page-framework') {
        $this->_sTextDomain = $sTextDomain;
        $this->aMessages = array('option_updated' => null, 'option_cleared' => null, 'export' => null, 'export_options' => null, 'import_options' => null, 'import_options' => null, 'submit' => null, 'import_error' => null, 'uploaded_file_type_not_supported' => null, 'could_not_load_importing_data' => null, 'imported_data' => null, 'not_imported_data' => null, 'reset_options' => null, 'confirm_perform_task' => null, 'specified_option_been_deleted' => null, 'nonce_verification_failed' => null, 'send_email' => null, 'email_sent' => null, 'email_scheduled' => null, 'email_could_not_send' => null, 'title' => null, 'author' => null, 'categories' => null, 'tags' => null, 'comments' => null, 'date' => null, 'show_all' => null, 'powered_by' => null, 'settings' => null, 'manage' => null, 'upload_image' => null, 'use_this_image' => null, 'select_image' => null, 'upload_file' => null, 'use_this_file' => null, 'select_file' => null, 'remove_value' => null, 'select_all' => null, 'select_none' => null, 'no_term_found' => null, 'insert_from_url' => null, 'select' => null, 'insert' => null, 'use_this' => null, 'return_to_library' => null, 'queries_in_seconds' => null, 'out_of_x_memory_used' => null, 'peak_memory_usage' => null, 'initial_memory_usage' => null, 'allowed_maximum_number_of_fields' => null, 'allowed_minimum_number_of_fields' => null, 'add' => null, 'remove' => null, 'allowed_maximum_number_of_sections' => null, 'allowed_minimum_number_of_sections' => null, 'add_section' => null, 'remove_section' => null, 'toggle_all' => null, 'toggle_all_collapsible_sections' => null, 'reset' => null,);
    }
    public function getTextDomain() {
        return $this->_sTextDomain;
    }
    public function get($sKey) {
        return isset($this->aMessages[$sKey]) ? __($this->aMessages[$sKey], $this->_sTextDomain) : __($this->{$sKey}, $this->_sTextDomain);
    }
    public function output($sKey) {
        echo $this->get($sKey);
    }
    public function __($sKey) {
        return $this->get($sKey);
    }
    public function _e($sKey) {
        $this->output($sKey);
    }
    public function __get($sPropertyName) {
        return $this->_getTranslation($sPropertyName);
    }
    private function _getTranslation($_sLabelKey) {
        switch ($_sLabelKey) {
            case 'option_updated':
                return __('The options have been updated.', 'admin-page-framework');
            case 'option_cleared':
                return __('The options have been cleared.', 'admin-page-framework');
            case 'export':
                return __('Export', 'admin-page-framework');
            case 'export_options':
                return __('Export Options', 'admin-page-framework');
            case 'import_options':
                return __('Import', 'admin-page-framework');
            case 'import_options':
                return __('Import Options', 'admin-page-framework');
            case 'submit':
                return __('Submit', 'admin-page-framework');
            case 'import_error':
                return __('An error occurred while uploading the import file.', 'admin-page-framework');
            case 'uploaded_file_type_not_supported':
                return __('The uploaded file type is not supported: %1$s', 'admin-page-framework');
            case 'could_not_load_importing_data':
                return __('Could not load the importing data.', 'admin-page-framework');
            case 'imported_data':
                return __('The uploaded file has been imported.', 'admin-page-framework');
            case 'not_imported_data':
                return __('No data could be imported.', 'admin-page-framework');
            case 'upload_image':
                return __('Upload Image', 'admin-page-framework');
            case 'use_this_image':
                return __('Use This Image', 'admin-page-framework');
            case 'insert_from_url':
                return __('Insert from URL', 'admin-page-framework');
            case 'reset_options':
                return __('Are you sure you want to reset the options?', 'admin-page-framework');
            case 'confirm_perform_task':
                return __('Please confirm your action.', 'admin-page-framework');
            case 'specified_option_been_deleted':
                return __('The specified options have been deleted.', 'admin-page-framework');
            case 'nonce_verification_failed':
                return __('A problem occurred while processing the form data. Please try again.', 'admin-page-framework');
            case 'send_email':
                return __('Is it okay to send the email?', 'admin-page-framework');
            case 'email_sent':
                return __('The email has been sent.', 'admin-page-framework');
            case 'email_scheduled':
                return __('The email has been scheduled.', 'admin-page-framework');
            case 'email_could_not_send':
                return __('There was a problem sending the email', 'admin-page-framework');
            case 'title':
                return __('Title', 'admin-page-framework');
            case 'author':
                return __('Author', 'admin-page-framework');
            case 'categories':
                return __('Categories', 'admin-page-framework');
            case 'tags':
                return __('Tags', 'admin-page-framework');
            case 'comments':
                return __('Comments', 'admin-page-framework');
            case 'date':
                return __('Date', 'admin-page-framework');
            case 'show_all':
                return __('Show All', 'admin-page-framework');
            case 'powered_by':
                return __('Powered by', 'admin-page-framework');
            case 'settings':
                return __('Settings', 'admin-page-framework');
            case 'manage':
                return __('Manage', 'admin-page-framework');
            case 'select_image':
                return __('Select Image', 'admin-page-framework');
            case 'upload_file':
                return __('Upload File', 'admin-page-framework');
            case 'use_this_file':
                return __('Use This File', 'admin-page-framework');
            case 'select_file':
                return __('Select File', 'admin-page-framework');
            case 'remove_value':
                return __('Remove Value', 'admin-page-framework');
            case 'select_all':
                return __('Select All', 'admin-page-framework');
            case 'select_none':
                return __('Select None', 'admin-page-framework');
            case 'no_term_found':
                return __('No term found.', 'admin-page-framework');
            case 'select':
                return __('Select', 'admin-page-framework');
            case 'insert':
                return __('Insert', 'admin-page-framework');
            case 'use_this':
                return __('Use This', 'admin-page-framework');
            case 'return_to_library':
                return __('Return to Library', 'admin-page-framework');
            case 'queries_in_seconds':
                return __('%1$s queries in %2$s seconds.', 'admin-page-framework');
            case 'out_of_x_memory_used':
                return __('%1$s out of %2$s MB (%3$s) memory used.', 'admin-page-framework');
            case 'peak_memory_usage':
                return __('Peak memory usage %1$s MB.', 'admin-page-framework');
            case 'initial_memory_usage':
                return __('Initial memory usage  %1$s MB.', 'admin-page-framework');
            case 'allowed_maximum_number_of_fields':
                return __('The allowed maximum number of fields is {0}.', 'admin-page-framework');
            case 'allowed_minimum_number_of_fields':
                return __('The allowed minimum number of fields is {0}.', 'admin-page-framework');
            case 'add':
                return __('Add', 'admin-page-framework');
            case 'remove':
                return __('Remove', 'admin-page-framework');
            case 'allowed_maximum_number_of_sections':
                return __('The allowed maximum number of sections is {0}', 'admin-page-framework');
            case 'allowed_minimum_number_of_sections':
                return __('The allowed minimum number of sections is {0}', 'admin-page-framework');
            case 'add_section':
                return __('Add Section', 'admin-page-framework');
            case 'remove_section':
                return __('Remove Section', 'admin-page-framework');
            case 'toggle_all':
                return __('Toggle All', 'admin-page-framework');
            case 'toggle_all_collapsible_sections':
                return __('Toggle all collapsible sections', 'admin-page-framework');
            case 'reset':
                return __('Reset', 'admin-page-framework');
        }
    }
}
if (php_sapi_name() === 'cli') {
    $_sFrameworkFilePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/admin-page-framework.php';
    if (file_exists($_sFrameworkFilePath)) {
        include_once ($_sFrameworkFilePath);
    }
}
final class Legull_AdminPageFramework_MinifiedVersionHeader extends Legull_AdminPageFramework_Registry_Base {
    const Name = 'Admin Page Framework - Minified Version';
    const Description = 'Generated by PHP Class Minifier <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>';
}
abstract class Legull_AdminPageFramework_Property_Base {
    private static $_aStructure_CallerInfo = array('sPath' => null, 'sType' => null, 'sName' => null, 'sURI' => null, 'sVersion' => null, 'sThemeURI' => null, 'sScriptURI' => null, 'sAuthorURI' => null, 'sAuthor' => null, 'sDescription' => null,);
    static public $_aLibraryData;
    public $_sPropertyType = '';
    protected $oCaller;
    public $sCallerPath;
    public $sClassName;
    public $sClassHash;
    public $sScript = '';
    public $sStyle = '';
    public $sStyleIE = '';
    public $aFieldTypeDefinitions = array();
    public static $_sDefaultScript = "";
    public static $_sDefaultStyle = "";
    public static $_sDefaultStyleIE = '';
    public $aEnqueuingScripts = array();
    public $aEnqueuingStyles = array();
    public $aResourceAttributes = array();
    public $iEnqueuedScriptIndex = 0;
    public $iEnqueuedStyleIndex = 0;
    public $bIsAdmin;
    public $bIsMinifiedVersion;
    public $sCapability;
    public $sFieldsType;
    public $sTextDomain;
    public $sPageNow;
    public $_bSetupLoaded;
    public $bIsAdminAjax;
    public $aFieldCallbacks = array('hfID' => null, 'hfTagID' => null, 'hfName' => null, 'hfNameFlat' => null, 'hfClass' => null,);
    public function __construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $sFieldsType) {
        $this->oCaller = $oCaller;
        $this->sCallerPath = $sCallerPath ? $sCallerPath : null;
        $this->sClassName = $sClassName;
        $this->sClassHash = md5($sClassName);
        $this->sCapability = empty($sCapability) ? 'manage_options' : $sCapability;
        $this->sTextDomain = empty($sTextDomain) ? 'admin-page-framework' : $sTextDomain;
        $this->sFieldsType = $sFieldsType;
        $GLOBALS['aLegull_AdminPageFramework'] = isset($GLOBALS['aLegull_AdminPageFramework']) && is_array($GLOBALS['aLegull_AdminPageFramework']) ? $GLOBALS['aLegull_AdminPageFramework'] : array('aFieldFlags' => array());
        $this->sPageNow = Legull_AdminPageFramework_WPUtility::getPageNow();
        $this->bIsAdmin = is_admin();
        $this->bIsAdminAjax = in_array($this->sPageNow, array('admin-ajax.php'));
    }
    public function _getCallerObject() {
        return $this->oCaller;
    }
    static public function _setLibraryData() {
        self::$_aLibraryData = array('sName' => Legull_AdminPageFramework_Registry::Name, 'sURI' => Legull_AdminPageFramework_Registry::URI, 'sScriptName' => Legull_AdminPageFramework_Registry::Name, 'sLibraryName' => Legull_AdminPageFramework_Registry::Name, 'sLibraryURI' => Legull_AdminPageFramework_Registry::URI, 'sPluginName' => '', 'sPluginURI' => '', 'sThemeName' => '', 'sThemeURI' => '', 'sVersion' => Legull_AdminPageFramework_Registry::getVersion(), 'sDescription' => Legull_AdminPageFramework_Registry::Description, 'sAuthor' => Legull_AdminPageFramework_Registry::Author, 'sAuthorURI' => Legull_AdminPageFramework_Registry::AuthorURI, 'sTextDomain' => Legull_AdminPageFramework_Registry::TextDomain, 'sDomainPath' => Legull_AdminPageFramework_Registry::TextDomainPath, 'sNetwork' => '', '_sitewide' => '',);
        return self::$_aLibraryData;
    }
    static public function _getLibraryData() {
        return isset(self::$_aLibraryData) ? self::$_aLibraryData : self::_setLibraryData();
    }
    protected function getCallerInfo($sCallerPath = null) {
        $_aCallerInfo = self::$_aStructure_CallerInfo;
        $_aCallerInfo['sPath'] = $sCallerPath;
        $_aCallerInfo['sType'] = $this->_getCallerType($_aCallerInfo['sPath']);
        if ('unknown' == $_aCallerInfo['sType']) {
            return $_aCallerInfo;
        }
        if ('plugin' == $_aCallerInfo['sType']) {
            return Legull_AdminPageFramework_WPUtility::getScriptData($_aCallerInfo['sPath'], $_aCallerInfo['sType']) + $_aCallerInfo;
        }
        if ('theme' == $_aCallerInfo['sType']) {
            $_oTheme = wp_get_theme();
            return array('sName' => $_oTheme->Name, 'sVersion' => $_oTheme->Version, 'sThemeURI' => $_oTheme->get('ThemeURI'), 'sURI' => $_oTheme->get('ThemeURI'), 'sAuthorURI' => $_oTheme->get('AuthorURI'), 'sAuthor' => $_oTheme->get('Author'),) + $_aCallerInfo;
        }
        return array();
    }
    protected function _getCallerType($sScriptPath) {
        if (preg_match('/[\/\\\\]themes[\/\\\\]/', $sScriptPath, $m)) {
            return 'theme';
        }
        if (preg_match('/[\/\\\\]plugins[\/\\\\]/', $sScriptPath, $m)) {
            return 'plugin';
        }
        return 'unknown';
    }
    public function isPostDefinitionPage($asPostTypes = array()) {
        $_aPostTypes = ( array )$asPostTypes;
        if (!in_array($this->sPageNow, array('post.php', 'post-new.php',))) {
            return false;
        }
        if (empty($_aPostTypes)) {
            return true;
        }
        if (isset($_GET['post_type']) && in_array($_GET['post_type'], $_aPostTypes)) {
            return true;
        }
        $this->_sCurrentPostType = isset($this->_sCurrentPostType) ? $this->_sCurrentPostType : (isset($_GET['post']) ? get_post_type($_GET['post']) : '');
        if (isset($_GET['post'], $_GET['action']) && in_array($this->_sCurrentPostType, $_aPostTypes)) {
            return true;
        }
        return false;
    }
    protected function _getOptions() {
        return array();
    }
    protected function _getLastInput() {
        $_vValue = Legull_AdminPageFramework_WPUtility::getTransient('apf_tfd' . md5('temporary_form_data_' . $this->sClassName . get_current_user_id()));
        if (is_array($_vValue)) {
            return $_vValue;
        }
        return array();
    }
    public function __get($sName) {
        if ('aScriptInfo' === $sName) {
            $this->sCallerPath = $this->sCallerPath ? $this->sCallerPath : Legull_AdminPageFramework_Utility::getCallerScriptPath(__FILE__);
            $this->aScriptInfo = $this->getCallerInfo($this->sCallerPath);
            return $this->aScriptInfo;
        }
        if ('aOptions' === $sName) {
            $this->aOptions = $this->_getOptions();
            return $this->aOptions;
        }
        if ('aLastInput' === $sName) {
            $this->aLastInput = $this->_getLastInput();
            return $this->aLastInput;
        }
    }
}
class Legull_AdminPageFramework_Property_Page extends Legull_AdminPageFramework_Property_Base {
    public $_sPropertyType = 'page';
    public $sFieldsType = 'page';
    public $sClassName;
    public $sClassHash;
    public $sCapability = 'manage_options';
    public $sPageHeadingTabTag = 'h2';
    public $sInPageTabTag = 'h3';
    public $sDefaultPageSlug;
    public $aPages = array();
    public $aHiddenPages = array();
    public $aRegisteredSubMenuPages = array();
    public $aRootMenu = array('sTitle' => null, 'sPageSlug' => null, 'sIcon16x16' => null, 'iPosition' => null, 'fCreateRoot' => null,);
    public $aInPageTabs = array();
    public $aDefaultInPageTabs = array();
    public $aPluginDescriptionLinks = array();
    public $aPluginTitleLinks = array();
    public $aFooterInfo = array('sLeft' => '', 'sRight' => '',);
    public $sOptionKey = '';
    public $aHelpTabs = array();
    public $sFormEncType = 'multipart/form-data';
    public $sThickBoxButtonUseThis = '';
    public $bEnableForm = false;
    public $bShowPageTitle = true;
    public $bShowPageHeadingTabs = true;
    public $bShowInPageTabs = true;
    public $aAdminNotices = array();
    public $aDisallowedQueryKeys = array('settings-updated', 'confirmation', 'field_errors');
    public $sTargetFormPage = '';
    public $_bBuiltMenu = false;
    public $sLabelPluginSettingsLink = null;
    public $_bDisableSavingOptions = false;
    public $aPageHooks = array();
    public $sWrapperClassAttribute = 'wrap';
    public function __construct($oCaller, $sCallerPath, $sClassName, $sOptionKey, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        parent::__construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $this->sFieldsType);
        $this->sTargetFormPage = $_SERVER['REQUEST_URI'];
        $this->sOptionKey = $sOptionKey ? $sOptionKey : $sClassName;
        $this->_bDisableSavingOptions = '' === $sOptionKey ? true : false;
        $GLOBALS['aLegull_AdminPageFramework']['aPageClasses'] = isset($GLOBALS['aLegull_AdminPageFramework']['aPageClasses']) && is_array($GLOBALS['aLegull_AdminPageFramework']['aPageClasses']) ? $GLOBALS['aLegull_AdminPageFramework']['aPageClasses'] : array();
        $GLOBALS['aLegull_AdminPageFramework']['aPageClasses'][$sClassName] = $oCaller;
        add_filter("option_page_capability_{$this->sOptionKey}", array($this, '_replyToGetCapability'));
    }
    protected function _isAdminPage() {
        if (!is_admin()) {
            return false;
        }
        return isset($_GET['page']);
    }
    protected function _getOptions() {
        $_aOptions = Legull_AdminPageFramework_WPUtility::addAndApplyFilter($this->oCaller, 'options_' . $this->sClassName, $this->sOptionKey ? get_option($this->sOptionKey, array()) : array());
        $_aLastInput = isset($_GET['field_errors']) && $_GET['field_errors'] ? $this->_getLastInput() : array();
        $_aOptions = empty($_aOptions) ? array() : Legull_AdminPageFramework_WPUtility::getAsArray($_aOptions);
        $_aOptions = $_aLastInput + $_aOptions;
        return $_aOptions;
    }
    public function updateOption($aOptions = null) {
        if ($this->_bDisableSavingOptions) {
            return;
        }
        return update_option($this->sOptionKey, $aOptions !== null ? $aOptions : $this->aOptions);
    }
    public function isPageAdded($sPageSlug = '') {
        $sPageSlug = $sPageSlug ? trim($sPageSlug) : (isset($_GET['page']) ? $_GET['page'] : '');
        return isset($this->aPages[$sPageSlug]);
    }
    public function getCurrentTab($sCurrentPageSlug = '') {
        if (isset($_GET['tab']) && $_GET['tab']) {
            return $_GET['tab'];
        }
        $sCurrentPageSlug = $sCurrentPageSlug ? $sCurrentPageSlug : (isset($_GET['page']) && $_GET['page'] ? $_GET['page'] : '');
        return $sCurrentPageSlug ? $this->getDefaultInPageTab($sCurrentPageSlug) : null;
    }
    public function getDefaultInPageTab($sPageSlug) {
        if (!$sPageSlug) return '';
        return isset($this->aDefaultInPageTabs[$sPageSlug]) ? $this->aDefaultInPageTabs[$sPageSlug] : '';
    }
    public function getDefaultOptions($aFields) {
        $_aDefaultOptions = array();
        foreach ($aFields as $_sSectionID => $_aFields) {
            foreach ($_aFields as $_sFieldID => $_aField) {
                $_vDefault = $this->_getDefautValue($_aField);
                if (isset($_aField['section_id']) && $_aField['section_id'] != '_default') $_aDefaultOptions[$_aField['section_id']][$_sFieldID] = $_vDefault;
                else $_aDefaultOptions[$_sFieldID] = $_vDefault;
            }
        }
        return $_aDefaultOptions;
    }
    private function _getDefautValue($aField) {
        $_aSubFields = Legull_AdminPageFramework_Utility::getIntegerElements($aField);
        if (count($_aSubFields) == 0) {
            $_aField = $aField;
            return isset($_aField['value']) ? $_aField['value'] : (isset($_aField['default']) ? $_aField['default'] : null);
        }
        $_aDefault = array();
        array_unshift($_aSubFields, $aField);
        foreach ($_aSubFields as $_iIndex => $_aField) $_aDefault[$_iIndex] = isset($_aField['value']) ? $_aField['value'] : (isset($_aField['default']) ? $_aField['default'] : null);
        return $_aDefault;
    }
    public function _replyToGetCapability() {
        return $this->sCapability;
    }
}
class Legull_AdminPageFramework_Property_MetaBox extends Legull_AdminPageFramework_Property_Base {
    public $_sPropertyType = 'post_meta_box';
    public $sMetaBoxID = '';
    public $sTitle = '';
    public $aPostTypes = array();
    public $aPages = array();
    public $sContext = 'normal';
    public $sPriority = 'default';
    public $sClassName = '';
    public $sCapability = 'edit_posts';
    public $sThickBoxTitle = '';
    public $sThickBoxButtonUseThis = '';
    public $aHelpTabText = array();
    public $aHelpTabTextSide = array();
    public $sFieldsType = 'post_meta_box';
    function __construct($oCaller, $sClassName, $sCapability = 'edit_posts', $sTextDomain = 'admin-page-framework', $sFieldsType = 'post_meta_box') {
        parent::__construct($oCaller, null, $sClassName, $sCapability, $sTextDomain, $sFieldsType);
    }
    protected function _getOptions() {
        return array();
    }
}
class Legull_AdminPageFramework_Property_PostType extends Legull_AdminPageFramework_Property_Base {
    public $_sPropertyType = 'post_type';
    public $sPostType = '';
    public $aPostTypeArgs = array();
    public $sClassName = '';
    public $aColumnHeaders = array('cb' => '<input type="checkbox" />', 'title' => 'Title', 'author' => 'Author', 'comments' => '<div class="comment-grey-bubble"></div>', 'date' => 'Date',);
    public $aColumnSortable = array('title' => true, 'date' => true,);
    public $sCallerPath = '';
    public $aTaxonomies;
    public $aTaxonomyObjectTypes = array();
    public $aTaxonomyTableFilters = array();
    public $aTaxonomyRemoveSubmenuPages = array();
    public $bEnableAutoSave = true;
    public $bEnableAuthorTableFileter = false;
    public function __construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $sFieldsType) {
        parent::__construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $sFieldsType);
        if (!$sCallerPath) {
            return;
        }
        switch ($this->_getCallerType($sCallerPath)) {
            case 'theme':
                add_action('after_switch_theme', array('Legull_AdminPageFramework_WPUtility', 'FlushRewriteRules'));
            break;
            case 'plugin':
                register_activation_hook($sCallerPath, array('Legull_AdminPageFramework_WPUtility', 'FlushRewriteRules'));
                register_deactivation_hook($sCallerPath, array('Legull_AdminPageFramework_WPUtility', 'FlushRewriteRules'));
            break;
        }
    }
}
class Legull_AdminPageFramework_Property_Widget extends Legull_AdminPageFramework_Property_Base {
    public $_sPropertyType = 'widget';
    public $sFieldsType = 'widget';
    public $sClassName = '';
    public $sCallerPath = '';
    public $sWidgetTitle = '';
    public $aWidgetArguments = array();
}
class Legull_AdminPageFramework_Property_NetworkAdmin extends Legull_AdminPageFramework_Property_Page {
    public $_sPropertyType = 'network_admin_page';
    public $sFieldsType = 'network_admin_page';
    protected function _getOptions() {
        return Legull_AdminPageFramework_WPUtility::addAndApplyFilter($GLOBALS['aLegull_AdminPageFramework']['aPageClasses'][$this->sClassName], 'options_' . $this->sClassName, $this->sOptionKey ? get_site_option($this->sOptionKey, array()) : array());
    }
    public function updateOption($aOptions = null) {
        if ($this->_bDisableSavingOptions) {
            return;
        }
        return update_site_option($this->sOptionKey, $aOptions !== null ? $aOptions : $this->aOptions);
    }
}
class Legull_AdminPageFramework_Property_MetaBox_Page extends Legull_AdminPageFramework_Property_MetaBox {
    public $_sPropertyType = 'page_meta_box';
    public $aPageSlugs = array();
    public $oAdminPage;
    public $aHelpTabs = array();
    function __construct($oCaller, $sClassName, $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework', $sFieldsType = 'page_meta_box') {
        add_action('admin_menu', array($this, '_replyToSetUpProperties'), 100);
        if (is_network_admin()) {
            add_action('network_admin_menu', array($this, '_replyToSetUpProperties'), 100);
        }
        parent::__construct($oCaller, $sClassName, $sCapability, $sTextDomain, $sFieldsType);
        $GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses'] = isset($GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses']) && is_array($GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses']) ? $GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses'] : array();
        $GLOBALS['aLegull_AdminPageFramework']['aMetaBoxForPagesClasses'][$sClassName] = $oCaller;
    }
    public function _replyToSetUpProperties() {
        if (!isset($_GET['page'])) {
            return;
        }
        $this->oAdminPage = $this->_getOwnerObjectOfPage($_GET['page']);
        if (!$this->oAdminPage) {
            return;
        }
        $this->aHelpTabs = $this->oAdminPage->oProp->aHelpTabs;
        $this->oAdminPage->oProp->bEnableForm = true;
        $this->aOptions = $this->oAdminPage->oProp->aOptions;
    }
    public function _getScreenIDOfPage($sPageSlug) {
        return ($_oAdminPage = $this->_getOwnerObjectOfPage($sPageSlug)) ? $_oAdminPage->oProp->aPages[$sPageSlug]['_page_hook'] . (is_network_admin() ? '-network' : '') : '';
    }
    public function isPageAdded($sPageSlug = '') {
        return ($_oAdminPage = $this->_getOwnerObjectOfPage($sPageSlug)) ? $_oAdminPage->oProp->isPageAdded($sPageSlug) : false;
    }
    public function isCurrentTab($sTabSlug) {
        $_sCurrentPageSlug = isset($_GET['page']) ? $_GET['page'] : '';
        if (!$_sCurrentPageSlug) {
            return false;
        }
        $_sCurrentTabSlug = isset($_GET['tab']) ? $_GET['tab'] : $this->getDefaultInPageTab($_sCurrentPageSlug);
        return ($sTabSlug == $_sCurrentTabSlug);
    }
    public function getCurrentTab($sPageSlug) {
        $_oAdminPage = $this->_getOwnerObjectOfPage($sPageSlug);
        return $_oAdminPage->oProp->getCurrentTab($sPageSlug);
    }
    public function getDefaultInPageTab($sPageSlug) {
        if (!$sPageSlug) {
            return '';
        }
        return ($_oAdminPage = $this->_getOwnerObjectOfPage($sPageSlug)) ? $_oAdminPage->oProp->getDefaultInPageTab($sPageSlug) : '';
    }
    public function getOptionKey($sPageSlug) {
        if (!$sPageSlug) {
            return '';
        }
        return ($_oAdminPage = $this->_getOwnerObjectOfPage($sPageSlug)) ? $_oAdminPage->oProp->sOptionKey : '';
    }
    private function _getOwnerObjectOfPage($sPageSlug) {
        if (!isset($GLOBALS['aLegull_AdminPageFramework']['aPageClasses'])) {
            return null;
        }
        if (!is_array($GLOBALS['aLegull_AdminPageFramework']['aPageClasses'])) {
            return null;
        }
        foreach ($GLOBALS['aLegull_AdminPageFramework']['aPageClasses'] as $__oClass) {
            if ($__oClass->oProp->isPageAdded($sPageSlug)) {
                return $__oClass;
            }
        }
        return null;
    }
}
class Legull_AdminPageFramework_Property_TaxonomyField extends Legull_AdminPageFramework_Property_MetaBox {
    public $_sPropertyType = 'taxonomy_field';
    public $aTaxonomySlugs;
    public $sOptionKey;
}
class Legull_AdminPageFramework_Property_UserMeta extends Legull_AdminPageFramework_Property_MetaBox {
    public $_sPropertyType = 'user_meta';
    protected function _getOptions() {
        return array();
    }
}
class Legull_AdminPageFramework_ErrorReporting {
    private $_aLevels = array(1 => 'E_ERROR', 2 => 'E_WARNING', 4 => 'E_PARSE', 8 => 'E_NOTICE', 16 => 'E_CORE_ERROR', 32 => 'E_CORE_WARNING', 64 => 'E_COMPILE_ERROR', 128 => 'E_COMPILE_WARNING', 256 => 'E_USER_ERROR', 512 => 'E_USER_WARNING', 1024 => 'E_USER_NOTICE', 2048 => 'E_STRICT', 4096 => 'E_RECOVERABLE_ERROR', 8192 => 'E_DEPRECATED', 16384 => 'E_USER_DEPRECATED');
    private $_iLevel;
    public function __construct($iLevel = null) {
        $this->_iLevel = null !== $iLevel ? $iLeevl : error_reporting();
    }
    public function getErrorLevel() {
        return $this->_getErrorDescription($this->_getIncluded());
    }
    private function _getIncluded() {
        $_aIncluded = array();
        foreach ($this->_aLevels as $_iLevel => $iLevelText) {
            if ($this->_iLevel & $_iLevel) {
                $_aIncluded[] = $_iLevel;
            }
        }
        return $_aIncluded;
    }
    private function _getErrorDescription($aIncluded) {
        $_iAll = count($this->_aLevels);
        $_aValues = array();
        if (count($aIncluded) > $_iAll / 2) {
            $_aValues[] = 'E_ALL';
            foreach ($this->_aLevels as $_iLevel => $iLevelText) {
                if (!in_array($_iLevel, $aIncluded)) {
                    $_aValues[] = $iLevelText;
                }
            }
            return implode(' & ~', $_aValues);
        }
        foreach ($aIncluded as $_iLevel) {
            $_aValues[] = $this->_aLevels[$_iLevel];
        }
        return implode(' | ', $_aValues);
    }
}
class Legull_AdminPageFramework_RegisterClasses {
    public $_aClasses = array();
    static protected $_aStructure_Options = array('is_recursive' => true, 'exclude_dir_paths' => array(), 'exclude_dir_names' => array('asset', 'assets', 'css', 'js', 'image', 'images', 'license', 'document', 'documents'), 'allowed_extensions' => array('php',), 'include_function' => 'include', 'exclude_class_names' => array(),);
    function __construct($asScanDirPaths, array $aOptions = array(), array $aClasses = array()) {
        $_aOptions = $aOptions + self::$_aStructure_Options;
        $this->_aClasses = $aClasses + $this->_constructClassArray($asScanDirPaths, $_aOptions);
        $_sIncludeFunciton = in_array($_aOptions['include_function'], array('require', 'require_once', 'include', 'include_once')) ? $_aOptions['include_function'] : 'include';
        $this->_registerClasses($_sIncludeFunciton);
    }
    protected function _constructClassArray($asScanDirPaths, array $aSearchOptions) {
        if (empty($asScanDirPaths)) {
            return array();
        }
        $_aFilePaths = array();
        foreach (( array )$asScanDirPaths as $_sClassDirPath) {
            if (realpath($_sClassDirPath)) {
                $_aFilePaths = array_merge($this->getFilePaths($_sClassDirPath, $aSearchOptions), $_aFilePaths);
            }
        }
        $_aClasses = array();
        foreach ($_aFilePaths as $_sFilePath) {
            $_sClassNameWOExt = pathinfo($_sFilePath, PATHINFO_FILENAME);
            if (in_array($_sClassNameWOExt, $aSearchOptions['exclude_class_names'])) {
                continue;
            }
            $_aClasses[$_sClassNameWOExt] = $_sFilePath;
        }
        return $_aClasses;
    }
    protected function getFilePaths($sClassDirPath, array $aSearchOptions) {
        $sClassDirPath = rtrim($sClassDirPath, '\\/') . DIRECTORY_SEPARATOR;
        $_aAllowedExtensions = $aSearchOptions['allowed_extensions'];
        $_aExcludeDirPaths = ( array )$aSearchOptions['exclude_dir_paths'];
        $_aExcludeDirNames = ( array )$aSearchOptions['exclude_dir_names'];
        $_bIsRecursive = $aSearchOptions['is_recursive'];
        if (defined('GLOB_BRACE')) {
            $_aFilePaths = $_bIsRecursive ? $this->doRecursiveGlob($sClassDirPath . '*.' . $this->_getGlobPatternExtensionPart($_aAllowedExtensions), GLOB_BRACE, $_aExcludeDirPaths, $_aExcludeDirNames) : ( array )glob($sClassDirPath . '*.' . $this->_getGlobPatternExtensionPart($_aAllowedExtensions), GLOB_BRACE);
            return array_filter($_aFilePaths);
        }
        $_aFilePaths = array();
        foreach ($_aAllowedExtensions as $__sAllowedExtension) {
            $__aFilePaths = $_bIsRecursive ? $this->doRecursiveGlob($sClassDirPath . '*.' . $__sAllowedExtension, 0, $_aExcludeDirPaths, $_aExcludeDirNames) : ( array )glob($sClassDirPath . '*.' . $__sAllowedExtension);
            $_aFilePaths = array_merge($__aFilePaths, $_aFilePaths);
        }
        return array_unique(array_filter($_aFilePaths));
    }
    protected function _getGlobPatternExtensionPart(array $aExtensions = array('php', 'inc')) {
        return empty($aExtensions) ? '*' : '{' . implode(',', $aExtensions) . '}';
    }
    protected function doRecursiveGlob($sPathPatten, $nFlags = 0, array $aExcludeDirs = array(), array $aExcludeDirNames = array()) {
        $_aFiles = glob($sPathPatten, $nFlags);
        $_aFiles = is_array($_aFiles) ? $_aFiles : array();
        $_aDirs = glob(dirname($sPathPatten) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT);
        $_aDirs = is_array($_aDirs) ? $_aDirs : array();
        foreach ($_aDirs as $_sDirPath) {
            if (in_array($_sDirPath, $aExcludeDirs)) {
                continue;
            }
            if (in_array(pathinfo($_sDirPath, PATHINFO_DIRNAME), $aExcludeDirNames)) {
                continue;
            }
            $_aFiles = array_merge($_aFiles, $this->doRecursiveGlob($_sDirPath . DIRECTORY_SEPARATOR . basename($sPathPatten), $nFlags, $aExcludeDirs));
        }
        return $_aFiles;
    }
    protected function _registerClasses($sIncludeFunction) {
        spl_autoload_register(array($this, '_replyToAutoLoad_' . $sIncludeFunction));
    }
    public function _replyToAutoLoad_include($sCalledUnknownClassName) {
        if (!isset($this->_aClasses[$sCalledUnknownClassName])) {
            return;
        }
        include ($this->_aClasses[$sCalledUnknownClassName]);
    }
    public function _replyToAutoLoad_include_once($sCalledUnknownClassName) {
        if (!isset($this->_aClasses[$sCalledUnknownClassName])) {
            return;
        }
        include_once ($this->_aClasses[$sCalledUnknownClassName]);
    }
    public function _replyToAutoLoad_require($sCalledUnknownClassName) {
        if (!isset($this->_aClasses[$sCalledUnknownClassName])) {
            return;
        }
        require ($this->_aClasses[$sCalledUnknownClassName]);
    }
    public function _replyToAutoLoad_require_once($sCalledUnknownClassName) {
        if (!isset($this->_aClasses[$sCalledUnknownClassName])) {
            return;
        }
        require_once ($this->_aClasses[$sCalledUnknownClassName]);
    }
}
abstract class Legull_AdminPageFramework_Utility_String {
    public static function sanitizeSlug($sSlug) {
        return is_null($sSlug) ? null : preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', '_', trim($sSlug));
    }
    public static function sanitizeString($sString) {
        return is_null($sString) ? null : preg_replace('/[^a-zA-Z0-9_\x7f-\xff\-]/', '_', $sString);
    }
    static public function fixNumber($nToFix, $nDefault, $nMin = "", $nMax = "") {
        if (!is_numeric(trim($nToFix))) return $nDefault;
        if ($nMin !== "" && $nToFix < $nMin) return $nMin;
        if ($nMax !== "" && $nToFix > $nMax) return $nMax;
        return $nToFix;
    }
    static public function minifyCSS($sCSSRules) {
        return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $sCSSRules));
    }
    static public function getStringLength($sString) {
        return function_exists('mb_strlen') ? mb_strlen($sString) : strlen($sString);
    }
    static public function getNumberOfReadableSize($nSize) {
        $_nReturn = substr($nSize, 0, -1);
        switch (strtoupper(substr($nSize, -1))) {
            case 'P':
                $_nReturn*= 1024;
            case 'T':
                $_nReturn*= 1024;
            case 'G':
                $_nReturn*= 1024;
            case 'M':
                $_nReturn*= 1024;
            case 'K':
                $_nReturn*= 1024;
        }
        return $_nReturn;
    }
    static public function getReadableBytes($nBytes) {
        $_aUnits = array(0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB');
        $_nLog = log($nBytes, 1024);
        $_iPower = ( int )$_nLog;
        $_iSize = pow(1024, $_nLog - $_iPower);
        return $_iSize . $_aUnits[$_iPower];
    }
}
abstract class Legull_AdminPageFramework_Utility_Array extends Legull_AdminPageFramework_Utility_String {
    public static function getCorrespondingArrayValue($vSubject, $sKey, $sDefault = '', $bBlankToDefault = false) {
        if (!isset($vSubject)) {
            return $sDefault;
        }
        if ($bBlankToDefault && $vSubject == '') {
            return $sDefault;
        }
        if (!is_array($vSubject)) {
            return ( string )$vSubject;
        }
        if (isset($vSubject[$sKey])) {
            return $vSubject[$sKey];
        }
        return $sDefault;
    }
    static public function getElement($aSubject, $isKey, $vDefault = null) {
        return isset($aSubject[$isKey]) ? $aSubject[$isKey] : $vDefault;
    }
    static public function getElementAsArray($aSubject, $isKey, $vDefault = null) {
        return self::getAsArray(self::getElement($aSubject, $isKey, $vDefault));
    }
    public static function getArrayDimension($array) {
        return (is_array(reset($array))) ? self::getArrayDimension(reset($array)) + 1 : 1;
    }
    public static function castArrayContents($aModel, $aSubject) {
        $aMod = array();
        foreach ($aModel as $sKey => $_v) {
            $aMod[$sKey] = isset($aSubject[$sKey]) ? $aSubject[$sKey] : null;
        }
        return $aMod;
    }
    public static function invertCastArrayContents($sModel, $aSubject) {
        $_aMod = array();
        foreach ($sModel as $_sKey => $_v) {
            if (array_key_exists($_sKey, $aSubject)) {
                continue;
            }
            $_aMod[$_sKey] = $_v;
        }
        return $_aMod;
    }
    public static function uniteArrays() {
        $_aArgs = array_reverse(func_get_args());
        $_aArray = array();
        foreach ($_aArgs as $_aArg) {
            $_aArray = self::uniteArraysRecursive($_aArg, $_aArray);
        }
        return $_aArray;
    }
    public static function uniteArraysRecursive($aPrecedence, $aDefault) {
        if (is_null($aPrecedence)) {
            $aPrecedence = array();
        }
        if (!is_array($aDefault) || !is_array($aPrecedence)) {
            return $aPrecedence;
        }
        foreach ($aDefault as $sKey => $v) {
            if (!array_key_exists($sKey, $aPrecedence) || is_null($aPrecedence[$sKey])) $aPrecedence[$sKey] = $v;
            else {
                if (is_array($aPrecedence[$sKey]) && is_array($v)) {
                    $aPrecedence[$sKey] = self::uniteArraysRecursive($aPrecedence[$sKey], $v);
                }
            }
        }
        return $aPrecedence;
    }
    static public function isLastElement(array $aArray, $sKey) {
        end($aArray);
        return $sKey === key($aArray);
    }
    static public function isFirstElement(array $aArray, $sKey) {
        reset($aArray);
        return $sKey === key($aArray);
    }
    static public function getIntegerElements($aParse) {
        if (!is_array($aParse)) {
            return array();
        }
        foreach ($aParse as $isKey => $v) {
            if (!is_numeric($isKey)) {
                unset($aParse[$isKey]);
                continue;
            }
            $isKey = $isKey + 0;
            if (!is_int($isKey)) {
                unset($aParse[$isKey]);
            }
        }
        return $aParse;
    }
    static public function getNonIntegerElements($aParse) {
        foreach ($aParse as $isKey => $v) {
            if (is_numeric($isKey) && is_int($isKey + 0)) {
                unset($aParse[$isKey]);
            }
        }
        return $aParse;
    }
    static public function numerizeElements($aSubject) {
        $_aNumeric = self::getIntegerElements($aSubject);
        $_aAssociative = self::invertCastArrayContents($aSubject, $_aNumeric);
        foreach ($_aNumeric as & $_aElem) {
            $_aElem = self::uniteArrays($_aElem, $_aAssociative);
        }
        if (!empty($_aAssociative)) {
            array_unshift($_aNumeric, $_aAssociative);
        }
        return $_aNumeric;
    }
    static public function isAssociativeArray(array $aArray) {
        return ( bool )count(array_filter(array_keys($aArray), 'is_string'));
    }
    static public function shiftTillTrue(array $aArray) {
        foreach ($aArray as & $vElem) {
            if ($vElem) {
                break;
            }
            unset($vElem);
        }
        return array_values($aArray);
    }
    static public function getArrayValueByArrayKeys($aArray, $aKeys, $vDefault = null) {
        $sKey = array_shift($aKeys);
        if (isset($aArray[$sKey])) {
            if (empty($aKeys)) {
                return $aArray[$sKey];
            }
            if (is_array($aArray[$sKey])) {
                return self::getArrayValueByArrayKeys($aArray[$sKey], $aKeys, $vDefault);
            }
        }
        return $vDefault;
    }
    static public function getAsArray($asValue) {
        if (is_array($asValue)) {
            return $asValue;
        }
        if (!isset($asValue)) {
            return array();
        }
        return ( array )$asValue;
    }
    static public function getReadableListOfArray(array $aArray) {
        $_aOutput = array();
        foreach ($aArray as $_sKey => $_vValue) {
            $_aOutput[] = self::getReadableArrayContents($_sKey, $_vValue, 32) . PHP_EOL;
        }
        return implode(PHP_EOL, $_aOutput);
    }
    static public function getReadableArrayContents($sKey, $vValue, $sLabelCharLengths = 16, $iOffset = 0) {
        $_aOutput = array();
        $_aOutput[] = ($iOffset ? str_pad(' ', $iOffset) : '') . ($sKey ? '[' . $sKey . ']' : '');
        if (!is_array($vValue) && !is_object($vValue)) {
            $_aOutput[] = $vValue;
            return implode(PHP_EOL, $_aOutput);
        }
        foreach ($vValue as $_sTitle => $_asDescription) {
            if (!is_array($_asDescription) && !is_object($_asDescription)) {
                $_aOutput[] = str_pad(' ', $iOffset) . $_sTitle . str_pad(':', $sLabelCharLengths - self::getStringLength($_sTitle)) . $_asDescription;
                continue;
            }
            $_aOutput[] = str_pad(' ', $iOffset) . $_sTitle . ": {" . self::getReadableArrayContents('', $_asDescription, 16, $iOffset + 4) . PHP_EOL . str_pad(' ', $iOffset) . "}";
        }
        return implode(PHP_EOL, $_aOutput);
    }
    static public function getReadableListOfArrayAsHTML(array $aArray) {
        $_aOutput = array();
        foreach ($aArray as $_sKey => $_vValue) {
            $_aOutput[] = "<ul class='array-contents'>" . self::getReadableArrayContentsHTML($_sKey, $_vValue) . "</ul>" . PHP_EOL;
        }
        return implode(PHP_EOL, $_aOutput);
    }
    static public function getReadableArrayContentsHTML($sKey, $vValue) {
        $_aOutput = array();
        $_aOutput[] = $sKey ? "<h3 class='array-key'>" . $sKey . "</h3>" : "";
        if (!is_array($vValue) && !is_object($vValue)) {
            $_aOutput[] = "<div class='array-value'>" . html_entity_decode(nl2br(str_replace(' ', '&nbsp;', $vValue)), ENT_QUOTES) . "</div>";
            return "<li>" . implode(PHP_EOL, $_aOutput) . "</li>";
        }
        foreach ($vValue as $_sKey => $_vValue) {
            $_aOutput[] = "<ul class='array-contents'>" . self::getReadableArrayContentsHTML($_sKey, $_vValue) . "</ul>";
        }
        return implode(PHP_EOL, $_aOutput);
    }
    static public function dropElementsByType(array $aArray, $aTypes = array('array')) {
        foreach ($aArray as $isKey => $vValue) {
            if (in_array(gettype($vValue), $aTypes)) {
                unset($aArray[$isKey]);
            }
        }
        return $aArray;
    }
    static public function dropElementByValue(array $aArray, $vValue) {
        $_aValues = is_array($vValue) ? $vValue : array($vValue);
        foreach ($_aValues as $_vValue) {
            $_sKey = array_search($_vValue, $aArray, true);
            if ($_sKey === false) {
                continue;
            }
            unset($aArray[$_sKey]);
        }
        return $aArray;
    }
    static public function dropElementsByKey(array $aArray, $asKeys) {
        $_aKeys = is_array($asKeys) ? $asKeys : array($asKeys);
        foreach ($_aKeys as $_isKey) {
            unset($aArray[$_isKey]);
        }
        return $aArray;
    }
}
abstract class Legull_AdminPageFramework_Utility_Path extends Legull_AdminPageFramework_Utility_Array {
    static public function getRelativePath($from, $to) {
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);
        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;
        foreach ($from as $depth => $dir) {
            if ($dir === $to[$depth]) {
                array_shift($relPath);
            } else {
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
    static public function getCallerScriptPath($asRedirectedFiles = array(__FILE__)) {
        $aRedirectedFiles = ( array )$asRedirectedFiles;
        $aRedirectedFiles[] = __FILE__;
        $_sCallerFilePath = '';
        foreach (debug_backtrace() as $aDebugInfo) {
            $_sCallerFilePath = $aDebugInfo['file'];
            if (in_array($_sCallerFilePath, $aRedirectedFiles)) {
                continue;
            }
            break;
        }
        return $_sCallerFilePath;
    }
}
abstract class Legull_AdminPageFramework_Utility_URL extends Legull_AdminPageFramework_Utility_Path {
    static public function getCurrentURL() {
        $sSSL = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
        $sServerProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);
        $sProtocol = substr($sServerProtocol, 0, strpos($sServerProtocol, '/')) . (($sSSL) ? 's' : '');
        $sPort = $_SERVER['SERVER_PORT'];
        $sPort = ((!$sSSL && $sPort == '80') || ($sSSL && $sPort == '443')) ? '' : ':' . $sPort;
        $sHost = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        return $sProtocol . '://' . $sHost . $sPort . $_SERVER['REQUEST_URI'];
    }
}
abstract class Legull_AdminPageFramework_Utility_File extends Legull_AdminPageFramework_Utility_URL {
    static public function getFileTailContents($asPath = array(), $iLines = 1) {
        $_aPath = is_array($asPath) ? $asPath : array($asPath);
        $_aPath = array_values($_aPath);
        $_sPath = array_shift($_aPath);
        return file_exists($_sPath) ? trim(implode("", array_slice(file($_sPath), -$iLines))) : '';
    }
    static public function sanitizeFileName($sFileName, $sReplacement = '_') {
        $sFileName = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", $sReplacement, $sFileName);
        return preg_replace("([\.]{2,})", '', $sFileName);
    }
}
abstract class Legull_AdminPageFramework_Utility_SystemInformation extends Legull_AdminPageFramework_Utility_File {
    static private $_aPHPInfo;
    static public function getPHPInfo() {
        if (isset(self::$_aPHPInfo)) {
            return self::$_aPHPInfo;
        }
        ob_start();
        phpinfo(-1);
        $_sOutput = preg_replace(array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms', '#<h1>Configuration</h1>#', "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#', "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%', '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>' . '<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#', '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#', '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#', "# +#", '#<tr>#', '#</tr>#'), array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ', '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' . "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>', '<tr><td>PHP Credits Egg</td><td>$1</td></tr>', '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" . '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'), ob_get_clean());
        $_aSections = explode('<h2>', strip_tags($_sOutput, '<h2><th><td>'));
        unset($_aSections[0]);
        $_aOutput = array();
        foreach ($_aSections as $_sSection) {
            $_iIndex = substr($_sSection, 0, strpos($_sSection, '</h2>'));
            preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $_sSection, $_aAskApache, PREG_SET_ORDER);
            foreach ($_aAskApache as $_aMatches) {
                if (!isset($_aMatches[1], $_aMatches[2])) {
                    array_slice($_aMatches, 2);
                    continue;
                }
                $_aOutput[$_iIndex][$_aMatches[1]] = !isset($_aMatches[3]) || $_aMatches[2] == $_aMatches[3] ? $_aMatches[2] : array_slice($_aMatches, 2);
            }
        }
        self::$_aPHPInfo = $_aOutput;
        return self::$_aPHPInfo;
    }
    static public function getDefinedConstants($asCategories = null, $asRemovingCategories = null) {
        $_aCategories = is_array($asCategories) ? $asCategories : array($asCategories);
        $_aCategories = array_filter($_aCategories);
        $_aRemovingCategories = is_array($asRemovingCategories) ? $asRemovingCategories : array($asRemovingCategories);
        $_aRemovingCategories = array_filter($_aRemovingCategories);
        $_aConstants = get_defined_constants(true);
        if (empty($_aCategories)) {
            return self::dropElementsByKey($_aConstants, $_aRemovingCategories);
        }
        return self::dropElementsByKey(array_intersect_key($_aConstants, array_flip($_aCategories)), $_aRemovingCategories);
    }
    static public function getPHPErrorLogPath() {
        $_aPHPInfo = self::getPHPInfo();
        return isset($_aPHPInfo['PHP Core']['error_log']) ? $_aPHPInfo['PHP Core']['error_log'] : '';
    }
    static public function getPHPErrorLog($iLines = 1) {
        $_sLog = self::getFileTailContents(self::getPHPErrorLogPath(), $iLines);
        return $_sLog ? $_sLog : print_r(@error_get_last(), true);
    }
}
abstract class Legull_AdminPageFramework_Utility extends Legull_AdminPageFramework_Utility_SystemInformation {
    static public function sanitizeLength($sLength, $sUnit = 'px') {
        return is_numeric($sLength) ? $sLength . $sUnit : $sLength;
    }
    static public function getQueryValueInURLByKey($sURL, $sQueryKey) {
        $aURL = parse_url($sURL);
        parse_str($aURL['query'], $aQuery);
        return isset($aQuery[$sQueryKey]) ? $aQuery[$sQueryKey] : null;
    }
    static public function generateInlineCSS(array $aCSSRules) {
        $_sOutput = '';
        foreach ($aCSSRules as $_sProperty => $_sValue) {
            $_sOutput.= $_sProperty . ': ' . $_sValue . '; ';
        }
        return trim($_sOutput);
    }
    static public function generateStyleAttribute($asInlineCSSes) {
        $_aCSSRules = array();
        foreach (array_reverse(func_get_args()) as $_asCSSRules) {
            if (is_array($_asCSSRules)) {
                $_aCSSRules = array_merge($_asCSSRules, $_aCSSRules);
                continue;
            }
            $__aCSSRules = explode(';', $_asCSSRules);
            foreach ($__aCSSRules as $_sPair) {
                $_aCSSPair = explode(':', $_sPair);
                if (!isset($_aCSSPair[0], $_aCSSPair[1])) {
                    continue;
                }
                $_aCSSRules[$_aCSSPair[0]] = $_aCSSPair[1];
            }
        }
        return self::generateInlineCSS(array_unique($_aCSSRules));
    }
    static public function generateClassAttribute($asClassSelectors) {
        $_aClasses = array();
        foreach (func_get_args() as $_asClasses) {
            if (!is_string($_asClasses) && !is_array($_asClasses)) {
                continue;
            }
            $_aClasses = array_merge($_aClasses, is_array($_asClasses) ? $_asClasses : explode(' ', $_asClasses));
        }
        $_aClasses = array_unique($_aClasses);
        return trim(implode(' ', $_aClasses));
    }
    static public function generateAttributes(array $aAttributes) {
        $_sQuoteCharactor = "'";
        $_aOutput = array();
        foreach ($aAttributes as $sAttribute => $sProperty) {
            if (is_array($sProperty) || is_object($sProperty)) {
                continue;
            }
            $_aOutput[] = "{$sAttribute}={$_sQuoteCharactor}{$sProperty}{$_sQuoteCharactor}";
        }
        return implode(' ', $_aOutput);
    }
    static public function getDataAttributeArray(array $aArray) {
        $_aNewArray = array();
        foreach ($aArray as $sKey => $v) {
            if (is_object($v) || is_array($v)) {
                continue;
            }
            $_aNewArray["data-{$sKey}"] = $v ? $v : '0';
        }
        return $_aNewArray;
    }
}
class Legull_AdminPageFramework_WPUtility_URL extends Legull_AdminPageFramework_Utility {
    static public function getCurrentAdminURL() {
        $sRequestURI = $GLOBALS['is_IIS'] ? $_SERVER['PATH_INFO'] : $_SERVER["REQUEST_URI"];
        $sPageURL = 'on' == @$_SERVER["HTTPS"] ? "https://" : "http://";
        if ("80" != $_SERVER["SERVER_PORT"]) {
            $sPageURL.= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $sRequestURI;
        } else {
            $sPageURL.= $_SERVER["SERVER_NAME"] . $sRequestURI;
        }
        return $sPageURL;
    }
    static public function getQueryAdminURL($aAddingQueries = array(), $aRemovingQueryKeys = array(), $sSubjectURL = '') {
        $_sAdminURL = is_network_admin() ? network_admin_url(Legull_AdminPageFramework_WPUtility_Page::getPageNow()) : admin_url(Legull_AdminPageFramework_WPUtility_Page::getPageNow());
        $sSubjectURL = $sSubjectURL ? $sSubjectURL : add_query_arg($_GET, $_sAdminURL);
        return self::getQueryURL($aAddingQueries, $aRemovingQueryKeys, $sSubjectURL);
    }
    static public function getQueryURL($aAddingQueries, $aRemovingQueryKeys, $sSubjectURL) {
        $sSubjectURL = empty($aRemovingQueryKeys) ? $sSubjectURL : remove_query_arg(( array )$aRemovingQueryKeys, $sSubjectURL);
        $sSubjectURL = add_query_arg($aAddingQueries, $sSubjectURL);
        return $sSubjectURL;
    }
    static public function getSRCFromPath($sFilePath) {
        $oWPStyles = new WP_Styles();
        $sRelativePath = Legull_AdminPageFramework_Utility::getRelativePath(ABSPATH, $sFilePath);
        $sRelativePath = preg_replace("/^\.[\/\\\]/", '', $sRelativePath, 1);
        $sHref = trailingslashit($oWPStyles->base_url) . $sRelativePath;
        unset($oWPStyles);
        return esc_url($sHref);
    }
    static public function resolveSRC($sSRC, $bReturnNullIfNotExist = false) {
        if (!$sSRC) {
            return $bReturnNullIfNotExist ? null : $sSRC;
        }
        if (filter_var($sSRC, FILTER_VALIDATE_URL)) {
            return esc_url($sSRC);
        }
        if (file_exists(realpath($sSRC))) {
            return self::getSRCFromPath($sSRC);
        }
        if ($bReturnNullIfNotExist) {
            return null;
        }
        return $sSRC;
    }
}
class Legull_AdminPageFramework_WPUtility_HTML extends Legull_AdminPageFramework_WPUtility_URL {
    static public function generateAttributes(array $aAttributes) {
        foreach ($aAttributes as $_sAttribute => & $_vProperty) {
            if (is_array($_vProperty) || is_object($_vProperty)) {
                unset($aAttributes[$_sAttribute]);
            }
            if (is_null($_vProperty)) {
                unset($aAttributes[$_sAttribute]);
            }
            if (is_string($_vProperty)) {
                $_vProperty = esc_attr($_vProperty);
            }
        }
        return parent::generateAttributes($aAttributes);
    }
    static public function generateDataAttributes(array $aArray) {
        return self::generateAttributes(self::getDataAttributeArray($aArray));
    }
}
class Legull_AdminPageFramework_WPUtility_Page extends Legull_AdminPageFramework_WPUtility_HTML {
    static public function getCurrentPostType() {
        static $_sCurrentPostType;
        if ($_sCurrentPostType) {
            return $_sCurrentPostType;
        }
        if (isset($GLOBALS['post'], $GLOBALS['post']->post_type) && $GLOBALS['post']->post_type) {
            $_sCurrentPostType = $GLOBALS['post']->post_type;
            return $_sCurrentPostType;
        }
        if (isset($GLOBALS['typenow']) && $GLOBALS['typenow']) {
            $_sCurrentPostType = $GLOBALS['typenow'];
            return $_sCurrentPostType;
        }
        if (isset($GLOBALS['current_screen']->post_type) && $GLOBALS['current_screen']->post_type) {
            $_sCurrentPostType = $GLOBALS['current_screen']->post_type;
            return $_sCurrentPostType;
        }
        if (isset($_REQUEST['post_type'])) {
            $_sCurrentPostType = sanitize_key($_REQUEST['post_type']);
            return $_sCurrentPostType;
        }
        if (isset($_GET['post']) && $_GET['post']) {
            $_sCurrentPostType = get_post_type($_GET['post']);
            return $_sCurrentPostType;
        }
        return null;
    }
    static public function isCustomTaxonomyPage($asPostTypes = array()) {
        $_aPostTypes = is_array($asPostTypes) ? $asPostTypes : empty($asPostTypes) ? array() : array($asPostTypes);
        if (!in_array(self::getPageNow(), array('tags.php', 'edit-tags.php',))) {
            return false;
        }
        if (empty($_aPostTypes)) {
            return true;
        }
        return in_array(self::getCurrentPostType(), $_aPostTypes);
    }
    static public function isPostDefinitionPage($asPostTypes = array()) {
        $_aPostTypes = is_array($asPostTypes) ? $asPostTypes : empty($asPostTypes) ? array() : array($asPostTypes);
        if (!in_array(self::getPageNow(), array('post.php', 'post-new.php',))) return false;
        if (empty($_aPostTypes)) return true;
        return in_array(self::getCurrentPostType(), $_aPostTypes);
    }
    static public function isPostListingPage($asPostTypes = array()) {
        if ('edit.php' != self::getPageNow()) return false;
        $_aPostTypes = is_array($asPostTypes) ? $asPostTypes : empty($asPostTypes) ? array() : array($asPostTypes);
        if (!isset($_GET['post_type'])) return in_array('post', $_aPostTypes);
        return in_array($_GET['post_type'], $_aPostTypes);
    }
    static private $_sPageNow;
    static public function getPageNow() {
        if (isset(self::$_sPageNow)) {
            return self::$_sPageNow;
        }
        if (isset($GLOBALS['pagenow'])) {
            self::$_sPageNow = $GLOBALS['pagenow'];
            return self::$_sPageNow;
        }
        if (!is_admin()) {
            if (preg_match('#([^/]+\.php)([?/].*?)?$#i', $_SERVER['PHP_SELF'], $_aMatches)) {
                self::$_sPageNow = strtolower($_aMatches[1]);
                return self::$_sPageNow;
            }
            self::$_sPageNow = 'index.php';
            return self::$_sPageNow;
        }
        if (is_network_admin()) preg_match('#/wp-admin/network/?(.*?)$#i', $_SERVER['PHP_SELF'], $_aMatches);
        elseif (is_user_admin()) preg_match('#/wp-admin/user/?(.*?)$#i', $_SERVER['PHP_SELF'], $_aMatches);
        else preg_match('#/wp-admin/?(.*?)$#i', $_SERVER['PHP_SELF'], $_aMatches);
        $_sPageNow = $_aMatches[1];
        $_sPageNow = trim($_sPageNow, '/');
        $_sPageNow = preg_replace('#\?.*?$#', '', $_sPageNow);
        if ('' === $_sPageNow || 'index' === $_sPageNow || 'index.php' === $_sPageNow) {
            self::$_sPageNow = 'index.php';
            return self::$_sPageNow;
        }
        preg_match('#(.*?)(/|$)#', $_sPageNow, $_aMatches);
        $_sPageNow = strtolower($_aMatches[1]);
        if ('.php' !== substr($_sPageNow, -4, 4)) {
            $_sPageNow.= '.php';
            self::$_sPageNow = $_sPageNow;
        }
        return self::$_sPageNow;
    }
    static public function getCurrentScreenID() {
        $_oScreen = get_current_screen();
        if (is_string($_oScreen)) {
            $_oScreen = convert_to_screen($_oScreen);
        }
        if (isset($_oScreen->id)) {
            return $_oScreen->id;
        }
        if (isset($GLBOALS['page_hook'])) {
            return is_network_admin() ? $GLBOALS['page_hook'] . '-network' : $GLBOALS['page_hook'];
        }
        return '';
    }
}
class Legull_AdminPageFramework_WPUtility_Hook extends Legull_AdminPageFramework_WPUtility_Page {
    static public function registerAction($sActionHook, $oCallable) {
        if (did_action($sActionHook)) {
            return call_user_func_array($oCallable, array());
        }
        add_action($sActionHook, $oCallable);
    }
    static public function doActions($aActionHooks, $vArgs1 = null, $vArgs2 = null, $_and_more = null) {
        $aArgs = func_get_args();
        $aActionHooks = $aArgs[0];
        foreach (( array )$aActionHooks as $sActionHook) {
            $aArgs[0] = $sActionHook;
            call_user_func_array('do_action', $aArgs);
        }
    }
    static public function addAndDoActions($oCallerObject, $aActionHooks, $vArgs1 = null, $vArgs2 = null, $_and_more = null) {
        $aArgs = func_get_args();
        $oCallerObject = $aArgs[0];
        $aActionHooks = $aArgs[1];
        foreach (( array )$aActionHooks as $sActionHook) {
            if (!$sActionHook) {
                continue;
            }
            $aArgs[1] = $sActionHook;
            call_user_func_array(array(get_class(), 'addAndDoAction'), $aArgs);
        }
    }
    static public function addAndDoAction($oCallerObject, $sActionHook, $vArgs1 = null, $vArgs2 = null, $_and_more = null) {
        $_iArgs = func_num_args();
        $_aArgs = func_get_args();
        $_oCallerObject = $_aArgs[0];
        $_sActionHook = $_aArgs[1];
        if (!$_sActionHook) {
            return;
        }
        add_action($_sActionHook, array($_oCallerObject, $_sActionHook), 10, $_iArgs - 2);
        array_shift($_aArgs);
        call_user_func_array('do_action', $_aArgs);
    }
    static public function addAndApplyFilters() {
        $_aArgs = func_get_args();
        $_aFilters = $_aArgs[1];
        $_vInput = $_aArgs[2];
        foreach (( array )$_aFilters as $_sFilter) {
            if (!$_sFilter) {
                continue;
            }
            $_aArgs[1] = $_sFilter;
            $_aArgs[2] = $_vInput;
            $_vInput = call_user_func_array(array(get_class(), 'addAndApplyFilter'), $_aArgs);
        }
        return $_vInput;
    }
    static public function addAndApplyFilter() {
        $_iArgs = func_num_args();
        $_aArgs = func_get_args();
        $_oCallerObject = $_aArgs[0];
        $_sFilter = $_aArgs[1];
        if (!$_sFilter) {
            return $_aArgs[2];
        }
        add_filter($_sFilter, array($_oCallerObject, $_sFilter), 10, $_iArgs - 2);
        array_shift($_aArgs);
        return call_user_func_array('apply_filters', $_aArgs);
    }
    static public function getFilterArrayByPrefix($sPrefix, $sClassName, $sPageSlug, $sTabSlug, $bReverse = false) {
        $_aFilters = array();
        if ($sTabSlug && $sPageSlug) {
            $_aFilters[] = "{$sPrefix}{$sPageSlug}_{$sTabSlug}";
        }
        if ($sPageSlug) {
            $_aFilters[] = "{$sPrefix}{$sPageSlug}";
        }
        if ($sClassName) {
            $_aFilters[] = "{$sPrefix}{$sClassName}";
        }
        return $bReverse ? array_reverse($_aFilters) : $_aFilters;
    }
}
class Legull_AdminPageFramework_WPUtility_File extends Legull_AdminPageFramework_WPUtility_Hook {
    static public function getScriptData($sPath, $sType = 'plugin') {
        $aData = get_file_data($sPath, array('sName' => 'Name', 'sURI' => 'URI', 'sScriptName' => 'Script Name', 'sLibraryName' => 'Library Name', 'sLibraryURI' => 'Library URI', 'sPluginName' => 'Plugin Name', 'sPluginURI' => 'Plugin URI', 'sThemeName' => 'Theme Name', 'sThemeURI' => 'Theme URI', 'sVersion' => 'Version', 'sDescription' => 'Description', 'sAuthor' => 'Author', 'sAuthorURI' => 'Author URI', 'sTextDomain' => 'Text Domain', 'sDomainPath' => 'Domain Path', 'sNetwork' => 'Network', '_sitewide' => 'Site Wide Only',), $sType);
        switch (trim($sType)) {
            case 'theme':
                $aData['sName'] = $aData['sThemeName'];
                $aData['sURI'] = $aData['sThemeURI'];
            break;
            case 'library':
                $aData['sName'] = $aData['sLibraryName'];
                $aData['sURI'] = $aData['sLibraryURI'];
            break;
            case 'script':
                $aData['sName'] = $aData['sScriptName'];
            break;
            case 'plugin':
                $aData['sName'] = $aData['sPluginName'];
                $aData['sURI'] = $aData['sPluginURI'];
            break;
            default:
            break;
        }
        return $aData;
    }
    static public function download($sURL, $iTimeOut = 300) {
        if (false === filter_var($sURL, FILTER_VALIDATE_URL)) {
            return false;
        }
        $_sTmpFileName = self::setTempPath(self::getBaseNameOfURL($sURL));
        if (!$_sTmpFileName) {
            return false;
        }
        $_aoResponse = wp_safe_remote_get($sURL, array('timeout' => $iTimeOut, 'stream' => true, 'filename' => $_sTmpFileName));
        if (is_wp_error($_aoResponse)) {
            unlink($_sTmpFileName);
            return false;
        }
        if (200 != wp_remote_retrieve_response_code($_aoResponse)) {
            unlink($_sTmpFileName);
            return false;
        }
        $_sContent_md5 = wp_remote_retrieve_header($_aoResponse, 'content-md5');
        if ($_sContent_md5) {
            $_boIsMD5 = verify_file_md5($_sTmpFileName, $_sContent_md5);
            if (is_wp_error($_boIsMD5)) {
                unlink($_sTmpFileName);
                return false;
            }
        }
        return $_sTmpFileName;
    }
    static public function setTempPath($sFilePath = '') {
        $_sDir = get_temp_dir();
        $sFilePath = basename($sFilePath);
        if (empty($sFilePath)) {
            $sFilePath = time() . '.tmp';
        }
        $sFilePath = $_sDir . wp_unique_filename($_sDir, $sFilePath);
        touch($sFilePath);
        return $sFilePath;
    }
    static public function getBaseNameOfURL($sURL) {
        $_sPath = parse_url($sURL, PHP_URL_PATH);
        $_sFileBaseName = basename($_sPath);
        return $_sFileBaseName;
    }
}
class Legull_AdminPageFramework_WPUtility_Option extends Legull_AdminPageFramework_WPUtility_File {
    static private $_bIsNetworkAdmin;
    static public function deleteTransient($sTransientKey) {
        global $_wp_using_ext_object_cache;
        $_bWpUsingExtObjectCacheTemp = $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = false;
        self::$_bIsNetworkAdmin = isset(self::$_bIsNetworkAdmin) ? self::$_bIsNetworkAdmin : is_network_admin();
        $_vTransient = (self::$_bIsNetworkAdmin) ? delete_site_transient($sTransientKey) : delete_transient($sTransientKey);
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp;
        return $_vTransient;
    }
    static public function getTransient($sTransientKey, $vDefault = null) {
        global $_wp_using_ext_object_cache;
        $_bWpUsingExtObjectCacheTemp = $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = false;
        self::$_bIsNetworkAdmin = isset(self::$_bIsNetworkAdmin) ? self::$_bIsNetworkAdmin : is_network_admin();
        $_vTransient = (self::$_bIsNetworkAdmin) ? get_site_transient($sTransientKey) : get_transient($sTransientKey);
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp;
        return null === $vDefault ? $_vTransient : (false === $_vTransient ? $vDefault : $_vTransient);
    }
    static public function setTransient($sTransientKey, $vValue, $iExpiration = 0) {
        global $_wp_using_ext_object_cache;
        $_bWpUsingExtObjectCacheTemp = $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = false;
        self::$_bIsNetworkAdmin = isset(self::$_bIsNetworkAdmin) ? self::$_bIsNetworkAdmin : is_network_admin();
        $_bIsSet = (self::$_bIsNetworkAdmin) ? set_site_transient($sTransientKey, $vValue, $iExpiration) : set_transient($sTransientKey, $vValue, $iExpiration);
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp;
        return $_bIsSet;
    }
    static public function getOption($sOptionKey, $asKey = null, $vDefault = null, $aOptions = array()) {
        if (!$asKey) {
            return get_option($sOptionKey, isset($vDefault) ? $vDefault : array());
        }
        $_aOptions = get_option($sOptionKey, array());
        $_aKeys = self::shiftTillTrue(self::getAsArray($asKey));
        return self::getArrayValueByArrayKeys(self::uniteArrays($_aOptions, $aOptions), $_aKeys, $vDefault);
    }
    static public function getSiteOption($sOptionKey, $asKey = null, $vDefault = null) {
        if (!$asKey) {
            return get_site_option($sOptionKey, isset($vDefault) ? $vDefault : array());
        }
        $_aOptions = get_site_option($sOptionKey, array());
        $_aKeys = self::shiftTillTrue(self::getAsArray($asKey));
        return self::getArrayValueByArrayKeys($_aOptions, $_aKeys, $vDefault);
    }
}
class Legull_AdminPageFramework_WPUtility_Post extends Legull_AdminPageFramework_WPUtility_Option {
    static public function getSavedMetaArray($iPostID, array $aKeys) {
        $_aSavedMeta = array();
        foreach ($aKeys as $_sKey) {
            $_aSavedMeta[$_sKey] = get_post_meta($iPostID, $_sKey, true);
        }
        return $_aSavedMeta;
    }
}
class Legull_AdminPageFramework_WPUtility_SystemInformation extends Legull_AdminPageFramework_WPUtility_Post {
    static private $_aMySQLInfo;
    static public function getMySQLInfo() {
        if (isset(self::$_aMySQLInfo)) {
            return self::$_aMySQLInfo;
        }
        global $wpdb;
        $_aOutput = array('Version' => isset($wpdb->use_mysqli) && $wpdb->use_mysqli ? @mysqli_get_server_info($wpdb->dbh) : @mysql_get_server_info(),);
        foreach (( array )$wpdb->get_results("SHOW VARIABLES", ARRAY_A) as $_iIndex => $_aItem) {
            $_aItem = array_values($_aItem);
            $_sKey = array_shift($_aItem);
            $_sValue = array_shift($_aItem);
            $_aOutput[$_sKey] = $_sValue;
        }
        self::$_aMySQLInfo = $_aOutput;
        return self::$_aMySQLInfo;
    }
    static public function getMySQLErrorLogPath() {
        $_aMySQLInfo = self::getMySQLInfo();
        return isset($_aMySQLInfo['log_error']) ? $_aMySQLInfo['log_error'] : '';
    }
    static public function getMySQLErrorLog($iLines = 1) {
        $_sLog = self::getFileTailContents(self::getMySQLErrorLogPath(), $iLines);
        return $_sLog ? $_sLog : '';
    }
}
class Legull_AdminPageFramework_WPUtility extends Legull_AdminPageFramework_WPUtility_SystemInformation {
    static private $_bIsFlushed;
    static public function FlushRewriteRules() {
        $_bIsFlushed = isset(self::$_bIsFlushed) ? self::$_bIsFlushed : false;
        if ($_bIsFlushed) {
            return;
        }
        flush_rewrite_rules();
        self::$_bIsFlushed = true;
    }
}
abstract class Legull_AdminPageFramework_CustomSubmitFields extends Legull_AdminPageFramework_WPUtility {
    public function __construct($aPostElement) {
        $this->aPost = $aPostElement;
        $this->sInputID = $this->getInputID($aPostElement['submit']);
    }
    protected function getSubmitValueByType($aElement, $sInputID, $sElementKey = 'format') {
        return (isset($aElement[$sInputID][$sElementKey])) ? $aElement[$sInputID][$sElementKey] : null;
    }
    public function getSiblingValue($sKey) {
        return $this->getSubmitValueByType($this->aPost, $this->sInputID, $sKey);
    }
    public function getInputID($aSubmitElement) {
        foreach ($aSubmitElement as $sInputID => $v) {
            $this->sInputID = $sInputID;
            return $this->sInputID;
        }
    }
}
class Legull_AdminPageFramework_FormEmail extends Legull_AdminPageFramework_WPUtility {
    public function __construct(array $aEmailOptions, array $aInput, $sSubmitSectionID) {
        $this->aEmailOptions = $aEmailOptions;
        $this->aInput = $aInput;
        $this->sSubmitSectionID = $sSubmitSectionID;
        $this->_aPathsToDelete = array();
    }
    public function send() {
        $aEmailOptions = $this->aEmailOptions;
        $aInput = $this->aInput;
        $sSubmitSectionID = $this->sSubmitSectionID;
        if ($_bIsHTML = $this->_getEmailArgument($aInput, $aEmailOptions, 'is_html', $sSubmitSectionID)) {
            add_filter('wp_mail_content_type', array($this, '_replyToSetMailContentTypeToHTML'));
        }
        if ($this->_sEmailSenderAddress = $this->_getEmailArgument($aInput, $aEmailOptions, 'from', $sSubmitSectionID)) {
            add_filter('wp_mail_from', array($this, '_replyToSetEmailSenderAddress'));
        }
        if ($this->_sEmailSenderName = $this->_getEmailArgument($aInput, $aEmailOptions, 'name', $sSubmitSectionID)) {
            add_filter('wp_mail_from_name', array($this, '_replyToSetEmailSenderAddress'));
        }
        $_bSent = wp_mail($this->_getEmailArgument($aInput, $aEmailOptions, 'to', $sSubmitSectionID), $this->_getEmailArgument($aInput, $aEmailOptions, 'subject', $sSubmitSectionID), $_bIsHTML ? $this->getReadableListOfArrayAsHTML(( array )$this->_getEmailArgument($aInput, $aEmailOptions, 'message', $sSubmitSectionID)) : $this->getReadableListOfArray(( array )$this->_getEmailArgument($aInput, $aEmailOptions, 'message', $sSubmitSectionID)), $this->_getEmailArgument($aInput, $aEmailOptions, 'headers', $sSubmitSectionID), $this->_formatAttachements($this->_getEmailArgument($aInput, $aEmailOptions, 'attachments', $sSubmitSectionID)));
        remove_filter('wp_mail_content_type', array($this, '_replyToSetMailContentTypeToHTML'));
        remove_filter('wp_mail_from', array($this, '_replyToSetEmailSenderAddress'));
        remove_filter('wp_mail_from_name', array($this, '_replyToSetEmailSenderAddress'));
        foreach ($this->_aPathsToDelete as $_sPath) {
            unlink($_sPath);
        }
        return $_bSent;
    }
    private function _formatAttachements($asAttachments) {
        if (empty($asAttachments)) {
            return '';
        }
        $_aAttachments = $this->getAsArray($asAttachments);
        foreach ($_aAttachments as $_iIndex => $_sPathORURL) {
            if (is_file($_sPathORURL)) {
                continue;
            }
            if (false !== filter_var($_sPathORURL, FILTER_VALIDATE_URL)) {
                if ($_sPath = $this->_getPathFromURL($_sPathORURL)) {
                    $_aAttachments[$_iIndex] = $_sPath;
                    continue;
                }
            }
            unset($_aAttachments[$_iIndex]);
        }
        return $_aAttachments;
    }
    private function _getPathFromURL($sURL) {
        $_sPath = realpath(str_replace(get_bloginfo('url'), ABSPATH, $sURL));
        if ($_sPath) {
            return $_sPath;
        }
        $_sPath = $this->download($sURL, 10);
        if (is_string($_sPath)) {
            $this->_aPathsToDelete[$_sPath] = $_sPath;
            return $_sPath;
        }
        return '';
    }
    public function _replyToSetMailContentTypeToHTML($sContentType) {
        return 'text/html';
    }
    function _replyToSetEmailSenderAddress($sEmailSenderAddress) {
        return $this->_sEmailSenderAddress;
    }
    function _replyToSetEmailSenderName($sEmailSenderAddress) {
        return $this->_sEmailSenderName;
    }
    private function _getEmailArgument($aInput, array $aEmailOptions, $sKey, $sSectionID) {
        if (is_array($aEmailOptions[$sKey])) {
            return $this->getArrayValueByArrayKeys($aInput, $aEmailOptions[$sKey]);
        }
        if (!$aEmailOptions[$sKey]) {
            return $this->getArrayValueByArrayKeys($aInput, array($sSectionID, $sKey));
        }
        return $aEmailOptions[$sKey];
    }
}
abstract class Legull_AdminPageFramework_Link_Base extends Legull_AdminPageFramework_WPUtility {
    protected function _setFooterInfoLeft($aScriptInfo, &$sFooterInfoLeft) {
        $sDescription = empty($aScriptInfo['sDescription']) ? "" : "&#13;{$aScriptInfo['sDescription']}";
        $sVersion = empty($aScriptInfo['sVersion']) ? "" : "&nbsp;{$aScriptInfo['sVersion']}";
        $sPluginInfo = empty($aScriptInfo['sURI']) ? $aScriptInfo['sName'] : "<a href='{$aScriptInfo['sURI']}' target='_blank' title='{$aScriptInfo['sName']}{$sVersion}{$sDescription}'>{$aScriptInfo['sName']}</a>";
        $sAuthorInfo = empty($aScriptInfo['sAuthorURI']) ? $aScriptInfo['sAuthor'] : "<a href='{$aScriptInfo['sAuthorURI']}' target='_blank'>{$aScriptInfo['sAuthor']}</a>";
        $sAuthorInfo = empty($aScriptInfo['sAuthor']) ? $sAuthorInfo : ' by ' . $sAuthorInfo;
        $sFooterInfoLeft = $sPluginInfo . $sAuthorInfo;
    }
    protected function _setFooterInfoRight($aScriptInfo, &$sFooterInfoRight) {
        $sDescription = empty($aScriptInfo['sDescription']) ? "" : "&#13;{$aScriptInfo['sDescription']}";
        $sVersion = empty($aScriptInfo['sVersion']) ? "" : "&nbsp;{$aScriptInfo['sVersion']}";
        $sLibraryInfo = empty($aScriptInfo['sURI']) ? $aScriptInfo['sName'] : "<a href='{$aScriptInfo['sURI']}' target='_blank' title='{$aScriptInfo['sName']}{$sVersion}{$sDescription}'>{$aScriptInfo['sName']}</a>";
        $sFooterInfoRight = $this->oMsg->get('powered_by') . '&nbsp;' . $sLibraryInfo . ", <a href='http://wordpress.org' target='_blank' title='WordPress {$GLOBALS['wp_version']}'>WordPress</a>";
    }
}
class Legull_AdminPageFramework_Link_Page extends Legull_AdminPageFramework_Link_Base {
    public $oProp;
    public function __construct(&$oProp, $oMsg = null) {
        if (!$oProp->bIsAdmin) {
            return;
        }
        $this->oProp = $oProp;
        $this->oMsg = $oMsg;
        if ($oProp->bIsAdminAjax) {
            return;
        }
        $this->oProp->sLabelPluginSettingsLink = null === $this->oProp->sLabelPluginSettingsLink ? $this->oMsg->get('settings') : $this->oProp->sLabelPluginSettingsLink;
        add_action('in_admin_footer', array($this, '_replyToSetFooterInfo'));
        if (in_array($this->oProp->sPageNow, array('plugins.php')) && 'plugin' == $this->oProp->aScriptInfo['sType']) {
            add_filter('plugin_action_links_' . plugin_basename($this->oProp->aScriptInfo['sPath']), array($this, '_replyToAddSettingsLinkInPluginListingPage'));
        }
    }
    public function _replyToSetFooterInfo() {
        if (!$this->oProp->isPageAdded()) {
            return;
        }
        $this->_setFooterInfoLeft($this->oProp->aScriptInfo, $this->oProp->aFooterInfo['sLeft']);
        $this->_setFooterInfoRight($this->oProp->_getLibraryData(), $this->oProp->aFooterInfo['sRight']);
        add_filter('admin_footer_text', array($this, '_replyToAddInfoInFooterLeft'));
        add_filter('update_footer', array($this, '_replyToAddInfoInFooterRight'), 11);
    }
    public function _addLinkToPluginDescription($asLinks) {
        if (!is_array($asLinks)) {
            $this->oProp->aPluginDescriptionLinks[] = $asLinks;
        } else {
            $this->oProp->aPluginDescriptionLinks = array_merge($this->oProp->aPluginDescriptionLinks, $asLinks);
        }
        if ('plugins.php' !== $this->oProp->sPageNow) {
            return;
        }
        add_filter('plugin_row_meta', array($this, '_replyToAddLinkToPluginDescription'), 10, 2);
    }
    public function _addLinkToPluginTitle($asLinks) {
        static $_sPluginBaseName;
        if (!is_array($asLinks)) {
            $this->oProp->aPluginTitleLinks[] = $asLinks;
        } else {
            $this->oProp->aPluginTitleLinks = array_merge($this->oProp->aPluginTitleLinks, $asLinks);
        }
        if ('plugins.php' !== $this->oProp->sPageNow) {
            return;
        }
        if (!isset($_sPluginBaseName)) {
            $_sPluginBaseName = plugin_basename($this->oProp->aScriptInfo['sPath']);
            add_filter("plugin_action_links_{$_sPluginBaseName}", array($this, '_replyToAddLinkToPluginTitle'));
        }
    }
    public function _replyToAddInfoInFooterLeft($sLinkHTML = '') {
        if (!isset($_GET['page']) || !$this->oProp->isPageAdded($_GET['page'])) {
            return $sLinkHTML;
        }
        if (empty($this->oProp->aScriptInfo['sName'])) {
            return $sLinkHTML;
        }
        return $this->oProp->aFooterInfo['sLeft'];
    }
    public function _replyToAddInfoInFooterRight($sLinkHTML = '') {
        if (!isset($_GET['page']) || !$this->oProp->isPageAdded($_GET['page'])) {
            return $sLinkHTML;
        }
        return $this->oProp->aFooterInfo['sRight'];
    }
    public function _replyToAddSettingsLinkInPluginListingPage($aLinks) {
        if (count($this->oProp->aPages) < 1) {
            return $aLinks;
        }
        if (!$this->oProp->sLabelPluginSettingsLink) {
            return $aLinks;
        }
        $_sLinkURL = preg_match('/^.+\.php/', $this->oProp->aRootMenu['sPageSlug']) ? add_query_arg(array('page' => $this->oProp->sDefaultPageSlug), admin_url($this->oProp->aRootMenu['sPageSlug'])) : "admin.php?page={$this->oProp->sDefaultPageSlug}";
        array_unshift($aLinks, '<a href="' . esc_url($_sLinkURL) . '">' . $this->oProp->sLabelPluginSettingsLink . '</a>');
        return $aLinks;
    }
    public function _replyToAddLinkToPluginDescription($aLinks, $sFile) {
        if ($sFile != plugin_basename($this->oProp->aScriptInfo['sPath'])) {
            return $aLinks;
        }
        $_aAddingLinks = array();
        foreach (array_filter($this->oProp->aPluginDescriptionLinks) as $_sLLinkHTML) {
            if (!$_sLLinkHTML) {
                continue;
            }
            if (is_array($_sLLinkHTML)) {
                $_aAddingLinks = array_merge($_sLLinkHTML, $_aAddingLinks);
                continue;
            }
            $_aAddingLinks[] = ( string )$_sLLinkHTML;
        }
        return array_merge($aLinks, $_aAddingLinks);
    }
    public function _replyToAddLinkToPluginTitle($aLinks) {
        $_aAddingLinks = array();
        foreach (array_filter($this->oProp->aPluginTitleLinks) as $_sLinkHTML) {
            if (!$_sLinkHTML) {
                continue;
            }
            if (is_array($_sLinkHTML)) {
                $_aAddingLinks = array_merge($_sLinkHTML, $aAddingLinks);
                continue;
            }
            $_aAddingLinks[] = ( string )$_sLinkHTML;
        }
        return array_merge($aLinks, $_aAddingLinks);
    }
}
class Legull_AdminPageFramework_FormElement_Utility extends Legull_AdminPageFramework_WPUtility {
    public function dropRepeatableElements(array $aOptions) {
        foreach ($aOptions as $_sFieldOrSectionID => $_aSectionOrFieldValue) {
            if ($this->isSection($_sFieldOrSectionID)) {
                $_aFields = $_aSectionOrFieldValue;
                $_sSectionID = $_sFieldOrSectionID;
                if (!$this->isCurrentUserCapable($_sSectionID)) {
                    continue;
                }
                if ($this->isRepeatableSection($_sSectionID)) {
                    unset($aOptions[$_sSectionID]);
                    continue;
                }
                if (!is_array($_aFields)) {
                    continue;
                }
                foreach ($_aFields as $_sFieldID => $_aField) {
                    if (!$this->isCurrentUserCapable($_sSectionID, $_sFieldID)) {
                        continue;
                    }
                    if ($this->isRepeatableField($_sFieldID, $_sSectionID)) {
                        unset($aOptions[$_sSectionID][$_sFieldID]);
                        continue;
                    }
                }
                continue;
            }
            $_sFieldID = $_sFieldOrSectionID;
            if (!$this->isCurrentUserCapable('_default', $_sFieldID)) {
                continue;
            }
            if ($this->isRepeatableField($_sFieldID, '_default')) {
                unset($aOptions[$_sFieldID]);
            }
        }
        return $aOptions;
    }
    private function isCurrentUserCapable($sSectionID, $sFieldID = '') {
        if (!$sFieldID) {
            return isset($this->aSections[$sSectionID]['capability']) ? current_user_can($this->aSections[$sSectionID]['capability']) : true;
        }
        return isset($this->aFields[$sSectionID][$sFieldID]['capability']) ? current_user_can($this->aFields[$sSectionID][$sFieldID]['capability']) : true;
    }
    private function isRepeatableSection($sSectionID) {
        return isset($this->aSections[$sSectionID]['repeatable']) && $this->aSections[$sSectionID]['repeatable'];
    }
    private function isRepeatableField($sFieldID, $sSectionID) {
        return (isset($this->aFields[$sSectionID][$sFieldID]['repeatable']) && $this->aFields[$sSectionID][$sFieldID]['repeatable']);
    }
    public function isSection($sID) {
        if (is_numeric($sID) && is_int($sID + 0)) {
            return false;
        }
        if (!array_key_exists($sID, $this->aSections)) {
            return false;
        }
        if (!array_key_exists($sID, $this->aFields)) {
            return false;
        }
        $_bIsSeciton = false;
        foreach ($this->aFields as $_sSectionID => $_aFields) {
            if ($_sSectionID == $sID) {
                $_bIsSeciton = true;
            }
            if (array_key_exists($sID, $_aFields)) {
                return false;
            }
        }
        return $_bIsSeciton;
    }
    public function getFieldsModel(array $aFields = array()) {
        $_aFieldsModel = array();
        $aFields = empty($aFields) ? $this->aFields : $aFields;
        foreach ($aFields as $_sSectionID => $_aFields) {
            if ($_sSectionID != '_default') {
                $_aFieldsModel[$_sSectionID] = $_aFields;
                continue;
            }
            foreach ($_aFields as $_sFieldID => $_aField) {
                $_aFieldsModel[$_aField['field_id']] = $_aField;
            }
        }
        return $_aFieldsModel;
    }
    public function _sortByOrder($a, $b) {
        return isset($a['order'], $b['order']) ? $a['order'] - $b['order'] : 1;
    }
    public function applyFiltersToFields($oCaller, $sClassName) {
        foreach ($this->aConditionedFields as $_sSectionID => $_aSubSectionOrFields) {
            foreach ($_aSubSectionOrFields as $_sIndexOrFieldID => $_aSubSectionOrField) {
                if (is_numeric($_sIndexOrFieldID) && is_int($_sIndexOrFieldID + 0)) {
                    $_sSubSectionIndex = $_sIndexOrFieldID;
                    $_aFields = $_aSubSectionOrField;
                    $_sSectionSubString = '_default' == $_sSectionID ? '' : "_{$_sSectionID}";
                    foreach ($_aFields as $_aField) {
                        $this->aConditionedFields[$_sSectionID][$_sSubSectionIndex][$_aField['field_id']] = $this->addAndApplyFilter($oCaller, "field_definition_{$sClassName}{$_sSectionSubString}_{$_aField['field_id']}", $_aField, $_sSubSectionIndex);
                    }
                    continue;
                }
                $_aField = $_aSubSectionOrField;
                $_sSectionSubString = '_default' == $_sSectionID ? '' : "_{$_sSectionID}";
                $this->aConditionedFields[$_sSectionID][$_aField['field_id']] = $this->addAndApplyFilter($oCaller, "field_definition_{$sClassName}{$_sSectionSubString}_{$_aField['field_id']}", $_aField);
            }
        }
        $this->aConditionedFields = $this->addAndApplyFilter($oCaller, "field_definition_{$sClassName}", $this->aConditionedFields);
        $this->aConditionedFields = $this->formatFields($this->aConditionedFields, $this->sFieldsType, $this->sCapability);
    }
}
class Legull_AdminPageFramework_FormElement extends Legull_AdminPageFramework_FormElement_Utility {
    static public $_aStructure_Section = array('section_id' => '_default', '_fields_type' => null, 'page_slug' => null, 'tab_slug' => null, 'section_tab_slug' => null, 'title' => null, 'description' => null, 'capability' => null, 'if' => true, 'order' => null, 'help' => null, 'help_aside' => null, 'repeatable' => null, 'attributes' => array('class' => null, 'style' => null, 'tab' => array(),), 'class' => array('tab' => array(),), 'hidden' => false, 'collapsible' => false, '_is_first_index' => false, '_is_last_index' => false,);
    static public $_aStructure_CollapsibleArguments = array('title' => null, 'is_collapsed' => true, 'toggle_all_button' => null, 'collapse_others_on_expand' => true, 'container' => 'sections');
    static public $_aStructure_Field = array('field_id' => null, 'type' => null, 'section_id' => null, 'section_title' => null, 'page_slug' => null, 'tab_slug' => null, 'option_key' => null, 'class_name' => null, 'capability' => null, 'title' => null, 'tip' => null, 'description' => null, 'error_message' => null, 'before_label' => null, 'after_label' => null, 'if' => true, 'order' => null, 'default' => null, 'value' => null, 'help' => null, 'help_aside' => null, 'repeatable' => null, 'sortable' => null, 'show_title_column' => true, 'hidden' => null, '_fields_type' => null, '_section_index' => null, 'attributes' => null, 'class' => array('fieldrow' => array(), 'fieldset' => array(), 'fields' => array(), 'field' => array(),), '_caller_object' => null, '_nested_depth' => 0,);
    public $aFields = array();
    public $aSections = array('_default' => array(),);
    public $aConditionedFields = array();
    public $aConditionedSections = array();
    protected $sFieldsType = '';
    protected $_sTargetSectionID = '_default';
    public $sCapability = 'manage_option';
    public function __construct($sFieldsType, $sCapability, $oCaller = null) {
        $this->sFieldsType = $sFieldsType;
        $this->sCapability = $sCapability;
        $this->oCaller = $oCaller;
    }
    public function addSection(array $aSection) {
        $aSection = $aSection + self::$_aStructure_Section;
        $aSection['section_id'] = $this->sanitizeSlug($aSection['section_id']);
        $this->aSections[$aSection['section_id']] = $aSection;
        $this->aFields[$aSection['section_id']] = isset($this->aFields[$aSection['section_id']]) ? $this->aFields[$aSection['section_id']] : array();
    }
    public function removeSection($sSectionID) {
        if ('_default' === $sSectionID) {
            return;
        }
        unset($this->aSections[$sSectionID]);
        unset($this->aFields[$sSectionID]);
    }
    public function addField($asField) {
        if (!is_array($asField)) {
            $this->_sTargetSectionID = is_string($asField) ? $asField : $this->_sTargetSectionID;
            return $this->_sTargetSectionID;
        }
        $aField = $asField;
        $this->_sTargetSectionID = isset($aField['section_id']) ? $aField['section_id'] : $this->_sTargetSectionID;
        $aField = $this->uniteArrays(array('_fields_type' => $this->sFieldsType), $aField, array('section_id' => $this->_sTargetSectionID), self::$_aStructure_Field);
        if (!isset($aField['field_id'], $aField['type'])) {
            return null;
        }
        $aField['field_id'] = $this->sanitizeSlug($aField['field_id']);
        $aField['section_id'] = $this->sanitizeSlug($aField['section_id']);
        $this->aFields[$aField['section_id']][$aField['field_id']] = $aField;
        return $aField;
    }
    public function removeField($sFieldID) {
        foreach ($this->aFields as $_sSectionID => $_aSubSectionsOrFields) {
            if (array_key_exists($sFieldID, $_aSubSectionsOrFields)) {
                unset($this->aFields[$_sSectionID][$sFieldID]);
            }
            foreach ($_aSubSectionsOrFields as $_sIndexOrFieldID => $_aSubSectionOrFields) {
                if (is_numeric($_sIndexOrFieldID) && is_int($_sIndexOrFieldID + 0)) {
                    if (array_key_exists($sFieldID, $_aSubSectionOrFields)) {
                        unset($this->aFields[$_sSectionID][$_sIndexOrFieldID]);
                    }
                    continue;
                }
            }
        }
    }
    public function format() {
        $this->aSections = $this->formatSections($this->aSections, $this->sFieldsType, $this->sCapability);
        $this->aFields = $this->formatFields($this->aFields, $this->sFieldsType, $this->sCapability);
    }
    public function formatSections(array $aSections, $sFieldsType, $sCapability) {
        $_aNewSectionArray = array();
        foreach ($aSections as $_sSectionID => $_aSection) {
            if (!is_array($_aSection)) {
                continue;
            }
            $_aSection = $this->formatSection($_aSection, $sFieldsType, $sCapability, count($_aNewSectionArray));
            if (!$_aSection) {
                continue;
            }
            $_aNewSectionArray[$_sSectionID] = $_aSection;
        }
        uasort($_aNewSectionArray, array($this, '_sortByOrder'));
        return $_aNewSectionArray;
    }
    protected function formatSection(array $aSection, $sFieldsType, $sCapability, $iCountOfElements) {
        $aSection = $this->uniteArrays($aSection, array('_fields_type' => $sFieldsType, 'capability' => $sCapability,), self::$_aStructure_Section);
        $aSection['order'] = is_numeric($aSection['order']) ? $aSection['order'] : $iCountOfElements + 10;
        if (empty($aSection['collapsible'])) {
            $aSection['collapsible'] = $aSection['collapsible'];
        } else {
            $aSection['collapsible'] = $this->getAsArray($aSection['collapsible']) + array('title' => $aSection['title'],) + self::$_aStructure_CollapsibleArguments;
            $aSection['collapsible']['toggle_all_button'] = implode(',', $this->getAsArray($aSection['collapsible']['toggle_all_button']));
        }
        return $aSection;
    }
    public function formatFields(array $aFields, $sFieldsType, $sCapability) {
        $_aNewFields = array();
        foreach ($aFields as $_sSectionID => $_aSubSectionsOrFields) {
            if (!isset($this->aSections[$_sSectionID])) {
                continue;
            }
            $_aNewFields[$_sSectionID] = isset($_aNewFields[$_sSectionID]) ? $_aNewFields[$_sSectionID] : array();
            $_abSectionRepeatable = $this->aSections[$_sSectionID]['repeatable'];
            if (count($this->getIntegerElements($_aSubSectionsOrFields)) || $_abSectionRepeatable) {
                foreach ($this->numerizeElements($_aSubSectionsOrFields) as $_iSectionIndex => $_aFields) {
                    foreach ($_aFields as $_aField) {
                        $_iCountElement = isset($_aNewFields[$_sSectionID][$_iSectionIndex]) ? count($_aNewFields[$_sSectionID][$_iSectionIndex]) : 0;
                        $_aField = $this->formatField($_aField, $sFieldsType, $sCapability, $_iCountElement, $_iSectionIndex, $_abSectionRepeatable, $this->oCaller);
                        if (!empty($_aField)) {
                            $_aNewFields[$_sSectionID][$_iSectionIndex][$_aField['field_id']] = $_aField;
                        }
                    }
                    uasort($_aNewFields[$_sSectionID][$_iSectionIndex], array($this, '_sortByOrder'));
                }
                continue;
            }
            $_aSectionedFields = $_aSubSectionsOrFields;
            foreach ($_aSectionedFields as $_sFieldID => $_aField) {
                $_iCountElement = isset($_aNewFields[$_sSectionID]) ? count($_aNewFields[$_sSectionID]) : 0;
                $_aField = $this->formatField($_aField, $sFieldsType, $sCapability, $_iCountElement, null, $_abSectionRepeatable, $this->oCaller);
                if (!empty($_aField)) {
                    $_aNewFields[$_sSectionID][$_aField['field_id']] = $_aField;
                }
            }
            uasort($_aNewFields[$_sSectionID], array($this, '_sortByOrder'));
        }
        if (!empty($this->aSections) && !empty($_aNewFields)):
            $_aSortedFields = array();
            foreach ($this->aSections as $sSectionID => $aSeciton) {
                if (isset($_aNewFields[$sSectionID])) {
                    $_aSortedFields[$sSectionID] = $_aNewFields[$sSectionID];
                }
            }
            $_aNewFields = $_aSortedFields;
        endif;
        return $_aNewFields;
    }
    protected function formatField($aField, $sFieldsType, $sCapability, $iCountOfElements, $iSectionIndex, $bIsSectionRepeatable, $oCallerObject) {
        if (!isset($aField['field_id'], $aField['type'])) {
            return;
        }
        $_aField = $this->uniteArrays(array('_fields_type' => $sFieldsType, '_caller_object' => $oCallerObject,) + $aField, array('capability' => $sCapability, 'section_id' => '_default', '_section_index' => $iSectionIndex, '_section_repeatable' => $bIsSectionRepeatable,) + self::$_aStructure_Field);
        $_aField['field_id'] = $this->sanitizeSlug($_aField['field_id']);
        $_aField['section_id'] = $this->sanitizeSlug($_aField['section_id']);
        $_aField['tip'] = esc_attr(strip_tags(isset($_aField['tip']) ? $_aField['tip'] : (is_array($_aField['description']) ? implode('&#10;', $_aField['description']) : $_aField['description'])));
        $_aField['order'] = is_numeric($_aField['order']) ? $_aField['order'] : $iCountOfElements + 10;
        return $_aField;
    }
    public function applyConditions($aFields = null, $aSections = null) {
        return $this->getConditionedFields($aFields, $this->getConditionedSections($aSections));
    }
    public function getConditionedSections($aSections = null) {
        $aSections = is_null($aSections) ? $this->aSections : $aSections;
        $_aNewSections = array();
        foreach ($aSections as $_sSectionID => $_aSection) {
            $_aSection = $this->getConditionedSection($_aSection);
            if ($_aSection) {
                $_aNewSections[$_sSectionID] = $_aSection;
            }
        }
        $this->aConditionedSections = $_aNewSections;
        return $_aNewSections;
    }
    protected function getConditionedSection(array $aSection) {
        if (!current_user_can($aSection['capability'])) {
            return;
        }
        if (!$aSection['if']) {
            return;
        }
        return $aSection;
    }
    public function getConditionedFields($aFields = null, $aSections = null) {
        $aFields = is_null($aFields) ? $this->aFields : $aFields;
        $aSections = is_null($aSections) ? $this->aSections : $aSections;
        $aFields = ( array )$this->castArrayContents($aSections, $aFields);
        $_aNewFields = array();
        foreach ($aFields as $_sSectionID => $_aSubSectionOrFields) {
            if (!is_array($_aSubSectionOrFields)) {
                continue;
            }
            if (!array_key_exists($_sSectionID, $aSections)) {
                continue;
            }
            foreach ($_aSubSectionOrFields as $_sIndexOrFieldID => $_aSubSectionOrField) {
                if (is_numeric($_sIndexOrFieldID) && is_int($_sIndexOrFieldID + 0)) {
                    $_sSubSectionIndex = $_sIndexOrFieldID;
                    $_aFields = $_aSubSectionOrField;
                    foreach ($_aFields as $_aField) {
                        $_aField = $this->getConditionedField($_aField);
                        if ($_aField) {
                            $_aNewFields[$_sSectionID][$_sSubSectionIndex][$_aField['field_id']] = $_aField;
                        }
                    }
                    continue;
                }
                $_aField = $_aSubSectionOrField;
                $_aField = $this->getConditionedField($_aField);
                if ($_aField) {
                    $_aNewFields[$_sSectionID][$_aField['field_id']] = $_aField;
                }
            }
        }
        $this->aConditionedFields = $_aNewFields;
        return $_aNewFields;
    }
    protected function getConditionedField($aField) {
        if (!current_user_can($aField['capability'])) {
            return null;
        }
        if (!$aField['if']) {
            return null;
        }
        return $aField;
    }
    public function setDynamicElements($aOptions) {
        $aOptions = $this->castArrayContents($this->aConditionedSections, $aOptions);
        foreach ($aOptions as $_sSectionID => $_aSubSectionOrFields) {
            if (!is_array($_aSubSectionOrFields)) {
                continue;
            }
            $_aSubSection = array();
            foreach ($_aSubSectionOrFields as $_isIndexOrFieldID => $_aSubSectionOrFieldOptions) {
                if (!(is_numeric($_isIndexOrFieldID) && is_int($_isIndexOrFieldID + 0))) {
                    continue;
                }
                $_iIndex = $_isIndexOrFieldID;
                $_aSubSection[$_iIndex] = isset($this->aConditionedFields[$_sSectionID][$_iIndex]) ? $this->aConditionedFields[$_sSectionID][$_iIndex] : $this->getNonIntegerElements($this->aConditionedFields[$_sSectionID]);
                $_aSubSection[$_iIndex] = !empty($_aSubSection[$_iIndex]) ? $_aSubSection[$_iIndex] : (isset($_aSubSection[$_iPrevIndex]) ? $_aSubSection[$_iPrevIndex] : array());
                foreach ($_aSubSection[$_iIndex] as & $_aField) {
                    $_aField['_section_index'] = $_iIndex;
                }
                unset($_aField);
                $_iPrevIndex = $_iIndex;
            }
            if (!empty($_aSubSection)) {
                $this->aConditionedFields[$_sSectionID] = $_aSubSection;
            }
        }
    }
}
class Legull_AdminPageFramework_FormElement_Page extends Legull_AdminPageFramework_FormElement {
    protected $sDefaultPageSlug;
    protected $sOptionKey;
    protected $sClassName;
    protected $sCurrentPageSlug;
    protected $sCurrentTabSlug;
    public function isPageAdded($sPageSlug) {
        foreach ($this->aSections as $_sSectionID => $_aSection) {
            if (isset($_aSection['page_slug']) && $sPageSlug == $_aSection['page_slug']) {
                return true;
            }
        }
        return false;
    }
    public function getFieldsByPageSlug($sPageSlug, $sTabSlug = '') {
        return $this->castArrayContents($this->getSectionsByPageSlug($sPageSlug, $sTabSlug), $this->aFields);
    }
    public function getSectionsByPageSlug($sPageSlug, $sTabSlug = '') {
        $_aSections = array();
        foreach ($this->aSections as $_sSecitonID => $_aSection) {
            if ($sTabSlug && $_aSection['tab_slug'] != $sTabSlug) {
                continue;
            }
            if ($_aSection['page_slug'] != $sPageSlug) {
                continue;
            }
            $_aSections[$_sSecitonID] = $_aSection;
        }
        uasort($_aSections, array($this, '_sortByOrder'));
        return $_aSections;
    }
    public function getPageSlugBySectionID($sSectionID) {
        return isset($this->aSections[$sSectionID]['page_slug']) ? $this->aSections[$sSectionID]['page_slug'] : null;
    }
    public function setDefaultPageSlug($sDefaultPageSlug) {
        $this->sDefaultPageSlug = $sDefaultPageSlug;
    }
    public function setOptionKey($sOptionKey) {
        $this->sOptionKey = $sOptionKey;
    }
    public function setCallerClassName($sClassName) {
        $this->sClassName = $sClassName;
    }
    public function setCurrentPageSlug($sCurrentPageSlug) {
        $this->sCurrentPageSlug = $sCurrentPageSlug;
    }
    public function setCurrentTabSlug($sCurrentTabSlug) {
        $this->sCurrentTabSlug = $sCurrentTabSlug;
    }
    protected function formatSection(array $aSection, $sFieldsType, $sCapability, $iCountOfElements) {
        $aSection = $aSection + array('_fields_type' => $sFieldsType, 'capability' => $sCapability, 'page_slug' => $this->sDefaultPageSlug,);
        return parent::formatSection($aSection, $sFieldsType, $sCapability, $iCountOfElements);
    }
    protected function formatField($aField, $sFieldsType, $sCapability, $iCountOfElements, $iSectionIndex, $bIsSectionRepeatable, $oCallerObject) {
        $_aField = parent::formatField($aField, $sFieldsType, $sCapability, $iCountOfElements, $iSectionIndex, $bIsSectionRepeatable, $oCallerObject);
        if (!$_aField) {
            return;
        }
        $_aField['option_key'] = $this->sOptionKey;
        $_aField['class_name'] = $this->sClassName;
        $_aField['page_slug'] = isset($this->aSections[$_aField['section_id']]['page_slug']) ? $this->aSections[$_aField['section_id']]['page_slug'] : null;
        $_aField['tab_slug'] = isset($this->aSections[$_aField['section_id']]['tab_slug']) ? $this->aSections[$_aField['section_id']]['tab_slug'] : null;
        $_aField['section_title'] = isset($this->aSections[$_aField['section_id']]['title']) ? $this->aSections[$_aField['section_id']]['title'] : null;
        return $_aField;
    }
    protected function getConditionedSection(array $aSection) {
        if (!current_user_can($aSection['capability'])) {
            return;
        }
        if (!$aSection['if']) {
            return;
        }
        if (!$aSection['page_slug']) {
            return;
        }
        if ('options.php' != $this->getPageNow() && $this->sCurrentPageSlug != $aSection['page_slug']) {
            return;
        }
        if (!$this->_isSectionOfCurrentTab($aSection, $this->sCurrentPageSlug, $this->sCurrentTabSlug)) {
            return;
        }
        return $aSection;
    }
    private function _isSectionOfCurrentTab($aSection, $sCurrentPageSlug, $sCurrentTabSlug) {
        if ($aSection['page_slug'] != $sCurrentPageSlug) {
            return false;
        }
        if (!isset($aSection['tab_slug'])) {
            return true;
        }
        if ($aSection['tab_slug'] == $sCurrentTabSlug) {
            return true;
        }
        return false;
    }
    public function getPageOptions($aOptions, $sPageSlug) {
        $_aOtherPageOptions = $this->getOtherPageOptions($aOptions, $sPageSlug);
        return $this->invertCastArrayContents($aOptions, $_aOtherPageOptions);
    }
    public function getPageOnlyOptions($aOptions, $sPageSlug) {
        $_aStoredOptionsOfThePage = array();
        foreach ($this->aFields as $_sSectionID => $_aFields) {
            if (isset($this->aSections[$_sSectionID]['page_slug']) && $this->aSections[$_sSectionID]['page_slug'] != $sPageSlug) continue;
            foreach ($_aFields as $_sFieldID => $_aField) {
                if (!isset($_aField['page_slug']) || $_aField['page_slug'] != $sPageSlug) {
                    continue;
                }
                if (is_numeric($_sFieldID) && is_int($_sFieldID + 0)) {
                    if (array_key_exists($_sSectionID, $aOptions)) {
                        $_aStoredOptionsOfThePage[$_sSectionID] = $aOptions[$_sSectionID];
                    }
                    continue;
                }
                if (isset($_aField['section_id']) && $_aField['section_id'] != '_default') {
                    if (array_key_exists($_aField['section_id'], $aOptions)) $_aStoredOptionsOfThePage[$_aField['section_id']] = $aOptions[$_aField['section_id']];
                    continue;
                }
                if (array_key_exists($_aField['field_id'], $aOptions)) {
                    $_aStoredOptionsOfThePage[$_aField['field_id']] = $aOptions[$_aField['field_id']];
                }
            }
        }
        return $_aStoredOptionsOfThePage;
    }
    public function getOtherPageOptions($aOptions, $sPageSlug) {
        $_aStoredOptionsNotOfThePage = array();
        foreach ($this->aFields as $_sSectionID => $_aFields) {
            if (isset($this->aSections[$_sSectionID]['page_slug']) && $this->aSections[$_sSectionID]['page_slug'] == $sPageSlug) {
                continue;
            }
            foreach ($_aFields as $_sFieldID => $_aField) {
                if (!isset($_aField['page_slug'])) {
                    continue;
                }
                if ($_aField['page_slug'] == $sPageSlug) {
                    continue;
                }
                if (is_numeric($_sFieldID) && is_int($_sFieldID + 0)) {
                    continue;
                }
                if (isset($_aField['section_id']) && $_aField['section_id'] != '_default') {
                    if (array_key_exists($_aField['section_id'], $aOptions)) {
                        $_aStoredOptionsNotOfThePage[$_aField['section_id']] = $aOptions[$_aField['section_id']];
                    }
                    continue;
                }
                if (array_key_exists($_aField['field_id'], $aOptions)) {
                    $_aStoredOptionsNotOfThePage[$_aField['field_id']] = $aOptions[$_aField['field_id']];
                }
            }
        }
        return $_aStoredOptionsNotOfThePage;
    }
    public function getOtherTabOptions($aOptions, $sPageSlug, $sTabSlug) {
        $_aStoredOptionsNotOfTheTab = array();
        foreach ($this->aFields as $_sSectionID => $_aSubSectionsOrFields) {
            if (isset($this->aSections[$_sSectionID]['page_slug']) && $this->aSections[$_sSectionID]['page_slug'] == $sPageSlug && isset($this->aSections[$_sSectionID]['tab_slug']) && $this->aSections[$_sSectionID]['tab_slug'] == $sTabSlug) {
                continue;
            }
            foreach ($_aSubSectionsOrFields as $_isSubSectionIndexOrFieldID => $_aSubSectionOrField) {
                if (is_numeric($_isSubSectionIndexOrFieldID) && is_int($_isSubSectionIndexOrFieldID + 0)) {
                    if (array_key_exists($_sSectionID, $aOptions)) {
                        $_aStoredOptionsNotOfTheTab[$_sSectionID] = $aOptions[$_sSectionID];
                    }
                    continue;
                }
                $_aField = $_aSubSectionOrField;
                if (isset($_aField['section_id']) && $_aField['section_id'] != '_default') {
                    if (array_key_exists($_aField['section_id'], $aOptions)) {
                        $_aStoredOptionsNotOfTheTab[$_aField['section_id']] = $aOptions[$_aField['section_id']];
                    }
                    continue;
                }
                if (array_key_exists($_aField['field_id'], $aOptions)) {
                    $_aStoredOptionsNotOfTheTab[$_aField['field_id']] = $aOptions[$_aField['field_id']];
                }
            }
        }
        return $_aStoredOptionsNotOfTheTab;
    }
    public function getTabOptions($aOptions, $sPageSlug, $sTabSlug = '') {
        $_aOtherTabOptions = $this->getOtherTabOptions($aOptions, $sPageSlug, $sTabSlug);
        return $this->invertCastArrayContents($aOptions, $_aOtherTabOptions);
    }
    public function getTabOnlyOptions($aOptions, $sPageSlug, $sTabSlug = '') {
        $_aStoredOptionsOfTheTab = array();
        if (!$sTabSlug) {
            return $_aStoredOptionsOfTheTab;
        }
        foreach ($this->aFields as $_sSectionID => $_aSubSectionsOrFields) {
            if (isset($this->aSections[$_sSectionID]['page_slug']) && $this->aSections[$_sSectionID]['page_slug'] != $sPageSlug) {
                continue;
            }
            if (isset($this->aSections[$_sSectionID]['tab_slug']) && $this->aSections[$_sSectionID]['tab_slug'] != $sTabSlug) {
                continue;
            }
            foreach ($_aSubSectionsOrFields as $_sFieldID => $_aField) {
                if (is_numeric($_sFieldID) && is_int($_sFieldID + 0)) {
                    if (array_key_exists($_sSectionID, $aOptions)) {
                        $_aStoredOptionsOfTheTab[$_sSectionID] = $aOptions[$_sSectionID];
                    }
                    continue;
                }
                if (isset($_aField['section_id']) && '_default' != $_aField['section_id']) {
                    if (array_key_exists($_aField['section_id'], $aOptions)) {
                        $_aStoredOptionsOfTheTab[$_aField['section_id']] = $aOptions[$_aField['section_id']];
                    }
                    continue;
                }
                if (array_key_exists($_aField['field_id'], $aOptions)) {
                    $_aStoredOptionsOfTheTab[$_aField['field_id']] = $aOptions[$_aField['field_id']];
                }
            }
        }
        return $_aStoredOptionsOfTheTab;
    }
}
class Legull_AdminPageFramework_Debug extends Legull_AdminPageFramework_WPUtility {
    static public function dump($asArray, $sFilePath = null) {
        echo self::get($asArray, $sFilePath);
    }
    static public function dumpArray($asArray, $sFilePath = null) {
        self::dump($asArray, $sFilePath);
    }
    static public function get($asArray, $sFilePath = null, $bEscape = true) {
        if ($sFilePath) self::log($asArray, $sFilePath);
        return $bEscape ? "<pre class='dump-array'>" . htmlspecialchars(self::getAsString($asArray)) . "</pre>" : self::getAsString($asArray);
    }
    static public function getArray($asArray, $sFilePath = null, $bEscape = true) {
        return self::get($asArray, $sFilePath, $bEscape);
    }
    static public function log($vValue, $sFilePath = null) {
        static $_iPageLoadID;
        static $_nGMTOffset;
        static $_fPreviousTimeStamp = 0;
        $_iPageLoadID = $_iPageLoadID ? $_iPageLoadID : uniqid();
        $_oCallerInfo = debug_backtrace();
        $_sCallerFunction = isset($_oCallerInfo[1]['function']) ? $_oCallerInfo[1]['function'] : '';
        $_sCallerClasss = isset($_oCallerInfo[1]['class']) ? $_oCallerInfo[1]['class'] : '';
        $sFilePath = !$sFilePath ? WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . $_sCallerClasss . '_' . date("Ymd") . '.log' : (true === $sFilePath ? WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . date("Ymd") . '.log' : $sFilePath);
        $_nGMTOffset = isset($_nGMTOffset) ? $_nGMTOffset : get_option('gmt_offset');
        $_fCurrentTimeStamp = microtime(true);
        $_nNow = $_fCurrentTimeStamp + ($_nGMTOffset * 60 * 60);
        $_nMicroseconds = round(($_nNow - floor($_nNow)) * 10000);
        $_nMicroseconds = str_pad($_nMicroseconds, 4, '0');
        $_nElapsed = round($_fCurrentTimeStamp - $_fPreviousTimeStamp, 3);
        $_aElapsedParts = explode(".", ( string )$_nElapsed);
        $_sElapsedFloat = str_pad(isset($_aElapsedParts[1]) ? $_aElapsedParts[1] : 0, 3, '0');
        $_sElapsed = isset($_aElapsedParts[0]) ? $_aElapsedParts[0] : 0;
        $_sElapsed = strlen($_sElapsed) > 1 ? '+' . substr($_sElapsed, -1, 2) : ' ' . $_sElapsed;
        $_sHeading = date("Y/m/d H:i:s", $_nNow) . '.' . $_nMicroseconds . ' ' . $_sElapsed . '.' . $_sElapsedFloat . ' ' . $_iPageLoadID . ' ' . Legull_AdminPageFramework_Registry::Version . (Legull_AdminPageFramework_Registry::$bIsMinifiedVersion ? '.min' : '') . ' ' . "{$_sCallerClasss}::{$_sCallerFunction} " . current_filter() . ' ' . self::getCurrentURL() . ' ';
        $_sType = gettype($vValue);
        $_iLengths = is_string($vValue) || is_integer($vValue) ? strlen($vValue) : (is_array($vValue) ? count($vValue) : null);
        file_put_contents($sFilePath, $_sHeading . PHP_EOL . '(' . $_sType . (null !== $_iLengths ? ', length: ' . $_iLengths : '') . ') ' . self::getAsString($vValue) . PHP_EOL . PHP_EOL, FILE_APPEND);
        $_fPreviousTimeStamp = $_fCurrentTimeStamp;
    }
    static public function logArray($asArray, $sFilePath = null) {
        self::log($asArray, $sFilePath);
    }
    static public function getAsString($mValue) {
        $mValue = is_object($mValue) ? (method_exists($mValue, '__toString') ? ( string )$mValue : ( array )$mValue) : $mValue;
        $mValue = is_array($mValue) ? self::getSliceByDepth($mValue, 5) : $mValue;
        return print_r($mValue, true);
    }
    static public function getSliceByDepth(array $aSubject, $iDepth = 0) {
        foreach ($aSubject as $_sKey => $_vValue) {
            if (is_object($_vValue)) {
                $aSubject[$_sKey] = method_exists($_vValue, '__toString') ? ( string )$_vValue : get_object_vars($_vValue);
            }
            if (is_array($_vValue)) {
                if ($iDepth > 0) {
                    $aSubject[$_sKey] = self::getSliceByDepth($_vValue, --$iDepth);
                    continue;
                }
                unset($aSubject[$_sKey]);
            }
        }
        return $aSubject;
    }
}
abstract class Legull_AdminPageFramework_HelpPane_Base extends Legull_AdminPageFramework_Debug {
    protected $_oScreen;
    function __construct($oProp) {
        $this->oProp = $oProp;
        $this->oUtil = new Legull_AdminPageFramework_WPUtility;
    }
    protected function _setHelpTab($sID, $sTitle, $aContents, $aSideBarContents = array()) {
        if (empty($aContents)) return;
        $this->_oScreen = isset($this->_oScreen) ? $this->_oScreen : get_current_screen();
        $this->_oScreen->add_help_tab(array('id' => $sID, 'title' => $sTitle, 'content' => implode(PHP_EOL, $aContents),));
        if (!empty($aSideBarContents)) $this->_oScreen->set_help_sidebar(implode(PHP_EOL, $aSideBarContents));
    }
    protected function _formatHelpDescription($sHelpDescription) {
        return "<div class='contextual-help-description'>" . $sHelpDescription . "</div>";
    }
}
class Legull_AdminPageFramework_HelpPane_Page extends Legull_AdminPageFramework_HelpPane_Base {
    protected static $_aStructure_HelpTabUserArray = array('page_slug' => null, 'page_tab_slug' => null, 'help_tab_title' => null, 'help_tab_id' => null, 'help_tab_content' => null, 'help_tab_sidebar_content' => null,);
    function __construct($oProp) {
        parent::__construct($oProp);
        if ($oProp->bIsAdminAjax) {
            return;
        }
        add_action('admin_head', array($this, '_replyToRegisterHelpTabs'), 200);
    }
    public function _replyToRegisterHelpTabs() {
        $sCurrentPageSlug = isset($_GET['page']) ? $_GET['page'] : '';
        $sCurrentPageTabSlug = isset($_GET['tab']) ? $_GET['tab'] : (isset($this->oProp->aDefaultInPageTabs[$sCurrentPageSlug]) ? $this->oProp->aDefaultInPageTabs[$sCurrentPageSlug] : '');
        if (empty($sCurrentPageSlug)) return;
        if (!$this->oProp->isPageAdded($sCurrentPageSlug)) return;
        foreach ($this->oProp->aHelpTabs as $aHelpTab) {
            if ($sCurrentPageSlug != $aHelpTab['sPageSlug']) continue;
            if (isset($aHelpTab['sPageTabSlug']) && !empty($aHelpTab['sPageTabSlug']) && $sCurrentPageTabSlug != $aHelpTab['sPageTabSlug']) continue;
            $this->_setHelpTab($aHelpTab['sID'], $aHelpTab['sTitle'], $aHelpTab['aContent'], $aHelpTab['aSidebar']);
        }
    }
    public function _addHelpTab($aHelpTab) {
        $aHelpTab = ( array )$aHelpTab + self::$_aStructure_HelpTabUserArray;
        if (!isset($this->oProp->aHelpTabs[$aHelpTab['help_tab_id']])) {
            $this->oProp->aHelpTabs[$aHelpTab['help_tab_id']] = array('sID' => $aHelpTab['help_tab_id'], 'sTitle' => $aHelpTab['help_tab_title'], 'aContent' => !empty($aHelpTab['help_tab_content']) ? array($this->_formatHelpDescription($aHelpTab['help_tab_content'])) : array(), 'aSidebar' => !empty($aHelpTab['help_tab_sidebar_content']) ? array($this->_formatHelpDescription($aHelpTab['help_tab_sidebar_content'])) : array(), 'sPageSlug' => $aHelpTab['page_slug'], 'sPageTabSlug' => $aHelpTab['page_tab_slug'],);
            return;
        }
        if (!empty($aHelpTab['help_tab_content'])) {
            $this->oProp->aHelpTabs[$aHelpTab['help_tab_id']]['aContent'][] = $this->_formatHelpDescription($aHelpTab['help_tab_content']);
        }
        if (!empty($aHelpTab['help_tab_sidebar_content'])) {
            $this->oProp->aHelpTabs[$aHelpTab['help_tab_id']]['aSidebar'][] = $this->_formatHelpDescription($aHelpTab['help_tab_sidebar_content']);
        }
    }
}
abstract class Legull_AdminPageFramework_FieldType_Base extends Legull_AdminPageFramework_WPUtility {
    public $_sFieldSetType = '';
    public $aFieldTypeSlugs = array('default');
    protected $aDefaultKeys = array();
    protected static $_aDefaultKeys = array('value' => null, 'default' => null, 'repeatable' => false, 'sortable' => false, 'label' => '', 'delimiter' => '', 'before_input' => '', 'after_input' => '', 'before_label' => null, 'after_label' => null, 'before_field' => null, 'after_field' => null, 'label_min_width' => 140, 'before_fieldset' => null, 'after_fieldset' => null, 'field_id' => null, 'page_slug' => null, 'section_id' => null, 'before_fields' => null, 'after_fields' => null, 'attributes' => array('disabled' => null, 'class' => '', 'fieldrow' => array(), 'fieldset' => array(), 'fields' => array(), 'field' => array(),),);
    protected $oMsg;
    function __construct($asClassName = 'admin_page_framework', $asFieldTypeSlug = null, $oMsg = null, $bAutoRegister = true) {
        $this->aFieldTypeSlugs = empty($asFieldTypeSlug) ? $this->aFieldTypeSlugs : ( array )$asFieldTypeSlug;
        $this->oMsg = $oMsg ? $oMsg : Legull_AdminPageFramework_Message::getInstance();
        if ($bAutoRegister) {
            foreach (( array )$asClassName as $_sClassName) {
                add_filter("field_types_{$_sClassName}", array($this, '_replyToRegisterInputFieldType'));
            }
        }
        $this->construct();
    }
    protected function construct() {
    }
    protected function geFieldOutput(array $aField) {
        if (!is_object($aField['_caller_object'])) {
            return '';
        }
        $aField['_nested_depth']++;
        $_oCaller = $aField['_caller_object'];
        $_aOptions = $_oCaller->getSavedOptions();
        $_oField = new Legull_AdminPageFramework_FormField($aField, $_aOptions, $_oCaller->getFieldErrors(), $_oCaller->oProp->aFieldTypeDefinitions, $_oCaller->oMsg, $_oCaller->oProp->aFieldCallbacks);
        return $_oField->_getFieldOutput();
    }
    public function _replyToRegisterInputFieldType($aFieldDefinitions) {
        foreach ($this->aFieldTypeSlugs as $sFieldTypeSlug) {
            $aFieldDefinitions[$sFieldTypeSlug] = $this->getDefinitionArray($sFieldTypeSlug);
        }
        return $aFieldDefinitions;
    }
    public function getDefinitionArray($sFieldTypeSlug = '') {
        $_aDefaultKeys = $this->aDefaultKeys + self::$_aDefaultKeys;
        $_aDefaultKeys['attributes'] = isset($this->aDefaultKeys['attributes']) && is_array($this->aDefaultKeys['attributes']) ? $this->aDefaultKeys['attributes'] + self::$_aDefaultKeys['attributes'] : self::$_aDefaultKeys['attributes'];
        return array('sFieldTypeSlug' => $sFieldTypeSlug, 'aFieldTypeSlugs' => $this->aFieldTypeSlugs, 'hfRenderField' => array($this, "_replyToGetField"), 'hfGetScripts' => array($this, "_replyToGetScripts"), 'hfGetStyles' => array($this, "_replyToGetStyles"), 'hfGetIEStyles' => array($this, "_replyToGetInputIEStyles"), 'hfFieldLoader' => array($this, "_replyToFieldLoader"), 'hfFieldSetTypeSetter' => array($this, "_replyToFieldTypeSetter"), 'hfDoOnRegistration' => array($this, "_replyToDoOnFieldRegistration"), 'aEnqueueScripts' => $this->_replyToGetEnqueuingScripts(), 'aEnqueueStyles' => $this->_replyToGetEnqueuingStyles(), 'aDefaultKeys' => $_aDefaultKeys,);
    }
    public function _replyToGetField($aField) {
        return '';
    }
    public function _replyToGetScripts() {
        return '';
    }
    public function _replyToGetInputIEStyles() {
        return '';
    }
    public function _replyToGetStyles() {
        return '';
    }
    public function _replyToFieldLoader() {
    }
    public function _replyToFieldTypeSetter($sFieldSetType = '') {
        $this->_sFieldSetType = $sFieldSetType;
    }
    public function _replyToDoOnFieldRegistration(array $aField) {
    }
    protected function _replyToGetEnqueuingScripts() {
        return array();
    }
    protected function _replyToGetEnqueuingStyles() {
        return array();
    }
    protected function getFieldElementByKey($asElement, $sKey, $asDefault = '') {
        if (!is_array($asElement) || !isset($sKey)) {
            return $asElement;
        }
        $aElements = & $asElement;
        return isset($aElements[$sKey]) ? $aElements[$sKey] : $asDefault;
    }
    protected function enqueueMediaUploader() {
        add_filter('media_upload_tabs', array($this, '_replyToRemovingMediaLibraryTab'));
        wp_enqueue_script('jquery');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        if (function_exists('wp_enqueue_media')) {
            new Legull_AdminPageFramework_Script_MediaUploader($this->oMsg);
        } else {
            wp_enqueue_script('media-upload');
        }
        if (in_array($this->getPageNow(), array('media-upload.php', 'async-upload.php',))) {
            add_filter('gettext', array($this, '_replyToReplaceThickBoxText'), 1, 2);
        }
    }
    public function _replyToReplaceThickBoxText($sTranslated, $sText) {
        if (!in_array($this->getPageNow(), array('media-upload.php', 'async-upload.php'))) {
            return $sTranslated;
        }
        if ($sText != 'Insert into Post') {
            return $sTranslated;
        }
        if ($this->getQueryValueInURLByKey(wp_get_referer(), 'referrer') != 'admin_page_framework') {
            return $sTranslated;
        }
        if (isset($_GET['button_label'])) {
            return $_GET['button_label'];
        }
        return $this->oProp->sThickBoxButtonUseThis ? $this->oProp->sThickBoxButtonUseThis : $this->oMsg->get('use_this_image');
    }
    public function _replyToRemovingMediaLibraryTab($aTabs) {
        if (!isset($_REQUEST['enable_external_source'])) {
            return $aTabs;
        }
        if (!$_REQUEST['enable_external_source']) {
            unset($aTabs['type_url']);
        }
        return $aTabs;
    }
}
abstract class Legull_AdminPageFramework_FieldType extends Legull_AdminPageFramework_FieldType_Base {
    public function _replyToFieldLoader() {
        $this->setUp();
    }
    public function _replyToGetScripts() {
        return $this->getScripts();
    }
    public function _replyToGetInputIEStyles() {
        return $this->getIEStyles();
    }
    public function _replyToGetStyles() {
        return $this->getStyles();
    }
    public function _replyToGetField($aField) {
        return $this->getField($aField);
    }
    public function _replyToDoOnFieldRegistration(array $aField) {
        return $this->doOnFieldRegistration($aField);
    }
    protected function _replyToGetEnqueuingScripts() {
        return $this->getEnqueuingScripts();
    }
    protected function _replyToGetEnqueuingStyles() {
        return $this->getEnqueuingStyles();
    }
    public $aFieldTypeSlugs = array('default',);
    protected $aDefaultKeys = array();
    protected function construct() {
    }
    protected function setUp() {
    }
    protected function getScripts() {
        return '';
    }
    protected function getIEStyles() {
        return '';
    }
    protected function getStyles() {
        return '';
    }
    protected function getField($aField) {
        return '';
    }
    protected function getEnqueuingScripts() {
        return array();
    }
    protected function getEnqueuingStyles() {
        return array();
    }
    protected function doOnFieldRegistration($aField) {
    }
}
abstract class Legull_AdminPageFramework_FormOutput extends Legull_AdminPageFramework_WPUtility {
    protected function _getFieldContainerAttributes($aField, $aAttributes = array(), $sContext = 'fieldrow') {
        $_aAttributes = $this->uniteArrays(isset($aField['attributes'][$sContext]) && is_array($aField['attributes'][$sContext]) ? $aField['attributes'][$sContext] : array(), $aAttributes);
        $_aAttributes['class'] = $this->generateClassAttribute(isset($_aAttributes['class']) ? $_aAttributes['class'] : array(), isset($aField['class'][$sContext]) ? $aField['class'][$sContext] : array());
        if ('fieldrow' === $sContext && $aField['hidden']) {
            $_aAttributes['style'] = $this->generateStyleAttribute(isset($_aAttributes['style']) ? $_aAttributes['style'] : array(), 'display:none');
        }
        return $this->generateAttributes($_aAttributes);
    }
}
class Legull_AdminPageFramework_FormField_Base extends Legull_AdminPageFramework_FormOutput {
    public $aField = array();
    public $aFIeldTypeDefinitions = array();
    public $aOptions = array();
    public $aErrors = array();
    public $oMsg;
    public $aCallbacks = array();
    public function __construct(&$aField, $aOptions, $aErrors, &$aFieldTypeDefinitions, &$oMsg, array $aCallbacks = array()) {
        $aFieldTypeDefinition = isset($aFieldTypeDefinitions[$aField['type']]) ? $aFieldTypeDefinitions[$aField['type']] : $aFieldTypeDefinitions['default'];
        $aFieldTypeDefinition['aDefaultKeys']['attributes'] = array('fieldrow' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['fieldrow'], 'fieldset' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['fieldset'], 'fields' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['fields'], 'field' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['field'],);
        $this->aField = $this->uniteArrays($aField, $aFieldTypeDefinition['aDefaultKeys']);
        $this->aFieldTypeDefinitions = $aFieldTypeDefinitions;
        $this->aOptions = $aOptions;
        $this->aErrors = $aErrors ? $aErrors : array();
        $this->oMsg = $oMsg;
        $this->aCallbacks = $aCallbacks + array('hfID' => null, 'hfTagID' => null, 'hfName' => null, 'hfNameFlat' => null, 'hfClass' => null,);
        $this->_loadScripts($this->aField['_fields_type']);
    }
    static private $_bIsLoadedSScripts = false;
    static private $_bIsLoadedSScripts_Widget = false;
    private function _loadScripts($sFieldsType = '') {
        if ('widget' === $sFieldsType && !self::$_bIsLoadedSScripts_Widget) {
            new Legull_AdminPageFramework_Script_Widget;
            self::$_bIsLoadedSScripts_Widget = true;
        }
        if (self::$_bIsLoadedSScripts) {
            return;
        }
        self::$_bIsLoadedSScripts = true;
        new Legull_AdminPageFramework_Script_Utility;
        new Legull_AdminPageFramework_Script_OptionStorage;
        new Legull_AdminPageFramework_Script_AttributeUpdator;
        new Legull_AdminPageFramework_Script_RepeatableField($this->oMsg);
        new Legull_AdminPageFramework_Script_Sortable;
        new Legull_AdminPageFramework_Script_RegisterCallback;
    }
    protected function _getRepeaterFieldEnablerScript($sFieldsContainerID, $iFieldCount, $aSettings) {
        $_sAdd = $this->oMsg->get('add');
        $_sRemove = $this->oMsg->get('remove');
        $_sVisibility = $iFieldCount <= 1 ? " style='visibility: hidden;'" : "";
        $_sSettingsAttributes = $this->generateDataAttributes(( array )$aSettings);
        $_bDashiconSupported = false;
        $_sDashiconPlus = $_bDashiconSupported ? 'dashicons dashicons-plus' : '';
        $_sDashiconMinus = $_bDashiconSupported ? 'dashicons dashicons-minus' : '';
        $_sButtons = "<div class='admin-page-framework-repeatable-field-buttons' {$_sSettingsAttributes} >" . "<a class='repeatable-field-remove button-secondary repeatable-field-button button button-small {$_sDashiconMinus}' href='#' title='{$_sRemove}' {$_sVisibility} data-id='{$sFieldsContainerID}'>" . ($_bDashiconSupported ? '' : '-') . "</a>" . "<a class='repeatable-field-add button-secondary repeatable-field-button button button-small {$_sDashiconPlus}' href='#' title='{$_sAdd}' data-id='{$sFieldsContainerID}'>" . ($_bDashiconSupported ? '' : '+') . "</a>" . "</div>";
        $_aJSArray = json_encode($aSettings);
        $_sButtonsHTML = '"' . $_sButtons . '"';
        $_sScript = "jQuery(document).ready(function(){ var _nodePositionIndicators=jQuery('#{$sFieldsContainerID} .admin-page-framework-field .repeatable-field-buttons');if(_nodePositionIndicators.length>0){ _nodePositionIndicators.replaceWith($_sButtonsHTML) }else if(!jQuery('#{$sFieldsContainerID} .admin-page-framework-repeatable-field-buttons').length)jQuery('#{$sFieldsContainerID} .admin-page-framework-field').prepend($_sButtonsHTML);jQuery('#{$sFieldsContainerID}').updateAPFRepeatableFields($_aJSArray) });";
        return "<script type='text/javascript'>" . $_sScript . "</script>";
    }
    protected function _getSortableFieldEnablerScript($sFieldsContainerID) {
        $_sScript = "jQuery(document).ready(function(){ jQuery(this).enableAPFSortable('$sFieldsContainerID') });";
        return "<script type='text/javascript' class='admin-page-framework-sortable-field-enabler-script'>" . $_sScript . "</script>";
    }
}
class Legull_AdminPageFramework_FormField extends Legull_AdminPageFramework_FormField_Base {
    private function _getInputName($aField = null, $sKey = '', $hfFilterCallback = null) {
        $sKey = ( string )$sKey;
        $aField = isset($aField) ? $aField : $this->aField;
        $_sKey = '0' !== $sKey && empty($sKey) ? '' : "[{$sKey}]";
        $_sSectionIndex = isset($aField['section_id'], $aField['_section_index']) ? "[{$aField['_section_index']}]" : "";
        $_sResult = '';
        $_sResultTail = '';
        switch ($aField['_fields_type']) {
            default:
            case 'page':
                $sSectionDimension = isset($aField['section_id']) && $aField['section_id'] && '_default' != $aField['section_id'] ? "[{$aField['section_id']}]" : '';
                $_sResult = "{$aField['option_key']}{$sSectionDimension}{$_sSectionIndex}[{$aField['field_id']}]{$_sKey}";
            break;
            case 'page_meta_box':
            case 'post_meta_box':
                $_sResult = isset($aField['section_id']) && $aField['section_id'] && '_default' != $aField['section_id'] ? "{$aField['section_id']}{$_sSectionIndex}[{$aField['field_id']}]{$_sKey}" : "{$aField['field_id']}{$_sKey}";
            break;
            case 'taxonomy':
                $_sResult = "{$aField['field_id']}{$_sKey}";
            break;
            case 'widget':
            case 'user_meta':
                $_sResult = isset($aField['section_id']) && $aField['section_id'] && '_default' != $aField['section_id'] ? "{$aField['section_id']}{$_sSectionIndex}[{$aField['field_id']}]" : "{$aField['field_id']}";
                $_sResultTail = $_sKey;
            break;
        }
        return is_callable($hfFilterCallback) ? call_user_func_array($hfFilterCallback, array($_sResult)) . $_sResultTail : $_sResult . $_sResultTail;
    }
    protected function _getFlatInputName($aField, $sKey = '', $hfFilterCallback = null) {
        $sKey = ( string )$sKey;
        $_sKey = '0' !== $sKey && empty($sKey) ? '' : "|{$sKey}";
        $_sSectionIndex = isset($aField['section_id'], $aField['_section_index']) ? "|{$aField['_section_index']}" : "";
        $_sResult = '';
        $_sResultTail = '';
        switch ($aField['_fields_type']) {
            default:
            case 'page':
                $sSectionDimension = isset($aField['section_id']) && $aField['section_id'] && '_default' != $aField['section_id'] ? "|{$aField['section_id']}" : '';
                $_sResult = "{$aField['option_key']}{$sSectionDimension}{$_sSectionIndex}|{$aField['field_id']}{$_sKey}";
            break;
            case 'page_meta_box':
            case 'post_meta_box':
                $_sResult = isset($aField['section_id']) && $aField['section_id'] && '_default' != $aField['section_id'] ? "{$aField['section_id']}{$_sSectionIndex}|{$aField['field_id']}{$_sKey}" : "{$aField['field_id']}{$_sKey}";
            break;
            case 'taxonomy':
                $_sResult = "{$aField['field_id']}{$_sKey}";
            break;
            case 'widget':
            case 'user_meta':
                $_sResult = isset($aField['section_id']) && $aField['section_id'] && '_default' != $aField['section_id'] ? "{$aField['section_id']}{$_sSectionIndex}|{$aField['field_id']}" : "{$aField['field_id']}";
                $_sResultTail = $_sKey;
            break;
        }
        return is_callable($hfFilterCallback) ? call_user_func_array($hfFilterCallback, array($_sResult)) . $_sResultTail : $_sResult . $_sResultTail;
    }
    static public function _getInputID($aField, $isIndex = 0, $hfFilterCallback = null) {
        $_sSectionIndex = isset($aField['_section_index']) ? '__' . $aField['_section_index'] : '';
        $_isFieldIndex = '__' . $isIndex;
        $_sResult = isset($aField['section_id']) && '_default' != $aField['section_id'] ? $aField['section_id'] . $_sSectionIndex . '_' . $aField['field_id'] . $_isFieldIndex : $aField['field_id'] . $_isFieldIndex;
        return is_callable($hfFilterCallback) ? call_user_func_array($hfFilterCallback, array($_sResult)) : $_sResult;
    }
    static public function _getInputTagBaseID($aField, $hfFilterCallback = null) {
        $_sSectionIndex = isset($aField['_section_index']) ? '__' . $aField['_section_index'] : '';
        $_sResult = isset($aField['section_id']) && '_default' != $aField['section_id'] ? $aField['section_id'] . $_sSectionIndex . '_' . $aField['field_id'] : $aField['field_id'];
        return is_callable($hfFilterCallback) ? call_user_func_array($hfFilterCallback, array($_sResult)) : $_sResult;
    }
    public function _getFieldOutput() {
        $_aFieldsOutput = array();
        $_sFieldError = $this->_getFieldError($this->aErrors, $this->aField['section_id'], $this->aField['field_id']);
        if ($_sFieldError) {
            $_aFieldsOutput[] = $_sFieldError;
        }
        $this->aField['tag_id'] = $this->_getInputTagBaseID($this->aField, $this->aCallbacks['hfTagID']);
        $_aFields = $this->_constructFieldsArray($this->aField, $this->aOptions);
        $_aFieldsOutput[] = $this->_getFieldsOutput($_aFields, $this->aCallbacks);
        return $this->_getFinalOutput($this->aField, $_aFieldsOutput, count($_aFields));
    }
    private function _getFieldsOutput(array $aFields, array $aCallbacks = array()) {
        $_aOutput = array();
        foreach ($aFields as $__sKey => $__aField) {
            $_aFieldTypeDefinition = isset($this->aFieldTypeDefinitions[$__aField['type']]) ? $this->aFieldTypeDefinitions[$__aField['type']] : $this->aFieldTypeDefinitions['default'];
            if (!is_callable($_aFieldTypeDefinition['hfRenderField'])) {
                continue;
            }
            $_bIsSubField = is_numeric($__sKey) && 0 < $__sKey;
            $__aField['_index'] = $__sKey;
            $__aField['input_id'] = $this->_getInputID($__aField, $__sKey, $aCallbacks['hfID']);
            $__aField['_input_name'] = $this->_getInputName($__aField, $__aField['_is_multiple_fields'] ? $__sKey : '', $aCallbacks['hfName']);
            $__aField['_input_name_flat'] = $this->_getFlatInputName($__aField, $__aField['_is_multiple_fields'] ? $__sKey : '', $aCallbacks['hfNameFlat']);
            $__aField['_field_container_id'] = "field-{$__aField['input_id']}";
            $__aField['_input_id_model'] = $this->_getInputID($__aField, '-fi-', $aCallbacks['hfID']);
            $__aField['_input_name_model'] = $this->_getInputName($__aField, $__aField['_is_multiple_fields'] ? '-fi-' : '', $aCallbacks['hfName']);
            $__aField['_fields_container_id_model'] = "field-{$__aField['_input_id_model']}";
            $__aField['_fields_container_id'] = "fields-{$this->aField['tag_id']}";
            $__aField['_fieldset_container_id'] = "fieldset-{$this->aField['tag_id']}";
            $__aField = $this->uniteArrays($__aField, array('attributes' => array('id' => $__aField['input_id'], 'name' => $__aField['_input_name'], 'value' => $__aField['value'], 'type' => $__aField['type'], 'disabled' => null, 'data-id_model' => $__aField['_input_id_model'], 'data-name_model' => $__aField['_input_name_model'],)), ( array )$_aFieldTypeDefinition['aDefaultKeys']);
            $__aField['attributes']['class'] = 'widget' === $__aField['_fields_type'] && is_callable($aCallbacks['hfClass']) ? call_user_func_array($aCallbacks['hfClass'], array($__aField['attributes']['class'])) : $__aField['attributes']['class'];
            $__aField['attributes']['class'] = $this->generateClassAttribute($__aField['attributes']['class'], $this->dropElementsByType($__aField['class']));
            $_aFieldAttributes = array('id' => $__aField['_field_container_id'], 'data-type' => "{$__aField['type']}", 'data-id_model' => $__aField['_fields_container_id_model'], 'class' => "admin-page-framework-field admin-page-framework-field-{$__aField['type']}" . ($__aField['attributes']['disabled'] ? ' disabled' : null) . ($_bIsSubField ? ' admin-page-framework-subfield' : null),);
            $_aOutput[] = $__aField['before_field'] . "<div " . $this->_getFieldContainerAttributes($__aField, $_aFieldAttributes, 'field') . ">" . call_user_func_array($_aFieldTypeDefinition['hfRenderField'], array($__aField)) . (($sDelimiter = $__aField['delimiter']) ? "<div " . $this->generateAttributes(array('class' => 'delimiter', 'id' => "delimiter-{$__aField['input_id']}", 'style' => $this->isLastElement($aFields, $__sKey) ? "display:none;" : "",)) . ">{$sDelimiter}</div>" : "") . "</div>" . $__aField['after_field'];
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getFinalOutput(array $aField, array $aFieldsOutput, $iFieldsCount) {
        $_aFieldsSetAttributes = array('id' => 'fieldset-' . $aField['tag_id'], 'class' => 'admin-page-framework-fieldset', 'data-field_id' => $aField['tag_id'],);
        $_aFieldsContainerAttributes = array('id' => 'fields-' . $aField['tag_id'], 'class' => 'admin-page-framework-fields' . ($aField['repeatable'] ? ' repeatable' : '') . ($aField['sortable'] ? ' sortable' : ''), 'data-type' => $aField['type'],);
        return $aField['before_fieldset'] . "<fieldset " . $this->_getFieldContainerAttributes($aField, $_aFieldsSetAttributes, 'fieldset') . ">" . "<div " . $this->_getFieldContainerAttributes($aField, $_aFieldsContainerAttributes, 'fields') . ">" . $aField['before_fields'] . implode(PHP_EOL, $aFieldsOutput) . $aField['after_fields'] . "</div>" . $this->_getExtras($aField, $iFieldsCount) . "</fieldset>" . $aField['after_fieldset'];
    }
    private function _getExtras($aField, $iFieldsCount) {
        $_aOutput = array();
        if (isset($aField['description'])) {
            $_aOutput[] = $this->_getDescription($aField['description']);
        }
        $_aOutput[] = $this->_getFieldScripts($aField, $iFieldsCount);
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getDescription($asDescription) {
        if (empty($asDescription)) {
            return '';
        }
        $_aOutput = array();
        foreach ($this->getAsArray($asDescription) as $_sDescription) {
            $_aOutput[] = "<p class='admin-page-framework-fields-description'>" . "<span class='description'>{$_sDescription}</span>" . "</p>";
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getFieldScripts($aField, $iFieldsCount) {
        $_aOutput = array();
        $_aOutput[] = $aField['repeatable'] ? $this->_getRepeaterFieldEnablerScript('fields-' . $aField['tag_id'], $iFieldsCount, $aField['repeatable']) : '';
        $_aOutput[] = $aField['sortable'] && ($iFieldsCount > 1 || $aField['repeatable']) ? $this->_getSortableFieldEnablerScript('fields-' . $aField['tag_id']) : '';
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getFieldError($aErrors, $sSectionID, $sFieldID) {
        if (isset($aErrors[$sSectionID], $aErrors[$sSectionID][$sFieldID]) && is_array($aErrors[$sSectionID]) && !is_array($aErrors[$sSectionID][$sFieldID])) {
            return "<span class='field-error'>*&nbsp;{$this->aField['error_message']}" . $aErrors[$sSectionID][$sFieldID] . "</span>";
        }
        if (isset($aErrors[$sFieldID]) && !is_array($aErrors[$sFieldID])) {
            return "<span class='field-error'>*&nbsp;{$this->aField['error_message']}" . $aErrors[$sFieldID] . "</span>";
        }
    }
    protected function _constructFieldsArray(&$aField, &$aOptions) {
        $vSavedValue = $this->_getStoredInputFieldValue($aField, $aOptions);
        $aFirstField = array();
        $aSubFields = array();
        foreach ($aField as $nsIndex => $vFieldElement) {
            if (is_numeric($nsIndex)) {
                $aSubFields[] = $vFieldElement;
            } else {
                $aFirstField[$nsIndex] = $vFieldElement;
            }
        }
        if ($aField['repeatable']) {
            foreach (( array )$vSavedValue as $iIndex => $vValue) {
                if (0 == $iIndex) {
                    continue;
                }
                $aSubFields[$iIndex - 1] = isset($aSubFields[$iIndex - 1]) && is_array($aSubFields[$iIndex - 1]) ? $aSubFields[$iIndex - 1] : array();
            }
        }
        foreach ($aSubFields as & $aSubField) {
            $aLabel = isset($aSubField['label']) ? $aSubField['label'] : (isset($aFirstField['label']) ? $aFirstField['label'] : null);
            $aSubField = $this->uniteArrays($aSubField, $aFirstField);
            $aSubField['label'] = $aLabel;
        }
        $aFields = array_merge(array($aFirstField), $aSubFields);
        if (count($aSubFields) > 0 || $aField['repeatable'] || $aField['sortable']) {
            foreach ($aFields as $iIndex => & $aThisField) {
                $aThisField['_saved_value'] = isset($vSavedValue[$iIndex]) ? $vSavedValue[$iIndex] : null;
                $aThisField['_is_multiple_fields'] = true;
            }
        } else {
            $aFields[0]['_saved_value'] = $vSavedValue;
            $aFields[0]['_is_multiple_fields'] = false;
        }
        unset($aThisField);
        foreach ($aFields as & $aThisField) {
            $aThisField['_is_value_set_by_user'] = isset($aThisField['value']);
            $aThisField['value'] = isset($aThisField['value']) ? $aThisField['value'] : (isset($aThisField['_saved_value']) ? $aThisField['_saved_value'] : (isset($aThisField['default']) ? $aThisField['default'] : null));
        }
        return $aFields;
    }
    private function _getStoredInputFieldValue($aField, $aOptions) {
        if (!isset($aField['section_id']) || '_default' == $aField['section_id']) {
            return isset($aOptions[$aField['field_id']]) ? $aOptions[$aField['field_id']] : null;
        }
        if (isset($aField['_section_index'])) {
            return isset($aOptions[$aField['section_id']][$aField['_section_index']][$aField['field_id']]) ? $aOptions[$aField['section_id']][$aField['_section_index']][$aField['field_id']] : null;
        }
        return isset($aOptions[$aField['section_id']][$aField['field_id']]) ? $aOptions[$aField['section_id']][$aField['field_id']] : null;
    }
}
abstract class Legull_AdminPageFramework_Input_Base extends Legull_AdminPageFramework_WPUtility {
    public $aField = array();
    public $aOptions = array();
    public $aStructureOptions = array('input_container_tag' => 'span', 'input_container_attributes' => array('class' => 'admin-page-framework-input-container',), 'label_container_tag' => 'span', 'label_container_attributes' => array('class' => 'admin-page-framework-input-label-string',),);
    public function __construct(array $aField, array $aOptions = array()) {
        $this->aField = $aField;
        $this->aOptions = $aOptions + $this->aStructureOptions;
    }
    public function get() {
    }
}
class Legull_AdminPageFramework_ExportOptions extends Legull_AdminPageFramework_CustomSubmitFields {
    public function __construct($aPostExport, $sClassName) {
        parent::__construct($aPostExport);
        $this->sClassName = $sClassName;
        $this->sFileName = $this->getSubmitValueByType($aPostExport, $this->sInputID, 'file_name');
        $this->sFormatType = $this->getSubmitValueByType($aPostExport, $this->sInputID, 'format');
        $this->bIsDataSet = $this->getSubmitValueByType($aPostExport, $this->sInputID, 'transient');
    }
    public function getTransientIfSet($vData) {
        if ($this->bIsDataSet) {
            $_tmp = $this->getTransient(md5("{$this->sClassName}_{$this->sInputID}"));
            if ($_tmp !== false) {
                $vData = $_tmp;
            }
        }
        return $vData;
    }
    public function getFileName() {
        return $this->sFileName;
    }
    public function getFormat() {
        return $this->sFormatType;
    }
    public function doExport($vData, $sFileName = null, $sFormatType = null) {
        $sFileName = isset($sFileName) ? $sFileName : $this->sFileName;
        $sFormatType = isset($sFormatType) ? $sFormatType : $this->sFormatType;
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $sFileName);
        switch (strtolower($sFormatType)) {
            case 'text':
                if (is_array($vData) || is_object($vData)) die(Legull_AdminPageFramework_Debug::getArray($vData, null, false));
                die($vData);
            case 'json':
                die(json_encode(( array )$vData));
            case 'array':
            default:
                die(serialize(( array )$vData));
            }
    }
}
class Legull_AdminPageFramework_ImportOptions extends Legull_AdminPageFramework_CustomSubmitFields {
    public function __construct($aFilesImport, $aPostImport) {
        parent::__construct($aPostImport);
        $this->aFilesImport = $aFilesImport;
    }
    private function getElementInFilesArray($aFilesImport, $sInputID, $sElementKey = 'error') {
        $sElementKey = strtolower($sElementKey);
        return isset($aFilesImport[$sElementKey][$sInputID]) ? $aFilesImport[$sElementKey][$sInputID] : null;
    }
    public function getError() {
        return $this->getElementInFilesArray($this->aFilesImport, $this->sInputID, 'error');
    }
    public function getType() {
        return $this->getElementInFilesArray($this->aFilesImport, $this->sInputID, 'type');
    }
    public function getImportData() {
        $sFilePath = $this->getElementInFilesArray($this->aFilesImport, $this->sInputID, 'tmp_name');
        $vData = file_exists($sFilePath) ? file_get_contents($sFilePath, true) : false;
        return $vData;
    }
    public function formatImportData(&$vData, $sFormatType = null) {
        $sFormatType = isset($sFormatType) ? $sFormatType : $this->getFormatType();
        switch (strtolower($sFormatType)) {
            case 'text':
                return;
            case 'json':
                $vData = json_decode(( string )$vData, true);
                return;
            case 'array':
            default:
                $vData = maybe_unserialize(trim($vData));
                return;
        }
    }
    public function getFormatType() {
        $this->sFormatType = isset($this->sFormatType) && $this->sFormatType ? $this->sFormatType : $this->getSubmitValueByType($this->aPost, $this->sInputID, 'format');
        return $this->sFormatType;
    }
}
class Legull_AdminPageFramework_Link_PostType extends Legull_AdminPageFramework_Link_Base {
    public $aFooterInfo = array('sLeft' => '', 'sRight' => '',);
    public function __construct($oProp, $oMsg = null) {
        if (!$oProp->bIsAdmin) {
            return;
        }
        $this->oProp = $oProp;
        $this->oMsg = $oMsg;
        if ($oProp->bIsAdminAjax) {
            return;
        }
        add_action('in_admin_footer', array($this, '_replyToSetFooterInfo'));
        if (isset($_GET['post_type']) && $_GET['post_type'] == $this->oProp->sPostType) {
            add_action('get_edit_post_link', array($this, '_replyToAddPostTypeQueryInEditPostLink'), 10, 3);
        }
        if ('plugins.php' === $this->oProp->sPageNow && 'plugin' === $this->oProp->aScriptInfo['sType']) {
            add_filter('plugin_action_links_' . plugin_basename($this->oProp->aScriptInfo['sPath']), array($this, '_replyToAddSettingsLinkInPluginListingPage'), 20);
        }
    }
    public function _replyToAddSettingsLinkInPluginListingPage($aLinks) {
        $_sLinkLabel = isset($this->oProp->aPostTypeArgs['labels']['plugin_listing_table_title_cell_link']) ? $this->oProp->aPostTypeArgs['labels']['plugin_listing_table_title_cell_link'] : $this->oMsg->get('manage');
        if (!$_sLinkLabel) {
            return $aLinks;
        }
        array_unshift($aLinks, "<a href='" . esc_url("edit.php?post_type={$this->oProp->sPostType}") . "'>" . $_sLinkLabel . "</a>");
        return $aLinks;
    }
    public function _replyToSetFooterInfo() {
        if (!$this->isPostDefinitionPage($this->oProp->sPostType) && !$this->isPostListingPage($this->oProp->sPostType) && !$this->isCustomTaxonomyPage($this->oProp->sPostType)) {
            return;
        }
        $this->_setFooterInfoLeft($this->oProp->aScriptInfo, $this->aFooterInfo['sLeft']);
        $this->_setFooterInfoRight($this->oProp->_getLibraryData(), $this->aFooterInfo['sRight']);
        add_filter('admin_footer_text', array($this, '_replyToAddInfoInFooterLeft'));
        add_filter('update_footer', array($this, '_replyToAddInfoInFooterRight'), 11);
    }
    public function _replyToAddPostTypeQueryInEditPostLink($sURL, $iPostID = null, $sContext = null) {
        return add_query_arg(array('post' => $iPostID, 'action' => 'edit', 'post_type' => $this->oProp->sPostType), $sURL);
    }
    public function _replyToAddInfoInFooterLeft($sLinkHTML = '') {
        if (empty($this->oProp->aScriptInfo['sName'])) {
            return $sLinkHTML;
        }
        return $this->aFooterInfo['sLeft'];
    }
    public function _replyToAddInfoInFooterRight($sLinkHTML = '') {
        return $this->aFooterInfo['sRight'];
    }
}
class Legull_AdminPageFramework_Link_NetworkAdmin extends Legull_AdminPageFramework_Link_Page {
    public $oProp;
    public function __construct(&$oProp, $oMsg = null) {
        if (!$oProp->bIsAdmin) {
            return;
        }
        $this->oProp = $oProp;
        $this->oMsg = $oMsg;
        if ($oProp->bIsAdminAjax) {
            return;
        }
        $this->oProp->sLabelPluginSettingsLink = null === $this->oProp->sLabelPluginSettingsLink ? $this->oMsg->get('settings') : $this->oProp->sLabelPluginSettingsLink;
        add_action('in_admin_footer', array($this, '_replyToSetFooterInfo'));
        if (in_array($this->oProp->sPageNow, array('plugins.php')) && 'plugin' == $this->oProp->aScriptInfo['sType']) {
            add_filter('network_admin_plugin_action_links_' . plugin_basename($this->oProp->aScriptInfo['sPath']), array($this, '_replyToAddSettingsLinkInPluginListingPage'));
        }
    }
    public function _addLinkToPluginTitle($asLinks) {
        static $_sPluginBaseName;
        if (!is_array($asLinks)) {
            $this->oProp->aPluginTitleLinks[] = $asLinks;
        } else {
            $this->oProp->aPluginTitleLinks = array_merge($this->oProp->aPluginTitleLinks, $asLinks);
        }
        if ('plugins.php' !== $this->oProp->sPageNow) {
            return;
        }
        if (!isset($_sPluginBaseName)) {
            $_sPluginBaseName = plugin_basename($this->oProp->aScriptInfo['sPath']);
            add_filter("network_admin_plugin_action_links_{$_sPluginBaseName}", array($this, '_replyToAddLinkToPluginTitle'));
        }
    }
}
class Legull_AdminPageFramework_HelpPane_MetaBox extends Legull_AdminPageFramework_HelpPane_Base {
    function __construct($oProp) {
        parent::__construct($oProp);
        if ($oProp->bIsAdminAjax) {
            return;
        }
        add_action('admin_head', array($this, '_replyToRegisterHelpTabTextForMetaBox'));
    }
    public function _addHelpText($sHTMLContent, $sHTMLSidebarContent = "") {
        $this->oProp->aHelpTabText[] = "<div class='contextual-help-description'>" . $sHTMLContent . "</div>";
        $this->oProp->aHelpTabTextSide[] = "<div class='contextual-help-description'>" . $sHTMLSidebarContent . "</div>";
    }
    public function _addHelpTextForFormFields($sFieldTitle, $sHelpText, $sHelpTextSidebar = "") {
        $this->_addHelpText("<span class='contextual-help-tab-title'>" . $sFieldTitle . "</span> - " . PHP_EOL . $sHelpText, $sHelpTextSidebar);
    }
    public function _replyToRegisterHelpTabTextForMetaBox() {
        if (!$this->_isInThePage()) {
            return false;
        }
        $this->_setHelpTab($this->oProp->sMetaBoxID, $this->oProp->sTitle, $this->oProp->aHelpTabText, $this->oProp->aHelpTabTextSide);
    }
    protected function _isInThePage() {
        if (!$this->oProp->bIsAdmin) {
            return false;
        }
        if (!in_array($this->oProp->sPageNow, array('post.php', 'post-new.php'))) {
            return false;
        }
        if (!in_array($this->oUtil->getCurrentPostType(), $this->oProp->aPostTypes)) {
            return false;
        }
        return true;
    }
}
class Legull_AdminPageFramework_FieldType_image extends Legull_AdminPageFramework_FieldType_Base {
    public $aFieldTypeSlugs = array('image',);
    protected $aDefaultKeys = array('attributes_to_store' => array(), 'show_preview' => true, 'allow_external_source' => true, 'attributes' => array('input' => array('size' => 40, 'maxlength' => 400,), 'button' => array(), 'remove_button' => array(), 'preview' => array(),),);
    public function _replyToFieldLoader() {
        $this->enqueueMediaUploader();
    }
    public function _replyToGetScripts() {
        return $this->_getScript_ImageSelector("admin_page_framework") . PHP_EOL . $this->_getScript_RegisterCallbacks();
    }
    protected function _getScript_RegisterCallbacks() {
        $_aJSArray = json_encode($this->aFieldTypeSlugs);
        return "jQuery(document).ready(function(){ jQuery().registerAPFCallback({ added_repeatable_field:function(node,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;if(node.find('.select_image').length<=0)return;var sValue=node.find('input').first().val();if(1!==iCallType||!sValue){ node.find('.image_preview').hide();node.find('.image_preview img').attr('src','') };var nodeFieldContainer=node.closest('.admin-page-framework-field'),iOccurrence=1===iCallType?1:0;nodeFieldContainer.nextAll().andSelf().each(function(iIndex){ var nodeButton=jQuery(this).find('.select_image');if(!(1===iCallType&&0!==iIndex)){ nodeButton.incrementIDAttribute('id',iOccurrence);jQuery(this).find('.image_preview').incrementIDAttribute('id',iOccurrence);jQuery(this).find('.image_preview img').incrementIDAttribute('id',iOccurrence) };var nodeImageInput=jQuery(this).find('.image-field input');if(nodeImageInput.length<=0)return true;var fExternalSource=jQuery(nodeButton).attr('data-enable_external_source');setAPFImageUploader(nodeImageInput.attr('id'),true,fExternalSource) }) },removed_repeatable_field:function(oNextFieldContainer,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;if(oNextFieldContainer.find('.select_image').length<=0)return;var iOccurrence=1===iCallType?1:0;oNextFieldContainer.nextAll().andSelf().each(function(iIndex){ var nodeButton=jQuery(this).find('.select_image');if(!(1===iCallType&&0!==iIndex)){ nodeButton.decrementIDAttribute('id',iOccurrence);jQuery(this).find('.image_preview').decrementIDAttribute('id',iOccurrence);jQuery(this).find('.image_preview img').decrementIDAttribute('id',iOccurrence) };var nodeImageInput=jQuery(this).find('.image-field input');if(nodeImageInput.length<=0)return true;var fExternalSource=jQuery(nodeButton).attr('data-enable_external_source');setAPFImageUploader(nodeImageInput.attr('id'),true,fExternalSource) }) },sorted_fields:function(node,sFieldType,sFieldsTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;if(node.find('.select_image').length<=0)return;var iCount=0,iOccurrence=1===iCallType?1:0;node.children('.admin-page-framework-field').each(function(){ var nodeButton=jQuery(this).find('.select_image');nodeButton.setIndexIDAttribute('id',iCount,iOccurrence);jQuery(this).find('.image_preview').setIndexIDAttribute('id',iCount,iOccurrence);jQuery(this).find('.image_preview img').setIndexIDAttribute('id',iCount,iOccurrence);var nodeImageInput=jQuery(this).find('.image-field input');if(nodeImageInput.length<=0)return true;setAPFImageUploader(nodeImageInput.attr('id'),true,jQuery(nodeButton).attr('data-enable_external_source'));iCount++ }) }}) });";
    }
    private function _getScript_ImageSelector($sReferrer) {
        $_sThickBoxTitle = esc_js($this->oMsg->get('upload_image'));
        $_sThickBoxButtonUseThis = esc_js($this->oMsg->get('use_this_image'));
        $_sInsertFromURL = esc_js($this->oMsg->get('insert_from_url'));
        if (!function_exists('wp_enqueue_media')) {
            return "setAPFImageUploader=function(sInputID,fMultiple,fExternalSource){ jQuery('#select_image_'+sInputID).unbind('click');jQuery('#select_image_'+sInputID).click(function(){ var sPressedID=jQuery(this).attr('id');window.sInputID=sPressedID.substring(13);window.original_send_to_editor=window.send_to_editor;window.send_to_editor=hfAPFSendToEditorImage;var fExternalSource=jQuery(this).attr('data-enable_external_source');tb_show('{$_sThickBoxTitle}','media-upload.php?post_id=1&amp;enable_external_source='+fExternalSource+'&amp;referrer={$sReferrer}&amp;button_label={$_sThickBoxButtonUseThis}&amp;type=image&amp;TB_iframe=true',false);return false }) };var hfAPFSendToEditorImage=function(sRawHTML){ var sHTML='<div>'+sRawHTML+'</div>',src=jQuery('img',sHTML).attr('src'),alt=jQuery('img',sHTML).attr('alt'),title=jQuery('img',sHTML).attr('title'),width=jQuery('img',sHTML).attr('width'),height=jQuery('img',sHTML).attr('height'),classes=jQuery('img',sHTML).attr('class'),id=classes?classes.replace(/(.*?)wp-image-/,''):'',sCaption=sRawHTML.replace(/\[(\w+).*?\](.*?)\[\/(\w+)\]/m,'$2').replace(/<a.*?>(.*?)<\/a>/m,''),align=sRawHTML.replace(/^.*?\[\w+.*?\salign=([\'\"])(.*?)[\'\"]\s.+$/mg,'$2'),link=jQuery(sHTML).find('a:first').attr('href'),sCaption=jQuery('<div/>').text(sCaption).html(),sAlt=jQuery('<div/>').text(alt).html(),title=jQuery('<div/>').text(title).html(),sInputID=window.sInputID;jQuery('#'+sInputID).val(src);jQuery('#'+sInputID+'_id').val(id);jQuery('#'+sInputID+'_width').val(width);jQuery('#'+sInputID+'_height').val(height);jQuery('#'+sInputID+'_caption').val(sCaption);jQuery('#'+sInputID+'_alt').val(sAlt);jQuery('#'+sInputID+'_title').val(title);jQuery('#'+sInputID+'_align').val(align);jQuery('#'+sInputID+'_link').val(link);jQuery('#image_preview_'+sInputID).attr('alt',alt);jQuery('#image_preview_'+sInputID).attr('title',title);jQuery('#image_preview_'+sInputID).attr('data-classes',classes);jQuery('#image_preview_'+sInputID).attr('data-id',id);jQuery('#image_preview_'+sInputID).attr('src',src);jQuery('#image_preview_container_'+sInputID).css('display','');jQuery('#image_preview_'+sInputID).show();window.send_to_editor=window.original_send_to_editor;tb_remove() };";
        }
        return "setAPFImageUploader=function(sInputID,fMultiple,fExternalSource){ var _bEscaped=false,_oCustomImageUploader;jQuery('#'+sInputID+'[data-show_preview=\"1\"]').unbind('change');jQuery('#'+sInputID+'[data-show_preview=\"1\"]').change(function(e){ var _sImageURL=jQuery(this).val();jQuery('<img>',{ src:_sImageURL,error:function(){  },load:function(){ setImagePreviewElement(sInputID,{ url:_sImageURL}) }}) });jQuery('#select_image_'+sInputID).unbind('click');jQuery('#select_image_'+sInputID).click(function(e){ var sInputID=jQuery(this).attr('id').substring(13);window.wpActiveEditor=null;e.preventDefault();if('object'===typeof _oCustomImageUploader){ _oCustomImageUploader.open();return };oAPFOriginalImageUploaderSelectObject=wp.media.view.MediaFrame.Select;wp.media.view.MediaFrame.Select=fExternalSource?getAPFCustomMediaUploaderSelectObject():oAPFOriginalImageUploaderSelectObject;_oCustomImageUploader=wp.media({ id:sInputID,title:fExternalSource?'{$_sInsertFromURL}':'{$_sThickBoxTitle}',button:{ text:'{$_sThickBoxButtonUseThis}'},type:'image',library:{ type:'image'},multiple:fMultiple,metadata:{ }});_oCustomImageUploader.on('escape',function(){ _bEscaped=true;return false });_oCustomImageUploader.on('close',function(){ var state=_oCustomImageUploader.state();if(typeof(state.props)!='undefined'&&typeof(state.props.attributes)!='undefined'){ var _oImage={ },_sKey;for(_sKey in state.props.attributes)_oImage[_sKey]=state.props.attributes[_sKey] };if(typeof _oImage!=='undefined'){ setImagePreviewElementWithDelay(sInputID,_oImage) }else { var _oNewField;_oCustomImageUploader.state().get('selection').each(function(oAttachment,iIndex){ var _oAttributes=oAttachment.hasOwnProperty('attributes')?oAttachment.attributes:{ };if(0===iIndex){ setImagePreviewElementWithDelay(sInputID,_oAttributes);return true };var _oFieldContainer='undefined'===typeof _oNewField?jQuery('#'+sInputID).closest('.admin-page-framework-field'):_oNewField;_oNewField=jQuery(this).addAPFRepeatableField(_oFieldContainer.attr('id'));var sInputIDOfNewField=_oNewField.find('input').attr('id');setImagePreviewElementWithDelay(sInputIDOfNewField,_oAttributes) }) };wp.media.view.MediaFrame.Select=oAPFOriginalImageUploaderSelectObject });_oCustomImageUploader.open();return false });var setImagePreviewElementWithDelay=function(sInputID,oImage,iMilliSeconds){ iMilliSeconds='undefined'===typeof iMilliSeconds?100:iMilliSeconds;setTimeout(function(){ if(!_bEscaped)setImagePreviewElement(sInputID,oImage);_bEscaped=false },iMilliSeconds) } };removeInputValuesForImage=function(oElem){ var _oImageInput=jQuery(oElem).closest('.admin-page-framework-field').find('.image-field input');if(_oImageInput.length<=0)return;var _sInputID=_oImageInput.first().attr('id');setImagePreviewElement(_sInputID,{ }) };setImagePreviewElement=function(sInputID,oImage){ var oImage=jQuery.extend(true,{ caption:'',alt:'',title:'',url:'',id:'',width:'',height:'',align:'',link:''},oImage),_sCaption=jQuery('<div/>').text(oImage.caption).html(),_sAlt=jQuery('<div/>').text(oImage.alt).html(),_sTitle=jQuery('<div/>').text(oImage.title).html();jQuery('input#'+sInputID).val(oImage.url);jQuery('input#'+sInputID+'_id').val(oImage.id);jQuery('input#'+sInputID+'_width').val(oImage.width);jQuery('input#'+sInputID+'_height').val(oImage.height);jQuery('input#'+sInputID+'_caption').val(_sCaption);jQuery('input#'+sInputID+'_alt').val(_sAlt);jQuery('input#'+sInputID+'_title').val(_sTitle);jQuery('input#'+sInputID+'_align').val(oImage.align);jQuery('input#'+sInputID+'_link').val(oImage.link);jQuery('#image_preview_'+sInputID).attr('data-id',oImage.id);jQuery('#image_preview_'+sInputID).attr('data-width',oImage.width);jQuery('#image_preview_'+sInputID).attr('data-height',oImage.height);jQuery('#image_preview_'+sInputID).attr('data-caption',_sCaption);jQuery('#image_preview_'+sInputID).attr('alt',_sAlt);jQuery('#image_preview_'+sInputID).attr('title',_sTitle);jQuery('#image_preview_'+sInputID).attr('src',oImage.url);if(oImage.url){ jQuery('#image_preview_container_'+sInputID).show() }else jQuery('#image_preview_container_'+sInputID).hide() };";
    }
    public function _replyToGetStyles() {
        return ".admin-page-framework-field .image_preview {border: none; clear:both; margin-top: 0.4em;margin-bottom: 0.8em;display: block; max-width: 100%;height: auto; width: inherit;} .admin-page-framework-field .image_preview img { height: auto; max-width: 100%;display: block; }.widget .admin-page-framework-field .image_preview {max-width: 100%;}@media only screen and ( max-width: 1200px ) {.admin-page-framework-field .image_preview {max-width: 600px;} } @media only screen and ( max-width: 900px ) {.admin-page-framework-field .image_preview {max-width: 440px;}}@media only screen and ( max-width: 600px ) {.admin-page-framework-field .image_preview {max-width: 300px;}} @media only screen and ( max-width: 480px ) {.admin-page-framework-field .image_preview {max-width: 240px;}}@media only screen and ( min-width: 1200px ) {.admin-page-framework-field .image_preview {max-width: 600px;}}.admin-page-framework-field-image input {margin-right: 0.5em;vertical-align: middle;}.select_image.button.button-small,.remove_image.button.button-small{ vertical-align: middle;}.remove_image.button.button-small {margin-left: 0.2em;}@media screen and (max-width: 782px) {.admin-page-framework-field-image input {margin: 0.5em 0.5em 0.5em 0;}} ";
    }
    public function _replyToGetField($aField) {
        $_aOutput = array();
        $_iCountAttributes = count(( array )$aField['attributes_to_store']);
        $_sCaptureAttribute = $_iCountAttributes ? 'url' : '';
        $_sImageURL = $_sCaptureAttribute ? (isset($aField['attributes']['value'][$_sCaptureAttribute]) ? $aField['attributes']['value'][$_sCaptureAttribute] : "") : $aField['attributes']['value'];
        $_aBaseAttributes = $aField['attributes'] + array('class' => null);
        unset($_aBaseAttributes['input'], $_aBaseAttributes['button'], $_aBaseAttributes['preview'], $_aBaseAttributes['name'], $_aBaseAttributes['value'], $_aBaseAttributes['type'], $_aBaseAttributes['remove_button']);
        $_aInputAttributes = array('name' => $aField['attributes']['name'] . ($_iCountAttributes ? "[url]" : ""), 'value' => $_sImageURL, 'type' => 'text', 'data-show_preview' => $aField['show_preview'],) + $aField['attributes']['input'] + $_aBaseAttributes;
        $_aButtonAtributes = $aField['attributes']['button'] + $_aBaseAttributes;
        $_aRemoveButtonAtributes = $aField['attributes']['remove_button'] + $_aBaseAttributes;
        $_aPreviewAtrributes = $aField['attributes']['preview'] + $_aBaseAttributes;
        $_aOutput[] = $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-input-container {$aField['type']}-field'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<input " . $this->generateAttributes($_aInputAttributes) . " />" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . $this->getExtraInputFields($aField) . "</label>" . "</div>" . $aField['after_label'] . $this->_getPreviewContainer($aField, $_sImageURL, $_aPreviewAtrributes) . $this->_getRemoveButtonScript($aField['input_id'], $_aRemoveButtonAtributes) . $this->_getUploaderButtonScript($aField['input_id'], $aField['repeatable'], $aField['allow_external_source'], $_aButtonAtributes);
        return implode(PHP_EOL, $_aOutput);
    }
    protected function getExtraInputFields(&$aField) {
        $_aOutputs = array();
        foreach (( array )$aField['attributes_to_store'] as $sAttribute) $_aOutputs[] = "<input " . $this->generateAttributes(array('id' => "{$aField['input_id']}_{$sAttribute}", 'type' => 'hidden', 'name' => "{$aField['_input_name']}[{$sAttribute}]", 'disabled' => isset($aField['attributes']['disabled']) && $aField['attributes']['disabled'] ? 'disabled' : null, 'value' => isset($aField['attributes']['value'][$sAttribute]) ? $aField['attributes']['value'][$sAttribute] : '',)) . "/>";
        return implode(PHP_EOL, $_aOutputs);
    }
    protected function _getPreviewContainer($aField, $sImageURL, $aPreviewAtrributes) {
        if (!$aField['show_preview']) {
            return '';
        }
        $sImageURL = $this->resolveSRC($sImageURL, true);
        return "<div " . $this->generateAttributes(array('id' => "image_preview_container_{$aField['input_id']}", 'class' => 'image_preview ' . (isset($aPreviewAtrributes['class']) ? $aPreviewAtrributes['class'] : ''), 'style' => ($sImageURL ? '' : "display: none; ") . (isset($aPreviewAtrributes['style']) ? $aPreviewAtrributes['style'] : ''),) + $aPreviewAtrributes) . ">" . "<img src='{$sImageURL}' " . "id='image_preview_{$aField['input_id']}' " . "/>" . "</div>";
    }
    protected function _getUploaderButtonScript($sInputID, $bRpeatable, $bExternalSource, array $aButtonAttributes) {
        $_bIsLabelSet = isset($aButtonAttributes['data-label']) && $aButtonAttributes['data-label'];
        $_bDashiconSupported = !$_bIsLabelSet && version_compare($GLOBALS['wp_version'], '3.8', '>=');
        $_sDashIconSelector = !$_bDashiconSupported ? '' : ($bRpeatable ? 'dashicons dashicons-images-alt2' : 'dashicons dashicons-format-image');
        $_aAttributes = array('id' => "select_image_{$sInputID}", 'href' => '#', 'data-uploader_type' => function_exists('wp_enqueue_media') ? 1 : 0, 'data-enable_external_source' => $bExternalSource ? 1 : 0,) + $aButtonAttributes + array('title' => $_bIsLabelSet ? $aButtonAttributes['data-label'] : $this->oMsg->get('select_image'),);
        $_aAttributes['class'] = $this->generateClassAttribute('select_image button button-small ', trim($aButtonAttributes['class']) ? $aButtonAttributes['class'] : $_sDashIconSelector);
        $_sButton = "<a " . $this->generateAttributes($_aAttributes) . ">" . ($_bIsLabelSet ? $aButtonAttributes['data-label'] : (strrpos($_aAttributes['class'], 'dashicons') ? '' : $this->oMsg->get('select_image'))) . "</a>";
        $_sButtonHTML = '"' . $_sButton . '"';
        $_sScript = "if(0===jQuery('a#select_image_{$sInputID}').length)jQuery('input#{$sInputID}').after($_sButtonHTML);jQuery(document).ready(function(){ setAPFImageUploader('{$sInputID}','{$bRpeatable}','{$bExternalSource}') });";
        return "<script type='text/javascript' class='admin-page-framework-image-uploader-button'>" . $_sScript . "</script>" . PHP_EOL;
    }
    protected function _getRemoveButtonScript($sInputID, array $aButtonAttributes) {
        if (!function_exists('wp_enqueue_media')) {
            return '';
        }
        $_bIsLabelSet = isset($aButtonAttributes['data-label']) && $aButtonAttributes['data-label'];
        $_bDashiconSupported = !$_bIsLabelSet && version_compare($GLOBALS['wp_version'], '3.8', '>=');
        $_sDashIconSelector = $_bDashiconSupported ? 'dashicons dashicons-dismiss' : '';
        $_aAttributes = array('id' => "remove_image_{$sInputID}", 'href' => '#', 'onclick' => esc_js("removeInputValuesForImage( this ); return false;"),) + $aButtonAttributes + array('title' => $_bIsLabelSet ? $aButtonAttributes['data-label'] : $this->oMsg->get('remove_value'),);
        $_aAttributes['class'] = $this->generateClassAttribute('remove_value remove_image button button-small', trim($aButtonAttributes['class']) ? $aButtonAttributes['class'] : $_sDashIconSelector);
        $_sButtonHTML = "<a " . $this->generateAttributes($_aAttributes) . ">" . ($_bIsLabelSet ? $_aAttributes['data-label'] : (strrpos($_aAttributes['class'], 'dashicons') ? '' : 'x')) . "</a>";
        $_sButtonHTML = '"' . $_sButtonHTML . '"';
        $_sScript = "if(0===jQuery('a#remove_image_{$sInputID}').length)jQuery('input#{$sInputID}').after($_sButtonHTML);";
        return "<script type='text/javascript' class='admin-page-framework-image-remove-button'>" . $_sScript . "</script>" . PHP_EOL;
    }
}
class Legull_AdminPageFramework_FieldType_checkbox extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('checkbox');
    protected $aDefaultKeys = array('select_all_button' => false, 'select_none_button' => false,);
    protected function getScripts() {
        new Legull_AdminPageFramework_Script_CheckboxSelector;
        return "jQuery(document).ready(function(){ jQuery('.admin-page-framework-checkbox-container[data-select_all_button]').each(function(){ jQuery(this).before('<div class=\"select_all_button_container\" onclick=\"jQuery( this ).selectALLAPFCheckboxes(); return false;\"><a class=\"select_all_button button button-small\">'+jQuery(this).data('select_all_button')+'</a></div>') });jQuery('.admin-page-framework-checkbox-container[data-select_none_button]').each(function(){ jQuery(this).before('<div class=\"select_none_button_container\" onclick=\"jQuery( this ).deselectAllAPFCheckboxes(); return false;\"><a class=\"select_all_button button button-small\">'+jQuery(this).data('select_none_button')+'</a></div>') }) });";
    }
    protected function getStyles() {
        return ".select_all_button_container, .select_none_button_container{display: inline-block;margin-bottom: 0.4em;}.admin-page-framework-checkbox-label {margin-top: 0.1em;}.admin-page-framework-field input[type='checkbox'] {margin-right: 0.5em;} .admin-page-framework-field-checkbox .admin-page-framework-input-label-container {padding-right: 1em;}.admin-page-framework-field-checkbox .admin-page-framework-input-label-string{display: inline; }";
    }
    protected $_sCheckboxClassSelector = 'apf_checkbox';
    protected function getField($aField) {
        $_aOutput = array();
        $_oCheckbox = new Legull_AdminPageFramework_Input_checkbox($aField);
        foreach ($this->getAsArray($aField['label']) as $_sKey => $_sLabel) {
            $_aInputAttributes = $_oCheckbox->getAttributeArray($_sKey);
            $_aInputAttributes['class'] = $this->generateClassAttribute($_aInputAttributes['class'], $this->_sCheckboxClassSelector);
            $_aOutput[] = $this->getFieldElementByKey($aField['before_label'], $_sKey) . "<div class='admin-page-framework-input-label-container admin-page-framework-checkbox-label' style='min-width: " . $this->sanitizeLength($aField['label_min_width']) . ";'>" . "<label " . $this->generateAttributes(array('for' => $_aInputAttributes['id'], 'class' => $_aInputAttributes['disabled'] ? 'disabled' : null,)) . ">" . $this->getFieldElementByKey($aField['before_input'], $_sKey) . $_oCheckbox->get($_sLabel, $_aInputAttributes) . $this->getFieldElementByKey($aField['after_input'], $_sKey) . "</label>" . "</div>" . $this->getFieldElementByKey($aField['after_label'], $_sKey);
        }
        $_aCheckboxContainerAttributes = array('class' => 'admin-page-framework-checkbox-container', 'data-select_all_button' => $aField['select_all_button'] ? (!is_string($aField['select_all_button']) ? $this->oMsg->get('select_all') : $aField['select_all_button']) : null, 'data-select_none_button' => $aField['select_none_button'] ? (!is_string($aField['select_none_button']) ? $this->oMsg->get('select_none') : $aField['select_none_button']) : null,);
        return "<div " . $this->generateAttributes($_aCheckboxContainerAttributes) . ">" . "<div class='repeatable-field-buttons'></div>" . implode(PHP_EOL, $_aOutput) . "</div>";
    }
}
class Legull_AdminPageFramework_FieldType_color extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('color');
    protected $aDefaultKeys = array('attributes' => array('size' => 10, 'maxlength' => 400, 'value' => 'transparent',),);
    protected function setUp() {
        if (version_compare($GLOBALS['wp_version'], '3.5', '>=')) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        } else {
            wp_enqueue_style('farbtastic');
            wp_enqueue_script('farbtastic');
        }
    }
    protected function getStyles() {
        return ".repeatable .colorpicker {display: inline;}.admin-page-framework-field-color .wp-picker-container {vertical-align: middle;}.admin-page-framework-field-color .ui-widget-content {border: none;background: none;color: transparent;}.admin-page-framework-field-color .ui-slider-vertical {width: inherit;height: auto;margin-top: -11px;}.admin-page-framework-field-color .admin-page-framework-field .admin-page-framework-input-label-container {vertical-align: top; }.admin-page-framework-field-color .admin-page-framework-repeatable-field-buttons {margin-top: 0;}";
    }
    protected function getScripts() {
        $_aJSArray = json_encode($this->aFieldTypeSlugs);
        $_sDoubleQuote = '\"';
        return "registerAPFColorPickerField=function(osTragetInput){ var osTargetInput=typeof osTragetInput==='string'?'#'+osTragetInput:osTragetInput,sInputID=typeof osTragetInput==='string'?osTragetInput:osTragetInput.attr('id');'use strict';if('object'===typeof jQuery.wp&&'function'===typeof jQuery.wp.wpColorPicker){ { var aColorPickerOptions={ defaultColor:false,change:function(event,ui){  },clear:function(){  },hide:true,palettes:true};jQuery(osTargetInput).wpColorPicker(aColorPickerOptions) } }else jQuery('#color_'+sInputID).farbtastic(osTargetInput) };jQuery(document).ready(function(){ jQuery().registerAPFCallback({ added_repeatable_field:function(node,sFieldType,sFieldTagID,sCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;var nodeNewColorInput=node.find('input.input_color');if(nodeNewColorInput.length<=0)return;var nodeIris=node.find('.wp-picker-container').first();if(nodeIris.length>0)var nodeNewColorInput=nodeNewColorInput.clone();var sInputID=nodeNewColorInput.attr('id'),sInputValue=nodeNewColorInput.val()?nodeNewColorInput.val():'transparent',sInputStyle=sInputValue!='transparent'&&nodeNewColorInput.attr('style')?nodeNewColorInput.attr('style'):'';nodeNewColorInput.val(sInputValue);nodeNewColorInput.attr('style',sInputStyle);if(nodeIris.length>0){ jQuery(nodeIris).replaceWith(nodeNewColorInput) }else node.find('.colorpicker').replaceWith('<div class=\"colorpicker\" id=\"color_'+sInputID+'\"></div>');registerAPFColorPickerField(nodeNewColorInput) }}) });";
    }
    protected function getField($aField) {
        $aField['attributes'] = array('color' => $aField['value'], 'type' => 'text', 'class' => trim('input_color ' . $aField['attributes']['class']),) + $aField['attributes'];
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<input " . $this->generateAttributes($aField['attributes']) . " />" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "<div class='colorpicker' id='color_{$aField['input_id']}'></div>" . $this->_getColorPickerEnablerScript("{$aField['input_id']}") . "</div>" . $aField['after_label'];
    }
    private function _getColorPickerEnablerScript($sInputID) {
        $_sScript = "jQuery(document).ready(function(){ registerAPFColorPickerField('{$sInputID}') });";
        return "<script type='text/javascript' class='color-picker-enabler-script'>" . $_sScript . "</script>";
    }
}
class Legull_AdminPageFramework_FieldType_default extends Legull_AdminPageFramework_FieldType {
    public $aDefaultKeys = array();
    public function _replyToGetField($aField) {
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . $aField['value'] . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label'];
    }
}
class Legull_AdminPageFramework_FieldType_hidden extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('hidden');
    protected $aDefaultKeys = array();
    protected function getField($aField) {
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<input " . $this->generateAttributes($aField['attributes']) . " />" . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label'];
    }
}
class Legull_AdminPageFramework_FieldType_radio extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('radio');
    protected $aDefaultKeys = array('label' => array(), 'attributes' => array(),);
    protected function getStyles() {
        return ".admin-page-framework-field input[type='radio'] {margin-right: 0.5em;} .admin-page-framework-field-radio .admin-page-framework-input-label-container {padding-right: 1em;} .admin-page-framework-field-radio .admin-page-framework-input-container {display: inline;} .admin-page-framework-field-radio .admin-page-framework-input-label-string{display: inline; }";
    }
    protected function getScripts() {
        $_aJSArray = json_encode($this->aFieldTypeSlugs);
        return "jQuery(document).ready(function(){ jQuery().registerAPFCallback({ added_repeatable_field:function(nodeField,sFieldType,sFieldTagID,sCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;nodeField.closest('.admin-page-framework-fields').find('input[type=radio][checked=checked]').attr('checked','checked');nodeField.find('input[type=radio]').change(function(){ jQuery(this).closest('.admin-page-framework-field').find('input[type=radio]').attr('checked',false);jQuery(this).attr('checked','checked') }) }}) });";
    }
    protected function getField($aField) {
        $_aOutput = array();
        $_oRadio = new Legull_AdminPageFramework_Input_radio($aField);
        foreach ($aField['label'] as $_sKey => $_sLabel) {
            $_aInputAttributes = $_oRadio->getAttributeArray($_sKey);
            $_aOutput[] = $this->getFieldElementByKey($aField['before_label'], $_sKey) . "<div class='admin-page-framework-input-label-container admin-page-framework-radio-label' style='min-width: " . $this->sanitizeLength($aField['label_min_width']) . ";'>" . "<label " . $this->generateAttributes(array('for' => $_aInputAttributes['id'], 'class' => $_aInputAttributes['disabled'] ? 'disabled' : null,)) . ">" . $this->getFieldElementByKey($aField['before_input'], $_sKey) . $_oRadio->get($_sLabel, $_aInputAttributes) . $this->getFieldElementByKey($aField['after_input'], $_sKey) . "</label>" . "</div>" . $this->getFieldElementByKey($aField['after_label'], $_sKey);
        }
        $_aOutput[] = $this->_getUpdateCheckedScript($aField['input_id']);
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getUpdateCheckedScript($sInputID) {
        $_sScript = "jQuery(document).ready(function(){ jQuery('input[type=radio][data-id=\"{$sInputID}\"]').change(function(){ jQuery(this).closest('.admin-page-framework-field').find('input[type=radio][data-id=\"{$sInputID}\"]').attr('checked',false);jQuery(this).attr('checked','checked') }) });";
        return "<script type='text/javascript' class='radio-button-checked-attribute-updater'>" . $_sScript . "</script>";
    }
}
class Legull_AdminPageFramework_FieldType_section_title extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('section_title',);
    protected $aDefaultKeys = array('label_min_width' => 30, 'attributes' => array('size' => 20, 'maxlength' => 100,),);
    protected function getStyles() {
        return ".admin-page-framework-section-tab .admin-page-framework-field-section_title {padding: 0.5em;} .admin-page-framework-section-tab .admin-page-framework-field-section_title .admin-page-framework-input-label-string { vertical-align: middle; }.admin-page-framework-section-tab .admin-page-framework-fields {display: inline-block;} .admin-page-framework-field.admin-page-framework-field-section_title {float: none;} .admin-page-framework-field.admin-page-framework-field-section_title input {background-color: #fff;color: #333;border-color: #ddd;box-shadow: inset 0 1px 2px rgba(0,0,0,.07);border-width: 1px;border-style: solid;outline: 0;box-sizing: border-box;vertical-align: middle;}";
    }
    protected function getField($aField) {
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<input " . $this->generateAttributes(array('type' => 'text') + $aField['attributes']) . " />" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label'];
    }
}
class Legull_AdminPageFramework_FieldType_select extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('select',);
    protected $aDefaultKeys = array('label' => array(), 'is_multiple' => false, 'attributes' => array('select' => array('size' => 1, 'autofocusNew' => null, 'multiple' => null, 'required' => null,), 'optgroup' => array(), 'option' => array(),),);
    protected function getStyles() {
        return ".admin-page-framework-field-select .admin-page-framework-input-label-container {vertical-align: top; }.admin-page-framework-field-select .admin-page-framework-input-label-container {padding-right: 1em;}";
    }
    protected function getField($aField) {
        $_oSelectInput = new Legull_AdminPageFramework_Input_select($aField);
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-select-label' style='min-width: " . $this->sanitizeLength($aField['label_min_width']) . ";'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . $_oSelectInput->get() . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label'];
    }
}
class Legull_AdminPageFramework_FieldType_submit extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('submit',);
    protected $aDefaultKeys = array('redirect_url' => null, 'href' => null, 'reset' => null, 'email' => null, 'attributes' => array('class' => 'button button-primary',),);
    protected function getStyles() {
        return ".admin-page-framework-field input[type='submit'] {margin-bottom: 0.5em;}";
    }
    protected function getField($aField) {
        $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->get('submit');
        if (isset($aField['attributes']['src'])) {
            $aField['attributes']['src'] = $this->resolveSRC($aField['attributes']['src']);
        }
        $_bIsImageButton = isset($aField['attributes']['src']) && filter_var($aField['attributes']['src'], FILTER_VALIDATE_URL);
        $_aInputAttributes = array('type' => $_bIsImageButton ? 'image' : 'submit', 'value' => ($_sValue = $this->_getInputFieldValueFromLabel($aField)),) + $aField['attributes'] + array('title' => $_sValue, 'alt' => $_bIsImageButton ? 'submit' : '',);
        $_aLabelAttributes = array('style' => $aField['label_min_width'] ? "min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";" : null, 'for' => $_aInputAttributes['id'], 'class' => $_aInputAttributes['disabled'] ? 'disabled' : null,);
        $_aLabelContainerAttributes = array('style' => $aField['label_min_width'] ? "min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";" : null, 'class' => 'admin-page-framework-input-label-container admin-page-framework-input-button-container admin-page-framework-input-container',);
        return $aField['before_label'] . "<div " . $this->generateAttributes($_aLabelContainerAttributes) . ">" . $this->_getExtraFieldsBeforeLabel($aField) . "<label " . $this->generateAttributes($_aLabelAttributes) . ">" . $aField['before_input'] . $this->_getExtraInputFields($aField) . "<input " . $this->generateAttributes($_aInputAttributes) . " />" . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label'];
    }
    protected function _getExtraFieldsBeforeLabel(&$aField) {
        return '';
    }
    protected function _getExtraInputFields(&$aField) {
        $_aOutput = array();
        $_aOutput[] = "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][input_id]", 'value' => $aField['input_id'],)) . " />";
        $_aOutput[] = "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][field_id]", 'value' => $aField['field_id'],)) . " />";
        $_aOutput[] = "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][name]", 'value' => $aField['_input_name_flat'],)) . " />";
        $_aOutput[] = "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][section_id]", 'value' => isset($aField['section_id']) && '_default' !== $aField['section_id'] ? $aField['section_id'] : '',)) . " />";
        if ($aField['redirect_url']) {
            $_aOutput[] = "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][redirect_url]", 'value' => $aField['redirect_url'],)) . " />";
        }
        if ($aField['href']) {
            $_aOutput[] = "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][link_url]", 'value' => $aField['href'],)) . " />";
        }
        if ($aField['reset']) {
            $_aOutput[] = !$this->_checkConfirmationDisplayed($aField['_input_name_flat'], 'reset') ? "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][is_reset]", 'value' => '1',)) . " />" : "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][reset_key]", 'value' => is_array($aField['reset']) ? implode('|', $aField['reset']) : $aField['reset'],)) . " />";
        }
        if (!empty($aField['email'])) {
            $this->setTransient('apf_em_' . md5($aField['_input_name_flat'] . get_current_user_id()), $aField['email']);
            $_aOutput[] = !$this->_checkConfirmationDisplayed($aField['_input_name_flat'], 'email') ? "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][confirming_sending_email]", 'value' => '1',)) . " />" : "<input " . $this->generateAttributes(array('type' => 'hidden', 'name' => "__submit[{$aField['input_id']}][confirmed_sending_email]", 'value' => '1',)) . " />";
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _checkConfirmationDisplayed($sFlatFieldName, $sType = 'reset') {
        switch ($sType) {
            default:
            case 'reset':
                $_sTransientKey = 'apf_rc_' . md5($sFlatFieldName . get_current_user_id());
            break;
            case 'email':
                $_sTransientKey = 'apf_ec_' . md5($sFlatFieldName . get_current_user_id());
            break;
        }
        $_bConfirmed = false === $this->getTransient($_sTransientKey) ? false : true;
        if ($_bConfirmed) {
            $this->deleteTransient($_sTransientKey);
        }
        return $_bConfirmed;
    }
    protected function _getInputFieldValueFromLabel($aField) {
        if (isset($aField['value']) && $aField['value'] != '') {
            return $aField['value'];
        }
        if (isset($aField['label'])) {
            return $aField['label'];
        }
        if (isset($aField['default'])) {
            return $aField['default'];
        }
    }
}
class Legull_AdminPageFramework_FieldType_export extends Legull_AdminPageFramework_FieldType_submit {
    public $aFieldTypeSlugs = array('export',);
    protected $aDefaultKeys = array('data' => null, 'format' => 'json', 'file_name' => null, 'attributes' => array('class' => 'button button-primary',),);
    protected function setUp() {
    }
    protected function getScripts() {
        return "";
    }
    protected function getStyles() {
        return "";
    }
    protected function getField($aField) {
        if (isset($aField['data'])) {
            $this->setTransient(md5("{$aField['class_name']}_{$aField['input_id']}"), $aField['data'], 60 * 2);
        }
        $aField['attributes']['name'] = "__export[submit][{$aField['input_id']}]";
        $aField['file_name'] = $aField['file_name'] ? $aField['file_name'] : $this->_generateExportFileName($aField['option_key'] ? $aField['option_key'] : $aField['class_name'], $aField['format']);
        $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->get('export');
        return parent::getField($aField);
    }
    protected function _getExtraInputFields(&$aField) {
        $_aAttributes = array('type' => 'hidden');
        return "<input " . $this->generateAttributes(array('name' => "__export[{$aField['input_id']}][input_id]", 'value' => $aField['input_id'],) + $_aAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__export[{$aField['input_id']}][field_id]", 'value' => $aField['field_id'],) + $_aAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__export[{$aField['input_id']}][section_id]", 'value' => isset($aField['section_id']) && $aField['section_id'] != '_default' ? $aField['section_id'] : '',) + $_aAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__export[{$aField['input_id']}][file_name]", 'value' => $aField['file_name'],) + $_aAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__export[{$aField['input_id']}][format]", 'value' => $aField['format'],) + $_aAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__export[{$aField['input_id']}][transient]", 'value' => isset($aField['data']),) + $_aAttributes) . "/>";
    }
    private function _generateExportFileName($sOptionKey, $sExportFormat = 'json') {
        switch (trim(strtolower($sExportFormat))) {
            case 'text':
                $sExt = "txt";
            break;
            case 'json':
                $sExt = "json";
            break;
            case 'array':
            default:
                $sExt = "txt";
            break;
        }
        return $sOptionKey . '_' . date("Ymd") . '.' . $sExt;
    }
}
class Legull_AdminPageFramework_FieldType_import extends Legull_AdminPageFramework_FieldType_submit {
    public $aFieldTypeSlugs = array('import',);
    protected $aDefaultKeys = array('option_key' => null, 'format' => 'json', 'is_merge' => false, 'attributes' => array('class' => 'button button-primary', 'file' => array('accept' => 'audio/*|video/*|image/*|MIME_type', 'class' => 'import', 'type' => 'file',), 'submit' => array('class' => 'import button button-primary', 'type' => 'submit',),),);
    protected function setUp() {
    }
    protected function getScripts() {
        return "";
    }
    protected function getStyles() {
        return ".admin-page-framework-field-import input {margin-right: 0.5em;}.admin-page-framework-field-import label,.form-table td fieldset.admin-page-framework-fieldset .admin-page-framework-field-import label { display: inline; }";
    }
    protected function getField($aField) {
        $aField['attributes']['name'] = "__import[submit][{$aField['input_id']}]";
        $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->get('import');
        return parent::getField($aField);
    }
    protected function _getExtraFieldsBeforeLabel(&$aField) {
        return "<input " . $this->generateAttributes(array('id' => "{$aField['input_id']}_file", 'type' => 'file', 'name' => "__import[{$aField['input_id']}]",) + $aField['attributes']['file']) . " />";
    }
    protected function _getExtraInputFields(&$aField) {
        $aHiddenAttributes = array('type' => 'hidden',);
        return "<input " . $this->generateAttributes(array('name' => "__import[{$aField['input_id']}][input_id]", 'value' => $aField['input_id'],) + $aHiddenAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__import[{$aField['input_id']}][field_id]", 'value' => $aField['field_id'],) + $aHiddenAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__import[{$aField['input_id']}][section_id]", 'value' => isset($aField['section_id']) && $aField['section_id'] != '_default' ? $aField['section_id'] : '',) + $aHiddenAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__import[{$aField['input_id']}][is_merge]", 'value' => $aField['is_merge'],) + $aHiddenAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__import[{$aField['input_id']}][option_key]", 'value' => $aField['option_key'],) + $aHiddenAttributes) . "/>" . "<input " . $this->generateAttributes(array('name' => "__import[{$aField['input_id']}][format]", 'value' => $aField['format'],) + $aHiddenAttributes) . "/>";
    }
}
class Legull_AdminPageFramework_FieldType_system extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('system',);
    protected $aDefaultKeys = array('data' => array(), 'print_type' => 1, 'attributes' => array('rows' => 60, 'autofocus' => null, 'disabled' => null, 'formNew' => null, 'maxlength' => null, 'placeholder' => null, 'readonly' => 'readonly', 'required' => null, 'wrap' => null, 'style' => null, 'onclick' => 'this.focus();this.select()',),);
    protected function construct() {
    }
    protected function setUp() {
    }
    protected function getEnqueuingScripts() {
        return array();
    }
    protected function getEnqueuingStyles() {
        return array();
    }
    protected function getScripts() {
        $aJSArray = json_encode($this->aFieldTypeSlugs);
        return "jQuery(document).ready(function(){ jQuery().registerAPFCallback({ added_repeatable_field:function(oCopiedNode,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$aJSArray)<=-1)return;var nodeNewAutoComplete=oCopiedNode.find('input.autocomplete');if(nodeNewAutoComplete.length<=0)return }}) });";
    }
    protected function getIEStyles() {
        return '';
    }
    protected function getStyles() {
        return ".admin-page-framework-field-system {width: 100%;}.admin-page-framework-field-system .admin-page-framework-input-label-container {width: 100%;}.admin-page-framework-field-system textarea {background-color: #f9f9f9; width: 97%; outline: 0; font-family: Consolas, Monaco, monospace;white-space: pre;word-wrap: normal;overflow-x: scroll;}";
    }
    protected function getField($aField) {
        $_aInputAttributes = $aField['attributes'];
        $_aInputAttributes['class'].= ' system';
        unset($_aInputAttributes['value']);
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<textarea " . $this->generateAttributes($_aInputAttributes) . " >" . esc_textarea($this->_getSystemInfomation($aField['value'], $aField['data'], $aField['print_type'])) . "</textarea>" . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label'];
    }
    private function _getSystemInfomation($asValue = null, $asCustomData = null, $iPrintType = 1) {
        if (isset($asValue)) {
            return $asValue;
        }
        global $wpdb;
        $_aData = $this->getAsArray($asCustomData);
        $_aData = $_aData + array('Admin Page Framework' => isset($_aData['Admin Page Framework']) ? null : Legull_AdminPageFramework_Registry::getInfo(), 'WordPress' => isset($_aData['WordPress']) ? null : $this->_getSiteInfo(), 'PHP' => isset($_aData['PHP']) ? null : $this->_getPHPInfo(), 'PHP Error Log' => isset($_aData['PHP Error Log']) ? null : $this->_getPHPErrorLog(), 'MySQL' => isset($_aData['MySQL']) ? null : $this->getMySQLInfo(), 'MySQL Error Log' => isset($_aData['MySQL Error Log']) ? null : $this->_getMySQLErrorLog(), 'Server' => isset($_aData['Server']) ? null : $this->_getWebServerInfo(), 'Browser' => isset($_aData['Browser']) ? null : $this->_getClientInfo(),);
        $_aOutput = array();
        foreach ($_aData as $_sSection => $_aInfo) {
            if (empty($_aInfo)) {
                continue;
            }
            switch ($iPrintType) {
                default:
                case 1:
                    $_aOutput[] = $this->getReadableArrayContents($_sSection, $_aInfo, 32) . PHP_EOL;
                break;
                case 2:
                    $_aOutput[] = "[{$_sSection}]" . PHP_EOL . print_r($_aInfo, true) . PHP_EOL;
                break;
            }
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getClientInfo() {
        $_aBrowser = @ini_get('browscap') ? get_browser($_SERVER['HTTP_USER_AGENT'], true) : array();
        unset($_aBrowser['browser_name_regex']);
        return empty($_aBrowser) ? __('No browser information found.', 'admin-page-framework') : $_aBrowser;
    }
    private function _getPHPErrorLog() {
        $_sLog = $this->getPHPErrorLog(200);
        return empty($_sLog) ? __('No log found.', 'admin-page-framework') : $_sLog;
    }
    private function _getMySQLErrorLog() {
        $_sLog = $this->getMySQLErrorLog(200);
        return empty($_sLog) ? __('No log found.', 'admin-page-framework') : $_sLog;
    }
    static private $_aSiteInfo;
    private function _getSiteInfo() {
        if (isset(self::$_aSiteInfo)) {
            return self::$_aSiteInfo;
        }
        global $wpdb;
        self::$_aSiteInfo = array(__('Version', 'admin-page-framework') => $GLOBALS['wp_version'], __('Language', 'admin-page-framework') => (defined('WPLANG') && WPLANG ? WPLANG : 'en_US'), __('Memory Limit', 'admin-page-framework') => $this->getReadableBytes($this->getNumberOfReadableSize(WP_MEMORY_LIMIT)), __('Multi-site', 'admin-page-framework') => $this->_getYesOrNo(is_multisite()), __('Permalink Structure', 'admin-page-framework') => get_option('permalink_structure'), __('Active Theme', 'admin-page-framework') => $this->_getActiveThemeName(), __('Registered Post Statuses', 'admin-page-framework') => implode(', ', get_post_stati()), 'WP_DEBUG' => $this->_getEnabledOrDisabled(defined('WP_DEBUG') && WP_DEBUG), 'WP_DEBUG_LOG' => $this->_getEnabledOrDisabled(defined('WP_DEBUG_LOG') && WP_DEBUG_LOG), 'WP_DEBUG_DISPLAY' => $this->_getEnabledOrDisabled(defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY), __('Table Prefix', 'admin-page-framework') => $wpdb->prefix, __('Table Prefix Length', 'admin-page-framework') => strlen($wpdb->prefix), __('Table Prefix Status', 'admin-page-framework') => strlen($wpdb->prefix) > 16 ? __('Too Long', 'admin-page-framework') : __('Acceptable', 'admin-page-frmework'), 'wp_remote_post()' => $this->_getWPRemotePostStatus(), 'wp_remote_get()' => $this->_getWPRemoteGetStatus(), __('WP_CONTENT_DIR Writeable', 'admin-page-framework') => $this->_getYesOrNo(is_writable(WP_CONTENT_DIR)), __('Active Plugins', 'admin-page-framework') => PHP_EOL . $this->_getActivePlugins(), __('Network Active Plugins', 'admin-page-framework') => PHP_EOL . $this->_getNetworkActivePlugins(), __('Constants', 'admin-page-framework') => $this->getDefinedConstants('user'),);
        return self::$_aSiteInfo;
    }
    private function _getActiveThemeName() {
        if (version_compare($GLOBALS['wp_version'], '3.4', '<')) {
            $_aThemeData = get_theme_data(get_stylesheet_directory() . '/style.css');
            return $_aThemeData['Name'] . ' ' . $_aThemeData['Version'];
        }
        $_oThemeData = wp_get_theme();
        return $_oThemeData->Name . ' ' . $_oThemeData->Version;
    }
    private function _getActivePlugins() {
        $_aPluginList = array();
        $_aActivePlugins = get_option('active_plugins', array());
        foreach (get_plugins() as $_sPluginPath => $_aPlugin) {
            if (!in_array($_sPluginPath, $_aActivePlugins)) {
                continue;
            }
            $_aPluginList[] = '    ' . $_aPlugin['Name'] . ': ' . $_aPlugin['Version'];
        }
        return implode(PHP_EOL, $_aPluginList);
    }
    private function _getNetworkActivePlugins() {
        if (!is_multisite()) {
            return '';
        }
        $_aPluginList = array();
        $_aActivePlugins = get_site_option('active_sitewide_plugins', array());
        foreach (wp_get_active_network_plugins() as $_sPluginPath) {
            if (!array_key_exists(plugin_basename($_sPluginPath), $_aActivePlugins)) {
                continue;
            }
            $_aPlugin = get_plugin_data($_sPluginPath);
            $_aPluginList[] = '    ' . $_aPlugin['Name'] . ' :' . $_aPlugin['Version'];
        }
        return implode(PHP_EOL, $_aPluginList);
    }
    private function _getWPRemotePostStatus() {
        $_vResponse = $this->getTransient('apf_rp_check');
        $_vResponse = false === $_vResponse ? wp_remote_post(add_query_arg($_GET, admin_url($GLOBALS['pagenow'])), array('sslverify' => false, 'timeout' => 60, 'body' => array('apf_remote_request_test' => '_testing', 'cmd' => '_notify-validate'),)) : $_vResponse;
        $this->setTransient('apf_rp_check', $_vResponse, 60);
        return $this->_getFunctionalOrNot(!is_wp_error($_vResponse) && $_vResponse['response']['code'] >= 200 && $_vResponse['response']['code'] < 300);
    }
    private function _getWPRemoteGetStatus() {
        $_vResponse = $this->getTransient('apf_rg_check');
        $_vResponse = false === $_vResponse ? wp_remote_get(add_query_arg($_GET + array('apf_remote_request_test' => '_testing'), admin_url($GLOBALS['pagenow'])), array('sslverify' => false, 'timeout' => 60,)) : $_vResponse;
        $this->setTransient('apf_rg_check', $_vResponse, 60);
        return $this->_getFunctionalOrNot(!is_wp_error($_vResponse) && $_vResponse['response']['code'] >= 200 && $_vResponse['response']['code'] < 300);
    }
    static private $_aPHPInfo;
    private function _getPHPInfo() {
        if (isset(self::$_aPHPInfo)) {
            return self::$_aPHPInfo;
        }
        $_oErrorReporting = new Legull_AdminPageFramework_ErrorReporting;
        self::$_aPHPInfo = array(__('Version', 'admin-page-framework') => phpversion(), __('Safe Mode', 'admin-page-framework') => $this->_getYesOrNo(@ini_get('safe_mode')), __('Memory Limit', 'admin-page-framework') => @ini_get('memory_limit'), __('Upload Max Size', 'admin-page-framework') => @ini_get('upload_max_filesize'), __('Post Max Size', 'admin-page-framework') => @ini_get('post_max_size'), __('Upload Max File Size', 'admin-page-framework') => @ini_get('upload_max_filesize'), __('Max Execution Time', 'admin-page-framework') => @ini_get('max_execution_time'), __('Max Input Vars', 'admin-page-framework') => @ini_get('max_input_vars'), __('Argument Separator', 'admin-page-framework') => @ini_get('arg_separator.output'), __('Allow URL File Open', 'admin-page-framework') => $this->_getYesOrNo(@ini_get('allow_url_fopen')), __('Display Errors', 'admin-page-framework') => $this->_getOnOrOff(@ini_get('display_errors')), __('Log Errors', 'admin-page-framework') => $this->_getOnOrOff(@ini_get('log_errors')), __('Error log location', 'admin-page-framework') => @ini_get('error_log'), __('Error Reporting Level', 'admin-page-framework') => $_oErrorReporting->getErrorLevel(), __('FSOCKOPEN', 'admin-page-framework') => $this->_getSupportedOrNot(function_exists('fsockopen')), __('cURL', 'admin-page-framework') => $this->_getSupportedOrNot(function_exists('curl_init')), __('SOAP', 'admin-page-framework') => $this->_getSupportedOrNot(class_exists('SoapClient')), __('SUHOSIN', 'admin-page-framework') => $this->_getSupportedOrNot(extension_loaded('suhosin')), 'ini_set()' => $this->_getSupportedOrNot(function_exists('ini_set')),) + $this->getPHPInfo() + array(__('Constants', 'admin-page-framework') => $this->getDefinedConstants(null, 'user'));
        return self::$_aPHPInfo;
    }
    private function _getWebServerInfo() {
        return array(__('Web Server', 'admin-page-framework') => $_SERVER['SERVER_SOFTWARE'], 'SSL' => $this->_getYesOrNo(is_ssl()), __('Session', 'admin-page-framework') => $this->_getEnabledOrDisabled(isset($_SESSION)), __('Session Name', 'admin-page-framework') => esc_html(@ini_get('session.name')), __('Session Cookie Path', 'admin-page-framework') => esc_html(@ini_get('session.cookie_path')), __('Session Save Path', 'admin-page-framework') => esc_html(@ini_get('session.save_path')), __('Session Use Cookies', 'admin-page-framework') => $this->_getOnOrOff(@ini_get('session.use_cookies')), __('Session Use Only Cookies', 'admin-page-framework') => $this->_getOnOrOff(@ini_get('session.use_only_cookies')),) + $_SERVER;
    }
    private function _getYesOrNo($bBoolean) {
        return $bBoolean ? __('Yes', 'admin-page-framework') : __('No', 'admin-page-framework');
    }
    private function _getEnabledOrDisabled($bBoolean) {
        return $bBoolean ? __('Enabled', 'admin-page-framework') : __('Disabled', 'admin-page-framework');
    }
    private function _getOnOrOff($bBoolean) {
        return $bBoolean ? __('On', 'admin-page-framework') : __('Off', 'admin-page-framework');
    }
    private function _getSupportedOrNot($bBoolean) {
        return $bBoolean ? __('Supported', 'admin-page-framework') : __('Not supported', 'admin-page-framework');
    }
    private function _getFunctionalOrNot($bBoolean) {
        return $bBoolean ? __('Functional', 'admin-page-framework') : __('Not functional', 'admin-page-framework');
    }
}
class Legull_AdminPageFramework_FieldType_taxonomy extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('taxonomy',);
    protected $aDefaultKeys = array('taxonomy_slugs' => 'category', 'height' => '250px', 'max_width' => '100%', 'show_post_count' => true, 'attributes' => array(), 'select_all_button' => true, 'select_none_button' => true, 'label_no_term_found' => null, 'label_list_title' => '', 'query' => array('child_of' => 0, 'parent' => '', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false, 'hierarchical' => true, 'number' => '', 'pad_counts' => false, 'exclude' => array(), 'exclude_tree' => array(), 'include' => array(), 'fields' => 'all', 'slug' => '', 'get' => '', 'name__like' => '', 'description__like' => '', 'offset' => '', 'search' => '', 'cache_domain' => 'core',), 'queries' => array(),);
    protected function setUp() {
        new Legull_AdminPageFramework_Script_CheckboxSelector;
    }
    protected function getScripts() {
        $_aJSArray = json_encode($this->aFieldTypeSlugs);
        return "var enableAPFTabbedBox=function(nodeTabBoxContainer){ jQuery(nodeTabBoxContainer).each(function(){ jQuery(this).find('.tab-box-tab').each(function(i){ if(0===i)jQuery(this).addClass('active');jQuery(this).click(function(e){ e.preventDefault();jQuery(this).siblings('li.active').removeClass('active');jQuery(this).addClass('active');var thisTab=jQuery(this).find('a').attr('href');active_content=jQuery(this).closest('.tab-box-container').find(thisTab).css('display','block');active_content.siblings().css('display','none') }) }) }) };jQuery(document).ready(function(){ enableAPFTabbedBox(jQuery('.tab-box-container'));jQuery().registerAPFCallback({ added_repeatable_field:function(oClonedField,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;oClonedField.nextAll().andSelf().each(function(){ jQuery(this).find('div').incrementIDAttribute('id');jQuery(this).find('li.tab-box-tab a').incrementIDAttribute('href');jQuery(this).find('li.category-list').incrementIDAttribute('id');enableAPFTabbedBox(jQuery(this).find('.tab-box-container')) }) },removed_repeatable_field:function(oNextFieldConainer,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;oNextFieldConainer.nextAll().andSelf().each(function(){ jQuery(this).find('div').decrementIDAttribute('id');jQuery(this).find('li.tab-box-tab a').decrementIDAttribute('href');jQuery(this).find('li.category-list').decrementIDAttribute('id') }) }}) });";
    }
    protected function getStyles() {
        return ".admin-page-framework-field .taxonomy-checklist li { margin: 8px 0 8px 20px; }.admin-page-framework-field div.taxonomy-checklist {padding: 8px 0 8px 10px;margin-bottom: 20px;}.admin-page-framework-field .taxonomy-checklist ul {list-style-type: none;margin: 0;}.admin-page-framework-field .taxonomy-checklist ul ul {margin-left: 1em;}.admin-page-framework-field .taxonomy-checklist-label {white-space: nowrap; }.admin-page-framework-field .tab-box-container.categorydiv {max-height: none;}.admin-page-framework-field .tab-box-tab-text {display: inline-block;}.admin-page-framework-field .tab-box-tabs {line-height: 12px;margin-bottom: 0;}.admin-page-framework-field .tab-box-tabs .tab-box-tab.active {display: inline;border-color: #dfdfdf #dfdfdf #fff;margin-bottom: 0px;padding-bottom: 2px;background-color: #fff;}.admin-page-framework-field .tab-box-container { position: relative; width: 100%; clear: both;margin-bottom: 1em;}.admin-page-framework-field .tab-box-tabs li a { color: #333; text-decoration: none; }.admin-page-framework-field .tab-box-contents-container {padding: 0 0 0 1.8em;padding: 0.55em 0.5em 0.55em 1.8em;border: 1px solid #dfdfdf; background-color: #fff;}.admin-page-framework-field .tab-box-contents { overflow: hidden; overflow-x: hidden; position: relative; top: -1px; height: 300px;}.admin-page-framework-field .tab-box-content { display: none; overflow: auto; display: block; position: relative; overflow-x: hidden;}.admin-page-framework-field .tab-box-content .taxonomychecklist {margin-right: 3.2em;}.admin-page-framework-field .tab-box-content:target, .admin-page-framework-field .tab-box-content:target, .admin-page-framework-field .tab-box-content:target { display: block; }.admin-page-framework-field .tab-box-content .select_all_button_container, .admin-page-framework-field .tab-box-content .select_none_button_container{margin-top: 0.8em;}.admin-page-framework-field .taxonomychecklist .children {margin-top: 6px;margin-left: 1em;}";
    }
    protected function getIEStyles() {
        return ".tab-box-content { display: block; }.tab-box-contents { overflow: hidden;position: relative; }b { position: absolute; top: 0px; right: 0px; width:1px; height: 251px; overflow: hidden; text-indent: -9999px; }";
    }
    protected function getField($aField) {
        $aTabs = array();
        $aCheckboxes = array();
        $aField['label_no_term_found'] = isset($aField['label_no_term_found']) ? $aField['label_no_term_found'] : $this->oMsg->get('no_term_found');
        $_aCheckboxContainerAttributes = array('class' => 'admin-page-framework-checkbox-container', 'data-select_all_button' => $aField['select_all_button'] ? (!is_string($aField['select_all_button']) ? $this->oMsg->get('select_all') : $aField['select_all_button']) : null, 'data-select_none_button' => $aField['select_none_button'] ? (!is_string($aField['select_none_button']) ? $this->oMsg->get('select_none') : $aField['select_none_button']) : null,);
        foreach (( array )$aField['taxonomy_slugs'] as $sKey => $sTaxonomySlug) {
            $aInputAttributes = isset($aField['attributes'][$sKey]) && is_array($aField['attributes'][$sKey]) ? $aField['attributes'][$sKey] + $aField['attributes'] : $aField['attributes'];
            $aTabs[] = "<li class='tab-box-tab'>" . "<a href='#tab_{$aField['input_id']}_{$sKey}'>" . "<span class='tab-box-tab-text'>" . $this->_getLabelFromTaxonomySlug($sTaxonomySlug) . "</span>" . "</a>" . "</li>";
            $aCheckboxes[] = "<div id='tab_{$aField['input_id']}_{$sKey}' class='tab-box-content' style='height: {$aField['height']};'>" . $this->getFieldElementByKey($aField['before_label'], $sKey) . "<div " . $this->generateAttributes($_aCheckboxContainerAttributes) . "></div>" . "<ul class='list:category taxonomychecklist form-no-clear'>" . wp_list_categories(array('walker' => new Legull_AdminPageFramework_WalkerTaxonomyChecklist, 'name' => is_array($aField['taxonomy_slugs']) ? "{$aField['_input_name']}[{$sTaxonomySlug}]" : $aField['_input_name'], 'selected' => $this->_getSelectedKeyArray($aField['value'], $sTaxonomySlug), 'echo' => false, 'taxonomy' => $sTaxonomySlug, 'input_id' => $aField['input_id'], 'attributes' => $aInputAttributes, 'show_post_count' => $aField['show_post_count'], 'show_option_none' => $aField['label_no_term_found'], 'title_li' => $aField['label_list_title'],) + (isset($aField['queries'][$sTaxonomySlug]) ? $aField['queries'][$sTaxonomySlug] : array()) + $aField['query']) . "</ul>" . "<!--[if IE]><b>.</b><![endif]-->" . $this->getFieldElementByKey($aField['after_label'], $sKey) . "</div>";
        }
        $sTabs = "<ul class='tab-box-tabs category-tabs'>" . implode(PHP_EOL, $aTabs) . "</ul>";
        $sContents = "<div class='tab-box-contents-container'>" . "<div class='tab-box-contents' style='height: {$aField['height']};'>" . implode(PHP_EOL, $aCheckboxes) . "</div>" . "</div>";
        return '' . "<div id='tabbox-{$aField['field_id']}' class='tab-box-container categorydiv' style='max-width:{$aField['max_width']};'>" . $sTabs . PHP_EOL . $sContents . PHP_EOL . "</div>";
    }
    private function _getSelectedKeyArray($vValue, $sTaxonomySlug) {
        $vValue = ( array )$vValue;
        if (!isset($vValue[$sTaxonomySlug])) {
            return array();
        }
        if (!is_array($vValue[$sTaxonomySlug])) {
            return array();
        }
        return array_keys($vValue[$sTaxonomySlug], true);
    }
    private function _getLabelFromTaxonomySlug($sTaxonomySlug) {
        $_oTaxonomy = get_taxonomy($sTaxonomySlug);
        return isset($_oTaxonomy->label) ? $_oTaxonomy->label : null;
    }
}
class Legull_AdminPageFramework_FieldType_text extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('text', 'password', 'date', 'datetime', 'datetime-local', 'email', 'month', 'search', 'tel', 'url', 'week',);
    protected $aDefaultKeys = array('attributes' => array('maxlength' => 400,),);
    protected function getStyles() {
        return ".admin-page-framework-field-text .admin-page-framework-field .admin-page-framework-input-label-container {vertical-align: top; }";
    }
    protected function getField($aField) {
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<input " . $this->generateAttributes($aField['attributes']) . " />" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label'];
    }
}
class Legull_AdminPageFramework_FieldType_file extends Legull_AdminPageFramework_FieldType_text {
    public $aFieldTypeSlugs = array('file',);
    protected $aDefaultKeys = array('attributes' => array('accept' => 'audio/*|video/*|image/*|MIME_type',),);
    protected function setUp() {
    }
    protected function getScripts() {
        return "";
    }
    protected function getStyles() {
        return "";
    }
    protected function getField($aField) {
        return parent::getField($aField);
    }
}
class Legull_AdminPageFramework_FieldType_number extends Legull_AdminPageFramework_FieldType_text {
    public $aFieldTypeSlugs = array('number', 'range');
    protected $aDefaultKeys = array('attributes' => array('size' => 30, 'maxlength' => 400, 'class' => null, 'min' => null, 'max' => null, 'step' => null, 'readonly' => null, 'required' => null, 'placeholder' => null, 'list' => null, 'autofocus' => null, 'autocomplete' => null,),);
    protected function getStyles() {
        return "";
    }
}
class Legull_AdminPageFramework_FieldType_textarea extends Legull_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('textarea');
    protected $aDefaultKeys = array('rich' => false, 'attributes' => array('autofocus' => null, 'cols' => 60, 'disabled' => null, 'formNew' => null, 'maxlength' => null, 'placeholder' => null, 'readonly' => null, 'required' => null, 'rows' => 4, 'wrap' => null,),);
    public function getScripts() {
        $_aJSArray = json_encode($this->aFieldTypeSlugs);
        return "jQuery(document).ready(function(){ jQuery('link#editor-buttons-css').appendTo('#wpwrap');var isHandleable=function(oField,sFieldType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return false;if('object'!==typeof tinyMCEPreInit)return;return true },removeEditor=function(sTextAreaID){ if('object'!==typeof tinyMCEPreInit)return;var oTextArea=jQuery('#'+sTextAreaID),sTextAreaValue=oTextArea.val();tinyMCE.execCommand('mceRemoveEditor',false,sTextAreaID);delete tinyMCEPreInit.mceInit[sTextAreaID];delete tinyMCEPreInit.qtInit[sTextAreaID];oTextArea.val(sTextAreaValue) },updateEditor=function(sTextAreaID,oTinyMCESettings,oQickTagSettings){ removeEditor(sTextAreaID);var aTMCSettings=jQuery.extend({ },oTinyMCESettings,{ selector:'#'+sTextAreaID,body_class:sTextAreaID,height:'100px',setup:function(ed){ if(tinymce.majorVersion>=4){ ed.on('change',function(){ jQuery('#'+this.id).val(this.getContent());jQuery('#'+this.id).html(this.getContent()) }) }else ed.onChange.add(function(ed,l){ jQuery('#'+ed.id).val(ed.getContent());jQuery('#'+ed.id).html(ed.getContent()) }) }}),aQTSettings=jQuery.extend({ },oQickTagSettings,{ id:sTextAreaID});tinyMCEPreInit.mceInit[sTextAreaID]=aTMCSettings;tinyMCEPreInit.qtInit[sTextAreaID]=aQTSettings;QTags.instances[aQTSettings.id]=aQTSettings;quicktags(aQTSettings);QTags._buttonsInit();window.tinymce.dom.Event.domLoaded=true;tinyMCE.init(aTMCSettings);jQuery(this).find('.wp-editor-wrap').first().on('click.wp-editor',function(){ if(this.id)window.wpActiveEditor=this.id.slice(3,-5) }) },shouldEmpty=function(iCallType,iIndex,iCountNextAll,iSectionIndex){ if(0===iCallType)return(0===iCountNextAll||0===iIndex);return(0===iSectionIndex) };jQuery().registerAPFCallback({ added_repeatable_field:function(oCopied,sFieldType,sFieldTagID,iCallType,iSectionIndex,iFieldIndex){ if(!isHandleable(oCopied,sFieldType))return;var oTextAreas=oCopied.find('textarea.wp-editor-area');if(oTextAreas.length<=0)return;var oWrap=oCopied.find('.wp-editor-wrap');if(oWrap.length<=0)return;var oSettings=jQuery().getAPFInputOptions(oWrap.attr('data-id')),iOccurrence=1===iCallType?1:0,oFieldsNextAll=oCopied.closest('.admin-page-framework-field').nextAll();oFieldsNextAll.andSelf().each(function(iIndex){ var oWrap=jQuery(this).find('.wp-editor-wrap');if(oWrap.length<=0)return true;var oTextArea=jQuery(this).find('textarea.wp-editor-area').first().clone().show().removeAttr('aria-hidden');if(shouldEmpty(iCallType,iIndex,oFieldsNextAll.length,iSectionIndex)){ oTextArea.val('');oTextArea.empty() };var oEditorContainer=jQuery(this).find('.wp-editor-container').first().clone().empty(),oToolBar=jQuery(this).find('.wp-editor-tools').first().clone();oWrap.empty().prepend(oEditorContainer.prepend(oTextArea.show())).prepend(oToolBar);updateEditor(oTextArea.attr('id'),oSettings.TinyMCE,oSettings.QuickTags);oToolBar.find('a,div').incrementIDAttribute('id',iOccurrence);jQuery(this).find('.wp-editor-wrap a').incrementIDAttribute('data-editor',iOccurrence);jQuery(this).find('.wp-editor-wrap,.wp-editor-tools,.wp-editor-container').incrementIDAttribute('id',iOccurrence);if(0===iCallType)jQuery(this).find('a.wp-switch-editor').trigger('click');if(1===iCallType)return false }) },removed_repeatable_field:function(oNextFieldContainer,sFieldType,sFieldTagID,iCallType,iSectionIndex,iFieldIndex){ if(!isHandleable(oNextFieldContainer,sFieldType))return;var oWrap=oNextFieldContainer.find('.wp-editor-wrap');if(oWrap.length<=0){ removeEditor(sFieldTagID.substring(6));return };var oSettings=jQuery().getAPFInputOptions(oWrap.attr('data-id')),iOccurrence=1===iCallType?1:0;oNextFieldContainer.closest('.admin-page-framework-field').nextAll().andSelf().each(function(iIndex){ var oWrap=jQuery(this).find('.wp-editor-wrap');if(oWrap.length<=0)return true;var oTextArea=jQuery(this).find('textarea.wp-editor-area').first().show().removeAttr('aria-hidden'),oEditorContainer=jQuery(this).find('.wp-editor-container').first().clone().empty(),oToolBar=jQuery(this).find('.wp-editor-tools').first().clone(),oTextAreaPrevious=oTextArea.clone().incrementIDAttribute('id',iOccurrence);oWrap.empty().prepend(oEditorContainer.prepend(oTextArea.show())).prepend(oToolBar);if(0===iIndex)removeEditor(oTextAreaPrevious.attr('id'));updateEditor(oTextArea.attr('id'),oSettings.TinyMCE,oSettings.QuickTags);oToolBar.find('a,div').decrementIDAttribute('id',iOccurrence);jQuery(this).find('.wp-editor-wrap a').decrementIDAttribute('data-editor',iOccurrence);jQuery(this).find('.wp-editor-wrap,.wp-editor-tools,.wp-editor-container').decrementIDAttribute('id',iOccurrence);if(0===iCallType)jQuery(this).find('a.wp-switch-editor').trigger('click');if(1===iCallType)return false }) },stopped_sorting_fields:function(oSortedFields,sFieldType,sFieldsTagID,iCallType){ if(!isHandleable(oSortedFields,sFieldType))return;var iOccurrence=1===iCallType?1:0;oSortedFields.children('.admin-page-framework-field').each(function(iIndex){ var oTextAreas=jQuery(this).find('textarea.wp-editor-area');if(oTextAreas.length<=0)return true;var oWrap=jQuery(this).find('.wp-editor-wrap');if(oWrap.length<=0)return true;var oSettings=jQuery().getAPFInputOptions(oWrap.attr('data-id')),oTextArea=jQuery(this).find('textarea.wp-editor-area').first().show().removeAttr('aria-hidden'),oEditorContainer=jQuery(this).find('.wp-editor-container').first().clone().empty(),oToolBar=jQuery(this).find('.wp-editor-tools').first().clone();oWrap.empty().prepend(oEditorContainer.prepend(oTextArea.show())).prepend(oToolBar);updateEditor(oTextArea.attr('id'),oSettings.TinyMCE,oSettings.QuickTags);oToolBar.find('a,div').setIndexIDAttribute('id',iIndex,iOccurrence);jQuery(this).find('.wp-editor-wrap a').setIndexIDAttribute('data-editor',iIndex,iOccurrence);jQuery(this).find('.wp-editor-wrap,.wp-editor-tools,.wp-editor-container').setIndexIDAttribute('id',iIndex,iOccurrence);jQuery(this).find('a.wp-switch-editor').trigger('click') }) },saved_widget:function(oWidget){ if('object'!==typeof tinyMCEPreInit)return;var _sWidgetInitialTextareaID;jQuery(oWidget).find('.admin-page-framework-field').each(function(iIndex){ var oTextAreas=jQuery(this).find('textarea.wp-editor-area');if(oTextAreas.length<=0)return true;var oWrap=jQuery(this).find('.wp-editor-wrap');if(oWrap.length<=0)return true;var oTextArea=jQuery(this).find('textarea.wp-editor-area').first(),_sID=oTextArea.attr('id'),_sInitialTextareaID=_sID.replace(/(widget-.+-)([0-9]+)(-)/i,'$1__i__$3');_sWidgetInitialTextareaID='undefined'===typeof tinyMCEPreInit.mceInit[_sInitialTextareaID]?_sWidgetInitialTextareaID:_sInitialTextareaID;if('undefined'===typeof tinyMCEPreInit.mceInit[_sWidgetInitialTextareaID])return true;updateEditor(oTextArea.attr('id'),tinyMCEPreInit.mceInit[_sWidgetInitialTextareaID],tinyMCEPreInit.qtInit[_sWidgetInitialTextareaID]);jQuery().storeAPFInputOptions(oWrap.attr('data-id'),{ TinyMCE:tinyMCEPreInit.mceInit[_sWidgetInitialTextareaID],QuickTags:tinyMCEPreInit.qtInit[_sWidgetInitialTextareaID]}) }) }}) });";
    }
    protected function getStyles() {
        return ".admin-page-framework-field-textarea .admin-page-framework-input-label-string {vertical-align: top;margin-top: 2px;} .admin-page-framework-field-textarea .wp-core-ui.wp-editor-wrap {margin-bottom: 0.5em;}.admin-page-framework-field-textarea.admin-page-framework-field .admin-page-framework-input-label-container {vertical-align: top; } .postbox .admin-page-framework-field-textarea .admin-page-framework-input-label-container {width: 100%;}";
    }
    protected function getField($aField) {
        return "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . $this->_getEditor($aField) . "<div class='repeatable-field-buttons'></div>" . $aField['after_input'] . "</label>" . "</div>";
    }
    private function _getEditor($aField) {
        unset($aField['attributes']['value']);
        if (empty($aField['rich']) || !version_compare($GLOBALS['wp_version'], '3.3', '>=') || !function_exists('wp_editor')) {
            return "<textarea " . $this->generateAttributes($aField['attributes']) . " >" . esc_textarea($aField['value']) . "</textarea>";
        }
        ob_start();
        wp_editor($aField['value'], $aField['attributes']['id'], $this->uniteArrays(( array )$aField['rich'], array('wpautop' => true, 'media_buttons' => true, 'textarea_name' => $aField['attributes']['name'], 'textarea_rows' => $aField['attributes']['rows'], 'tabindex' => '', 'tabfocus_elements' => ':prev,:next', 'editor_css' => '', 'editor_class' => $aField['attributes']['class'], 'teeny' => false, 'dfw' => false, 'tinymce' => true, 'quicktags' => true)));
        $_sContent = ob_get_contents();
        ob_end_clean();
        return $_sContent . $this->_getScriptForRichEditor($aField['attributes']['id']);
    }
    private function _getScriptForRichEditor($sIDSelector) {
        $_sScript = "jQuery(document).ready(function(){ jQuery('#wp-{$sIDSelector}-wrap').attr('data-id','{$sIDSelector}');if('object'!==typeof tinyMCEPreInit)return;jQuery().storeAPFInputOptions('{$sIDSelector}',{ TinyMCE:tinyMCEPreInit.mceInit['{$sIDSelector}'],QuickTags:tinyMCEPreInit.qtInit['{$sIDSelector}']}) });";
        return "<script type='text/javascript' class='admin-page-framework-textarea-enabler'>" . $_sScript . "</script>";
    }
}
class Legull_AdminPageFramework_FormTable_Base extends Legull_AdminPageFramework_FormOutput {
    public function __construct($aFieldTypeDefinitions, array $aFieldErrors, $oMsg = null) {
        $this->aFieldTypeDefinitions = $aFieldTypeDefinitions;
        $this->aFieldErrors = $aFieldErrors;
        $this->oMsg = $oMsg ? $oMsg : Legull_AdminPageFramework_Message::getInstance();
        $this->_loadScripts();
    }
    static private $_bIsLoadedTabPlugin;
    private function _loadScripts() {
        if (self::$_bIsLoadedTabPlugin) {
            return;
        }
        self::$_bIsLoadedTabPlugin = true;
        new Legull_AdminPageFramework_Script_Tab;
    }
    protected function _getSectionTitle($sTitle, $sTag, $aFields, $hfFieldCallback) {
        $_aSectionTitleField = $this->_getSectionTitleField($aFields);
        return $_aSectionTitleField ? call_user_func_array($hfFieldCallback, array($_aSectionTitleField)) : "<{$sTag}>" . $sTitle . "</{$sTag}>";
    }
    private function _getSectionTitleField(array $aFields) {
        foreach ($aFields as $_aField) {
            if ('section_title' === $_aField['type']) {
                return $_aField;
            }
        }
    }
    protected function _getCollapsibleArgument(array $aSections = array(), $iSectionIndex = 0) {
        foreach ($aSections as $_aSection) {
            if (!isset($_aSection['collapsible'])) {
                continue;
            }
            if (empty($_aSection['collapsible'])) {
                return array();
            }
            $_aSection['collapsible']['toggle_all_button'] = $this->_sanitizeToggleAllButtonArgument($_aSection['collapsible']['toggle_all_button'], $_aSection);
            return $_aSection['collapsible'];
        }
        return array();
    }
    private function _sanitizeToggleAllButtonArgument($sToggleAll, array $aSection) {
        if (!$aSection['repeatable']) {
            return $sToggleAll;
        }
        if ($aSection['_is_first_index'] && $aSection['_is_last_index']) {
            return $sToggleAll;
        }
        if (!$aSection['_is_first_index'] && !$aSection['_is_last_index']) {
            return 0;
        }
        $_aToggleAll = true === $sToggleAll || 1 === $sToggleAll ? array('top-right', 'bottom-right') : explode(',', $sToggleAll);
        if ($aSection['_is_first_index']) {
            $_aToggleAll = $this->dropElementByValue($_aToggleAll, array(1, true, 0, false, 'bottom-right', 'bottom-left'));
        }
        if ($aSection['_is_last_index']) {
            $_aToggleAll = $this->dropElementByValue($_aToggleAll, array(1, true, 0, false, 'top-right', 'top-left'));
        }
        $_aToggleAll = empty($_aToggleAll) ? array(0) : $_aToggleAll;
        return implode(',', $_aToggleAll);
    }
    protected function _getCollapsibleSectionTitleBlock(array $aCollapsible, $sContainer = 'sections', array $aFields = array(), $hfFieldCallback = null) {
        if (empty($aCollapsible)) {
            return '';
        }
        if ($sContainer !== $aCollapsible['container']) {
            return '';
        }
        return $this->_getCollapsibleSectionsEnablerScript() . "<div " . $this->generateAttributes(array('class' => $this->generateClassAttribute('admin-page-framework-section-title', 'accordion-section-title', 'admin-page-framework-collapsible-title', 'sections' === $aCollapsible['container'] ? 'admin-page-framework-collapsible-sections-title' : 'admin-page-framework-collapsible-section-title', $aCollapsible['is_collapsed'] ? 'collapsed' : ''),) + $this->getDataAttributeArray($aCollapsible)) . ">" . $this->_getSectionTitle($aCollapsible['title'], 'h3', $aFields, $hfFieldCallback) . "</div>";
    }
    static private $_bLoadedTabEnablerScript = false;
    protected function _getSectionTabsEnablerScript() {
        if (self::$_bLoadedTabEnablerScript) {
            return '';
        }
        self::$_bLoadedTabEnablerScript = true;
        $_sScript = "jQuery(document).ready(function(){ jQuery('.admin-page-framework-section-tabs-contents').createTabs() });";
        return "<script type='text/javascript' class='admin-page-framework-section-tabs-script'>" . $_sScript . "</script>";
    }
    static private $_bLoadedCollapsibleSectionsEnablerScript = false;
    protected function _getCollapsibleSectionsEnablerScript() {
        if (self::$_bLoadedCollapsibleSectionsEnablerScript) {
            return;
        }
        self::$_bLoadedCollapsibleSectionsEnablerScript = true;
        new Legull_AdminPageFramework_Script_CollapsibleSection($this->oMsg);
    }
    static private $_aSetContainerIDsForRepeatableSections = array();
    protected function _getRepeatableSectionsEnablerScript($sContainerTagID, $iSectionCount, $aSettings) {
        if (empty($aSettings)) {
            return '';
        }
        if (in_array($sContainerTagID, self::$_aSetContainerIDsForRepeatableSections)) {
            return '';
        }
        self::$_aSetContainerIDsForRepeatableSections[$sContainerTagID] = $sContainerTagID;
        new Legull_AdminPageFramework_Script_RepeatableSection($this->oMsg);
        $aSettings = $this->getAsArray($aSettings) + array('min' => 0, 'max' => 0);
        $_sAdd = $this->oMsg->get('add_section');
        $_sRemove = $this->oMsg->get('remove_section');
        $_sVisibility = $iSectionCount <= 1 ? " style='display:none;'" : "";
        $_sSettingsAttributes = $this->generateDataAttributes($aSettings);
        $_sButtons = "<div class='admin-page-framework-repeatable-section-buttons' {$_sSettingsAttributes} >" . "<a class='repeatable-section-remove button-secondary repeatable-section-button button button-large' href='#' title='{$_sRemove}' {$_sVisibility} data-id='{$sContainerTagID}'>-</a>" . "<a class='repeatable-section-add button-secondary repeatable-section-button button button-large' href='#' title='{$_sAdd}' data-id='{$sContainerTagID}'>+</a>" . "</div>";
        $_sButtonsHTML = '"' . $_sButtons . '"';
        $_aJSArray = json_encode($aSettings);
        $_sScript = "jQuery(document).ready(function(){ jQuery('#{$sContainerTagID} .admin-page-framework-section-caption').each(function(){ jQuery(this).show();var _oButtons=jQuery($_sButtonsHTML);if(jQuery(this).children('.admin-page-framework-collapsible-section-title').children('fieldset').length>0)_oButtons.addClass('section_title_field_sibling');var _oCollapsibleSectionTitle=jQuery(this).find('.admin-page-framework-collapsible-section-title');if(_oCollapsibleSectionTitle.length){ { _oButtons.find('.repeatable-section-button').removeClass('button-large');_oCollapsibleSectionTitle.prepend(_oButtons) } }else jQuery(this).prepend(_oButtons) });jQuery('#{$sContainerTagID}').updateAPFRepeatableSections($_aJSArray) });";
        return "<script type='text/javascript' class='admin-page-framework-seciton-repeatable-script'>" . $_sScript . "</script>";
    }
}
class Legull_AdminPageFramework_Input_checkbox extends Legull_AdminPageFramework_Input_Base {
    public function get() {
        $_aParams = func_get_args() + array(0 => '', 1 => array());
        $_sLabel = $_aParams[0];
        $_aAttributes = $_aParams[1];
        return "<{$this->aOptions['input_container_tag']} " . $this->generateAttributes($this->aOptions['input_container_attributes']) . ">" . "<input " . $this->generateAttributes(array('type' => 'hidden', 'class' => $_aAttributes['class'], 'name' => $_aAttributes['name'], 'value' => '0',)) . " />" . "<input " . $this->generateAttributes($_aAttributes) . " />" . "</{$this->aOptions['input_container_tag']}>" . "<{$this->aOptions['label_container_tag']} " . $this->generateAttributes($this->aOptions['label_container_attributes']) . ">" . $_sLabel . "</{$this->aOptions['label_container_tag']}>";
    }
    public function getAttributeArray() {
        $_aParams = func_get_args() + array(0 => '',);
        $_sKey = $_aParams[0];
        return $this->getElement($this->aField['attributes'], $_sKey, array()) + array('type' => 'checkbox', 'id' => $this->aField['input_id'] . '_' . $_sKey, 'checked' => $this->getCorrespondingArrayValue($this->aField['attributes']['value'], $_sKey, null) ? 'checked' : null, 'value' => 1, 'name' => is_array($this->aField['label']) ? "{$this->aField['attributes']['name']}[{$_sKey}]" : $this->aField['attributes']['name'], 'data-id' => $this->aField['input_id'],) + $this->aField['attributes'];
    }
}
class Legull_AdminPageFramework_Input_radio extends Legull_AdminPageFramework_Input_Base {
    public function get() {
        $_aParams = func_get_args() + array(0 => '', 1 => array());
        $_sLabel = $_aParams[0];
        $_aAttributes = $_aParams[1];
        return "<{$this->aOptions['input_container_tag']} " . $this->generateAttributes($this->aOptions['input_container_attributes']) . ">" . "<input " . $this->generateAttributes($_aAttributes) . " />" . "</{$this->aOptions['input_container_tag']}>" . "<{$this->aOptions['label_container_tag']} " . $this->generateAttributes($this->aOptions['label_container_attributes']) . ">" . $_sLabel . "</{$this->aOptions['label_container_tag']}>";
    }
    public function getAttributeArray() {
        $_aParams = func_get_args() + array(0 => '',);
        $sKey = $_aParams[0];
        return $this->getElement($this->aField['attributes'], $sKey, array()) + array('type' => 'radio', 'checked' => isset($this->aField['attributes']['value']) && $this->aField['attributes']['value'] == $sKey ? 'checked' : null, 'value' => $sKey, 'id' => $this->aField['input_id'] . '_' . $sKey, 'data-default' => $this->aField['default'], 'data-id' => $this->aField['input_id'],) + $this->aField['attributes'];
    }
}
class Legull_AdminPageFramework_Input_select extends Legull_AdminPageFramework_Input_Base {
    public $aStructureOptions = array('input_container_tag' => 'span', 'input_container_attributes' => array('class' => 'admin-page-framework-input-container',), 'label_container_tag' => 'span', 'label_container_attributes' => array('class' => 'admin-page-framework-input-label-string',),);
    public function get() {
        $_bIsMultiple = $this->aField['is_multiple'] ? true : ($this->aField['attributes']['select']['multiple'] ? true : false);
        $_aSelectTagAttributes = $this->uniteArrays($this->aField['attributes']['select'], array('id' => $this->aField['input_id'], 'multiple' => $_bIsMultiple ? 'multiple' : null, 'name' => $_bIsMultiple ? "{$this->aField['_input_name']}[]" : $this->aField['_input_name'], 'data-id' => $this->aField['input_id'],));
        return "<{$this->aOptions['input_container_tag']} " . $this->generateAttributes($this->aOptions['input_container_attributes']) . ">" . "<select " . $this->generateAttributes($_aSelectTagAttributes) . " >" . $this->_getDropDownList($this->aField['input_id'], $this->getAsArray($this->aField['label']), $this->aField['attributes']) . "</select>" . "</{$this->aOptions['input_container_tag']}>";
    }
    private function _getDropDownList($sInputID, array $aLabels, array $aAttributes) {
        $_aOutput = array();
        $_aValues = $this->getAsArray($aAttributes['value']);
        foreach ($aLabels as $__sKey => $__asLabel) {
            if (is_array($__asLabel)) {
                $_aOptGroupAttributes = isset($aAttributes['optgroup'][$__sKey]) && is_array($aAttributes['optgroup'][$__sKey]) ? $aAttributes['optgroup'][$__sKey] + $aAttributes['optgroup'] : $aAttributes['optgroup'];
                $_aOutput[] = "<optgroup label='{$__sKey}'" . $this->generateAttributes($_aOptGroupAttributes) . ">" . $this->_getDropDownList($sInputID, $__asLabel, $aAttributes) . "</optgroup>";
                continue;
            }
            $_sLabel = $__asLabel;
            $_aValues = isset($aAttributes['option'][$__sKey]['value']) ? $aAttributes['option'][$__sKey]['value'] : $_aValues;
            $_aOutput[] = $this->_getOptionTag($_sLabel, array('id' => $sInputID . '_' . $__sKey, 'value' => $__sKey, 'selected' => in_array(( string )$__sKey, $_aValues) ? 'selected' : null,) + (isset($aAttributes['option'][$__sKey]) && is_array($aAttributes['option'][$__sKey]) ? $aAttributes['option'][$__sKey] + $aAttributes['option'] : $aAttributes['option']));
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getOptionTag($sLabel, array $aAttributes = array()) {
        return "<option " . $this->generateAttributes($aAttributes) . " >" . $sLabel . "</option>";
    }
}
class Legull_AdminPageFramework_HelpPane_MetaBox_Page extends Legull_AdminPageFramework_HelpPane_MetaBox {
    protected function _isInThePage() {
        if (!$this->oProp->bIsAdmin) return false;
        if (!isset($_GET['page'])) return false;
        if (!$this->oProp->isPageAdded($_GET['page'])) return false;
        if (!isset($_GET['tab'])) return true;
        return $this->oProp->isCurrentTab($_GET['tab']);
    }
}
class Legull_AdminPageFramework_HelpPane_TaxonomyField extends Legull_AdminPageFramework_HelpPane_MetaBox {
    public function _replyToRegisterHelpTabTextForMetaBox() {
        $this->_setHelpTab($this->oProp->sMetaBoxID, $this->oProp->sTitle, $this->oProp->aHelpTabText, $this->oProp->aHelpTabTextSide);
    }
}
class Legull_AdminPageFramework_HelpPane_UserMeta extends Legull_AdminPageFramework_HelpPane_MetaBox {
}
class Legull_AdminPageFramework_HelpPane_Widget extends Legull_AdminPageFramework_HelpPane_MetaBox {
}
class Legull_AdminPageFramework_FieldType_media extends Legull_AdminPageFramework_FieldType_image {
    public $aFieldTypeSlugs = array('media',);
    protected $aDefaultKeys = array('attributes_to_store' => array(), 'show_preview' => true, 'allow_external_source' => true, 'attributes' => array('input' => array('size' => 40, 'maxlength' => 400,), 'button' => array(), 'remove_button' => array(), 'preview' => array(),),);
    public function _replyToFieldLoader() {
        parent::_replyToFieldLoader();
    }
    public function _replyToGetScripts() {
        return $this->_getScript_MediaUploader("admin_page_framework") . PHP_EOL . $this->_getScript_RegisterCallbacks();
    }
    protected function _getScript_RegisterCallbacks() {
        $_aJSArray = json_encode($this->aFieldTypeSlugs);
        return "jQuery(document).ready(function(){ jQuery().registerAPFCallback({ added_repeatable_field:function(node,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;if(node.find('.select_media').length<=0)return;var nodeFieldContainer=node.closest('.admin-page-framework-field'),iOccurence=iCallType===1?1:0;nodeFieldContainer.nextAll().andSelf().each(function(iIndex){ nodeButton=jQuery(this).find('.select_media');if(!(iCallType===1&&iIndex!==0))nodeButton.incrementIDAttribute('id',iOccurence);var nodeMediaInput=jQuery(this).find('.media-field input');if(nodeMediaInput.length<=0)return true;setAPFMediaUploader(nodeMediaInput.attr('id'),true,jQuery(nodeButton).attr('data-enable_external_source')) }) },removed_repeatable_field:function(oNextFieldConainer,sFieldType,sFieldTagID,iCallType){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;if(oNextFieldConainer.find('.select_media').length<=0)return;var iOccurence=iCallType===1?1:0;oNextFieldConainer.nextAll().andSelf().each(function(iIndex){ nodeButton=jQuery(this).find('.select_media');if(!(iCallType===1&&iIndex!==0))nodeButton.decrementIDAttribute('id',iOccurence);var nodeMediaInput=jQuery(this).find('.media-field input');if(nodeMediaInput.length<=0)return true;setAPFMediaUploader(nodeMediaInput.attr('id'),true,jQuery(nodeButton).attr('data-enable_external_source')) }) },sorted_fields:function(node,sFieldType,sFieldsTagID){ if(jQuery.inArray(sFieldType,$_aJSArray)<=-1)return;if(node.find('.select_media').length<=0)return;var iCount=0;node.children('.admin-page-framework-field').each(function(){ nodeButton=jQuery(this).find('.select_media');nodeButton.setIndexIDAttribute('id',iCount);var nodeMediaInput=jQuery(this).find('.media-field input');if(nodeMediaInput.length<=0)return true;setAPFMediaUploader(nodeMediaInput.attr('id'),true,jQuery(nodeButton).attr('data-enable_external_source'));iCount++ }) }}) });";
    }
    private function _getScript_MediaUploader($sReferrer) {
        $_sThickBoxTitle = esc_js($this->oMsg->get('upload_file'));
        $_sThickBoxButtonUseThis = esc_js($this->oMsg->get('use_this_file'));
        $_sInsertFromURL = esc_js($this->oMsg->get('insert_from_url'));
        if (!function_exists('wp_enqueue_media')) {
            return "setAPFMediaUploader=function(sInputID,fMultiple,fExternalSource){ jQuery('#select_media_'+sInputID).unbind('click');jQuery('#select_media_'+sInputID).click(function(){ var sPressedID=jQuery(this).attr('id');window.sInputID=sPressedID.substring(13);window.original_send_to_editor=window.send_to_editor;window.send_to_editor=hfAPFSendToEditorMedia;var fExternalSource=jQuery(this).attr('data-enable_external_source');tb_show('{$_sThickBoxTitle}','media-upload.php?post_id=1&amp;enable_external_source='+fExternalSource+'&amp;referrer={$sReferrer}&amp;button_label={$_sThickBoxButtonUseThis}&amp;type=image&amp;TB_iframe=true',false);return false }) };var hfAPFSendToEditorMedia=function(sRawHTML,param){ var sHTML='<div>'+sRawHTML+'</div>',src=jQuery('a',sHTML).attr('href'),classes=jQuery('a',sHTML).attr('class'),id=classes?classes.replace(/(.*?)wp-image-/,''):'',sInputID=window.sInputID;jQuery('#'+sInputID).val(src);jQuery('#'+sInputID+'_id').val(id);window.send_to_editor=window.original_send_to_editor;tb_remove() };";
        }
        return "setAPFMediaUploader=function(sInputID,fMultiple,fExternalSource){ var _bEscaped=false,_oMediaUploader;jQuery('#select_media_'+sInputID).unbind('click');jQuery('#select_media_'+sInputID).click(function(e){ var sInputID=jQuery(this).attr('id').substring(13);window.wpActiveEditor=null;e.preventDefault();if('object'===typeof _oMediaUploader){ _oMediaUploader.open();return };oAPFOriginalMediaUploaderSelectObject=wp.media.view.MediaFrame.Select;wp.media.view.MediaFrame.Select=fExternalSource?getAPFCustomMediaUploaderSelectObject():oAPFOriginalMediaUploaderSelectObject;_oMediaUploader=wp.media({ title:fExternalSource?'{$_sInsertFromURL}':'{$_sThickBoxTitle}',button:{ text:'{$_sThickBoxButtonUseThis}'},multiple:fMultiple,metadata:{ }});_oMediaUploader.on('escape',function(){ _bEscaped=true;return false });_oMediaUploader.on('close',function(){ var state=_oMediaUploader.state();if(typeof(state.props)!='undefined'&&typeof(state.props.attributes)!='undefined'){ var _oMedia={ },_sKey;for(_sKey in state.props.attributes)_oMedia[_sKey]=state.props.attributes[_sKey] };if(typeof _oMedia!=='undefined'){ setMediaPreviewElementWithDelay(sInputID,_oMedia) }else { var _oNewField;_oMediaUploader.state().get('selection').each(function(oAttachment,iIndex){ var _oAttributes=oAttachment.hasOwnProperty('attributes')?oAttachment.attributes:{ };if(0===iIndex){ setMediaPreviewElementWithDelay(sInputID,_oAttributes);return true };var _oFieldContainer='undefined'===typeof _oNewField?jQuery('#'+sInputID).closest('.admin-page-framework-field'):_oNewField;_oNewField=jQuery(this).addAPFRepeatableField(_oFieldContainer.attr('id'));var sInputIDOfNewField=_oNewField.find('input').attr('id');setMediaPreviewElementWithDelay(sInputIDOfNewField,_oAttributes) }) };wp.media.view.MediaFrame.Select=oAPFOriginalMediaUploaderSelectObject });_oMediaUploader.open();return false });var setMediaPreviewElementWithDelay=function(sInputID,oImage,iMilliSeconds){ iMilliSeconds='undefiend'===typeof iMilliSeconds?100:iMilliSeconds;setTimeout(function(){ if(!_bEscaped)setMediaPreviewElement(sInputID,oImage);_bEscaped=false },iMilliSeconds) } };removeInputValuesForMedia=function(oElem){ var _oImageInput=jQuery(oElem).closest('.admin-page-framework-field').find('.media-field input');if(_oImageInput.length<=0)return;var _sInputID=_oImageInput.first().attr('id');setMediaPreviewElement(_sInputID,{ }) };setMediaPreviewElement=function(sInputID,oSelectedFile){ jQuery('#'+sInputID).val(oSelectedFile.url);jQuery('#'+sInputID+'_id').val(oSelectedFile.id);jQuery('#'+sInputID+'_caption').val(jQuery('<div/>').text(oSelectedFile.caption).html());jQuery('#'+sInputID+'_description').val(jQuery('<div/>').text(oSelectedFile.description).html()) };";
    }
    public function _replyToGetStyles() {
        return ".admin-page-framework-field-media input {margin-right: 0.5em;vertical-align: middle;}@media screen and (max-width: 782px) {.admin-page-framework-field-media input {margin: 0.5em 0.5em 0.5em 0;}} .select_media.button.button-small,.remove_media.button.button-small{ vertical-align: middle;}.remove_media.button.button-small {margin-left: 0.2em;}";
    }
    public function _replyToGetField($aField) {
        return parent::_replyToGetField($aField);
    }
    protected function _getPreviewContainer($aField, $sImageURL, $aPreviewAtrributes) {
        return "";
    }
    protected function _getUploaderButtonScript($sInputID, $bRpeatable, $bExternalSource, array $aButtonAttributes) {
        $_bIsLabelSet = isset($aButtonAttributes['data-label']) && $aButtonAttributes['data-label'];
        $_bDashiconSupported = !$_bIsLabelSet && version_compare($GLOBALS['wp_version'], '3.8', '>=');
        $_sDashIconSelector = !$_bDashiconSupported ? '' : 'dashicons dashicons-portfolio';
        $_aAttributes = array('id' => "select_media_{$sInputID}", 'href' => '#', 'data-uploader_type' => function_exists('wp_enqueue_media') ? 1 : 0, 'data-enable_external_source' => $bExternalSource ? 1 : 0,) + $aButtonAttributes + array('title' => $_bIsLabelSet ? $aButtonAttributes['data-label'] : $this->oMsg->get('select_file'),);
        $_aAttributes['class'] = $this->generateClassAttribute('select_media button button-small ', trim($aButtonAttributes['class']) ? $aButtonAttributes['class'] : $_sDashIconSelector);
        $_sButton = "<a " . $this->generateAttributes($_aAttributes) . ">" . ($_bIsLabelSet ? $aButtonAttributes['data-label'] : (strrpos($_aAttributes['class'], 'dashicons') ? '' : $this->oMsg->get('select_file'))) . "</a>";
        $_sButtonHTML = '"' . $_sButton . '"';
        $_sScript = "if(jQuery('a#select_media_{$sInputID}').length==0)jQuery('input#{$sInputID}').after($_sButtonHTML);jQuery(document).ready(function(){ setAPFMediaUploader('{$sInputID}','{$bRpeatable}','{$bExternalSource}') });";
        return "<script type='text/javascript' class='admin-page-framework-media-uploader-button'>" . $_sScript . "</script>" . PHP_EOL;
    }
    protected function _getRemoveButtonScript($sInputID, array $aButtonAttributes) {
        if (!function_exists('wp_enqueue_media')) {
            return '';
        }
        $_bIsLabelSet = isset($aButtonAttributes['data-label']) && $aButtonAttributes['data-label'];
        $_bDashiconSupported = !$_bIsLabelSet && version_compare($GLOBALS['wp_version'], '3.8', '>=');
        $_sDashIconSelector = $_bDashiconSupported ? 'dashicons dashicons-dismiss' : '';
        $_aAttributes = array('id' => "remove_media_{$sInputID}", 'href' => '#', 'onclick' => esc_js("removeInputValuesForMedia( this ); return false;"),) + $aButtonAttributes + array('title' => $_bIsLabelSet ? $aButtonAttributes['data-label'] : $this->oMsg->get('remove_value'),);
        $_aAttributes['class'] = $this->generateClassAttribute('remove_value remove_media button button-small', trim($aButtonAttributes['class']) ? $aButtonAttributes['class'] : $_sDashIconSelector);
        $_sButton = "<a " . $this->generateAttributes($_aAttributes) . ">" . ($_bIsLabelSet ? $_aAttributes['data-label'] : (strrpos($_aAttributes['class'], 'dashicons') ? '' : 'x')) . "</a>";
        $_sButtonHTML = '"' . $_sButton . '"';
        $_sScript = "if(0===jQuery('a#remove_media_{$sInputID}').length)jQuery('input#{$sInputID}').after($_sButtonHTML);";
        return "<script type='text/javascript' class='admin-page-framework-media-remove-button'>" . $_sScript . "</script>" . PHP_EOL;
    }
}
class Legull_AdminPageFramework_FieldType_posttype extends Legull_AdminPageFramework_FieldType_checkbox {
    public $aFieldTypeSlugs = array('posttype',);
    protected $aDefaultKeys = array('slugs_to_remove' => null, 'query' => array(), 'operator' => 'and', 'attributes' => array('size' => 30, 'maxlength' => 400,), 'select_all_button' => true, 'select_none_button' => true,);
    protected $aDefaultRemovingPostTypeSlugs = array('revision', 'attachment', 'nav_menu_item',);
    protected function getStyles() {
        $_sParentStyles = parent::getStyles();
        return $_sParentStyles . ".admin-page-framework-field input[type='checkbox'] {margin-right: 0.5em;} .admin-page-framework-field-posttype .admin-page-framework-input-label-container {padding-right: 1em;}";
    }
    protected function getField($aField) {
        $this->_sCheckboxClassSelector = '';
        $aField['label'] = $this->_getPostTypeArrayForChecklist(isset($aField['slugs_to_remove']) ? $this->getAsArray($aField['slugs_to_remove']) : $this->aDefaultRemovingPostTypeSlugs, $aField['query'], $aField['operator']);
        return parent::getField($aField);
    }
    private function _getPostTypeArrayForChecklist($aSlugsToRemove, $asQueryArgs = array(), $sOperator = 'and') {
        $_aPostTypes = array();
        foreach (get_post_types($asQueryArgs, 'objects') as $_oPostType) {
            if (isset($_oPostType->name, $_oPostType->label)) {
                $_aPostTypes[$_oPostType->name] = $_oPostType->label;
            }
        }
        return array_diff_key($_aPostTypes, array_flip($aSlugsToRemove));
    }
}
class Legull_AdminPageFramework_FieldType_size extends Legull_AdminPageFramework_FieldType_select {
    public $aFieldTypeSlugs = array('size',);
    protected $aDefaultKeys = array('is_multiple' => false, 'units' => null, 'attributes' => array('size' => array('size' => 10, 'maxlength' => 400, 'min' => null, 'max' => null,), 'unit' => array('multiple' => null, 'size' => 1, 'autofocusNew' => null, 'required' => null,), 'optgroup' => array(), 'option' => array(),),);
    protected $aDefaultUnits = array('px' => 'px', '%' => '%', 'em' => 'em', 'ex' => 'ex', 'in' => 'in', 'cm' => 'cm', 'mm' => 'mm', 'pt' => 'pt', 'pc' => 'pc',);
    protected function getStyles() {
        return ".admin-page-framework-field-size input {text-align: right;}.admin-page-framework-field-size select.size-field-select {vertical-align: 0px; }.admin-page-framework-field-size label {width: auto; } .form-table td fieldset .admin-page-framework-field-size label {display: inline;}";
    }
    protected function getField($aField) {
        $aField['units'] = isset($aField['units']) ? $aField['units'] : $this->aDefaultUnits;
        $aBaseAttributes = $aField['attributes'];
        unset($aBaseAttributes['unit'], $aBaseAttributes['size']);
        $aSizeAttributes = array('type' => 'number', 'id' => $aField['input_id'] . '_' . 'size', 'name' => $aField['_input_name'] . '[size]', 'value' => isset($aField['value']['size']) ? $aField['value']['size'] : '',) + $this->getFieldElementByKey($aField['attributes'], 'size', $this->aDefaultKeys['attributes']['size']) + $aBaseAttributes;
        $aSizeLabelAttributes = array('for' => $aSizeAttributes['id'], 'class' => $aSizeAttributes['disabled'] ? 'disabled' : null,);
        $_bIsMultiple = $aField['is_multiple'] ? true : ($aField['attributes']['unit']['multiple'] ? true : false);
        $_aUnitAttributes = array('type' => 'select', 'id' => $aField['input_id'] . '_' . 'unit', 'multiple' => $_bIsMultiple ? 'multiple' : null, 'name' => $_bIsMultiple ? "{$aField['_input_name']}[unit][]" : "{$aField['_input_name']}[unit]", 'value' => isset($aField['value']['unit']) ? $aField['value']['unit'] : '',) + $this->getFieldElementByKey($aField['attributes'], 'unit', $this->aDefaultKeys['attributes']['unit']) + $aBaseAttributes;
        $_aUnitField = array('label' => $aField['units'],) + $aField;
        $_aUnitField['attributes']['select'] = $_aUnitAttributes;
        $_oUnitInput = new Legull_AdminPageFramework_Input_select($_aUnitField);
        return $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-select-label' style='min-width: " . $this->sanitizeLength($aField['label_min_width']) . ";'>" . "<label " . $this->generateAttributes($aSizeLabelAttributes) . ">" . $this->getFieldElementByKey($aField['before_label'], 'size') . ($aField['label'] && !$aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->sanitizeLength($aField['label_min_width']) . ";'>" . $aField['label'] . "</span>" : "") . "<input " . $this->generateAttributes($aSizeAttributes) . " />" . $this->getFieldElementByKey($aField['after_input'], 'size') . "</label>" . "<label " . $this->generateAttributes(array('for' => $_aUnitAttributes['id'], 'class' => $_aUnitAttributes['disabled'] ? 'disabled' : null,)) . ">" . $this->getFieldElementByKey($aField['before_label'], 'unit') . $_oUnitInput->get() . $this->getFieldElementByKey($aField['after_input'], 'unit') . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label'];
    }
}
class Legull_AdminPageFramework_FormTable_Row extends Legull_AdminPageFramework_FormTable_Base {
    public function getFieldRows(array $aFields, $hfCallback) {
        if (!is_callable($hfCallback)) {
            return '';
        }
        $_aOutput = array();
        foreach ($aFields as $_aField) {
            $_aOutput[] = $this->_getFieldRow($_aField, $hfCallback);
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getFieldRow(array $aField, $hfCallback) {
        if ('section_title' === $aField['type']) {
            return '';
        }
        $_aFieldFinal = $this->_mergeFieldTypeDefault($aField);
        return $this->_getFieldByContainer($aField, $_aFieldFinal, $hfCallback, array('open_container' => "<tr " . $this->_getFieldContainerAttributes($_aFieldFinal, array('id' => 'fieldrow-' . Legull_AdminPageFramework_FormField::_getInputTagBaseID($_aFieldFinal), 'valign' => 'top', 'class' => 'admin-page-framework-fieldrow',), 'fieldrow') . ">", 'close_container' => "</tr>", 'open_title' => "<th>", 'close_title' => "</th>", 'open_main' => "<td " . $this->generateAttributes(array('colspan' => $_aFieldFinal['show_title_column'] ? 1 : 2, 'class' => $_aFieldFinal['show_title_column'] ? null : 'admin-page-framework-field-td-no-title',)) . ">", 'close_main' => "</td>",));
    }
    public function getFields(array $aFields, $hfCallback) {
        if (!is_callable($hfCallback)) {
            return '';
        }
        $_aOutput = array();
        foreach ($aFields as $_aField) {
            $_aOutput[] = $this->_getField($_aField, $hfCallback);
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function _getField(array $aField, $hfCallback) {
        if ('section_title' === $aField['type']) {
            return '';
        }
        $_aFieldFinal = $this->_mergeFieldTypeDefault($aField);
        return $this->_getFieldByContainer($aField, $_aFieldFinal, $hfCallback, array('open_main' => "<div " . $this->_getFieldContainerAttributes($_aFieldFinal, array(), 'fieldrow') . ">", 'close_main' => "</div>",));
    }
    private function _getFieldByContainer(array $aField, array $aFieldFinal, $hfCallback, array $aOpenCloseTags) {
        $aOpenCloseTags = $aOpenCloseTags + array('open_container' => '', 'close_container' => '', 'open_title' => '', 'close_title' => '', 'open_main' => '', 'close_main' => '',);
        $_aOutput = array();
        if ($aField['show_title_column']) {
            $_aOutput[] = $aOpenCloseTags['open_title'] . $this->_getFieldTitle($aFieldFinal) . $aOpenCloseTags['close_title'];
        }
        $_aOutput[] = $aOpenCloseTags['open_main'] . call_user_func_array($hfCallback, array($aField)) . $aOpenCloseTags['close_main'];
        return $aOpenCloseTags['open_container'] . implode(PHP_EOL, $_aOutput) . $aOpenCloseTags['close_container'];
    }
    private function _mergeFieldTypeDefault(array $aField) {
        return $this->uniteArrays($aField, isset($this->aFieldTypeDefinitions[$aField['type']]['aDefaultKeys']) ? $this->aFieldTypeDefinitions[$aField['type']]['aDefaultKeys'] : array());
    }
    private function _getFieldTitle(array $aField) {
        return "<label for='" . Legull_AdminPageFramework_FormField::_getInputID($aField) . "'>" . "<a id='{$aField['field_id']}'></a>" . "<span title='" . esc_attr(strip_tags(isset($aField['tip']) ? $aField['tip'] : (is_array($aField['description'] ? implode('&#10;', $aField['description']) : $aField['description'])))) . "'>" . $aField['title'] . (in_array($aField['_fields_type'], array('widget', 'post_meta_box', 'page_meta_box')) && isset($aField['title']) && '' !== $aField['title'] ? "<span class='title-colon'>:</span>" : '') . "</span>" . "</label>";
    }
}
abstract class Legull_AdminPageFramework_FormTable_Caption extends Legull_AdminPageFramework_FormTable_Row {
    protected function _getCaption(array $aSection, $hfSectionCallback, $iSectionIndex, $aFields, $hfFieldCallback) {
        if (!$aSection['description'] && !$aSection['title']) {
            return "<caption class='admin-page-framework-section-caption' style='display:none;'></caption>";
        }
        $_abCollapsible = $this->_getCollapsibleArgument(array($aSection), $iSectionIndex);
        $_bShowTitle = empty($_abCollapsible) && !$aSection['section_tab_slug'];
        return "<caption " . $this->generateAttributes(array('class' => 'admin-page-framework-section-caption', 'data-section_tab' => $aSection['section_tab_slug'],)) . ">" . $this->_getCollapsibleSectionTitleBlock($_abCollapsible, 'section', $aFields, $hfFieldCallback) . ($_bShowTitle ? $this->_getCaptionTitle($aSection, $iSectionIndex, $aFields, $hfFieldCallback) : '') . $this->_getCaptionDescription($aSection, $hfSectionCallback) . $this->_getSectionError($aSection) . "</caption>";
    }
    private function _getSectionError($aSection) {
        $_sSectionError = isset($this->aFieldErrors[$aSection['section_id']]) && is_string($this->aFieldErrors[$aSection['section_id']]) ? $this->aFieldErrors[$aSection['section_id']] : '';
        return $_sSectionError ? "<div class='admin-page-framework-error'><span class='section-error'>* " . $_sSectionError . "</span></div>" : '';
    }
    private function _getCaptionTitle($aSection, $iSectionIndex, $aFields, $hfFieldCallback) {
        return "<div " . $this->generateAttributes(array('class' => 'admin-page-framework-section-title', 'style' => $this->_shouldShowCaptionTitle($aSection, $iSectionIndex) ? '' : 'display: none;',)) . ">" . $this->_getSectionTitle($aSection['title'], 'h3', $aFields, $hfFieldCallback) . "</div>";
    }
    private function _getCaptionDescription($aSection, $hfSectionCallback) {
        if ($aSection['collapsible']) {
            return '';
        }
        if (!is_callable($hfSectionCallback)) {
            return '';
        }
        return "<div class='admin-page-framework-section-description'>" . call_user_func_array($hfSectionCallback, array($this->_getSectionDescription($aSection['description']), $aSection)) . "</div>";
    }
    private function _shouldShowCaptionTitle($aSection, $iSectionIndex) {
        if (!$aSection['title']) {
            return false;
        }
        if ($aSection['collapsible']) {
            return false;
        }
        if ($aSection['section_tab_slug']) {
            return false;
        }
        if ($aSection['repeatable'] && $iSectionIndex != 0) {
            return false;
        }
        return true;
    }
    private function _getSectionDescription($asDescription) {
        if (empty($asDescription)) {
            return '';
        }
        $_aOutput = array();
        foreach ($this->getAsArray($asDescription) as $_sDescription) {
            $_aOutput[] = "<p class='admin-page-framework-section-description'>" . "<span class='description'>{$_sDescription}</span>" . "</p>";
        }
        return implode(PHP_EOL, $_aOutput);
    }
}
class Legull_AdminPageFramework_FormTable extends Legull_AdminPageFramework_FormTable_Caption {
    public function getFormTables($aSections, $aFieldsInSections, $hfSectionCallback, $hfFieldCallback) {
        $_aOutput = array();
        $_sFieldsType = $this->_getSectionsFieldsType($aSections);
        $this->_divideElementsBySectionTabs($aSections, $aFieldsInSections);
        foreach ($aSections as $_sSectionTabSlug => $_aSectionsBySectionTab) {
            if (!count($aFieldsInSections[$_sSectionTabSlug])) {
                continue;
            }
            $_sSectionSet = $this->_getSectionsTables($_aSectionsBySectionTab, $aFieldsInSections[$_sSectionTabSlug], $hfSectionCallback, $hfFieldCallback);
            if (!$_sSectionSet) {
                continue;
            }
            $_aOutput[] = "<div " . $this->generateAttributes(array('class' => 'admin-page-framework-sectionset', 'id' => "sectionset-{$_sSectionTabSlug}_" . md5(serialize($_aSectionsBySectionTab)),)) . ">" . $_sSectionSet . "</div>";
        }
        return implode(PHP_EOL, $_aOutput) . $this->_getSectionTabsEnablerScript() . (defined('WP_DEBUG') && WP_DEBUG && in_array($_sFieldsType, array('widget', 'post_meta_box', 'page_meta_box', 'user_meta')) ? "<div class='admin-page-framework-info'>" . 'Debug Info: ' . Legull_AdminPageFramework_Registry::Name . ' ' . Legull_AdminPageFramework_Registry::getVersion() . "</div>" : '');
    }
    private function _divideElementsBySectionTabs(array & $aSections, array & $aFields) {
        $_aSectionsBySectionTab = array();
        $_aFieldsBySectionTab = array();
        $_iIndex = 0;
        foreach ($aSections as $_sSectionID => $_aSection) {
            if (!isset($aFields[$_sSectionID])) {
                continue;
            }
            $_sSectionTaqbSlug = $_aSection['section_tab_slug'] ? $_aSection['section_tab_slug'] : '_default_' . (++$_iIndex);
            $_aSectionsBySectionTab[$_sSectionTaqbSlug][$_sSectionID] = $_aSection;
            $_aFieldsBySectionTab[$_sSectionTaqbSlug][$_sSectionID] = $aFields[$_sSectionID];
        }
        $aSections = $_aSectionsBySectionTab;
        $aFields = $_aFieldsBySectionTab;
    }
    private function _getSectionsFieldsType(array $aSections = array()) {
        foreach ($aSections as $_aSection) {
            return $_aSection['_fields_type'];
        }
    }
    private function _getSectionsSectionID(array $aSections = array()) {
        foreach ($aSections as $_aSection) {
            return $_aSection['section_id'];
        }
    }
    private function _getSectionsTables($aSections, $aFieldsInSections, $hfSectionCallback, $hfFieldCallback) {
        if (empty($aSections)) {
            return '';
        }
        $_sSectionTabSlug = '';
        $_aSectionTabList = array();
        $_aOutput = array();
        $_sThisSectionID = $this->_getSectionsSectionID($aSections);
        $_sSectionsID = 'sections-' . $_sThisSectionID;
        $_aCollapsible = $this->_getCollapsibleArgument($aSections);
        $_aCollapsible = isset($_aCollapsible['container']) && 'sections' === $_aCollapsible['container'] ? $_aCollapsible : array();
        foreach ($aSections as $_sSectionID => $_aSection) {
            $_sSectionTabSlug = $aSections[$_sSectionID]['section_tab_slug'];
            $_aSubSections = $this->getIntegerElements(isset($aFieldsInSections[$_sSectionID]) ? $aFieldsInSections[$_sSectionID] : array());
            $_iCountSubSections = count($_aSubSections);
            if ($_iCountSubSections) {
                if ($_aSection['repeatable']) {
                    $_aOutput[] = $this->_getRepeatableSectionsEnablerScript($_sSectionsID, $_iCountSubSections, $_aSection['repeatable']);
                }
                $_aSubSections = $this->numerizeElements($_aSubSections);
                foreach ($_aSubSections as $_iIndex => $_aFields) {
                    $_aSection['_is_first_index'] = $this->isFirstElement($_aSubSections, $_iIndex);
                    $_aSection['_is_last_index'] = $this->isLastElement($_aSubSections, $_iIndex);
                    $_aSectionTabList[] = $this->_getTabList($_sSectionID, $_iIndex, $_aSection, $_aFields, $hfFieldCallback);
                    $_aOutput[] = $this->_getSectionTable($_sSectionID, $_iIndex, $_aSection, $_aFields, $hfSectionCallback, $hfFieldCallback);
                }
                continue;
            }
            $_aFields = isset($aFieldsInSections[$_sSectionID]) ? $aFieldsInSections[$_sSectionID] : array();
            $_aSectionTabList[] = $this->_getTabList($_sSectionID, 0, $_aSection, $_aFields, $hfFieldCallback);
            $_aOutput[] = $this->_getSectionTable($_sSectionID, 0, $_aSection, $_aFields, $hfSectionCallback, $hfFieldCallback);
        }
        return empty($_aOutput) ? '' : (empty($_aCollapsible) ? '' : $this->_getCollapsibleSectionTitleBlock($_aCollapsible, 'sections')) . "<div " . $this->generateAttributes(array('id' => $_sSectionsID, 'class' => $this->generateClassAttribute('admin-page-framework-sections', !$_sSectionTabSlug || '_default' === $_sSectionTabSlug ? null : 'admin-page-framework-section-tabs-contents', empty($_aCollapsible) ? null : 'admin-page-framework-collapsible-sections-content admin-page-framework-collapsible-content accordion-section-content'), 'data-seciton_id' => $_sThisSectionID,)) . ">" . ($_sSectionTabSlug ? "<ul class='admin-page-framework-section-tabs nav-tab-wrapper'>" . implode(PHP_EOL, $_aSectionTabList) . "</ul>" : '') . implode(PHP_EOL, $_aOutput) . "</div>";
    }
    private function _getTabList($sSectionID, $iIndex, array $aSection, array $aFields, $hfFieldCallback) {
        if (!$aSection['section_tab_slug']) {
            return '';
        }
        $_sSectionTagID = 'section-' . $sSectionID . '__' . $iIndex;
        $_aTabAttributes = $aSection['attributes']['tab'] + array('class' => 'admin-page-framework-section-tab nav-tab', 'id' => "section_tab-{$_sSectionTagID}", 'style' => null);
        $_aTabAttributes['class'] = $this->generateClassAttribute($_aTabAttributes['class'], $aSection['class']['tab']);
        $_aTabAttributes['style'] = $this->generateStyleAttribute($_aTabAttributes['style'], $aSection['hidden'] ? 'display:none' : null);
        return "<li " . $this->generateAttributes($_aTabAttributes) . ">" . "<a href='#{$_sSectionTagID}'>" . $this->_getSectionTitle($aSection['title'], 'h4', $aFields, $hfFieldCallback) . "</a>" . "</li>";
    }
    private function _getSectionTable($sSectionID, $iSectionIndex, $aSection, $aFields, $hfSectionCallback, $hfFieldCallback) {
        if (count($aFields) <= 0) {
            return '';
        }
        $_bCollapsible = $aSection['collapsible'] && 'section' === $aSection['collapsible']['container'];
        $_sSectionTagID = 'section-' . $sSectionID . '__' . $iSectionIndex;
        $_aOutput = array();
        $_aOutput[] = "<table " . $this->generateAttributes(array('id' => 'section_table-' . $_sSectionTagID, 'class' => $this->generateClassAttribute('form-table', 'admin-page-framework-section-table'),)) . ">" . $this->_getCaption($aSection, $hfSectionCallback, $iSectionIndex, $aFields, $hfFieldCallback) . "<tbody " . $this->generateAttributes(array('class' => $_bCollapsible ? 'admin-page-framework-collapsible-section-content admin-page-framework-collapsible-content accordion-section-content' : null,)) . ">" . $this->getFieldRows($aFields, $hfFieldCallback) . "</tbody>" . "</table>";
        $_aSectionAttributes = $this->uniteArrays($this->dropElementsByType($aSection['attributes']), array('id' => $_sSectionTagID, 'class' => $this->generateClassAttribute('admin-page-framework-section', $aSection['section_tab_slug'] ? 'admin-page-framework-tab-content' : null, $_bCollapsible ? 'is_subsection_collapsible' : null), 'data-id_model' => 'section-' . $sSectionID . '__' . '-si-',));
        $_aSectionAttributes['class'] = $this->generateClassAttribute($_aSectionAttributes['class'], $this->dropElementsByType($aSection['class']));
        $_aSectionAttributes['style'] = $this->generateStyleAttribute($_aSectionAttributes['style'], $aSection['hidden'] ? 'display:none' : null);
        return "<div " . $this->generateAttributes($_aSectionAttributes) . ">" . implode(PHP_EOL, $_aOutput) . "</div>";
    }
}
class Legull_AdminPageFramework_FieldTypeRegistration {
    static protected $aDefaultFieldTypeSlugs = array('default', 'text', 'number', 'textarea', 'radio', 'checkbox', 'select', 'hidden', 'file', 'submit', 'import', 'export', 'image', 'media', 'color', 'taxonomy', 'posttype', 'size', 'section_title', 'system',);
    static public function register($aFieldTypeDefinitions, $sExtendedClassName, $oMsg) {
        foreach (self::$aDefaultFieldTypeSlugs as $_sFieldTypeSlug) {
            $_sFieldTypeClassName = "Legull_AdminPageFramework_FieldType_{$_sFieldTypeSlug}";
            if (!class_exists($_sFieldTypeClassName)) {
                continue;
            }
            $_oFieldType = new $_sFieldTypeClassName($sExtendedClassName, null, $oMsg, false);
            foreach ($_oFieldType->aFieldTypeSlugs as $__sSlug) {
                $aFieldTypeDefinitions[$__sSlug] = $_oFieldType->getDefinitionArray();
            }
        }
        return $aFieldTypeDefinitions;
    }
    static private $_aLoadFlags = array();
    static public function _setFieldResources(array $aField, $oProp, &$oResource) {
        $_sFieldType = $aField['type'];
        self::$_aLoadFlags[$oProp->_sPropertyType] = isset(self::$_aLoadFlags[$oProp->_sPropertyType]) && is_array(self::$_aLoadFlags[$oProp->_sPropertyType]) ? self::$_aLoadFlags[$oProp->_sPropertyType] : array();
        if (isset(self::$_aLoadFlags[$oProp->_sPropertyType][$_sFieldType]) && self::$_aLoadFlags[$oProp->_sPropertyType][$_sFieldType]) {
            return;
        }
        self::$_aLoadFlags[$oProp->_sPropertyType][$_sFieldType] = true;
        if (!isset($oProp->aFieldTypeDefinitions[$_sFieldType])) {
            return;
        }
        if (is_callable($oProp->aFieldTypeDefinitions[$_sFieldType]['hfFieldSetTypeSetter'])) {
            call_user_func_array($oProp->aFieldTypeDefinitions[$_sFieldType]['hfFieldSetTypeSetter'], array($oProp->_sPropertyType));
        }
        if (is_callable($oProp->aFieldTypeDefinitions[$_sFieldType]['hfFieldLoader'])) {
            call_user_func_array($oProp->aFieldTypeDefinitions[$_sFieldType]['hfFieldLoader'], array());
        }
        if (is_callable($oProp->aFieldTypeDefinitions[$_sFieldType]['hfGetScripts'])) {
            $oProp->sScript.= call_user_func_array($oProp->aFieldTypeDefinitions[$_sFieldType]['hfGetScripts'], array());
        }
        if (is_callable($oProp->aFieldTypeDefinitions[$_sFieldType]['hfGetStyles'])) {
            $oProp->sStyle.= call_user_func_array($oProp->aFieldTypeDefinitions[$_sFieldType]['hfGetStyles'], array());
        }
        if (is_callable($oProp->aFieldTypeDefinitions[$_sFieldType]['hfGetIEStyles'])) {
            $oProp->sStyleIE.= call_user_func_array($oProp->aFieldTypeDefinitions[$_sFieldType]['hfGetIEStyles'], array());
        }
        foreach ($oProp->aFieldTypeDefinitions[$_sFieldType]['aEnqueueStyles'] as $asSource) {
            if (is_string($asSource)) {
                $oResource->_forceToEnqueueStyle($asSource);
            } else if (is_array($asSource) && isset($asSource['src'])) {
                $oResource->_forceToEnqueueStyle($asSource['src'], $asSource);
            }
        }
        foreach ($oProp->aFieldTypeDefinitions[$_sFieldType]['aEnqueueScripts'] as $asSource) {
            if (is_string($asSource)) {
                $oResource->_forceToEnqueueScript($asSource);
            } else if (is_array($asSource) && isset($asSource['src'])) {
                $oResource->_forceToEnqueueScript($asSource['src'], $asSource);
            }
        }
    }
}
abstract class Legull_AdminPageFramework_PageLoadInfo_Base {
    function __construct($oProp, $oMsg) {
        if ($oProp->bIsAdminAjax || !$oProp->bIsAdmin) {
            return;
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->oProp = $oProp;
            $this->oMsg = $oMsg;
            $this->_nInitialMemoryUsage = memory_get_usage();
            add_action('in_admin_footer', array($this, '_replyToSetPageLoadInfoInFooter'), 999);
        }
    }
    public function _replyToSetPageLoadInfoInFooter() {
    }
    static private $_bLoadedPageLoadInfo = false;
    public function _replyToGetPageLoadInfo($sFooterHTML) {
        if (self::$_bLoadedPageLoadInfo) {
            return;
        }
        self::$_bLoadedPageLoadInfo = true;
        $_nSeconds = timer_stop(0);
        $_nQueryCount = get_num_queries();
        $_nMemoryUsage = round($this->_convertBytesToHR(memory_get_usage()), 2);
        $_nMemoryPeakUsage = round($this->_convertBytesToHR(memory_get_peak_usage()), 2);
        $_nMemoryLimit = round($this->_convertBytesToHR($this->_convertToNumber(WP_MEMORY_LIMIT)), 2);
        $_sInitialMemoryUsage = round($this->_convertBytesToHR($this->_nInitialMemoryUsage), 2);
        return $sFooterHTML . "<div id='admin-page-framework-page-load-stats'>" . "<ul>" . "<li>" . sprintf($this->oMsg->get('queries_in_seconds'), $_nQueryCount, $_nSeconds) . "</li>" . "<li>" . sprintf($this->oMsg->get('out_of_x_memory_used'), $_nMemoryUsage, $_nMemoryLimit, round(($_nMemoryUsage / $_nMemoryLimit), 2) * 100 . '%') . "</li>" . "<li>" . sprintf($this->oMsg->get('peak_memory_usage'), $_nMemoryPeakUsage) . "</li>" . "<li>" . sprintf($this->oMsg->get('initial_memory_usage'), $_sInitialMemoryUsage) . "</li>" . "</ul>" . "</div>";
    }
    private function _convertToNumber($nSize) {
        $_nReturn = substr($nSize, 0, -1);
        switch (strtoupper(substr($nSize, -1))) {
            case 'P':
                $_nReturn*= 1024;
            case 'T':
                $_nReturn*= 1024;
            case 'G':
                $_nReturn*= 1024;
            case 'M':
                $_nReturn*= 1024;
            case 'K':
                $_nReturn*= 1024;
        }
        return $_nReturn;
    }
    private function _convertBytesToHR($nBytes) {
        $_aUnits = array(0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB');
        $_nLog = log($nBytes, 1024);
        $_iPower = ( int )$_nLog;
        $_iSize = pow(1024, $_nLog - $_iPower);
        return $_iSize . $_aUnits[$_iPower];
    }
}
class Legull_AdminPageFramework_PageLoadInfo_Page extends Legull_AdminPageFramework_PageLoadInfo_Base {
    private static $_oInstance;
    private static $aClassNames = array();
    public static function instantiate($oProp, $oMsg) {
        if (in_array($oProp->sClassName, self::$aClassNames)) return self::$_oInstance;
        self::$aClassNames[] = $oProp->sClassName;
        self::$_oInstance = new Legull_AdminPageFramework_PageLoadInfo_Page($oProp, $oMsg);
        return self::$_oInstance;
    }
    public function _replyToSetPageLoadInfoInFooter() {
        if ($this->oProp->isPageAdded()) {
            add_filter('update_footer', array($this, '_replyToGetPageLoadInfo'), 999);
        }
    }
}
class Legull_AdminPageFramework_PageLoadInfo_NetworkAdminPage extends Legull_AdminPageFramework_PageLoadInfo_Base {
    private static $_oInstance;
    private static $aClassNames = array();
    function __construct($oProp, $oMsg) {
        if (is_network_admin() && defined('WP_DEBUG') && WP_DEBUG) {
            add_action('in_admin_footer', array($this, '_replyToSetPageLoadInfoInFooter'), 999);
        }
        parent::__construct($oProp, $oMsg);
    }
    public static function instantiate($oProp, $oMsg) {
        if (!is_network_admin()) {
            return;
        }
        if (in_array($oProp->sClassName, self::$aClassNames)) return self::$_oInstance;
        self::$aClassNames[] = $oProp->sClassName;
        self::$_oInstance = new Legull_AdminPageFramework_PageLoadInfo_NetworkAdminPage($oProp, $oMsg);
        return self::$_oInstance;
    }
    public function _replyToSetPageLoadInfoInFooter() {
        if ($this->oProp->isPageAdded()) {
            add_filter('update_footer', array($this, '_replyToGetPageLoadInfo'), 999);
        }
    }
}
class Legull_AdminPageFramework_PageLoadInfo_PostType extends Legull_AdminPageFramework_PageLoadInfo_Base {
    private static $_oInstance;
    private static $aClassNames = array();
    public static function instantiate($oProp, $oMsg) {
        if (in_array($oProp->sClassName, self::$aClassNames)) return self::$_oInstance;
        self::$aClassNames[] = $oProp->sClassName;
        self::$_oInstance = new Legull_AdminPageFramework_PageLoadInfo_PostType($oProp, $oMsg);
        return self::$_oInstance;
    }
    public function _replyToSetPageLoadInfoInFooter() {
        if (isset($_GET['page']) && $_GET['page']) {
            return;
        }
        if (Legull_AdminPageFramework_WPUtility::getCurrentPostType() == $this->oProp->sPostType || Legull_AdminPageFramework_WPUtility::isPostDefinitionPage($this->oProp->sPostType) || Legull_AdminPageFramework_WPUtility::isCustomTaxonomyPage($this->oProp->sPostType)) {
            add_filter('update_footer', array($this, '_replyToGetPageLoadInfo'), 999);
        }
    }
}
class Legull_AdminPageFramework_WalkerTaxonomyChecklist extends Walker_Category {
    function start_el(&$sOutput, $oTerm, $iDepth = 0, $aArgs = array(), $iCurrentObjectID = 0) {
        $aArgs = $aArgs + array('name' => null, 'disabled' => null, 'selected' => array(), 'input_id' => null, 'attributes' => array(), 'taxonomy' => null,);
        $_iID = $oTerm->term_id;
        $_sTaxonomySlug = empty($aArgs['taxonomy']) ? 'category' : $aArgs['taxonomy'];
        $_sID = "{$aArgs['input_id']}_{$_sTaxonomySlug}_{$_iID}";
        $_sPostCount = $aArgs['show_post_count'] ? " <span class='font-lighter'>(" . $oTerm->count . ")</span>" : '';
        $_aInputAttributes = isset($_aInputAttributes[$_iID]) ? $_aInputAttributes[$_iID] + $aArgs['attributes'] : $aArgs['attributes'];
        $_aInputAttributes = array('id' => $_sID, 'value' => 1, 'type' => 'checkbox', 'name' => "{$aArgs['name']}[{$_iID}]", 'checked' => in_array($_iID, ( array )$aArgs['selected']) ? 'checked' : null,) + $_aInputAttributes;
        $_aInputAttributes['class'].= ' apf_checkbox';
        $_aLiTagAttributes = array('id' => "list-{$_sID}", 'class' => 'category-list', 'title' => $oTerm->description,);
        $sOutput.= "\n" . "<li " . Legull_AdminPageFramework_WPUtility::generateAttributes($_aLiTagAttributes) . ">" . "<label for='{$_sID}' class='taxonomy-checklist-label'>" . "<input value='0' type='hidden' name='{$aArgs['name']}[{$_iID}]' class='apf_checkbox' />" . "<input " . Legull_AdminPageFramework_WPUtility::generateAttributes($_aInputAttributes) . " />" . esc_html(apply_filters('the_category', $oTerm->name)) . $_sPostCount . "</label>";
    }
}
class Legull_AdminPageFramework_Script_Base {
    static public $_aEnqueued = array();
    public function __construct($oMsg = null) {
        $_sClassName = get_class($this);
        if (in_array($_sClassName, self::$_aEnqueued)) {
            return;
        }
        self::$_aEnqueued[$_sClassName] = $_sClassName;
        $this->oMsg = $oMsg;
        add_action('customize_controls_print_footer_scripts', array($this, '_replyToPrintScript'));
        add_action('admin_footer', array($this, '_replyToPrintScript'));
        $this->construct();
    }
    protected function construct() {
    }
    public function _replyToPrintScript() {
        $_sScript = $this->getScript($this->oMsg);
        if (!$_sScript) {
            return;
        }
        echo "<script type='text/javascript' class='" . strtolower(get_class($this)) . "'>" . $_sScript . "</script>";
    }
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "";
    }
}
class Legull_AdminPageFramework_Script_AttributeUpdator extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.incrementIDAttribute=function(sAttribute,biOccurrence){ return this.attr(sAttribute,function(iIndex,sValue){ return updateID(iIndex,sValue,1,biOccurrence) }) };$.fn.incrementNameAttribute=function(sAttribute,biOccurrence){ return this.attr(sAttribute,function(iIndex,sValue){ return updateName(iIndex,sValue,1,biOccurrence) }) };$.fn.decrementIDAttribute=function(sAttribute,biOccurrence){ return this.attr(sAttribute,function(iIndex,sValue){ return updateID(iIndex,sValue,-1,biOccurrence) }) };$.fn.decrementNameAttribute=function(sAttribute,biOccurrence){ return this.attr(sAttribute,function(iIndex,sValue){ return updateName(iIndex,sValue,-1,biOccurrence) }) };$.fn.setIndexIDAttribute=function(sAttribute,iIndex,biOccurrence){ return this.attr(sAttribute,function(i,sValue){ return updateID(iIndex,sValue,0,biOccurrence) }) };$.fn.setIndexNameAttribute=function(sAttribute,iIndex,biOccurrence){ return this.attr(sAttribute,function(i,sValue){ return updateName(iIndex,sValue,0,biOccurrence) }) };var sanitizeOccurrence=function(biOccurrence){ if('undefined'===typeof biOccurrence)return-1;if(true===biOccurrence)return 1;if(false===biOccurrence)return-1;if(0===biOccurrence)return-1;if('number'===typeof biOccurrence)return biOccurrence;return-1 },updateID=function(iIndex,sID,iIncrementType,biOccurrence){ if('undefined'===typeof sID)return sID;var _iCurrentOccurrence=1,_oNeedle=new RegExp('(.+?)__(\\\d+)(?=([_-]|$))','g'),_oMatch=sID.match(_oNeedle),_iTotalMatch=null!==_oMatch&&_oMatch.hasOwnProperty('length')?_oMatch.length:0;if(_iTotalMatch===0)return sID;var _iOccurrence=sanitizeOccurrence(biOccurrence),_bIsBackwards=_iOccurrence<0;_iOccurrence=_bIsBackwards?_iTotalMatch+1+_iOccurrence:_iOccurrence;return sID.replace(_oNeedle,function(sFullMatch,sMatch0,sMatch1){ if(_iCurrentOccurrence!==_iOccurrence){ _iCurrentOccurrence++;return sFullMatch };switch(iIncrementType){case 1:var _sResult=sMatch0+'__'+(Number(sMatch1)+1);break;case -1:var _sResult=sMatch0+'__'+(Number(sMatch1)-1);break;default:var _sResult=sMatch0+'__'+iIndex;break};_iCurrentOccurrence++;return _sResult }) },updateName=function(iIndex,sName,iIncrementType,biOccurrence){ if('undefined'===typeof sName)return sName;var _iCurrentOccurrence=1,_oNeedle=new RegExp('(.+?)\\\[(\\\d+)(?=\\\])','g'),_oMatch=sName.match(_oNeedle),_iTotalMatch=null!==_oMatch&&_oMatch.hasOwnProperty('length')?_oMatch.length:0;if(_iTotalMatch===0)return sName;var _iOccurrence=sanitizeOccurrence(biOccurrence),_bIsBackwards=_iOccurrence<0;_iOccurrence=_bIsBackwards?_iTotalMatch+1+_iOccurrence:_iOccurrence;return sName.replace(_oNeedle,function(sFullMatch,sMatch0,sMatch1){ if(_iCurrentOccurrence!==_iOccurrence){ _iCurrentOccurrence++;return sFullMatch };switch(iIncrementType){case 1:var _sResult=sMatch0+'['+(Number(sMatch1)+1);break;case -1:var _sResult=sMatch0+'['+(Number(sMatch1)-1);break;default:var _sResult=sMatch0+'['+iIndex;break};_iCurrentOccurrence++;return _sResult }) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_CheckboxSelector extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.selectALLAPFCheckboxes=function(){ jQuery(this).parent().find('input[type=checkbox]').attr('checked',true) };$.fn.deselectAllAPFCheckboxes=function(){ jQuery(this).parent().find('input[type=checkbox]').attr('checked',false) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_CollapsibleSection extends Legull_AdminPageFramework_Script_Base {
    protected function construct() {
        wp_enqueue_script('juery');
        wp_enqueue_script('juery-ui-accordion');
    }
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        $_sLabelToggleAll = $_oMsg->get('toggle_all');
        $_sLabelToggleAllSections = $_oMsg->get('toggle_all_collapsible_sections');
        $_sDashIconSort = version_compare($GLOBALS['wp_version'], '3.8', '<') ? '' : 'dashicons dashicons-sort';
        $_sToggleAllButton = "<div class='admin-page-framework-collapsible-toggle-all-button-container'>" . "<span class='admin-page-framework-collapsible-toggle-all-button button " . $_sDashIconSort . "' title='" . esc_attr($_sLabelToggleAllSections) . "'>" . ($_sDashIconSort ? '' : $_sLabelToggleAll) . "</span>" . "</div>";
        $_sToggleAllButtonHTML = '"' . $_sToggleAllButton . '"';
        return "(function($){ jQuery(document).ready(function(){ jQuery('.admin-page-framework-collapsible-sections-title[data-is_collapsed=\"0\"]').next('.admin-page-framework-collapsible-sections-content').slideDown('fast');jQuery('.admin-page-framework-collapsible-section-title[data-is_collapsed=\"0\"]').closest('.admin-page-framework-section-table').find('tbody').slideDown('fast');jQuery('.admin-page-framework-collapsible-section-title[data-is_collapsed=\"1\"]').closest('.admin-page-framework-section-table').find('tbody').hide();jQuery('.admin-page-framework-collapsible-sections-title, .admin-page-framework-collapsible-section-title').enableAPFCollapsibleButton();jQuery('.admin-page-framework-collapsible-title[data-toggle_all_button!=\"0\"]').each(function(){ var _oThis=jQuery(this),_bForSections=jQuery(this).hasClass('admin-page-framework-collapsible-sections-title'),_isPositions=jQuery(this).data('toggle_all_button'),_isPositions=1===_isPositions?'top-right':_isPositions,_aPositions='string'===typeof _isPositions?_isPositions.split(','):['top-right'];jQuery.each(_aPositions,function(iIndex,_sPosition){ var _oButton=jQuery($_sToggleAllButtonHTML),_sLeftOrRight=-1!==jQuery.inArray(_sPosition,['top-right','bottom-right','0'])?'right':'left';_oButton.find('.admin-page-framework-collapsible-toggle-all-button').css('float',_sLeftOrRight);var _sTopOrBottom=-1!==jQuery.inArray(_sPosition,['top-right','top-left','0'])?'before':'after';if(_bForSections){ { var _oTargetElement='before'===_sTopOrBottom?_oThis:_oThis.next('.admin-page-framework-collapsible-content');_oTargetElement[_sTopOrBottom](_oButton) } }else _oThis.closest('.admin-page-framework-section')[_sTopOrBottom](_oButton);_oButton.click(function(){ _oButton.toggleClass('flipped');if(_oButton.hasClass('flipped')&&_oButton.hasClass('dashicons')){ _oButton.css('transform','rotateY( 180deg )') }else _oButton.css('transform','');if(_bForSections){ _oButton.parent().parent().children().children('* > .admin-page-framework-collapsible-title').each(function(){ jQuery(this).trigger('click',['by_toggle_all_button']) }) }else _oButton.closest('.admin-page-framework-sections').children('.admin-page-framework-section').children('.admin-page-framework-section-table').children('caption').children('.admin-page-framework-collapsible-title').each(function(){ jQuery(this).trigger('click',['by_toggle_all_button']) }) }) }) }) });$.fn.enableAPFCollapsibleButton=function(){ jQuery(this).click(function(event,sContext){ var _oThis=jQuery(this),_sContainerType=jQuery(this).hasClass('admin-page-framework-collapsible-sections-title')?'sections':'section',_oTargetContent='sections'===_sContainerType?jQuery(this).next('.admin-page-framework-collapsible-content').first():jQuery(this).parent().siblings('tbody'),_sAction=_oTargetContent.is(':visible')?'collapse':'expand';_oThis.removeClass('collapsed');_oTargetContent.slideToggle('fast',function(){ var _bIsChrome=navigator.userAgent.toLowerCase().indexOf('chrome')>-1;if('expand'===_sAction&&'section'===_sContainerType&&!_bIsChrome)_oTargetContent.css('display','block');if(_oTargetContent.is(':visible')){ _oThis.removeClass('collapsed') }else _oThis.addClass('collapsed') });if('by_toggle_all_button'===sContext)return;if('expand'===_sAction&&_oThis.data('collapse_others_on_expand'))_oThis.parent().parent().children().children('* > .admin-page-framework-collapsible-content').not(_oTargetContent).slideUp('fast',function(){ jQuery(this).prev('.admin-page-framework-collapsible-title').addClass('collapsed') }) }) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_MediaUploader extends Legull_AdminPageFramework_Script_Base {
    protected function construct() {
        wp_enqueue_script('jquery');
        if (function_exists('wp_enqueue_media')) {
            add_action('admin_footer', array($this, '_replyToEnqueueMedia'), 1);
        }
    }
    public function _replyToEnqueueMedia() {
        wp_enqueue_media();
    }
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        if (!function_exists('wp_enqueue_media')) {
            return "";
        }
        $_sReturnToLibrary = esc_js($_oMsg->get('return_to_library'));
        $_sSelect = esc_js($_oMsg->get('select'));
        $_sInsert = esc_js($_oMsg->get('insert'));
        return "(function($){ getAPFCustomMediaUploaderSelectObject=function(){ return wp.media.view.MediaFrame.Select.extend({ initialize:function(){ wp.media.view.MediaFrame.prototype.initialize.apply(this,arguments);_.defaults(this.options,{ multiple:true,editing:false,state:'insert',metadata:{ }});this.createSelection();this.createStates();this.bindHandlers();this.createIframeStates() },createStates:function(){ var options=this.options;this.states.add([new wp.media.controller.Library({ id:'insert',title:'Insert Media',priority:20,toolbar:'main-insert',filterable:'image',library:wp.media.query(options.library),multiple:options.multiple?'reset':false,editable:true,allowLocalEdits:true,displaySettings:true,displayUserSettings:true}),new wp.media.controller.Embed(options)]);if(wp.media.view.settings.post.featuredImageId)this.states.add(new wp.media.controller.FeaturedImage()) },bindHandlers:function(){ this.on('router:create:browse',this.createRouter,this);this.on('router:render:browse',this.browseRouter,this);this.on('content:create:browse',this.browseContent,this);this.on('content:render:upload',this.uploadContent,this);this.on('toolbar:create:select',this.createSelectToolbar,this);this.on('menu:create:gallery',this.createMenu,this);this.on('toolbar:create:main-insert',this.createToolbar,this);this.on('toolbar:create:main-gallery',this.createToolbar,this);this.on('toolbar:create:featured-image',this.featuredImageToolbar,this);this.on('toolbar:create:main-embed',this.mainEmbedToolbar,this);var handlers={ menu:{ 'default':'mainMenu'},content:{ embed:'embedContent','edit-selection':'editSelectionContent'},toolbar:{ 'main-insert':'mainInsertToolbar'}};_.each(handlers,function(regionHandlers,region){ _.each(regionHandlers,function(callback,handler){ this.on(region+':render:'+handler,this[callback],this) },this) },this) },mainMenu:function(view){ view.set({ 'library-separator':new wp.media.View({ className:'separator',priority:100})}) },embedContent:function(){ var view=new wp.media.view.Embed({ controller:this,model:this.state()}).render();this.content.set(view);view.url.focus() },editSelectionContent:function(){ var state=this.state(),selection=state.get('selection'),view;view=new wp.media.view.AttachmentsBrowser({ controller:this,collection:selection,selection:selection,model:state,sortable:true,search:false,dragInfo:true,AttachmentView:wp.media.view.Attachment.EditSelection}).render();view.toolbar.set('backToLibrary',{ text:'{$_sReturnToLibrary}',priority:-100,click:function(){ this.controller.content.mode('browse') }});this.content.set(view) },selectionStatusToolbar:function(view){ var editable=this.state().get('editable');view.set('selection',new wp.media.view.Selection({ controller:this,collection:this.state().get('selection'),priority:-40,editable:editable&&function(){ this.controller.content.mode('edit-selection') }}).render()) },mainInsertToolbar:function(view){ var controller=this;this.selectionStatusToolbar(view);view.set('insert',{ style:'primary',priority:80,text:'{$_sSelect}',requires:{ selection:true},click:function(){ var state=controller.state(),selection=state.get('selection');controller.close();state.trigger('insert',selection).reset() }}) },featuredImageToolbar:function(toolbar){ this.createSelectToolbar(toolbar,{ text:l10n.setFeaturedImage,state:this.options.state||'upload'}) },mainEmbedToolbar:function(toolbar){ var state=this.state();state.set('library',false);toolbar.view=new wp.media.view.Toolbar.Embed({ controller:this,text:'{$_sInsert}'}) }}) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_OptionStorage extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.aAPFInputOptions={ };$.fn.storeAPFInputOptions=function(sID,vOptions){ var sID=sID.replace(/__\d+_/,'___');$.fn.aAPFInputOptions[sID]=vOptions };$.fn.getAPFInputOptions=function(sID){ var sID=sID.replace(/__\d+_/,'___');return('undefined'===typeof $.fn.aAPFInputOptions[sID])?null:$.fn.aAPFInputOptions[sID] } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_RegisterCallback extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.aAPFAddRepeatableFieldCallbacks=[];$.fn.aAPFRemoveRepeatableFieldCallbacks=[];$.fn.aAPFSortedFieldsCallbacks=[];$.fn.aAPFStoppedSortingFieldsCallbacks=[];$.fn.aAPFAddedWidgetCallbacks=[];$.fn.callBackAddRepeatableField=function(sFieldType,sID,iCallType,iSectionIndex,iFieldIndex){ var oThisNode=this;$.fn.aAPFAddRepeatableFieldCallbacks.forEach(function(hfCallback){ if(jQuery.isFunction(hfCallback))hfCallback(oThisNode,sFieldType,sID,iCallType,iSectionIndex,iFieldIndex) }) };$.fn.callBackRemoveRepeatableField=function(sFieldType,sID,iCallType,iSectionIndex,iFieldIndex){ var oThisNode=this;$.fn.aAPFRemoveRepeatableFieldCallbacks.forEach(function(hfCallback){ if(jQuery.isFunction(hfCallback))hfCallback(oThisNode,sFieldType,sID,iCallType,iSectionIndex.iFieldIndex) }) };$.fn.callBackSortedFields=function(sFieldType,sID,iCallType){ var oThisNode=this;$.fn.aAPFSortedFieldsCallbacks.forEach(function(hfCallback){ if(jQuery.isFunction(hfCallback))hfCallback(oThisNode,sFieldType,sID,iCallType) }) };$.fn.callBackStoppedSortingFields=function(sFieldType,sID,iCallType){ var oThisNode=this;$.fn.aAPFStoppedSortingFieldsCallbacks.forEach(function(hfCallback){ if(jQuery.isFunction(hfCallback))hfCallback(oThisNode,sFieldType,sID,iCallType) }) };$(document).bind('admin_page_framework_saved_widget',function(event,oWidget){ $.each($.fn.aAPFAddedWidgetCallbacks,function(iIndex,hfCallback){ if(!$.isFunction(hfCallback))return true;hfCallback(oWidget) }) });$.fn.registerAPFCallback=function(oOptions){ var oSettings=$.extend({ added_repeatable_field:null,removed_repeatable_field:null,sorted_fields:null,stopped_sorting_fields:null,saved_widget:null},oOptions);$.fn.aAPFAddRepeatableFieldCallbacks.push(oSettings.added_repeatable_field);$.fn.aAPFRemoveRepeatableFieldCallbacks.push(oSettings.removed_repeatable_field);$.fn.aAPFSortedFieldsCallbacks.push(oSettings.sorted_fields);$.fn.aAPFStoppedSortingFieldsCallbacks.push(oSettings.stopped_sorting_fields);$.fn.aAPFAddedWidgetCallbacks.push(oSettings.saved_widget);return } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_RepeatableField extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        $sCannotAddMore = $_oMsg->get('allowed_maximum_number_of_fields');
        $sCannotRemoveMore = $_oMsg->get('allowed_minimum_number_of_fields');
        return "(function($){ $.fn.updateAPFRepeatableFields=function(aSettings){ var nodeThis=this,sFieldsContainerID=nodeThis.find('.repeatable-field-add').first().data('id');if(!$.fn.aAPFRepeatableFieldsOptions)$.fn.aAPFRepeatableFieldsOptions=[];if(!$.fn.aAPFRepeatableFieldsOptions.hasOwnProperty(sFieldsContainerID))$.fn.aAPFRepeatableFieldsOptions[sFieldsContainerID]=$.extend({ max:0,min:0},aSettings);var aOptions=$.fn.aAPFRepeatableFieldsOptions[sFieldsContainerID];$(nodeThis).find('.admin-page-framework-repeatable-field-buttons').attr('data-max',aOptions.max);$(nodeThis).find('.admin-page-framework-repeatable-field-buttons').attr('data-min',aOptions.min);$(nodeThis).find('.repeatable-field-add').unbind('click');$(nodeThis).find('.repeatable-field-add').click(function(){ $(this).addAPFRepeatableField();return false });$(nodeThis).find('.repeatable-field-remove').unbind('click');$(nodeThis).find('.repeatable-field-remove').click(function(){ $(this).removeAPFRepeatableField();return false });var sFieldID=nodeThis.find('.repeatable-field-add').first().closest('.admin-page-framework-field').attr('id'),nCurrentFieldCount=jQuery('#'+sFieldsContainerID).find('.admin-page-framework-field').length;if(aOptions.min>0&&nCurrentFieldCount>0)if((aOptions.min-nCurrentFieldCount)>0)$('#'+sFieldID).addAPFRepeatableField(sFieldID) };$.fn.addAPFRepeatableField=function(sFieldContainerID){ if(typeof sFieldContainerID==='undefined')var sFieldContainerID=$(this).closest('.admin-page-framework-field').attr('id');var nodeFieldContainer=$('#'+sFieldContainerID),nodeNewField=nodeFieldContainer.clone(),nodeFieldsContainer=nodeFieldContainer.closest('.admin-page-framework-fields'),sFieldsContainerID=nodeFieldsContainer.attr('id');if(!$.fn.aAPFRepeatableFieldsOptions.hasOwnProperty(sFieldsContainerID)){ var nodeButtonContainer=nodeFieldContainer.find('.admin-page-framework-repeatable-field-buttons');$.fn.aAPFRepeatableFieldsOptions[sFieldsContainerID]={ max:nodeButtonContainer.attr('data-max'),min:nodeButtonContainer.attr('data-min')} };var sMaxNumberOfFields=$.fn.aAPFRepeatableFieldsOptions[sFieldsContainerID]['max'];if(sMaxNumberOfFields!=0&&nodeFieldsContainer.find('.admin-page-framework-field').length>=sMaxNumberOfFields){ var nodeLastRepeaterButtons=nodeFieldContainer.find('.admin-page-framework-repeatable-field-buttons').last(),sMessage=$(this).formatPrintText('{$sCannotAddMore}',sMaxNumberOfFields),nodeMessage=$('<span class=\"repeatable-error repeatable-field-error\" id=\"repeatable-error-'+sFieldsContainerID+'\" >'+sMessage+'</span>');if(nodeFieldsContainer.find('#repeatable-error-'+sFieldsContainerID).length>0){ nodeFieldsContainer.find('#repeatable-error-'+sFieldsContainerID).replaceWith(nodeMessage) }else nodeLastRepeaterButtons.before(nodeMessage);nodeMessage.delay(2e3).fadeOut(1e3);return };nodeNewField.find('input:not([type=radio], [type=checkbox], [type=submit], [type=hidden]),textarea').val('');nodeNewField.find('.repeatable-error').remove();nodeNewField.insertAfter(nodeFieldContainer);nodeFieldContainer.nextAll().each(function(){ $(this).incrementIDAttribute('id');$(this).find('label').incrementIDAttribute('for');$(this).find('input,textarea,select').incrementIDAttribute('id');$(this).find('input:not(.apf_checkbox),textarea,select').incrementNameAttribute('name');$(this).find('input.apf_checkbox').incrementNameAttribute('name',-2) });nodeNewField.updateAPFRepeatableFields();nodeFieldContainer.find('input[type=radio][checked=checked]').attr('checked','checked');nodeNewField.callBackAddRepeatableField(nodeNewField.data('type'),nodeNewField.attr('id'),0,0,0);var nodeRemoveButtons=nodeFieldsContainer.find('.repeatable-field-remove');if(nodeRemoveButtons.length>1)nodeRemoveButtons.css('visibility','visible');return nodeNewField };$.fn.removeAPFRepeatableField=function(){ var nodeFieldContainer=$(this).closest('.admin-page-framework-field'),nodeFieldsContainer=$(this).closest('.admin-page-framework-fields'),sFieldsContainerID=nodeFieldsContainer.attr('id'),sMinNumberOfFields=$.fn.aAPFRepeatableFieldsOptions[sFieldsContainerID]['min'];if(sMinNumberOfFields!=0&&nodeFieldsContainer.find('.admin-page-framework-field').length<=sMinNumberOfFields){ var nodeLastRepeaterButtons=nodeFieldContainer.find('.admin-page-framework-repeatable-field-buttons').last(),sMessage=$(this).formatPrintText('{$sCannotRemoveMore}',sMinNumberOfFields),nodeMessage=$('<span class=\"repeatable-error repeatable-field-error\" id=\"repeatable-error-'+sFieldsContainerID+'\">'+sMessage+'</span>');if(nodeFieldsContainer.find('#repeatable-error-'+sFieldsContainerID).length>0){ nodeFieldsContainer.find('#repeatable-error-'+sFieldsContainerID).replaceWith(nodeMessage) }else nodeLastRepeaterButtons.before(nodeMessage);nodeMessage.delay(2e3).fadeOut(1e3);return };nodeFieldContainer.nextAll().each(function(){ $(this).decrementIDAttribute('id');$(this).find('label').decrementIDAttribute('for');$(this).find('input,textarea,select').decrementIDAttribute('id');$(this).find('input:not(.apf_checkbox),textarea,select').decrementNameAttribute('name');$(this).find('input.apf_checkbox').decrementNameAttribute('name',-2) });var oNextField=nodeFieldContainer.next();nodeFieldContainer.remove();oNextField.callBackRemoveRepeatableField(nodeFieldContainer.data('type'),nodeFieldContainer.attr('id'),0,0,0);var nodeRemoveButtons=nodeFieldsContainer.find('.repeatable-field-remove');if(1===nodeRemoveButtons.length)nodeRemoveButtons.css('visibility','hidden') } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_RepeatableSection extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        $sCannotAddMore = $_oMsg->get('allowed_maximum_number_of_sections');
        $sCannotRemoveMore = $_oMsg->get('allowed_minimum_number_of_sections');
        return "(function($){ $.fn.updateAPFRepeatableSections=function(aSettings){ var nodeThis=this,sSectionsContainerID=nodeThis.find('.repeatable-section-add').first().closest('.admin-page-framework-sectionset').attr('id');if(!$.fn.aAPFRepeatableSectionsOptions)$.fn.aAPFRepeatableSectionsOptions=[];if(!$.fn.aAPFRepeatableSectionsOptions.hasOwnProperty(sSectionsContainerID))$.fn.aAPFRepeatableSectionsOptions[sSectionsContainerID]=$.extend({ max:0,min:0},aSettings);var aOptions=$.fn.aAPFRepeatableSectionsOptions[sSectionsContainerID];$(nodeThis).find('.repeatable-section-add').click(function(){ $(this).addAPFRepeatableSection();return false });$(nodeThis).find('.repeatable-section-remove').click(function(){ $(this).removeAPFRepeatableSection();return false });var sSectionID=nodeThis.find('.repeatable-section-add').first().closest('.admin-page-framework-section').attr('id'),nCurrentSectionCount=jQuery('#'+sSectionsContainerID).find('.admin-page-framework-section').length;if(aOptions.min>0&&nCurrentSectionCount>0)if((aOptions.min-nCurrentSectionCount)>0)$('#'+sSectionID).addAPFRepeatableSection(sSectionID) };$.fn.addAPFRepeatableSection=function(sSectionContainerID){ if(typeof sSectionContainerID==='undefined')var sSectionContainerID=$(this).closest('.admin-page-framework-section').attr('id');var nodeSectionContainer=$('#'+sSectionContainerID),nodeNewSection=nodeSectionContainer.clone(),nodeSectionsContainer=nodeSectionContainer.closest('.admin-page-framework-sectionset'),sSectionsContainerID=nodeSectionsContainer.attr('id'),nodeTabsContainer=$('#'+sSectionContainerID).closest('.admin-page-framework-sectionset').find('.admin-page-framework-section-tabs'),sMaxNumberOfSections=$.fn.aAPFRepeatableSectionsOptions[sSectionsContainerID]['max'];if(sMaxNumberOfSections!=0&&nodeSectionsContainer.find('.admin-page-framework-section').length>=sMaxNumberOfSections){ var nodeLastRepeaterButtons=nodeSectionContainer.find('.admin-page-framework-repeatable-section-buttons').last(),sMessage=$(this).formatPrintText('{$sCannotAddMore}',sMaxNumberOfSections),nodeMessage=$('<span class=\"repeatable-section-error\" id=\"repeatable-section-error-'+sSectionsContainerID+'\">'+sMessage+'</span>');if(nodeSectionsContainer.find('#repeatable-section-error-'+sSectionsContainerID).length>0){ nodeSectionsContainer.find('#repeatable-section-error-'+sSectionsContainerID).replaceWith(nodeMessage) }else nodeLastRepeaterButtons.before(nodeMessage);nodeMessage.delay(2e3).fadeOut(1e3);return };nodeNewSection.find('input:not([type=radio], [type=checkbox], [type=submit], [type=hidden]),textarea').val('');nodeNewSection.find('.repeatable-section-error').remove();var sSectionTabSlug=nodeNewSection.find('.admin-page-framework-section-caption').first().attr('data-section_tab');if(!sSectionTabSlug||sSectionTabSlug==='_default')nodeNewSection.find('.admin-page-framework-section-title').not('.admin-page-framework-collapsible-section-title').hide();if('function'===typeof nodeNewSection.enableAPFCollapsibleButton)nodeNewSection.find('.admin-page-framework-collapsible-sections-title, .admin-page-framework-collapsible-section-title').enableAPFCollapsibleButton();nodeNewSection.insertAfter(nodeSectionContainer);nodeSectionContainer.find('input[type=radio][checked=checked]').attr('checked','checked');nodeSectionContainer.nextAll().each(function(iSectionIndex){ incrementAttributes(this);$(this).find('.admin-page-framework-field').each(function(iFieldIndex){ $(this).updateAPFRepeatableFields();$(this).callBackAddRepeatableField($(this).data('type'),$(this).attr('id'),1,iSectionIndex,iFieldIndex) }) });nodeNewSection.updateAPFRepeatableSections();nodeNewSection.find('.admin-page-framework-fields.sortable').each(function(){ $(this).enableAPFSortable() });if(nodeTabsContainer.length>0&&!nodeSectionContainer.hasClass('is_subsection_collapsible')){ var nodeTab=nodeTabsContainer.find('#section_tab-'+sSectionContainerID),nodeNewTab=nodeTab.clone();nodeNewTab.removeClass('active');nodeNewTab.find('input:not([type=radio], [type=checkbox], [type=submit], [type=hidden]),textarea').val('');nodeNewTab.insertAfter(nodeTab);nodeTab.nextAll().each(function(){ incrementAttributes(this);$(this).find('a.anchor').incrementIDAttribute('href') });nodeTabsContainer.closest('.admin-page-framework-section-tabs-contents').createTabs('refresh') };var nodeRemoveButtons=nodeSectionsContainer.find('.repeatable-section-remove');if(nodeRemoveButtons.length>1)nodeRemoveButtons.show();return nodeNewSection };var incrementAttributes=function(oElement,iOccurrence){ var iOccurrence='undefined'!==typeof iOccurrence?iOccurrence:1;$(oElement).incrementIDAttribute('id',iOccurrence);$(oElement).find('tr.admin-page-framework-fieldrow').incrementIDAttribute('id',iOccurrence);$(oElement).find('.admin-page-framework-fieldset').incrementIDAttribute('id',iOccurrence);$(oElement).find('.admin-page-framework-fieldset').incrementIDAttribute('data-field_id',iOccurrence);$(oElement).find('.admin-page-framework-fields').incrementIDAttribute('id',iOccurrence);$(oElement).find('.admin-page-framework-field').incrementIDAttribute('id',iOccurrence);$(oElement).find('table.form-table').incrementIDAttribute('id',iOccurrence);$(oElement).find('.repeatable-field-add').incrementIDAttribute('data-id',iOccurrence);$(oElement).find('label').incrementIDAttribute('for',iOccurrence);$(oElement).find('input,textarea,select').incrementIDAttribute('id',iOccurrence);$(oElement).find('input,textarea,select').incrementNameAttribute('name',iOccurrence) };$.fn.removeAPFRepeatableSection=function(){ var nodeSectionContainer=$(this).closest('.admin-page-framework-section'),sSectionConteinrID=nodeSectionContainer.attr('id'),nodeSectionsContainer=$(this).closest('.admin-page-framework-sectionset'),sSectionsContainerID=nodeSectionsContainer.attr('id'),nodeTabsContainer=nodeSectionsContainer.find('.admin-page-framework-section-tabs'),nodeTabs=nodeTabsContainer.find('.admin-page-framework-section-tab'),sMinNumberOfSections=$.fn.aAPFRepeatableSectionsOptions[sSectionsContainerID]['min'];if(sMinNumberOfSections!=0&&nodeSectionsContainer.find('.admin-page-framework-section').length<=sMinNumberOfSections){ var nodeLastRepeaterButtons=nodeSectionContainer.find('.admin-page-framework-repeatable-section-buttons').last(),sMessage=$(this).formatPrintText('{$sCannotRemoveMore}',sMinNumberOfSections),nodeMessage=$('<span class=\"repeatable-section-error\" id=\"repeatable-section-error-'+sSectionsContainerID+'\">'+sMessage+'</span>');if(nodeSectionsContainer.find('#repeatable-section-error-'+sSectionsContainerID).length>0){ nodeSectionsContainer.find('#repeatable-section-error-'+sSectionsContainerID).replaceWith(nodeMessage) }else nodeLastRepeaterButtons.before(nodeMessage);nodeMessage.delay(2e3).fadeOut(1e3);return };var oNextAllSections=nodeSectionContainer.nextAll(),_bIsSubsectionCollapsible=nodeSectionContainer.hasClass('is_subsection_collapsible');nodeSectionContainer.remove();oNextAllSections.each(function(iSectionIndex){ decrementAttributes(this);$(this).find('.admin-page-framework-field').each(function(iFieldIndex){ $(this).callBackRemoveRepeatableField($(this).data('type'),$(this).attr('id'),1,iSectionIndex,iFieldIndex) }) });if(nodeTabsContainer.length>0&&nodeTabs.length>1&&!_bIsSubsectionCollapsible){ nodeSelectionTab=nodeTabsContainer.find('#section_tab-'+sSectionConteinrID);nodeSelectionTab.nextAll().each(function(){ $(this).find('a.anchor').decrementIDAttribute('href');decrementAttributes(this) });if(nodeSelectionTab.prev().length){ nodeSelectionTab.prev().addClass('active') }else nodeSelectionTab.next().addClass('active');nodeSelectionTab.remove();nodeTabsContainer.closest('.admin-page-framework-section-tabs-contents').createTabs('refresh') };var nodeRemoveButtons=nodeSectionsContainer.find('.repeatable-section-remove');if(1===nodeRemoveButtons.length){ nodeRemoveButtons.css('display','none');var sSectionTabSlug=nodeSectionsContainer.find('.admin-page-framework-section-caption').first().attr('data-section_tab');if(!sSectionTabSlug||sSectionTabSlug==='_default')nodeSectionsContainer.find('.admin-page-framework-section-title').first().show() } };var decrementAttributes=function(oElement,iOccurrence){ var iOccurrence='undefined'!==typeof iOccurrence?iOccurrence:1;$(oElement).decrementIDAttribute('id');$(oElement).find('tr.admin-page-framework-fieldrow').decrementIDAttribute('id',iOccurrence);$(oElement).find('.admin-page-framework-fieldset').decrementIDAttribute('id',iOccurrence);$(oElement).find('.admin-page-framework-fieldset').decrementIDAttribute('data-field_id',iOccurrence);$(oElement).find('.admin-page-framework-fields').decrementIDAttribute('id',iOccurrence);$(oElement).find('.admin-page-framework-field').decrementIDAttribute('id',iOccurrence);$(oElement).find('table.form-table').decrementIDAttribute('id',iOccurrence);$(oElement).find('.repeatable-field-add').decrementIDAttribute('data-id',iOccurrence);$(oElement).find('label').decrementIDAttribute('for',iOccurrence);$(oElement).find('input,textarea,select').decrementIDAttribute('id',iOccurrence);$(oElement).find('input,textarea,select').decrementNameAttribute('name',iOccurrence) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_Sortable extends Legull_AdminPageFramework_Script_Base {
    protected function construct() {
        wp_enqueue_script('jquery-ui-sortable');
    }
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.enableAPFSortable=function(sFieldsContainerID){ var _oTarget=typeof sFieldsContainerID==='string'?$('#'+sFieldsContainerID+'.sortable'):this;_oTarget.unbind('sortupdate');_oTarget.unbind('sortstop');var _oSortable=_oTarget.sortable({ items:'> div:not( .disabled )'});_oSortable.bind('sortstop',function(){ $(this).callBackStoppedSortingFields($(this).data('type'),$(this).attr('id'),0) });_oSortable.bind('sortupdate',function(){ var _oFields=$(this).children('div').reverse();_oFields.each(function(iIterationIndex){ var _iIndex=_oFields.length-iIterationIndex-1;$(this).setIndexIDAttribute('id',_iIndex);$(this).find('label').setIndexIDAttribute('for',_iIndex);$(this).find('input,textarea,select').setIndexIDAttribute('id',_iIndex);$(this).find('input:not(.apf_checkbox),textarea,select').setIndexNameAttribute('name',_iIndex);$(this).find('input.apf_checkbox').setIndexNameAttribute('name',_iIndex,-2);$(this).find('input[type=radio]').each(function(){ var sAttr=$(this).prop('checked');if('undefined'!==typeof sAttr&&false!==sAttr)$(this).attr('checked','checked') }) });$(this).find('input[type=radio][checked=checked]').attr('checked','checked');$(this).callBackSortedFields($(this).data('type'),$(this).attr('id'),0) }) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_Tab extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.createTabs=function(asOptions){ var _bIsRefresh=(typeof asOptions==='string'&&asOptions==='refresh');if(typeof asOptions==='object')var aOptions=$.extend({ },asOptions);this.children('ul').each(function(){ var bSetActive=false;$(this).children('li').each(function(i){ var sTabContentID=$(this).children('a').attr('href');if(!_bIsRefresh&&!bSetActive&&$(this).is(':visible')){ $(this).addClass('active');bSetActive=true };if($(this).hasClass('active')){ $(sTabContentID).show() }else $(sTabContentID).css('display','none');$(this).addClass('nav-tab');$(this).children('a').addClass('anchor');$(this).unbind('click');$(this).click(function(e){ e.preventDefault();$(this).siblings('li.active').removeClass('active');$(this).addClass('active');var sTabContentID=$(this).find('a').attr('href'),_oActiveContent=$(this).parent().parent().find(sTabContentID).css('display','block');_oActiveContent.siblings(':not( ul )').css('display','none') }) }) }) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_Utility extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $.fn.reverse=[].reverse;$.fn.formatPrintText=function(){ var aArgs=arguments;return aArgs[0].replace(/{(\d+)}/g,function(match,number){ return typeof aArgs[parseInt(number)+1]!='undefined'?aArgs[parseInt(number)+1]:match }) } }(jQuery));";
    }
}
class Legull_AdminPageFramework_Script_Widget extends Legull_AdminPageFramework_Script_Base {
    static public function getScript() {
        $_aParams = func_get_args() + array(null);
        $_oMsg = $_aParams[0];
        return "(function($){ $(document).ready(function(){ $(document).ajaxComplete(function(event,XMLHttpRequest,ajaxOptions){ var _aRequest={ },_iIndex,_aSplit,_oWidget,_aPairs='string'===typeof ajaxOptions.data?ajaxOptions.data.split('&'):{ };for(_iIndex in _aPairs){ _aSplit=_aPairs[_iIndex].split('=');_aRequest[decodeURIComponent(_aSplit[0])]=decodeURIComponent(_aSplit[1]) };if(_aRequest.action&&('save-widget'===_aRequest.action)){ _oWidget=$('input.widget-id[value=\"'+_aRequest['widget-id']+'\"]').parents('.widget');if($(_oWidget).find('.admin-page-framework-sectionset').length<=0)return;if(!XMLHttpRequest.responseText){ wpWidgets.save(_oWidget,0,1,0);return };$(document).trigger('admin_page_framework_saved_widget',_oWidget) } }) }) }(jQuery));";
    }
}
class Legull_AdminPageFramework_Widget_Factory extends WP_Widget {
    public function __construct($oCaller, $sWidgetTitle, array $aArguments = array()) {
        $aArguments = $aArguments + array('classname' => 'admin_page_framework_widget', 'description' => '',);
        parent::__construct($oCaller->oProp->sClassName, $sWidgetTitle, $aArguments);
        $this->oCaller = $oCaller;
    }
    public function widget($aArguments, $aFormData) {
        echo $aArguments['before_widget'];
        $_sTitle = apply_filters('widget_title', isset($aFormData['title']) ? $aFormData['title'] : '', $aFormData, $this->id_base);
        if ($_sTitle) {
            echo $aArguments['before_title'] . $_sTitle . $aArguments['after_title'];
        }
        $this->oCaller->oUtil->addAndDoActions($this->oCaller, 'do_' . $this->oCaller->oProp->sClassName, $this->oCaller);
        echo $this->oCaller->oUtil->addAndApplyFilters($this->oCaller, "content_{$this->oCaller->oProp->sClassName}", $this->oCaller->content('', $aArguments, $aFormData), $aArguments, $aFormData);
        echo $aArguments['after_widget'];
    }
    public function update($aSubmittedFormData, $aSavedFormData) {
        return $this->oCaller->oUtil->addAndApplyFilters($this->oCaller, "validation_{$this->oCaller->oProp->sClassName}", $this->oCaller->validate($aSubmittedFormData, $aSavedFormData, $this->oCaller), $aSavedFormData, $this->oCaller);
    }
    public function form($aFormData) {
        $this->oCaller->load($this->oCaller);
        $this->oCaller->oUtil->addAndDoActions($this->oCaller, 'load_' . $this->oCaller->oProp->sClassName, $this->oCaller);
        $this->oCaller->_registerFormElements($aFormData);
        $this->oCaller->oProp->aFieldCallbacks = array('hfID' => array($this, 'get_field_id'), 'hfTagID' => array($this, 'get_field_id'), 'hfName' => array($this, 'get_field_name'),);
        $this->oCaller->_printWidgetForm();
    }
    public function _replyToAddClassSelector($sClassSelectors) {
        $sClassSelectors.= ' widefat';
        return trim($sClassSelectors);
    }
}
class Legull_AdminPageFramework_AdminNotice {
    public function __construct($sNotice, array $aAttributes = array('class' => 'error')) {
        $this->sNotice = $sNotice;
        $this->aAttributes = $aAttributes + array('class' => 'error',);
        $this->aAttributes['class'].= ' admin-page-framework-settings-notice-message';
        if (did_action('admin_notices')) {
            $this->_replyToDisplayAdminNotice();
        } else {
            add_action('admin_notices', array($this, '_replyToDisplayAdminNotice'));
        }
    }
    public function _replyToDisplayAdminNotice() {
        echo "<div " . $this->_getAttributes($this->aAttributes) . ">" . "<p>" . $this->sNotice . "</p>" . "</div>";
    }
    private function _getAttributes(array $aAttributes) {
        $_sQuoteCharactor = "'";
        $_aOutput = array();
        foreach ($aAttributes as $_sAttribute => $_asProperty) {
            if ('style' === $_sAttribute && is_array($_asProperty)) {
                $_asProperty = $this->_getStyle();
            }
            if (is_array($_asProperty)) {
                continue;
            }
            if (is_object($_asProperty)) {
                continue;
            }
            if (is_null($_asProperty)) {
                continue;
            }
            $_aOutput[] = "{$_sAttribute}={$_sQuoteCharactor}" . esc_attr($_asProperty) . "{$_sQuoteCharactor}";
        }
        return trim(implode(' ', $_aOutput));
    }
    private function _getStyle(array $aCSSRules) {
        $_sOutput = '';
        foreach ($aCSSRules as $_sProperty => $_sValue) {
            $_sOutput.= $_sProperty . ': ' . $_sValue . '; ';
        }
        return trim($_sOutput);
    }
}
abstract class Legull_AdminPageFramework_PluginBootstrap {
    public $sFilePath;
    public $bIsAdmin;
    public $sHookPrefix;
    public function __construct($sPluginFilePath, $sPluginHookPrefix = '', $sSetUpHook = 'plugins_loaded', $iPriority = 10) {
        if ($this->_hasLoaded()) {
            return;
        }
        $this->sFilePath = $sPluginFilePath;
        $this->bIsAdmin = is_admin();
        $this->sHookPrefix = $sPluginHookPrefix;
        $this->sSetUpHook = $sSetUpHook;
        $this->iPriority = $iPriority;
        $_bValid = $this->start();
        if (false === $_bValid) {
            return;
        }
        $this->setConstants();
        $this->setGlobals();
        $this->_registerClasses();
        register_activation_hook($this->sFilePath, array($this, 'replyToPluginActivation'));
        register_deactivation_hook($this->sFilePath, array($this, 'replyToPluginDeactivation'));
        if (!$this->sSetUpHook || did_action($this->sSetUpHook)) {
            $this->_replyToLoadPluginComponents();
        } else {
            add_action($this->sSetUpHook, array($this, '_replyToLoadPluginComponents'), $this->iPriority);
        }
        add_action('init', array($this, 'setLocalization'));
        $this->construct();
    }
    protected function _hasLoaded() {
        static $_bLoaded = false;
        if ($_bLoaded) {
            return true;
        }
        $_bLoaded = true;
        return false;
    }
    protected function _registerClasses() {
        if (!class_exists('Legull_AdminPageFramework_RegisterClasses')) {
            return;
        }
        new Legull_AdminPageFramework_RegisterClasses($this->getScanningDirs(), array(), $this->getClasses());
    }
    public function _replyToLoadPluginComponents() {
        if ($this->sHookPrefix) {
            do_action("{$this->sHookPrefix}_action_before_loading_plugin");
        }
        $this->setUp();
        if ($this->sHookPrefix) {
            do_action("{$this->sHookPrefix}_action_after_loading_plugin");
        }
    }
    public function setConstants() {
    }
    public function setGlobals() {
    }
    public function getClasses() {
        $_aClasses = array();
        return $_aClasses;
    }
    public function getScanningDirs() {
        $_aDirs = array();
        return $_aDirs;
    }
    public function replyToPluginActivation() {
    }
    public function replyToPluginDeactivation() {
    }
    public function setLocalization() {
    }
    public function setUp() {
    }
    protected function construct() {
    }
    public function start() {
    }
}
class Legull_AdminPageFramework_Requirement {
    private $_aRequirements = array();
    public $aWarnings = array();
    private $_aDefaultRequirements = array('php' => array('version' => '5.2.4', 'error' => 'The plugin requires the PHP version %1$s or higher.',), 'wordpress' => array('version' => '3.3', 'error' => 'The plugin requires the WordPress version %1$s or higher.',), 'mysql' => array('version' => '5.0', 'error' => 'The plugin requires the MySQL version %1$s or higher.',), 'functions' => array(), 'classes' => array(), 'constants' => array(), 'files' => array(),);
    public function __construct(array $aRequirements = array(), $sScriptName = '') {
        $aRequirements = $aRequirements + $this->_aDefaultRequirements;
        $aRequirements = array_filter($aRequirements, 'is_array');
        foreach (array('php', 'mysql', 'wordpress') as $_iIndex => $_sName) {
            if (isset($aRequirements[$_sName])) {
                $aRequirements[$_sName] = $aRequirements[$_sName] + $this->_aDefaultRequirements[$_sName];
            }
        }
        $this->_aRequirements = $aRequirements;
        $this->_sScriptName = $sScriptName;
    }
    public function check() {
        $_aWarnings = array();
        if (isset($this->_aRequirements['php']['version']) && !$this->_checkPHPVersion($this->_aRequirements['php']['version'])) {
            $_aWarnings[] = sprintf($this->_aRequirements['php']['error'], $this->_aRequirements['php']['version']);
        }
        if (isset($this->_aRequirements['wordpress']['version']) && !$this->_checkWordPressVersion($this->_aRequirements['wordpress']['version'])) {
            $_aWarnings[] = sprintf($this->_aRequirements['wordpress']['error'], $this->_aRequirements['wordpress']['version']);
        }
        if (isset($this->_aRequirements['mysql']['version']) && !$this->_checkMySQLVersion($this->_aRequirements['mysql']['version'])) {
            $_aWarnings[] = sprintf($this->_aRequirements['mysql']['error'], $this->_aRequirements['mysql']['version']);
        }
        $_aWarnings = array_merge($_aWarnings, isset($this->_aRequirements['functions']) ? $this->_checkFunctions($this->_aRequirements['functions']) : array(), isset($this->_aRequirements['classes']) ? $this->_checkClasses($this->_aRequirements['classes']) : array(), isset($this->_aRequirements['constants']) ? $this->_checkConstants($this->_aRequirements['constants']) : array(), isset($this->_aRequirements['files']) ? $this->_checkFiles($this->_aRequirements['files']) : array());
        $this->aWarnings = array_filter($_aWarnings);
        return count($this->aWarnings);
    }
    private function _checkPHPVersion($sPHPVersion) {
        return version_compare(phpversion(), $sPHPVersion, ">=");
    }
    private function _checkWordPressVersion($sWordPressVersion) {
        return version_compare($GLOBALS['wp_version'], $sWordPressVersion, ">=");
    }
    private function _checkMySQLVersion($sMySQLVersion) {
        global $wpdb;
        $_sInstalledMySQLVersion = isset($wpdb->use_mysqli) && $wpdb->use_mysqli ? @mysqli_get_server_info($wpdb->dbh) : @mysql_get_server_info();
        return $_sInstalledMySQLVersion ? version_compare($_sInstalledMySQLVersion, $sMySQLVersion, ">=") : true;
    }
    private function _checkClasses($aClasses) {
        return $this->_getWarningsByFunctionName('class_exists', $aClasses);
    }
    private function _checkFunctions($aFunctions) {
        return $this->_getWarningsByFunctionName('function_exists', $aFunctions);
    }
    private function _checkConstants($aConstants) {
        return $this->_getWarningsByFunctionName('defined', $aConstants);
    }
    private function _checkFiles($aFilePaths) {
        return $this->_getWarningsByFunctionName('file_exists', $aFilePaths);
    }
    private function _getWarningsByFunctionName($sFuncName, $aSubjects) {
        $_aWarnings = array();
        foreach ($aSubjects as $_sSubject => $_sWarning) {
            if (!call_user_func_array($sFuncName, array($_sSubject))) {
                $_aWarnings[] = sprintf($_sWarning, $_sSubject);
            }
        }
        return $_aWarnings;
    }
    public function setAdminNotices() {
        add_action('admin_notices', array($this, '_replyToPrintAdminNotices'));
    }
    public function _replyToPrintAdminNotices() {
        $_aWarnings = array_unique($this->aWarnings);
        if (empty($_aWarnings)) {
            return;
        }
        echo "<div class='error'>" . "<p>" . $this->_getWarnings() . "</p>" . "</div>";
    }
    private function _getWarnings() {
        $_aWarnings = array_unique($this->aWarnings);
        if (empty($_aWarnings)) {
            return '';
        }
        $_sScripTitle = $this->_sScriptName ? "<strong>" . $this->_sScriptName . "</strong>:&nbsp;" : '';
        return $_sScripTitle . implode('<br />', $_aWarnings);
    }
    public function deactivatePlugin($sPluginFilePath, $sMessage = '', $bIsOnActivation = false) {
        add_action('admin_notices', array($this, '_replyToPrintAdminNotices'));
        $this->aWarnings[] = '<strong>' . $sMessage . '</strong>';
        if (!function_exists('deactivate_plugins')) {
            if (!@include (ABSPATH . '/wp-admin/includes/plugin.php')) {
                return;
            }
        }
        deactivate_plugins($sPluginFilePath);
        if ($bIsOnActivation) {
            $_sPluginListingPage = add_query_arg(array(), $GLOBALS['pagenow']);
            wp_die($this->_getWarnings() . "<p><a href='$_sPluginListingPage'>Go back</a>.</p>");
        }
    }
}
class Legull_AdminPageFramework_TableOfContents {
    public function __construct($sHTML, $iDepth = 4, $sTitle = '') {
        $this->sTitle = $sTitle;
        $this->sHTML = $sHTML;
        $this->iDepth = $iDepth;
    }
    public function get() {
        return $this->getTOC() . $this->getCOntents();
    }
    public function getContents() {
        return $this->sHTML;
    }
    public function getTOC() {
        $iDepth = $this->iDepth;
        $this->sHTML = preg_replace_callback('/<h[2-' . $iDepth . ']*[^>]*>.*?<\/h[2-' . $iDepth . ']>/i', array($this, '_replyToInsertNamedElement'), $this->sHTML);
        $_aOutput = array();
        foreach ($this->_aMatches as $_iIndex => $_sMatch) {
            $_sMatch = strip_tags($_sMatch, '<h1><h2><h3><h4><h5><h6><h7><h8>');
            $_sMatch = preg_replace('/<h([1-' . $iDepth . '])>/', '<li class="toc$1"><a href="#toc_' . $_iIndex . '">', $_sMatch);
            $_sMatch = preg_replace('/<\/h[1-' . $iDepth . ']>/', '</a></li>', $_sMatch);
            $_aOutput[] = $_sMatch;
        }
        $this->sTitle = $this->sTitle ? '<p class="toc-title">' . $this->sTitle . '</p>' : '';
        return '<div class="toc">' . $this->sTitle . '<ul>' . implode(PHP_EOL, $_aOutput) . '</ul>' . '</div>';
    }
    protected $_aMatches = array();
    public function _replyToInsertNamedElement($aMatches) {
        static $_icount = - 1;
        $_icount++;
        $this->_aMatches[] = $aMatches[0];
        return "<span class='toc_header_link' id='toc_{$_icount}'></span>" . PHP_EOL . $aMatches[0];
    }
}
class Legull_AdminPageFramework_WPReadmeParser {
    static private $_aStructure_Callbacks = array('code_block' => null, '%PLUGIN_DIR_URL%' => null, '%WP_ADMIN_URL%' => null,);
    public function __construct($sFilePath = '', array $aReplacements = array(), array $aCallbacks = array()) {
        $this->sText = file_exists($sFilePath) ? file_get_contents($sFilePath) : '';
        $this->_aSections = $this->sText ? $this->_getSplitContentsBySection($this->sText) : array();
        $this->aReplacements = $aReplacements;
        $this->aCallbacks = $aCallbacks + self::$_aStructure_Callbacks;
    }
    public function setText($sText) {
        $this->sText = $sText;
        $this->_aSections = $this->sText ? $this->_getSplitContentsBySection($this->sText) : array();
    }
    private function _getSplitContentsBySection($sText) {
        return preg_split('/^[\s]*==[\s]*(.+?)[\s]*==/m', $sText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }
    public function get($sSectionName = '') {
        return $sSectionName ? $this->getSection($sSectionName) : $this->_getParsedText($this->sText);
    }
    public function getSection($sSectionName) {
        $_sContent = $this->getRawSection($sSectionName);
        return $this->_getParsedText($_sContent);
    }
    private function _getParsedText($sContent) {
        $_sContent = preg_replace('/`(.*?)`/', '<code>\\1</code>', $sContent);
        $_sContent = preg_replace_callback('/`(.*?)`/ms', array($this, '_replyToReplaceCodeBlocks'), $_sContent);
        $_sContent = preg_replace('/= (.*?) =/', '<h4>\\1</h4>', $_sContent);
        $_sContent = str_replace(array_keys($this->aReplacements), array_values($this->aReplacements), $_sContent);
        $_oParsedown = new Legull_AdminPageFramework_Parsedown();
        return $_oParsedown->text($_sContent);
    }
    public function _replyToReplaceCodeBlocks($aMatches) {
        if (!isset($aMatches[1])) {
            return $aMatches[0];
        }
        $_sIntact = trim($aMatches[1]);
        $_sModified = "<pre><code>" . $this->getSyntaxHighlightedPHPCode($_sIntact) . "</code></pre>";
        return is_callable($this->aCallbacks['code_block']) ? call_user_func_array($this->aCallbacks['code_block'], array($_sModified, $_sIntact)) : $_sModified;
    }
    public function getRawSection($sSectionName) {
        $_iIndex = array_search($sSectionName, $this->_aSections);
        return false === $_iIndex ? '' : trim($this->_aSections[$_iIndex + 1]);
    }
    public function getSyntaxHighlightedPHPCode($sCode) {
        $_bHasPHPTag = "<?php" === substr($sCode, 0, 5);
        $sCode = $_bHasPHPTag ? $sCode : "<?php " . $sCode;
        $sCode = str_replace('"', "'", $sCode);
        $sCode = highlight_string($sCode, true);
        $sCode = $_bHasPHPTag ? $sCode : preg_replace('/(&lt;|<)\Q?php\E(&nbsp;)?/', '', $sCode, 1);
        return $sCode;
    }
}
class Legull_AdminPageFramework_Parsedown {
    function text($text) {
        $this->Definitions = array();
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = trim($text, "\n");
        $lines = explode("\n", $text);
        $markup = $this->lines($lines);
        $markup = trim($markup, "\n");
        return $markup;
    }
    private $breaksEnabled;
    function setBreaksEnabled($breaksEnabled) {
        $this->breaksEnabled = $breaksEnabled;
        return $this;
    }
    private $markupEscaped;
    function setMarkupEscaped($markupEscaped) {
        $this->markupEscaped = $markupEscaped;
        return $this;
    }
    private $urlsLinked = true;
    function setUrlsLinked($urlsLinked) {
        $this->urlsLinked = $urlsLinked;
        return $this;
    }
    protected $BlockTypes = array('#' => array('Header'), '*' => array('Rule', 'List'), '+' => array('List'), '-' => array('SetextHeader', 'Table', 'Rule', 'List'), '0' => array('List'), '1' => array('List'), '2' => array('List'), '3' => array('List'), '4' => array('List'), '5' => array('List'), '6' => array('List'), '7' => array('List'), '8' => array('List'), '9' => array('List'), ':' => array('Table'), '<' => array('Comment', 'Markup'), '=' => array('SetextHeader'), '>' => array('Quote'), '_' => array('Rule'), '`' => array('FencedCode'), '|' => array('Table'), '~' => array('FencedCode'),);
    protected $DefinitionTypes = array('[' => array('Reference'),);
    protected $unmarkedBlockTypes = array('Code',);
    private function lines(array $lines) {
        $CurrentBlock = null;
        foreach ($lines as $line) {
            if (chop($line) === '') {
                if (isset($CurrentBlock)) {
                    $CurrentBlock['interrupted'] = true;
                }
                continue;
            }
            if (strpos($line, "\t") !== false) {
                $parts = explode("\t", $line);
                $line = $parts[0];
                unset($parts[0]);
                foreach ($parts as $part) {
                    $shortage = 4 - mb_strlen($line, 'utf-8') % 4;
                    $line.= str_repeat(' ', $shortage);
                    $line.= $part;
                }
            }
            $indent = 0;
            while (isset($line[$indent]) and $line[$indent] === ' ') {
                $indent++;
            }
            $text = $indent > 0 ? substr($line, $indent) : $line;
            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);
            if (isset($CurrentBlock['incomplete'])) {
                $Block = $this->{'block' . $CurrentBlock['type'] . 'Continue'}($Line, $CurrentBlock);
                if (isset($Block)) {
                    $CurrentBlock = $Block;
                    continue;
                } else {
                    if (method_exists($this, 'block' . $CurrentBlock['type'] . 'Complete')) {
                        $CurrentBlock = $this->{'block' . $CurrentBlock['type'] . 'Complete'}($CurrentBlock);
                    }
                    unset($CurrentBlock['incomplete']);
                }
            }
            $marker = $text[0];
            if (isset($this->DefinitionTypes[$marker])) {
                foreach ($this->DefinitionTypes[$marker] as $definitionType) {
                    $Definition = $this->{'definition' . $definitionType}($Line, $CurrentBlock);
                    if (isset($Definition)) {
                        $this->Definitions[$definitionType][$Definition['id']] = $Definition['data'];
                        continue 2;
                    }
                }
            }
            $blockTypes = $this->unmarkedBlockTypes;
            if (isset($this->BlockTypes[$marker])) {
                foreach ($this->BlockTypes[$marker] as $blockType) {
                    $blockTypes[] = $blockType;
                }
            }
            foreach ($blockTypes as $blockType) {
                $Block = $this->{'block' . $blockType}($Line, $CurrentBlock);
                if (isset($Block)) {
                    $Block['type'] = $blockType;
                    if (!isset($Block['identified'])) {
                        $Elements[] = $CurrentBlock['element'];
                        $Block['identified'] = true;
                    }
                    if (method_exists($this, 'block' . $blockType . 'Continue')) {
                        $Block['incomplete'] = true;
                    }
                    $CurrentBlock = $Block;
                    continue 2;
                }
            }
            if (isset($CurrentBlock) and !isset($CurrentBlock['type']) and !isset($CurrentBlock['interrupted'])) {
                $CurrentBlock['element']['text'].= "\n" . $text;
            } else {
                $Elements[] = $CurrentBlock['element'];
                $CurrentBlock = $this->paragraph($Line);
                $CurrentBlock['identified'] = true;
            }
        }
        if (isset($CurrentBlock['incomplete']) and method_exists($this, 'block' . $CurrentBlock['type'] . 'Complete')) {
            $CurrentBlock = $this->{'block' . $CurrentBlock['type'] . 'Complete'}($CurrentBlock);
        }
        $Elements[] = $CurrentBlock['element'];
        unset($Elements[0]);
        $markup = $this->elements($Elements);
        return $markup;
    }
    protected function blockCode($Line, $Block = null) {
        if (isset($Block) and !isset($Block['type']) and !isset($Block['interrupted'])) {
            return;
        }
        if ($Line['indent'] >= 4) {
            $text = substr($Line['body'], 4);
            $Block = array('element' => array('name' => 'pre', 'handler' => 'element', 'text' => array('name' => 'code', 'text' => $text,),),);
            return $Block;
        }
    }
    protected function blockCodeContinue($Line, $Block) {
        if ($Line['indent'] >= 4) {
            if (isset($Block['interrupted'])) {
                $Block['element']['text']['text'].= "\n";
                unset($Block['interrupted']);
            }
            $Block['element']['text']['text'].= "\n";
            $text = substr($Line['body'], 4);
            $Block['element']['text']['text'].= $text;
            return $Block;
        }
    }
    protected function blockCodeComplete($Block) {
        $text = $Block['element']['text']['text'];
        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
        $Block['element']['text']['text'] = $text;
        return $Block;
    }
    protected function blockComment($Line) {
        if ($this->markupEscaped) {
            return;
        }
        if (isset($Line['text'][3]) and $Line['text'][3] === '-' and $Line['text'][2] === '-' and $Line['text'][1] === '!') {
            $Block = array('element' => array('text' => $Line['body'],),);
            if (preg_match('/-->$/', $Line['text'])) {
                $Block['closed'] = true;
            }
            return $Block;
        }
    }
    protected function blockCommentContinue($Line, array $Block) {
        if (isset($Block['closed'])) {
            return;
        }
        $Block['element']['text'].= "\n" . $Line['body'];
        if (preg_match('/-->$/', $Line['text'])) {
            $Block['closed'] = true;
        }
        return $Block;
    }
    protected function blockFencedCode($Line) {
        if (preg_match('/^([' . $Line['text'][0] . ']{3,})[ ]*([\w-]+)?[ ]*$/', $Line['text'], $matches)) {
            $Element = array('name' => 'code', 'text' => '',);
            if (isset($matches[2])) {
                $class = 'language-' . $matches[2];
                $Element['attributes'] = array('class' => $class,);
            }
            $Block = array('char' => $Line['text'][0], 'element' => array('name' => 'pre', 'handler' => 'element', 'text' => $Element,),);
            return $Block;
        }
    }
    protected function blockFencedCodeContinue($Line, $Block) {
        if (isset($Block['complete'])) {
            return;
        }
        if (isset($Block['interrupted'])) {
            $Block['element']['text']['text'].= "\n";
            unset($Block['interrupted']);
        }
        if (preg_match('/^' . $Block['char'] . '{3,}[ ]*$/', $Line['text'])) {
            $Block['element']['text']['text'] = substr($Block['element']['text']['text'], 1);
            $Block['complete'] = true;
            return $Block;
        }
        $Block['element']['text']['text'].= "\n" . $Line['body'];;
        return $Block;
    }
    protected function blockFencedCodeComplete($Block) {
        $text = $Block['element']['text']['text'];
        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
        $Block['element']['text']['text'] = $text;
        return $Block;
    }
    protected function blockHeader($Line) {
        if (isset($Line['text'][1])) {
            $level = 1;
            while (isset($Line['text'][$level]) and $Line['text'][$level] === '#') {
                $level++;
            }
            if ($level > 6 or $Line['text'][$level] !== ' ') {
                return;
            }
            $text = trim($Line['text'], '# ');
            $Block = array('element' => array('name' => 'h' . min(6, $level), 'text' => $text, 'handler' => 'line',),);
            return $Block;
        }
    }
    protected function blockList($Line) {
        list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');
        if (preg_match('/^(' . $pattern . '[ ]+)(.*)/', $Line['text'], $matches)) {
            $Block = array('indent' => $Line['indent'], 'pattern' => $pattern, 'element' => array('name' => $name, 'handler' => 'elements',),);
            $Block['li'] = array('name' => 'li', 'handler' => 'li', 'text' => array($matches[2],),);
            $Block['element']['text'][] = & $Block['li'];
            return $Block;
        }
    }
    protected function blockListContinue($Line, array $Block) {
        if ($Block['indent'] === $Line['indent'] and preg_match('/^' . $Block['pattern'] . '[ ]+(.*)/', $Line['text'], $matches)) {
            if (isset($Block['interrupted'])) {
                $Block['li']['text'][] = '';
                unset($Block['interrupted']);
            }
            unset($Block['li']);
            $Block['li'] = array('name' => 'li', 'handler' => 'li', 'text' => array($matches[1],),);
            $Block['element']['text'][] = & $Block['li'];
            return $Block;
        }
        if (!isset($Block['interrupted'])) {
            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);
            $Block['li']['text'][] = $text;
            return $Block;
        }
        if ($Line['indent'] > 0) {
            $Block['li']['text'][] = '';
            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);
            $Block['li']['text'][] = $text;
            unset($Block['interrupted']);
            return $Block;
        }
    }
    protected function blockQuote($Line) {
        if (preg_match('/^>[ ]?(.*)/', $Line['text'], $matches)) {
            $Block = array('element' => array('name' => 'blockquote', 'handler' => 'lines', 'text' => (array)$matches[1],),);
            return $Block;
        }
    }
    protected function blockQuoteContinue($Line, array $Block) {
        if ($Line['text'][0] === '>' and preg_match('/^>[ ]?(.*)/', $Line['text'], $matches)) {
            if (isset($Block['interrupted'])) {
                $Block['element']['text'][] = '';
                unset($Block['interrupted']);
            }
            $Block['element']['text'][] = $matches[1];
            return $Block;
        }
        if (!isset($Block['interrupted'])) {
            $Block['element']['text'][] = $Line['text'];
            return $Block;
        }
    }
    protected function blockRule($Line) {
        if (preg_match('/^([' . $Line['text'][0] . '])([ ]*\1){2,}[ ]*$/', $Line['text'])) {
            $Block = array('element' => array('name' => 'hr'),);
            return $Block;
        }
    }
    protected function blockSetextHeader($Line, array $Block = null) {
        if (!isset($Block) or isset($Block['type']) or isset($Block['interrupted'])) {
            return;
        }
        if (chop($Line['text'], $Line['text'][0]) === '') {
            $Block['element']['name'] = $Line['text'][0] === '=' ? 'h1' : 'h2';
            return $Block;
        }
    }
    protected function blockMarkup($Line) {
        if ($this->markupEscaped) {
            return;
        }
        $attrName = '[a-zA-Z_:][\w:.-]*';
        $attrValue = '(?:[^"\'=<>`\s]+|".*?"|\'.*?\')';
        preg_match('/^<(\w[\d\w]*)((?:\s' . $attrName . '(?:\s*=\s*' . $attrValue . ')?)*)\s*(\/?)>/', $Line['text'], $matches);
        if (!$matches or in_array($matches[1], $this->textLevelElements)) {
            return;
        }
        $Block = array('depth' => 0, 'element' => array('name' => $matches[1], 'text' => null,),);
        $remainder = substr($Line['text'], strlen($matches[0]));
        if (trim($remainder) === '') {
            if ($matches[3] or in_array($matches[1], $this->voidElements)) {
                $Block['closed'] = true;
            }
        } else {
            if ($matches[3] or in_array($matches[1], $this->voidElements)) {
                return;
            }
            preg_match('/(.*)<\/' . $matches[1] . '>\s*$/i', $remainder, $nestedMatches);
            if ($nestedMatches) {
                $Block['closed'] = true;
                $Block['element']['text'] = $nestedMatches[1];
            } else {
                $Block['element']['text'] = $remainder;
            }
        }
        if (!$matches[2]) {
            return $Block;
        }
        preg_match_all('/\s(' . $attrName . ')(?:\s*=\s*(' . $attrValue . '))?/', $matches[2], $nestedMatches, PREG_SET_ORDER);
        foreach ($nestedMatches as $nestedMatch) {
            if (!isset($nestedMatch[2])) {
                $Block['element']['attributes'][$nestedMatch[1]] = '';
            } elseif ($nestedMatch[2][0] === '"' or $nestedMatch[2][0] === '\'') {
                $Block['element']['attributes'][$nestedMatch[1]] = substr($nestedMatch[2], 1, -1);
            } else {
                $Block['element']['attributes'][$nestedMatch[1]] = $nestedMatch[2];
            }
        }
        return $Block;
    }
    protected function blockMarkupContinue($Line, array $Block) {
        if (isset($Block['closed'])) {
            return;
        }
        if (preg_match('/^<' . $Block['element']['name'] . '(?:\s.*[\'"])?\s*>/i', $Line['text'])) {
            $Block['depth']++;
        }
        if (preg_match('/(.*?)<\/' . $Block['element']['name'] . '>\s*$/i', $Line['text'], $matches)) {
            if ($Block['depth'] > 0) {
                $Block['depth']--;
            } else {
                $Block['element']['text'].= "\n";
                $Block['closed'] = true;
            }
            $Block['element']['text'].= $matches[1];
        }
        if (isset($Block['interrupted'])) {
            $Block['element']['text'].= "\n";
            unset($Block['interrupted']);
        }
        if (!isset($Block['closed'])) {
            $Block['element']['text'].= "\n" . $Line['body'];
        }
        return $Block;
    }
    protected function blockTable($Line, array $Block = null) {
        if (!isset($Block) or isset($Block['type']) or isset($Block['interrupted'])) {
            return;
        }
        if (strpos($Block['element']['text'], '|') !== false and chop($Line['text'], ' -:|') === '') {
            $alignments = array();
            $divider = $Line['text'];
            $divider = trim($divider);
            $divider = trim($divider, '|');
            $dividerCells = explode('|', $divider);
            foreach ($dividerCells as $dividerCell) {
                $dividerCell = trim($dividerCell);
                if ($dividerCell === '') {
                    continue;
                }
                $alignment = null;
                if ($dividerCell[0] === ':') {
                    $alignment = 'left';
                }
                if (substr($dividerCell, -1) === ':') {
                    $alignment = $alignment === 'left' ? 'center' : 'right';
                }
                $alignments[] = $alignment;
            }
            $HeaderElements = array();
            $header = $Block['element']['text'];
            $header = trim($header);
            $header = trim($header, '|');
            $headerCells = explode('|', $header);
            foreach ($headerCells as $index => $headerCell) {
                $headerCell = trim($headerCell);
                $HeaderElement = array('name' => 'th', 'text' => $headerCell, 'handler' => 'line',);
                if (isset($alignments[$index])) {
                    $alignment = $alignments[$index];
                    $HeaderElement['attributes'] = array('align' => $alignment,);
                }
                $HeaderElements[] = $HeaderElement;
            }
            $Block = array('alignments' => $alignments, 'identified' => true, 'element' => array('name' => 'table', 'handler' => 'elements',),);
            $Block['element']['text'][] = array('name' => 'thead', 'handler' => 'elements',);
            $Block['element']['text'][] = array('name' => 'tbody', 'handler' => 'elements', 'text' => array(),);
            $Block['element']['text'][0]['text'][] = array('name' => 'tr', 'handler' => 'elements', 'text' => $HeaderElements,);
            return $Block;
        }
    }
    protected function blockTableContinue($Line, array $Block) {
        if ($Line['text'][0] === '|' or strpos($Line['text'], '|')) {
            $Elements = array();
            $row = $Line['text'];
            $row = trim($row);
            $row = trim($row, '|');
            $cells = explode('|', $row);
            foreach ($cells as $index => $cell) {
                $cell = trim($cell);
                $Element = array('name' => 'td', 'handler' => 'line', 'text' => $cell,);
                if (isset($Block['alignments'][$index])) {
                    $Element['attributes'] = array('align' => $Block['alignments'][$index],);
                }
                $Elements[] = $Element;
            }
            $Element = array('name' => 'tr', 'handler' => 'elements', 'text' => $Elements,);
            $Block['element']['text'][1]['text'][] = $Element;
            return $Block;
        }
    }
    protected function definitionReference($Line) {
        if (preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/', $Line['text'], $matches)) {
            $Definition = array('id' => strtolower($matches[1]), 'data' => array('url' => $matches[2], 'title' => null,),);
            if (isset($matches[3])) {
                $Definition['data']['title'] = $matches[3];
            }
            return $Definition;
        }
    }
    protected function paragraph($Line) {
        $Block = array('element' => array('name' => 'p', 'text' => $Line['text'], 'handler' => 'line',),);
        return $Block;
    }
    protected function element(array $Element) {
        $markup = '';
        if (isset($Element['name'])) {
            $markup.= '<' . $Element['name'];
            if (isset($Element['attributes'])) {
                foreach ($Element['attributes'] as $name => $value) {
                    if ($value === null) {
                        continue;
                    }
                    $markup.= ' ' . $name . '="' . $value . '"';
                }
            }
            if (isset($Element['text'])) {
                $markup.= '>';
            } else {
                $markup.= ' />';
                return $markup;
            }
        }
        if (isset($Element['text'])) {
            if (isset($Element['handler'])) {
                $markup.= $this->$Element['handler']($Element['text']);
            } else {
                $markup.= $Element['text'];
            }
        }
        if (isset($Element['name'])) {
            $markup.= '</' . $Element['name'] . '>';
        }
        return $markup;
    }
    protected function elements(array $Elements) {
        $markup = '';
        foreach ($Elements as $Element) {
            if ($Element === null) {
                continue;
            }
            $markup.= "\n" . $this->element($Element);
        }
        $markup.= "\n";
        return $markup;
    }
    protected $InlineTypes = array('"' => array('QuotationMark'), '!' => array('Image'), '&' => array('Ampersand'), '*' => array('Emphasis'), '<' => array('UrlTag', 'EmailTag', 'Tag', 'LessThan'), '>' => array('GreaterThan'), '[' => array('Link'), '_' => array('Emphasis'), '`' => array('Code'), '~' => array('Strikethrough'), '\\' => array('EscapeSequence'),);
    protected $inlineMarkerList = '!"*_&[<>`~\\';
    public function line($text) {
        $markup = '';
        $remainder = $text;
        $markerPosition = 0;
        while ($excerpt = strpbrk($remainder, $this->inlineMarkerList)) {
            $marker = $excerpt[0];
            $markerPosition+= strpos($remainder, $marker);
            foreach ($this->InlineTypes[$marker] as $inlineType) {
                $handler = 'inline' . $inlineType;
                $Inline = $this->$handler($excerpt);
                if (!isset($Inline)) {
                    continue;
                }
                $plainText = substr($text, 0, $markerPosition);
                $markup.= $this->unmarkedText($plainText);
                $markup.= isset($Inline['markup']) ? $Inline['markup'] : $this->element($Inline['element']);
                $text = substr($text, $markerPosition + $Inline['extent']);
                $remainder = $text;
                $markerPosition = 0;
                continue 2;
            }
            $remainder = substr($excerpt, 1);
            $markerPosition++;
        }
        $markup.= $this->unmarkedText($text);
        return $markup;
    }
    protected function inlineAmpersand($excerpt) {
        if (!preg_match('/^&#?\w+;/', $excerpt)) {
            return array('markup' => '&amp;', 'extent' => 1,);
        }
    }
    protected function inlineStrikethrough($excerpt) {
        if (!isset($excerpt[1])) {
            return;
        }
        if ($excerpt[1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $excerpt, $matches)) {
            return array('extent' => strlen($matches[0]), 'element' => array('name' => 'del', 'text' => $matches[1], 'handler' => 'line',),);
        }
    }
    protected function inlineEscapeSequence($excerpt) {
        if (isset($excerpt[1]) and in_array($excerpt[1], $this->specialCharacters)) {
            return array('markup' => $excerpt[1], 'extent' => 2,);
        }
    }
    protected function inlineLessThan() {
        return array('markup' => '&lt;', 'extent' => 1,);
    }
    protected function inlineGreaterThan() {
        return array('markup' => '&gt;', 'extent' => 1,);
    }
    protected function inlineQuotationMark() {
        return array('markup' => '&quot;', 'extent' => 1,);
    }
    protected function inlineUrlTag($excerpt) {
        if (strpos($excerpt, '>') !== false and preg_match('/^<(https?:[\/]{2}[^\s]+?)>/i', $excerpt, $matches)) {
            $url = str_replace(array('&', '<'), array('&amp;', '&lt;'), $matches[1]);
            return array('extent' => strlen($matches[0]), 'element' => array('name' => 'a', 'text' => $url, 'attributes' => array('href' => $url,),),);
        }
    }
    protected function inlineEmailTag($excerpt) {
        if (strpos($excerpt, '>') !== false and preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $excerpt, $matches)) {
            $url = $matches[1];
            if (!isset($matches[2])) {
                $url = 'mailto:' . $url;
            }
            return array('extent' => strlen($matches[0]), 'element' => array('name' => 'a', 'text' => $matches[1], 'attributes' => array('href' => $url,),),);
        }
    }
    protected function inlineTag($excerpt) {
        if ($this->markupEscaped) {
            return;
        }
        if (strpos($excerpt, '>') !== false and preg_match('/^<\/?\w.*?>/s', $excerpt, $matches)) {
            return array('markup' => $matches[0], 'extent' => strlen($matches[0]),);
        }
    }
    protected function inlineCode($excerpt) {
        $marker = $excerpt[0];
        if (preg_match('/^(' . $marker . '+)[ ]*(.+?)[ ]*(?<!' . $marker . ')\1(?!' . $marker . ')/s', $excerpt, $matches)) {
            $text = $matches[2];
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/[ ]*\n/", ' ', $text);
            return array('extent' => strlen($matches[0]), 'element' => array('name' => 'code', 'text' => $text,),);
        }
    }
    protected function inlineImage($excerpt) {
        if (!isset($excerpt[1]) or $excerpt[1] !== '[') {
            return;
        }
        $excerpt = substr($excerpt, 1);
        $Inline = $this->inlineLink($excerpt);
        if ($Inline === null) {
            return;
        }
        $Inline['extent']++;
        $Inline['element'] = array('name' => 'img', 'attributes' => array('src' => $Inline['element']['attributes']['href'], 'alt' => $Inline['element']['text'], 'title' => $Inline['element']['attributes']['title'],),);
        return $Inline;
    }
    protected function inlineLink($excerpt) {
        $Element = array('name' => 'a', 'handler' => 'line', 'text' => null, 'attributes' => array('href' => null, 'title' => null,),);
        $extent = 0;
        $remainder = $excerpt;
        if (preg_match('/\[((?:[^][]|(?R))*)\]/', $remainder, $matches)) {
            $Element['text'] = $matches[1];
            $extent+= strlen($matches[0]);
            $remainder = substr($remainder, $extent);
        } else {
            return;
        }
        if (preg_match('/^\([ ]*([^ ]+?)(?:[ ]+(".+?"|\'.+?\'))?[ ]*\)/', $remainder, $matches)) {
            $Element['attributes']['href'] = $matches[1];
            if (isset($matches[2])) {
                $Element['attributes']['title'] = substr($matches[2], 1, -1);
            }
            $extent+= strlen($matches[0]);
        } else {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
                $definition = $matches[1] ? $matches[1] : $Element['text'];
                $definition = strtolower($definition);
                $extent+= strlen($matches[0]);
            } else {
                $definition = strtolower($Element['text']);
            }
            if (!isset($this->Definitions['Reference'][$definition])) {
                return;
            }
            $Definition = $this->Definitions['Reference'][$definition];
            $Element['attributes']['href'] = $Definition['url'];
            $Element['attributes']['title'] = $Definition['title'];
        }
        $Element['attributes']['href'] = str_replace(array('&', '<'), array('&amp;', '&lt;'), $Element['attributes']['href']);
        return array('extent' => $extent, 'element' => $Element,);
    }
    protected function inlineEmphasis($excerpt) {
        if (!isset($excerpt[1])) {
            return;
        }
        $marker = $excerpt[0];
        if ($excerpt[1] === $marker and preg_match($this->StrongRegex[$marker], $excerpt, $matches)) {
            $emphasis = 'strong';
        } elseif (preg_match($this->EmRegex[$marker], $excerpt, $matches)) {
            $emphasis = 'em';
        } else {
            return;
        }
        return array('extent' => strlen($matches[0]), 'element' => array('name' => $emphasis, 'handler' => 'line', 'text' => $matches[1],),);
    }
    protected $unmarkedInlineTypes = array("  \n" => 'Break', '://' => 'Url',);
    protected function unmarkedText($text) {
        foreach ($this->unmarkedInlineTypes as $snippet => $inlineType) {
            if (strpos($text, $snippet) !== false) {
                $text = $this->{'unmarkedInline' . $inlineType}($text);
            }
        }
        return $text;
    }
    protected function unmarkedInlineBreak($text) {
        if ($this->breaksEnabled) {
            $text = preg_replace('/[ ]*\n/', "<br />\n", $text);
        } else {
            $text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br />\n", $text);
            $text = str_replace(" \n", "\n", $text);
        }
        return $text;
    }
    protected function unmarkedInlineUrl($text) {
        if ($this->urlsLinked !== true) {
            return $text;
        }
        $re = '/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui';
        $offset = 0;
        while (strpos($text, '://', $offset) and preg_match($re, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $url = $matches[0][0];
            $urlLength = strlen($url);
            $urlPosition = $matches[0][1];
            $markup = '<a href="' . $url . '">' . $url . '</a>';
            $markupLength = strlen($markup);
            $text = substr_replace($text, $markup, $urlPosition, $urlLength);
            $offset = $urlPosition + $markupLength;
        }
        return $text;
    }
    protected function li($lines) {
        $markup = $this->lines($lines);
        $trimmedMarkup = trim($markup);
        if (!in_array('', $lines) and substr($trimmedMarkup, 0, 3) === '<p>') {
            $markup = $trimmedMarkup;
            $markup = substr($markup, 3);
            $position = strpos($markup, "</p>");
            $markup = substr_replace($markup, '', $position, 4);
        }
        return $markup;
    }
    static function instance($name = 'default') {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }
        $instance = new self();
        self::$instances[$name] = $instance;
        return $instance;
    }
    private static $instances = array();
    function parse($text) {
        $markup = $this->text($text);
        return $markup;
    }
    protected $Definitions;
    protected $specialCharacters = array('\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!',);
    protected $StrongRegex = array('*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s', '_' => '/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',);
    protected $EmRegex = array('*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s', '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',);
    protected $voidElements = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',);
    protected $textLevelElements = array('a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont', 'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing', 'i', 'rp', 'del', 'code', 'strike', 'marquee', 'q', 'rt', 'ins', 'font', 'strong', 's', 'tt', 'sub', 'mark', 'u', 'xm', 'sup', 'nobr', 'var', 'ruby', 'wbr', 'span', 'time',);
}