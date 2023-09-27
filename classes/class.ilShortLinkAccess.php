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

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');



/**
 * Class ilShortLinkAccess
 *
 * This class checks the access to the ShortLink object.
 *
 * @author  Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version $Id$
 */
class ilShortLinkAccess {

    protected ilObjUser $usr;
    protected ilShortLinkPlugin $pl;
    protected ilObjShortLink $obj;

    /**
     * ilShortLink constructor
     *
     * Fetches the current User and instantiates the ShortLink Plugin and ShortLink Object for access checking.
     */
    public function __construct() {
        global $ilUser;

        $this->usr = $ilUser;

        $this->pl = ilShortLinkPlugin::getInstance();
        $this->obj = new ilObjShortLink();

    }

    /**
     * Checking if the current user is owner OR admin. Returns true if the user is either the owner of
     * the shortlink or administrator
     * @param $idNum
     */
    public function checkPermission(ilObjShortLink $obj, int $idNum): bool
    {
        $isOwner = ($obj->getOwner($idNum) == $this->usr->getLogin());
        $isAdmin = $this->checkAdministrationPrivileges();
        if($isOwner || $isAdmin) {
            return true;
        }
        return false;
    }

    /**
     * Checking if the user is an administrator
     */
    public function checkAdministrationPrivileges(): bool
    {
        return $this->obj->checkAdministrationPrivilegesFromDB();
    }

    /**
     * Checking if the user is a valid registered user
     */
    public function checkIfUserIsAnonymous(): bool
    {
        return $this->obj->checkIfUserIsAnonymous();

    }

}