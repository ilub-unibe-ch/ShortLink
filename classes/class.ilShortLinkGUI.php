<?php
/**
 * Created by PhpStorm.
 * User: kaufmann
 * Date: 13.05.19
 * Time: 11:06
 * @ilCtrl_isCalledBy ilShortLinkUIHookGUI, ilUIPluginRouterGUI: ilShortLinkGUI
 */

require_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');
require_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkAccess.php');
include_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkTableGUI.php');
require_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('Services/Repository/classes/class.ilObjectPluginGUI.php');

class ilShortLinkGUI extends ilObjectPluginGUI {

	/**
	 * @var ilGlobalPageTemplate $my_tpl
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
	 * @var ilShortLinkPlugin $pl
	 */
	protected $pl;
	/**
	 * @var ilObjShortLink $externalFeedBlock
	 */
	protected $externalFeedBlock;

	/**
	 * ilShortLinkConfigGUI constructor
	 *
	 * Instantiates local form, plugin, object and AccessChecker member variables.
	 * Gets the standard template for HTML output.
	 */
	public function __construct() {

		global $ilCtrl, $tpl;

		$this->ctrl = $ilCtrl;

		$this->form = new ilPropertyFormGUI();
		$this->shortLinkAccessChecker = new ilShortLinkAccess();
		$this->pl = ilShortLinkPlugin::getInstance();
		$this->obj = new ilObjShortLink();

		$this->my_tpl = $tpl;
		$this->my_tpl->loadStandardTemplate();
	}

	/**
	 * Handles all commands of this class
	 *
	 * @return bool
	 */
	public function executeCommand() {

		$cmd = $this->ctrl->getCmd();

		switch($cmd){
			case 'add':
			case 'save':
			case 'listShortLinks':
			case 'confirmedDelete':

				$this->$cmd();
				break;
			case 'edit':
			case 'delete':
			case 'doUpdate':

				$this->$cmd();
				break;
			default:
				$this->showContent();
				break;
		}



	}

	/**
	 * Prepares the main layout with the infobox and lists all ShortLinks
	 */
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

	/**
	 * Initializes the toolbar and adds an "add" button.
	 */
	protected function initToolbar() {
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->pl->txt('add'), $this->ctrl->getLinkTarget($this, 'add'));
		$this->setToolbar($toolbar);
	}

	protected function add() {

		$this->form = $this->initConfigurationForm();
		$this->my_tpl->setContent($this->form->getHTML());
	}

	/**
	 * Saves all the information given in the form to a new ShortLink Object and adds it to the DB
	 */
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

	/**
	 * Returns true if the length of the full url does not exceed the maximum.
	 *
	 * @return bool
	 */
	private function checklongURL() {
		$isValid = TRUE;
		if(strlen($this->form->getInput("longUrl"))>99) {
			ilUtil::sendFailure($this->pl->txt("full_url_too_long"), true);
			$isValid = FALSE;
		}
		return $isValid;
	}

	/**
	 * Returns true if only allowed characters are used for the shortlink && the shortlink has not been
	 * used already.
	 *
	 * @return bool
	 */
	private function checkShortULR() {
		$isValid = TRUE;
		$regex = "/^[a-zA-Z0-9\-_]+$/";
		if(!preg_match($regex, $this->form->getInput("shortLink"))) {
			ilUtil::sendFailure($this->pl->txt("characters_not_allowed") . ", used regex: " . $regex, true);
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

	/**
	 * Initialzes new tableGUI layout and populates it with data from the DB
	 */
	public function listShortLinks() {
		$table = new ilShortLinkTableGUI($this, ilShortLinkPlugin::TABLE_NAME);
		$this->my_tpl->setContent($table->getHTML());
	}

	/**
	 * Creates new ShortLink object to be edited inside form
	 */
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

	/**
	 * updates the DB with edited information from the form
	 */
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

		$c_gui->addItem("obj_id", $id, $shortLinkEntry['short_link']);

		$this->my_tpl->setContent($c_gui->getHTML());

	}

	/**
	 * Deletes the shortLink entry and redirects to main shortlink content page
	 */
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

	/**
	 * Fills the form from $this->obj
	 */
	protected function fillForm() {
		$values['longUrl'] = $this->obj->getLongURL();
		$values['shortLink'] = $this->obj->getShortLink();
		$values['customer'] = $this->obj->getCustomer();
		$values['shortLink_id'] = $this->obj->getId();

		$this->form->setValuesByArray($values);
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
	 * The standard command is used for permanent links.
	 * @return string
	 */
	public function getStandardCmd() {
		return 'showShortLinks';
	}
}