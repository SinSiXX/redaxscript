<?php

/**
 * Redaxscript_DetectionLanguage
 *
 * @since 2.0.0
 *
 * @category Detection
 * @package Redaxscript
 * @author Henry Ruhs
 */

class Redaxscript_Detection_Language extends Redaxscript_Detection
{
	/**
	 * init
	 *
	 * @since 2.0.0
	 */

	public function init()
	{
		$this->_detect(array(
			'parameter' => $this->_getParameter('l'),
			'session' => isset($_SESSION[ROOT . '/language']) ? $_SESSION[ROOT . '/language'] : '',
			'contents' => retrieve('language', LAST_TABLE, 'id', LAST_ID),
			'settings' => s('language') === 'detect' ? '' : s('language'),
			'browser' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : '',
			'fallback' => 'en'
		), 'language', 'languages/{type}.php');
	}
}


?>