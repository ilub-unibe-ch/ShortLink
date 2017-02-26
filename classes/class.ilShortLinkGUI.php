<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');
require_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkAccess.php');
include_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkTableGUI.php');
require_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('Services/Repository/classes/class.ilObjectPluginGUI.php');


/**
 * GUI-Class ilShortLinkGUI
 * This class manages the ShortLink frontend.
 *
 * @author              Thomas Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version             $Id$
 *
 * @ilCtrl_isCalledBy   ilShortLinkGUI: ilUIPluginRouterGUI
 */
class ilShortLinkGUI extends ilObjectPluginGUI {
    /**
     * @var ilTemplate $my_tpl
     */
    protected $my_tpl;
    /**
     * @var ilCtrl $ctrl
     */
    protected $ctrl;
    /**
     * @var ilToolbarGUI $toolbar
     */
    protected $toolbar;
    /**
     * @var ilObjShortLink $obj
     */
    protected $obj;
    /**
     * @var ilShortLinkAccess $shortLinkAccessChecker
     */
    protected $shortLinkAccessChecker;
    /**
     * @var ilPropertyFormGUI $form
     */
    protected $form;
    /**
     * @var ilObjShortLink $externalFeedBlock
     */
    protected $externalFeedBlock;

    /**
     * ilShortLinkGUI constructor
     *
     * Instantiates local form, plugin, object and AccessChecker member variables.
     * Gets the standard template for HTML output.
     */
    public function __construct() {
        global $ilCtrl, $tpl;

        $this->ctrl = $ilCtrl;

        $this->form = new ilPropertyFormGUI();
        $this->shortLinkAccessChecker = new ilShortLinkAccess();
        $this->pl = new ilShortLinkPlugin();
        $this->obj = new ilObjShortLink();

        $this->my_tpl = $tpl;
        $this->my_tpl->getStandardTemplate();
    }

    /**
     * @return bool
     */
    public function executeCommand() {
        $cmd = $this->ctrl->getCmd();

        switch($cmd){
            case 'add':
                if($this->shortLinkAccessChecker->checkIfUserIsAnonymous()) {
                    ilUtil::sendFailure($this->pl->txt("permission_denied"), true);
                    ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
                    break;
                }
            case 'save':
                $this->$cmd();
                break;
            case 'edit':
            case 'delete':
            case 'confirmedDelete':
            case 'doUpdate':
                if($_GET['link_id'] != NULL) {
                    $this->shortLinkAccessChecker->checkPermission($this->obj, $_GET['link_id']);
                } else if($_POST['obj_id'] != NULL) {
                    $this->shortLinkAccessChecker->checkPermission($this->obj, $_POST['obj_id']);
                } else if($_POST['shortLink_id'] != NULL) {
                    $this->shortLinkAccessChecker->checkPermission($this->obj, $_POST['shortLink_id']);
                } else {
                    ilUtil::sendFailure($this->pl->txt("mapping_wrong"), true);
                    ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
                }
                $this->$cmd();
                break;
            case 'listShortLinks':
                $this->$cmd();
                break;
            default:
                $this->showContent();
                break;
        }
        $this->my_tpl->show();
    }

    protected function showContent(){
        $this->initToolbar();

        if ($this->getToolbar()) {
            $this->my_tpl->setContent($this->getToolbar()->getHTML());
            $this->obj = new ilObjShortLink();
        }

        ilUtil::sendInfo($this->pl->txt('info_box'), true);

        $this->my_tpl->setTitle($this->pl->txt('title'));
        $this->listShortLinks();
    }

    protected function initToolbar() {
        $toolbar = new ilToolbarGUI();
        $toolbar->addButton($this->pl->txt('add'), $this->ctrl->getLinkTarget($this, 'add'));
        $this->setToolbar($toolbar);
    }

    protected function add() {
        $this->form = $this->initConfigurationForm();
        $this->my_tpl->setContent($this->form->getHTML());
    }

    public function save() {
        global $ilUser;
        $this->initConfigurationForm();
        if($this->form->checkInput() && $this->checkInputValidity()) {
            $this->externalFeedBlock = new ilObjShortLink();
            $this->externalFeedBlock->nextId();
            $this->externalFeedBlock->setLongURL($this->form->getInput("longUrl"));
            $this->externalFeedBlock->setShortLink($this->form->getInput("shortLink"));
            $this->externalFeedBlock->setCustomer($this->form->getInput("customer"));
            $this->externalFeedBlock->setContact($ilUser->getLogin());
            $this->externalFeedBlock->doCreate();
        }
        $this->showContent();
    }

    /**
     * Checks for input validity
     *
     * @return bool
     */
    private function checkInputValidity() {
        $longURLOK = $this->checklongURL();
        $shortURLOK = $this->checkShortULR();

        if($longURLOK && $shortURLOK) {
            return TRUE;
        }
        return FALSE;
    }

    private function checklongURL() {
        $isValid = TRUE;
        if(strlen($this->form->getInput("longUrl"))>99) {
            ilUtil::sendFailure($this->pl->txt("full_url_too_long"), true);
            $isValid = FALSE;
        }
        return $isValid;
    }

    private function checkShortULR() {
        $isValid = TRUE;
        $regex = "/^[a-zA-Z0-9]+$/";
        if(!preg_match($regex, $this->form->getInput("shortLink"))) {
            ilUtil::sendFailure($this->pl->txt("characters_not_allowed"), true);
            $isValid = FALSE;
        }
        $this->obj = new ilObjShortLink();
        if($this->obj->checkIfShortLinkAlreadyMentioned($this->form->getInput("shortLink"))) {
            ilUtil::sendFailure($this->pl->txt("exists_already"), true);
            //$this->showContent();
            $isValid = FALSE;
        }
        return $isValid;
    }

    public function listShortLinks() {
        $table = new ilShortLinkTableGUI($this, ilShortLinkPlugin::TABLE_NAME);
        $this->my_tpl->setContent($table->getHTML());
    }

    public function edit() {
        $id = $_GET['link_id'];
        $this->obj = new ilObjShortLink();
        $shortLinkEntry = $this->obj->readSingleEntry($id);
        $this->obj->setId($id);
        $this->obj->setShortLink($shortLinkEntry['short_link']);
        $this->obj->setLongURL($shortLinkEntry['full_url']);
        $this->obj->setCustomer($shortLinkEntry['customer']);
        $this->form = $this->initConfigurationForm(TRUE);
        $this->fillForm();
        $this->my_tpl->setContent($this->form->getHTML());
    }

    public function doUpdate() {
        $this->obj->setId($_POST['shortLink_id']);
        $this->obj->setShortLink($_POST['shortLink']);
        $this->obj->setLongURL($_POST['longUrl']);
        $this->obj->setCustomer($_POST['customer']);
        $this->obj->doUpdate();
        ilUtil::sendSuccess($this->pl->txt('success_update_entry'), TRUE);
        $this->ctrl->redirect($this, 'showContent');
    }

    /**
     * Confirmation GUI for deleting an order
     */
    public function delete() {
        $c_gui = new ilConfirmationGUI();
        $c_gui->setFormAction($this->ctrl->getFormAction($this, $_GET['fallbackCmd']));
        $c_gui->setHeaderText($this->pl->txt('msg_delete_order'));
        $c_gui->setConfirm($this->pl->txt('delete'), 'confirmedDelete');
        $c_gui->setCancel($this->pl->txt('cancel'), 'showContent');
        $id = $_GET['link_id'];

        $objShortLink = new ilObjShortLink();
        $shortLinkEntry = $objShortLink->readSingleEntry($id);

        $c_gui->addItem("obj_id", $id, $shortLinkEntry[0]['short_link']);
        $this->my_tpl->setContent($c_gui->getHTML());
    }

    public function confirmedDelete() {
        $objShortLink = new ilObjShortLink();
        $objShortLink->doDelete($_POST['obj_id']);
        ilUtil::sendSuccess($this->pl->txt('success_delete_entry'), TRUE);
        $this->ctrl->redirect($this, 'showContent');
    }

    /**
     * Init Configuration Form
     *
     * @param bool $update
     * @return ilPropertyFormGUI
     */
    public function initConfigurationForm($update = FALSE)
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        if($update) {
            $this->form->setTitle($this->pl->txt('formUpdateTitle'));
        } else {
            $this->form->setTitle($this->pl->txt('formTitle'));
        }

        // Text input for long URL
        $ti = new ilTextInputGUI($this->pl->txt("longUrl"), 'longUrl');
        $ti->setRequired(true);
        $ti->setMaxLength(100);
        $ti->setSize(10);
        $this->form->addItem($ti);

        // Text input for ShortLink
        $ti = new ilTextInputGUI($this->pl->txt("shortLink"), 'shortLink');
        $ti->setRequired(true);
        $ti->setMaxLength(40);
        $ti->setSize(10);
        $this->form->addItem($ti);

        // Text input for customer
        $ti = new ilTextInputGUI($this->pl->txt("customer"), 'customer');
        $ti->setRequired(true);
        $ti->setMaxLength(40);
        $ti->setSize(10);
        $this->form->addItem($ti);

        if($update) {

            // HiddenInputGui for id
            $ti = new ilHiddenInputGUI($this->pl->txt('shortLink_id'), 'shortLink_id');
            $this->form->addItem($ti);

            $this->form->addCommandButton("doUpdate", $this->pl->txt("update"));
            $this->form->addCommandButton("cancel", $this->pl->txt("cancel"));
        } else {
            // HiddenInputGui for id
            $ti = new ilHiddenInputGUI($this->pl->txt('shortLink_id'), 'shortLink_id');
            $this->form->addItem($ti);

            $this->form->addCommandButton("save", $this->pl->txt("save"));
            $this->form->addCommandButton("cancel", $this->pl->txt("cancel"));
        }

        return $this->form;
    }

    protected function fillForm() {
        $values['longUrl'] = $this->obj->getLongURL();
        $values['shortLink'] = $this->obj->getShortLink();
        $values['customer'] = $this->obj->getCustomer();
        $values['shortLink_id'] = $this->obj->getId();

        $this->form->setValuesByArray($values);
    }

    /**
     * The standard command is used for permanent links.
     * @return string
     */
    public function getStandardCmd() {
        return 'showShortLinks';
    }

    /**
     * Functions that must be overwritten
     */
    public function getType() {
        return 'shortlink';
    }

    /**
     * Command that will be executed after creation of a new object.
     * @return string
     */
    public function getAfterCreationCmd() {
        return 'listShortLinks';
    }

    /**
     * @param ilToolbarGUI $toolbar
     */
    public function setToolbar($toolbar) {
        $this->toolbar = $toolbar;
    }

    /**
     * @return ilToolbarGUI
     */
    public function getToolbar() {
        return $this->toolbar;
    }

}