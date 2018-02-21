<?php
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');


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
        global $ilCtrl, $ilTabs, $ilAccess;

        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->access = $ilAccess;

        $this->pl = new ilShortLinkPlugin();
        $this->objShortLink = new ilObjShortLink();
    }

    /**
     * Redirects the user to the ShortLink Plugin if cmdNode is found. Otherwise
     * the user is redirected to ilPersonalDesktopGUI and an error message is shown.
     */
    public function gotoHook() {
        if (preg_match("/^ShortLink(.*)/", $_GET['target'], $matches)) {
            $cmdNode = $this->objShortLink->getCurrentShortLinkCmdNode();
            if($cmdNode !== "00:00") {
                ilUtil::redirect('ilias.php?cmdClass=ilshortlinkgui&cmdNode=' . $cmdNode . '&baseClass=iluipluginroutergui');
            } else {
                ilUtil::sendFailure($this->pl->txt("cmdNode_not_found") . ' ' . $cmdNode, true);
                ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
            }
        }
    }
}