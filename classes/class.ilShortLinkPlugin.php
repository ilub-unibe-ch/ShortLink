<?php
declare(strict_types=1);
require_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');

/**
 * RenderedBy Plugin
 *
 * @author  Thomas Kolonko <Thomas Kolonko@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilShortLinkPlugin extends ilUserInterfaceHookPlugin {

    const  TABLE_NAME = 'ui_uihk_shortlink';

	protected static ilShortLinkPlugin $instance;
    protected \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query;
    protected \ILIAS\Refinery\Factory $refinery;


    public function __construct()
    {
        global $DIC;
        $this->query = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $component_repository = $DIC["component.repository"];
        parent::__construct($DIC->database(), $component_repository, 'shortlink');
    }
	public static function getInstance(): ilShortLinkPlugin
    {
        global $DIC;

		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    public function getPluginName(): string {
        return 'ShortLink';
    }
}