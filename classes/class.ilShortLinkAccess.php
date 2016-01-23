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

class ilShortLinkAccess {

    /**
     * @var ilDB $db
     */
    protected $db;

    /**
     * @var ilUser $usr
     */
    protected $usr;

    // TODO: werden die regulären variablen wie $pl und $currentUserID
    // TODO: ebenfalls hier aufgelistet oder werden nur die globalen Variablen
    // TODO: erwähnt

    /**
     * @var ilShortLinkPlugin $pl
     */
    protected $pl;

    /**
     * @var $currentUserId;
     */
    protected $currentUserId;


    public function __construct() {
        global $ilDB, $ilUser;

        $this->db = $ilDB;
        $this->usr = $ilUser;

        $this->pl = new ilShortLinkPlugin();

        $this->currentUserId = $this->usr->getId();
    }

    // TODO: do I instantiate a new Object or pass it by reference? $this->obj = new ilObjShortLink() in constructor
    // TODO: or checkPermission($obj);

    public function checkPermission($permission, $command, $obj, $idNum) {

        $isOwner = $obj->getOwner($idNum) == $this->usr->getLogin();
        $isAdmin = $this->checkAdministrationPrivileges();
        if($isOwner || $isAdmin) {

        } else {
            ilUtil::sendFailure($this->pl->txt("permission_denied"), true);
            ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
        };

    }

    public function checkAdministrationPrivileges() {
        $administrationRole = $this->getRoleIdOfAdministrator();
        if($administrationRole == -1) {
            ilUtil::sendFailure($this->pl->txt("permission_denied"), true);
            ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
        } else {
            $set = $this->db->query('SELECT * FROM rbac_ua WHERE usr_id=' . $this->currentUserId .' AND rol_id=' . $administrationRole);
            if($rec = $this->db->fetchAssoc($set)) {
                return true;
            }
        }
        return false;

    }

    public function getRoleIdOfAdministrator() {
        $set = $this->db->query('SELECT obj_id FROM object_data WHERE title="Administrator" AND type="role"');
        if($rec = $this->db->fetchAssoc($set)) {
            return $rec['obj_id'];
        }
        return -1;
    }

}