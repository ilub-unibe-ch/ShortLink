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

	protected static $instance;

	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
     * @return string
     */
    public function getPluginName() {
        return 'ShortLink';
    }
}