<?php
/**
 * This redirect file is called when a ShortLink with its prefix is entered inside the browsers address field.
 * It fetches the full url from the DB and redirects the ShortLink to the full URI.
 *
 * Created by PhpStorm.
 * User: tomasz
 * Date: 16/01/16
 * Time: 21:10
 */
$path = stristr(__FILE__, 'Customizing', true);

if (is_file('path')) {
    $path = file_get_contents('path');
}

chdir($path);

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/Services/class.ilShortLinkContextInitialization.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');

/**
 * Initialization of the ShortLink Context, that allows to enter the shortlink without being logged into ILIAS. If user is not logged in yet she
 * will be redirected to the logging page. If the user is logged in, she will get redirected to the page, if it exists.
 */
ilShortLinkContextInitialization::init(ilShortLinkContextInitialization::CONTEXT_WAC);
define(CLIENT_ID, "ilias3_unibe");


require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$fetcher = new ilObjShortLink();
$plugin =ilShortLinkPlugin::getInstance();

$full_url = $fetcher->fetchLongURL($_GET['shortlink']);

if($full_url == NULL) {
    include_once('./Services/Utilities/classes/class.ilUtil.php');
    ilUtil::sendFailure($plugin->txt('link_not_found'), TRUE);
    $redirectToOverview = 'https://' . $_SERVER[HTTP_HOST] . '/goto.php?target=root_1&client_id=ilias3_unibe';
    ilUtil::redirect($redirectToOverview);
} else {
    ilUtil::redirect($full_url);
}