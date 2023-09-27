<?php
declare(strict_types=1);
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
require_once('Services/Table/classes/class.ilTable2GUI.php');
require_once('Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');



/**
 * Class ilShortLinkTableGUI
 * TableGUI for ShortLink.
 * Lists ShortLinks
 *
 * @author  Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version $Id$
 */
class ilShortLinkTableGUI extends ilTable2GUI {

    public ilToolbarGUI $toolbar;

    protected array $actions = array();

    protected ilCtrl $ctrl;

    protected ilObjShortLink $obj;

    protected ilShortLinkPlugin $pl;

    protected string $linkToShortURL;

    /**
     * ilShortLinkTableGUI constructor
     *
     * @param ilShortLinkConfigGUI    $a_parent_obj   the ShortLink main GUI
     * @param string            $a_parent_cmd   the table name ilShortLinkPlugin::TABLE_NAME
     */
    public function __construct(ilShortLinkGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $ilCtrl;
        $this->pl = ilShortLinkPlugin::getInstance();
        $this->ctrl = $ilCtrl;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->initColumns();
        $this->initRowTemplate();
        $this->initToolbar();
        $this->initActions();

        $this->setShowRowsSelector(true);

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->getMyDataFromDb();
        $this->setTitle("Title");
    }

    /**
     *
     * Get data and put it into an array

     */
    public function getMyDataFromDb(): void
    {
        $this->obj = new ilObjShortLink();

        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");

        // setExternalSorting was false before
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        // TODO: Sorting might be off because id is int in DB but string in GUI !!!!
        $this->setData($this->obj->readEntriesPerUser());
    }

    /**
     * Adds different actions to the GUI
     *
     * @param string    $id             the action identifier
     * @param string    $title          the action text
     * @param string    $target_class   the receiving class name
     * @param string    $target_cmd     the command which $target_class should execute
     */
    public function addAction(string $id, string $title, string $target_class, string $target_cmd): void
    {
        $this->actions[$id] = new stdClass();
        $this->actions[$id]->id = $id;
        $this->actions[$id]->title = $title;
        $this->actions[$id]->target_class = $target_class;
        $this->actions[$id]->target_cmd = $target_cmd;
    }

    /**
     * Fills the single rows by id
     */
    protected function fillRow(array $a_set): void
    {
        $this->initRowTemplate();

        $this->tpl->setVariable("ID", $a_set['id']);
        $this->linkToShortURL = "/link/" . $a_set['short_link'];
        $this->tpl->setVariable("SHORTLINK", $this->linkToShortURL);
        $this->tpl->setVariable("DOMAIN", $_SERVER['HTTP_HOST']);
        $this->tpl->setVariable("FULL_URL", $a_set['full_url']);
        $this->tpl->setVariable("CUSTOMER", $a_set['customer']);
        $this->tpl->setVariable("CONTACT", $a_set['contact']);
        $this->addActionsToRow($a_set);
    }

    /**
     * sets the template used for creating the rows
     */
    protected function initRowTemplate(): void
    {
        $this->setRowTemplate('tpl.table_list_row.html',
            'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink');
    }

    /**
     * Creates the lables for the columns in the table GUI for the ShortLink plugin
     */
    protected function initColumns()
    {
        $this->addColumn("ID", 'id');
        $this->addColumn("shortLink", 'short_link');
        $this->addColumn("url", 'full_url');
        $this->addColumn("customer", 'customer');
        $this->addColumn("user", 'contact_user_login');
        $this->addColumn('', '', '1');
    }

    /**
     * Adds edit and delete actions to every ShortLink row entry
     */
    protected function initActions(): void
    {
        global $lng;
        $this->addAction('edit', $lng->txt('edit'), get_class($this->parent_obj), 'edit');
        $this->addAction('delete', $lng->txt('delete'), get_class($this->parent_obj), 'delete');
    }

    /**
     * Initializes the toolbar with an add button to add new ShortLinks
     */
    protected function initToolbar() : void
    {
        $toolbar = new ilToolbarGUI();
        $toolbar->addButton($this->pl->txt('add'), $this->ctrl->getLinkTarget($this->parent_obj, 'add'));
        $this->setToolbar($toolbar);
    }

    /**
     * Adds the drop down Action Button to ever single ShortLink
     */
    protected function addActionsToRow(array $a_set)
    {
        global $lng;
        $this->ctrl->setParameterByClass(get_class($this->parent_obj), 'link_id',  $a_set['id']);
        if (! empty($this->actions)) {
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId((string)$a_set['id']);
            $alist->setListTitle($lng->txt('actions'));
            $alist->setAutoHide(TRUE);

            foreach ($this->actions as $action) {
                $alist->addItem($action->title, $action->id,
                    $this->ctrl->getLinkTargetByClass($action->target_class, $action->target_cmd));
            }
            $this->tpl->setVariable('ACTION', $alist->getHTML());
        }
    }

    // TODO: NOT WORKING AT ALL NEEDS FIXING
    /*public function initFilter() {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/Bugeno/classes/Administration/class.xbgTableFilter.php');
        $filter = new xbgTableFilter($this, $this->lng_xbg);
        foreach ($filter->getFilterInputItems() as $item) {
            $this->addFilterItem($item);
        }
    }

    /**
     *
     * Renders the view with to html elements set by setVariable
     *
     * @return string
     */
    public function render(): string
    {
        $index_table_tpl = new ilTemplate("tpl.table_with_toolbar.html", true, true, "Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink");
        if ($this->getToolbar()) {
            $index_table_tpl->setVariable('TOOLBAR', $this->getToolbar()->getHTML());
        }
        $index_table_tpl->setVariable('TABLE', parent::render());

        return $index_table_tpl->get();
    }

    protected function setToolbar(ilToolbarGUI $toolbar)
    {
        $this->toolbar = $toolbar;
    }

    protected function getToolbar(): ilToolbarGUI {
        return $this->toolbar;
    }
}