<?php
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkGUI.php');


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
     * @var ilShortLinkGUI
     */
    protected $pl;

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

    /**
     * Forwards the flow of control to the ShortLink Plugin and exits afterwards.
     *
     * Used URL is: ILIAS.ch/goto.php?target=ShortLink
     * The exit(); is needed to stop further processing within the goto.php file of ILIAS
     * since later on a redirect is performed and header() function is called again which leads
     * to headers already sent warning.
     */
    public function gotoHook() {
        if (preg_match("/^ShortLink(.*)/", $_GET['target'], $matches)) {

            $next_class = "ilshortlinkgui";
            $class_file = $this->ctrl->lookupClassPath($next_class);

            include_once($class_file);

            $gui = new $next_class();

            $this->ctrl->forwardCommand($gui);
            exit();
        }
    }
}