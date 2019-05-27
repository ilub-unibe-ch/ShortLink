<?php
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');
include_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkTableGUI.php');

/**
 * Class ilShortLinkUIHookGUI
 *
 * @author  Thomas Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version 0.0.1
 *
 *
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
     * @var ilShortLinkPlugin $pl
     */
    protected $pl;
    /**
     * @var ilObjShortLink
     */
    protected $objShortLink;


    /**
     * ilShortLinkUIHookGUI constructor
     *
     */
    function __construct() {
        global $ilCtrl, $ilTabs, $ilAccess, $tpl;

        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->access = $ilAccess;

        $this->pl = ilShortLinkPlugin::getInstance();

        $this->objShortLink = new ilObjShortLink();

    }

    /**
     * Redirects the user to the ShortLink Plugin if cmdNode is found. Otherwise
     * the user is redirected to ilPersonalDesktopGUI and an error message is shown.
     */
    public function gotoHook() {
		if (preg_match("/^ShortLink(.*)/", $_GET['target'], $matches)) {
			$this->ctrl->initBaseClass("ilUIPluginRouterGUI");
			$this->ctrl->setTargetScript("ilias.php");
			$this->ctrl->redirectByClass(["ilUIPluginRouterGUI","ilShortLinkGUI"], "listShortLinks");
		}

    }
}