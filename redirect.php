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
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilObjShortLink.php');
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$fetcher = new ilObjShortLink();
$full_url = $fetcher->fetchLongURL($_GET['shortlink']);
ilUtil::redirect($full_url);
exit;