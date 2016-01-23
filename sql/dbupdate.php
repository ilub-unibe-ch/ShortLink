<#1>
<?php
/** @var ilDB $ilDB */
global $ilDB;
$db = $ilDB;
require_once('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/classes/class.ilShortLinkPlugin.php');

if (! $db->tableExists(ilShortLinkPlugin::TABLE_NAME)) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => TRUE
        ),
        'short_link' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => FALSE
        ),
        'full_url' => array(
            'type' => 'text',
            'length' => 400,
            'notnull' => FALSE
        ),
        'contact_user_login' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => FALSE
        )
    );
    $db->createTable(ilShortLinkPlugin::TABLE_NAME, $fields);
    $db->addPrimaryKey(ilShortLinkPlugin::TABLE_NAME, array( 'id' ));
    $db->createSequence(ilShortLinkPlugin::TABLE_NAME);
}
?>