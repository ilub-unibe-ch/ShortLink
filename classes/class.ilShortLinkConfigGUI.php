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

require_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkGUI.php');


/**
 * GUI-Class ilShortLinkConfigGUI
 * This class manages the ShortLink frontend.
 *
 * @author              Thomas Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version             $Id$
 *
 * @ilCtrl_Calls ilShortLinkGUI: ilUIPluginRouterGUI
 *
 */
class ilShortLinkConfigGUI extends ilPluginConfigGUI {



	public function executeCommand() {


		parent::executeCommand();
		global $DIC;
		$mainGUI = new ilShortLinkGUI();
		$DIC->ctrl()->forwardCommand($mainGUI);




	}



	function performCommand($cmd) {

		global $DIC;

		$DIC->ctrl()->redirectByClass(["ilUIPluginRouterGUI","ilShortLinkGUI"]);

	}
}