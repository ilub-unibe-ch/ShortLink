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

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkAccess.php');


/**
 * Class ilObjShortLink
 * Object for the ShortLinkPlugin
 *
 * This class encapsulates the shortlink object with all it's member variables and functions.
 * It is also responsible for retrieving information from the database and write information to it.
 *
 * @author  Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version $Id$
 */
class ilObjShortLink {

    /**
     * @var ilDB $db
     */
    protected $db;

    /**
     * @var ilObjUser $usr
     */
    protected $usr;

    /**
     * @var ilShortLinkPlugin $pl
     */
    protected $pl;

    /**
     * @var int $id
     */
    protected $id = 0;

    /**
     * The currently logged in user creating this object
     *
     * @var string $contact
     */
    protected $contact;

    /**
     * Full URL to the resource.
     *
     * @var string $longURL
     */
    protected $longURL;

    /**
     * Short expression to substitute the full URL entered by user in browser
     *
     * @var string $shortLink
     */
    protected $shortLink;

    /**
     * The name of the person requesting the shortlink entered by user in browser.
     *
     * @var $customer
     */
    protected $customer;


    /**
     * ilObjShortLink constructor
     *
     * Makes global DB and User variable locally available trough member variables.
     * Creates new ilShortLinkPlugin and makes it locally available.
     */
    public function __construct() {
        global $ilDB, $ilUser;

        $this->db = $ilDB;
        $this->usr = $ilUser;

        $this->pl = new ilShortLinkPlugin();
    }

    /**
     * Inserts new item into DB
     */

    public function doCreate() {
        $stmt = $this->db->prepare('INSERT INTO ' . ilShortLinkPlugin::TABLE_NAME .
            ' (id, short_link, full_url, customer, contact_user_login) VALUES (?, ?, ?, ?, ?);',
            array('integer', 'text', 'text', 'text', 'text'));
        $this->db->execute($stmt, array($this->getId(), $this->getShortLink(), $this->getLongURL(), $this->getCustomer(), $this->getContact()));
    }

    /**
     * @param bool $as_obj
     * @param int $id
     * @return array $singleEntry
     */
    public function readSingleEntry($id) {
        $currentUser = $this->usr->getLogin();
        $set = $this->db->query('SELECT * FROM ' . ilShortLinkPlugin::TABLE_NAME . ' WHERE id=' . $id);

        $singleEntry = array();

        if ($rec = $this->db->fetchAssoc($set)) {
            if ($currentUser == $rec['contact_user_login'] || $this->checkAdministrationPrivilegesFromDB()) {
                $singleEntry = array('id' => $rec['id'], 'long_url' => $rec['full_url'], 'short_link' => $rec['short_link'],
                    'customer' => $rec['customer'], 'contact' => $rec['contact_user_login']);
            }
        } else {
            ilUtil::sendFailure($this->pl->txt("request_invalid"), true);
            ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
        }

        return $singleEntry;
    }

    public function nextId() {
        $this->setId($this->db->nextId(ilShortLinkPlugin::TABLE_NAME));
    }

    /**
     * Get data from DB and put it into an array
     *
     * @param bool $as_obj
     * @return array $shortLinks
     */
    public function readTablesPerUser($as_obj = TRUE) { // << ?? $as_obj reason?? >>
        $shortLinks = array();
        $currentUser = $this->usr->getLogin();

        $set = $this->db->query('SELECT * FROM ' . ilShortLinkPlugin::TABLE_NAME);
        $isAdministrator = $this->checkAdministrationPrivilegesFromDB();
        while ($rec = $this->db->fetchAssoc($set)) {
            if ($as_obj) {
                if($currentUser == $rec['contact_user_login'] || $isAdministrator){
                    $shortLinks[] = array('id'=> (int)$rec['id'], 'long_url'=>$rec['full_url'], 'short_link'=>$rec['short_link'],
                        'customer'=>$rec['customer'], 'contact'=>$rec['contact_user_login']);
                }
            } else {
                $shortLinks[] = $rec;
            }
        }
        return $shortLinks;
    }

    public function doUpdate() {
        $this->db->manipulate('UPDATE ' . ilShortLinkPlugin::TABLE_NAME . ' SET' .
            ' short_link = ' . $this->db->quote($this->getShortLink(), 'text') . ',' .
            ' full_url = ' . $this->db->quote($this->getLongURL(), 'text') . ',' .
            ' customer = ' . $this->db->quote($this->getCustomer(), 'text') .
            ' WHERE id = ' . $this->db->quote($this->getId(), 'integer') . ';'
        );
    }

    /**
     * Get user login name
     *
     * @param $idNum
     * @return mixed
     */
    public function getOwner($idNum) {
        $this->setId($idNum);
        $set = $this->db->query('SELECT contact_user_login FROM ' . ilShortLinkPlugin::TABLE_NAME . ' WHERE id=' . $this->getId());
        $rec = $this->db->fetchAssoc($set);
        return $rec['contact_user_login'];
    }

    /**
     * Gets longURL from shortURL
     *
     * @param $shortLink
     * @return mixed
     */
    public function fetchLongURL($shortLink) {
        $set = $this->db->query('SELECT full_url FROM ' .ilShortLinkPlugin::TABLE_NAME . ' WHERE short_link=' . "'" . $shortLink . "'");
        $rec = $this->db->fetchAssoc($set);
        return $rec['full_url'];
    }

    public function checkIfShortLinkAlreadyMentioned($shortLink) {
        $set = $this->db->query('SELECT full_url FROM ' .ilShortLinkPlugin::TABLE_NAME . ' WHERE short_link=' . "'" . $shortLink . "'");
        $rec = $this->db->fetchAssoc($set);
        if($rec == NULL)  {
            return false;
        }
        return true;
    }


    /**
     *
     * Deletes the entry with id $id from DB
     *
     * @param $id
     */
    public function doDelete($id) {
        $this->db->manipulate('DELETE FROM ' . ilShortLinkPlugin::TABLE_NAME .
            ' WHERE id = ' . $id, 'integer');
    }

    /**
     * Returns true if Administrator privilege is granted
     *
     * @return bool
     */
    public function checkAdministrationPrivilegesFromDB() {
        $administrationRole = $this->getRoleIdOfAdministrator();
        if($administrationRole == -1) {
            ilUtil::sendFailure($this->pl->txt("permission_denied"), true);
            ilUtil::redirect('login.php?baseClass=ilPersonalDesktopGUI');
        } else {
            $set = $this->db->query('SELECT * FROM rbac_ua WHERE usr_id=' . $this->usr->getId() .' AND rol_id=' . $administrationRole);
            if($rec = $this->db->fetchAssoc($set)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getRoleIdOfAdministrator() {
        $set = $this->db->query('SELECT obj_id FROM object_data WHERE title="Administrator" AND type="role"');
        if($rec = $this->db->fetchAssoc($set)) {
            return $rec['obj_id'];
        }
        return -1;
    }


    /**
     * @param $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return int $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param $customer
     */
    public function setCustomer($customer) {
        $this->customer = $customer;
    }

    /**
     * @return string $customer
     */
    public function getCustomer() {
        return $this->customer;
    }

    /**
     * @param $contact
     */
    public function setContact($contact) {
        $this->contact = $contact;
    }

    /**
     * @return string $contact
     */
    public function getContact() {
        return $this->contact;
    }

    /**
     * @param $longURL
     */
     public function setLongURL($longURL) {
         $this->longURL = $longURL;
     }

    /**
     * @return string $longURL
     */
    public function getLongURL() {
        return $this->longURL;
    }
    /**
     * @param $shortLink
     */
    public function setShortLink($shortLink) {
        $this->shortLink = $shortLink;
    }

    /**
     * @return string $shortLink
     */
    public function getShortLink() {
        return $this->shortLink;
    }

}