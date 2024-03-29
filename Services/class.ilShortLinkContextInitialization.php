<?php

require_once("./Services/Context/classes/class.ilContext.php");


/**
 * Class ilShortLinkContextGenerator
 *
 * This class uses a already existing Context (others are not possible) and maps it onto an
 * own context, that was created for better readability
 *
 * @author  Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version $Id$
 */
class ilShortLinkContextInitialization extends ilContext {
    /**
     * Initialization of a existing context (CONTEXT_WEB_ACCESS_CHECK)
     */
    public static function init($a_type) {
        parent::init($a_type);
    }

}