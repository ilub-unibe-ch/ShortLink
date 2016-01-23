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
 * Class shortLinkTable
 *
 * @author  Tomasz Kolonko <tz.kolonko@gmail.com>
 * @version $Id$
 */
abstract class shortLinkTable extends ilTable2GUI {

    /**
     * @var ilCtrl $ctrl
     */
    protected $ctrl;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar = NULL;
    /**
     * @var array
     */
    protected $actions = array();


    /**
     * @param string                  $table_id
     * @param string                  $a_parent_cmd
     * @param string                  $a_template_context
     */
    public function __construct($a_parent_obj, $table_id, $a_parent_cmd = '',
                                $a_template_context = '') {
        /** @var ilCtrl $ilCtrl */
        global $ilCtrl;
        $this->ctrl = $ilCtrl;
        $this->setId($table_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
        $this->setShowRowsSelector(TRUE);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setFilterCommand('applyFilter');
        $this->setResetCommand('resetFilter');
        $this->initFilter();
        $this->initRowTemplate();
        $this->initColumns();
        $this->initToolbar();
        $this->initActions();
    }


    /**
     * Call $this->setRowTemplate()
     */
    protected abstract function initRowTemplate();
    /**
     * Call $this->addColumn()
     */
    protected abstract function initColumns();
    /**
     * Call $this->addAction()
     */
    protected abstract function initActions();
    /**
     * Call $this->setToolbar()
     */
    protected abstract function initToolbar();


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


    /**
     * @param \ilToolbarGUI $toolbar
     */
    public function setToolbar($toolbar) {
        $this->toolbar = $toolbar;
    }


    /**
     * @return \ilToolbarGUI
     */
    public function getToolbar() {
        return $this->toolbar;
    }


    public function render() {
        $index_table_tpl = new ilTemplate('tpl.table_with_toolbar.html', TRUE, TRUE,
            'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink');
        if ($this->getToolbar()) {
            $index_table_tpl->setVariable('TOOLBAR', $this->getToolbar()->getHTML());
        }

        $index_table_tpl->setVariable('TABLE', parent::render());

        return $index_table_tpl->get();
    }
}