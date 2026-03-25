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
$path = stristr(__FILE__, 'public', true);

if (is_file('path')) {
    $path = file_get_contents('path');
}

chdir($path);

require_once 'vendor/composer/vendor/autoload.php';

/**
 * Initialization of the ShortLink Context, that allows to enter the shortlink without being logged into ILIAS. If user is not logged in yet she
 * will be redirected to the logging page. If the user is logged in, she will get redirected to the page, if it exists.
 */
ilShortLinkContextInitialization::init(ilContext::CONTEXT_WEB);

ilInitialisation::initILIAS();

global $DIC;
$fetcher = new ilObjShortLink();
$plugin =ilShortLinkPlugin::getInstance();

$full_url = $fetcher->fetchLongURL($DIC->http()->wrapper()->query()->retrieve('shortlink', $DIC->refinery()->kindlyTo()->string()));

global $DIC;
if($full_url == NULL) {
    $DIC->ui()->mainTemplate()->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE , $plugin->txt('link_not_found'));
    $redirectToOverview = 'https://' . $_SERVER['HTTP_HOST'] . '/goto.php?target=root_1';
    $DIC->ctrl()->redirectToURL($redirectToOverview);
} else {
    $DIC->ctrl()->redirectToURL($full_url);
}