<?php
declare(strict_types=1);

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory;


/**
 * RenderedBy Plugin
 *
 * @author  Thomas Kolonko <Thomas Kolonko@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilShortLinkPlugin extends ilUserInterfaceHookPlugin
{
    public const  TABLE_NAME = 'ui_uihk_shortlink';

    protected static ilShortLinkPlugin $instance;
    protected ArrayBasedRequestWrapper $query;
    protected Factory $refinery;


    public function __construct()
    {
        global $DIC;
        if(isset($DIC['http'])) {
            $this->query = $DIC->http()->wrapper()->query();
        }
        if(isset($DIC ['refinery'])) {
            $this->refinery = $DIC->refinery();
        }
        $component_repository = $DIC['component.repository'];
        parent::__construct($DIC->database(), $component_repository, 'shortlink');
    }
    public static function getInstance(): ilShortLinkPlugin
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPluginName(): string
    {
        return 'ShortLink';
    }
}
