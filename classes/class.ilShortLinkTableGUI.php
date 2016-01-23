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
require_once('Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class ilShortLinkTableGUI
 * TableGUI for ShortLink.
 * Lists ShortLinks
 *
 * @author  Tomasz Kolonko <tz.kolonko@gmail.com>
 * @version $Id$
 */
class ilShortLinkTableGUI extends ilTable2GUI {
    /**
     * @var ilToolbarGUI
     */
    public $toolbar;
    /**
     * @var array
     */
    protected $actions = array();

    public function __construct($a_parent_obj, $a_parent_cmd) {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->initColumns();
        $this->initRowTemplate();
        $this->initToolbar();
        $this->initActions();

        $this->setShowRowsSelector(true);

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->getMyDataFromDb();
        $this->setTitle("Title");
    }

    /**
     *
     * Get data and put it into an array
     *
     * @param bool $as_obj return array of objects or array of values
     * @return ShortLinks[]|array
     */
    public function getMyDataFromDb($as_obj = TRUE) { // << ?? $as_obj reason?? >>
        /** @var ilDB $ilDB */
        global $ilDB, $ilUser;

        $shortLinks = array();
        $this->tester = new ilObjShortLink();
        $shortLinks = $this->tester->readTablesPerUser();


        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");
        $this->setData($shortLinks);
    }

    /**
     * @param string    $id             the action identifier
     * @param string    $title          the action text
     * @param string    $target_class   the receiving class name
     * @param string    $target_cmd     the command which $target_class should execute
     */
    public function addAction($id, $title, $target_class, $target_cmd) {
        $this->actions[$id] = new stdClass();
        $this->actions[$id]->id = $id;
        $this->actions[$id]->title = $title;
        $this->actions[$id]->target_class = $target_class;
        $this->actions[$id]->target_cmd = $target_cmd;
    }

    protected function fillRow($a_set) {
        global $ilCtrl;

        $this->initRowTemplate();

        $this->tpl->setVariable("ID", $a_set['id']);
        $this->tpl->setVariable("LONG_URL", $a_set['long_url']);
        $this->tpl->setVariable("SHORTLINK", $a_set['short_link']);
        $this->tpl->setVariable("CONTACT", $a_set['contact']);

        // $this->ctrl->setParameter($this->parent_obj, 'order_id', $a_set['id']);
        $this->addActionsToRow($a_set);

    }


    protected function initRowTemplate() {
        $this->setRowTemplate('tpl.table_list_row.html',
            'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink');
    }

    protected function initColumns() {
        $this->addColumn("ID", 'id');
        $this->addColumn("url", 'full_url');
        $this->addColumn("shortLink", 'short_link');
        $this->addColumn("user", 'contact_user_login');
        // Action field
        $this->addColumn('', '', 1);
    }


    protected function initActions() {
        global $lng;

        $this->addAction('edit', $lng->txt('edit'), get_class($this->parent_obj), 'edit');
        $this->addAction('delete', $lng->txt('delete'), get_class($this->parent_obj), 'delete');
    }


    protected function initToolbar() {
        global $ilCtrl;
        $this->ctrl = $ilCtrl;
        $toolbar = new ilToolbarGUI();
        $toolbar->addButton("add", $this->ctrl->getLinkTarget($this->parent_obj, 'add'));
        $this->setToolbar($toolbar);
    }

    /**
     * @param array $a_set data array
     */
    protected function addActionsToRow($a_set) {
        global $lng, $ilCtrl;
        $this->ctrl->setParameterByClass(get_class($this->parent_obj), 'link_id',  $a_set['id']);

                if (! empty($this->actions)) {
                    $alist = new ilAdvancedSelectionListGUI();
                    $alist->setId($a_set['id']);
                    $alist->setListTitle($lng->txt('actions', FALSE));
                    $alist->setAutoHide(TRUE);

                    foreach ($this->actions as $action) {

                        $alist->addItem($action->title, $action->id,
                            $this->ctrl->getLinkTargetByClass($action->target_class, $action->target_cmd));
            }

            $this->tpl->setVariable('ACTION', $alist->getHTML());
        }
    }

    public function initFilter() {
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
    public function render() {
        $index_table_tpl = new ilTemplate("tpl.table_with_toolbar.html", true, true, "Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink");
        if ($this->getToolbar()) {
            $index_table_tpl->setVariable('TOOLBAR', $this->getToolbar()->getHTML());
        }

        $index_table_tpl->setVariable('TABLE', parent::render());

        return $index_table_tpl->get();
    }

    protected function setToolbar($toolbar) {
        $this->toolbar = $toolbar;
    }
    protected function getToolbar() {
        return $this->toolbar;
    }
}