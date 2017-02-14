<?php
/**
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
var_dump($_GET['shortlink']);
$long_url = $fetcher->fetchLongURL($_GET['shortlink']);
ilUtil::redirect($long_url);

exit;