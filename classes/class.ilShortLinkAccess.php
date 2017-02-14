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

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');

class ilShortLinkAccess {

    /**
     * @var ilObjUser $usr
     */
    protected $usr;

    /**
     * @var ilShortLinkPlugin $pl
     */
    protected $pl;

    /**
     * @var ilObjShortLink;
     */
    protected $obj;


    public function __construct() {
        global $ilUser;

        $this->usr = $ilUser;

        $this->pl = new ilShortLinkPlugin();
        $this->obj = new ilObjShortLink();

    }

    /**
     * Checking the owner of the item for owner and/or admin
     *
     * @param ilObjShortLink $obj
     * @param $idNum
     */
    public function checkPermission(ilObjShortLink $obj, $idNum) {
        $isOwner = ($obj->getOwner($idNum) == $this->usr->getLogin());
        $isAdmin = $this->checkAdministrationPrivileges();
        if(!$isOwner && !$isAdmin) {
            ilUtil::sendFailure($this->pl->txt("permission_denied"), true);
            ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
        }
    }

    /**
     * @return bool
     */
    public function checkAdministrationPrivileges() {
        return $this->obj->checkAdministrationPrivilegesFromDB();
   }

}