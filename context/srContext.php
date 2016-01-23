<?php
include_once "Services/Context/classes/class.ilContext.php";

/**
 * Class srContext
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class srContext extends ilContext {

	public function __construct() {
		self::init('srContextShortlink');
	}


	/**
	 * @param int $context
	 *
	 * @return bool|void
	 */
	public static function init($context) {
		include_once($context.'.php');
		self::$class_name = $context;
		self::$type = - 1;
	}
}

?>