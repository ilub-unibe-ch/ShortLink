<?php
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');

/**
 * Class ilShortLinkUIHookGUI
 *
 * @author  Thomas Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version 0.0.1
 */
class ilShortLinkUIHookGUI extends ilUIHookPluginGUI {

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var $ilTabs
     */
    protected $tabs;
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * ilShortLinkUIHookGUI constructor
     *
     */
    function __construct() {
        global $ilCtrl, $ilTabs, $ilAccess;

        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->access = $ilAccess;
    }
}