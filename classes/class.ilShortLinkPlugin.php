<?php

require_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');

/**
 * RenderedBy Plugin
 *
 * @author  Thomas Kolonko <Thomas Kolonko@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilShortLinkPlugin extends ilUserInterfaceHookPlugin {

    const TABLE_NAME = 'ui_uihk_shortlink';

    /**
     * @return string
     */
    public function getPluginName() {
        return 'ShortLink';
    }
}

?>