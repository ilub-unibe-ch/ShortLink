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


    protected ilDBInterface $db;
    protected ilObjUser $usr;
    protected ilShortLinkPlugin $pl;
    protected int $id = 0;
     //The currently logged in user creating this object
    protected string $contact;

    /**
     * Full URL to the resource.
     *
     */
    protected string $longURL;

    /**
     * Short expression to substitute the full URL entered by user in browser
     *
     * @var string $shortLink
     */
    protected string $shortLink;

    /**
     * The name of the person requesting the shortlink entered by user in browser.
     */
    protected string $customer;


    /**
     * ilObjShortLink constructor
     *
     * Makes global DB and User variable locally available trough member variables.
     * Creates new ilShortLinkPlugin and makes it locally available.
     */
    public function __construct() {
        global $DIC;

        $this->db = $DIC->database();
        $this->usr = $DIC->user();

        $this->pl = ilShortLinkPlugin::getInstance();
    }

    /**
     * Inserts new item into DB
     */
    public function doCreate(): void
    {
        $stmt = $this->db->prepare('INSERT INTO ' . ilShortLinkPlugin::TABLE_NAME .
            ' (id, short_link, full_url, customer, contact_user_login) VALUES (?, ?, ?, ?, ?);',
            array('integer', 'text', 'text', 'text', 'text'));
        $this->db->execute($stmt, array($this->getId(), $this->getShortLink(), $this->getLongURL(), $this->getCustomer(), $this->getContact()));
    }

    public function readSingleEntry(int $id): array {
        $currentUser = $this->usr->getLogin();
        $set = $this->db->query('SELECT * FROM ' . ilShortLinkPlugin::TABLE_NAME . ' WHERE id=' . $id);

        $singleEntry = array();

        if ($rec = $this->db->fetchAssoc($set)) {
            if ($currentUser == $rec['contact_user_login'] || $this->checkAdministrationPrivilegesFromDB()) {
                $singleEntry = array('id' => $rec['id'], 'full_url' => $rec['full_url'], 'short_link' => $rec['short_link'],
                    'customer' => $rec['customer'], 'contact' => $rec['contact_user_login']);
            }
        } else {
            ilUtil::sendFailure($this->pl->txt("request_invalid"), true);
            ilUtil::redirect('goto.php?target=root_1&client_id=ilias3_unibe');
        }
        return $singleEntry;
    }

    public function nextId(): void
    {
        $this->setId($this->db->nextId(ilShortLinkPlugin::TABLE_NAME));
    }

    /**
     * Get an array of ShortLinks that are visible for the currently logged in user
     *
     * @param bool $as_obj
     * @return array $shortLinks
     */
    public function readEntriesPerUser(): array
    {
        $shortLinks = array();
        $currentUser = $this->usr->getLogin();

        $isAdministrator = $this->checkAdministrationPrivilegesFromDB();

        if($isAdministrator) {
            $set = $this->db->query('SELECT * FROM ' . ilShortLinkPlugin::TABLE_NAME);
        } else {
            $set = $this->db->query('SELECT * FROM ' . ilShortLinkPlugin::TABLE_NAME . ' WHERE contact_user_login=' . "'" . $currentUser . "'");
        }

        while ($rec = $this->db->fetchAssoc($set)) {
           $shortLinks[] = array('id' => (int)$rec['id'], 'full_url' => $rec['full_url'], 'short_link' => $rec['short_link'],
               'customer' => $rec['customer'], 'contact' => $rec['contact_user_login']);
        }

        return $shortLinks;
    }

    /**
     * Updates an entry determined by id with new information
     */
    public function doUpdate(): void
    {
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
     * @return string
     */
    public function getOwner(int $idNum): string
    {
        $this->setId($idNum);
        $set = $this->db->query('SELECT contact_user_login FROM ' . ilShortLinkPlugin::TABLE_NAME . ' WHERE id=' . $this->getId());
        $rec = $this->db->fetchAssoc($set);
        return $rec['contact_user_login'];
    }

    /**
     * Gets longURL from shortURL
     */
    public function fetchLongURL(string $shortLink): string
    {
        $set = $this->db->query('SELECT full_url FROM ' .ilShortLinkPlugin::TABLE_NAME . ' WHERE short_link=' . "'" . $shortLink . "'");
        $rec = $this->db->fetchAssoc($set);
        return $rec['full_url'];
    }

    /**
     * Checks if the chosen shortLink name is already taken.
     */
    public function checkIfShortLinkAlreadyMentioned(string $shortLink): bool
    {
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
     */
    public function doDelete(int $id): void
    {
        $this->db->manipulate('DELETE FROM ' . ilShortLinkPlugin::TABLE_NAME .
            ' WHERE id = ' . $id, 'integer');
    }

    /**
     * Returns true if Administrator privilege is granted
     */
    public function checkAdministrationPrivilegesFromDB(): bool
    {
        $administrationRole = $this->getRoleIdOfAdministrator();
        $set = $this->db->query('SELECT * FROM rbac_ua WHERE usr_id=' . $this->usr->getId() .' AND rol_id=' . $administrationRole);
        if($rec = $this->db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the id of the Administrators role
     */
    public function getRoleIdOfAdministrator(): string {
        $set = $this->db->query('SELECT obj_id FROM object_data WHERE title="Administrator" AND type="role"');
        if($rec = $this->db->fetchAssoc($set)) {
            return $rec['obj_id'];
        }
        return '-1';
    }

    /**
     * Returns true if user is anonymous
     */
    public function checkIfUserIsAnonymous(): bool {
        $currentUserIsAnonymous = $this->usr->isAnonymous();
        if($currentUserIsAnonymous) {
            return true;
        }
        return false;
    }

    /**
     * Returns true if current User is a valid and registered one.
     *
     * @param $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int $id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param $customer
     */
    public function setCustomer(string $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return string $customer
     */
    public function getCustomer(): string
    {
        return $this->customer;
    }

    /**
     * @param $contact
     */
    public function setContact(string $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return string $contact
     */
    public function getContact(): string
    {
        return $this->contact;
    }

    /**
     * @param $longURL
     */
    public function setLongURL(string $longURL): void
    {
        $this->longURL = $longURL;
    }

    /**
     * @return string $longURL
     */
    public function getLongURL(): string
    {
        return $this->longURL;
    }
    /**
     * @param $shortLink
     */
    public function setShortLink(string $shortLink)
    {
        $this->shortLink = $shortLink;
    }

    /**
     * @return string $shortLink
     */
    public function getShortLink(): string
    {
        return $this->shortLink;
    }

}