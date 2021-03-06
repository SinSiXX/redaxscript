<?php

/**
 * Redaxscript Parser
 *
 * @since 2.0.0
 *
 * @package Redaxscript
 * @category Parser
 * @author Henry Ruhs
 */

class Redaxscript_Parser
{
	/**
	 * output
	 *
	 * @var string
	 */

	private $_output;

	/**
	 * route
	 *
	 * @var string
	 */

	protected $_route;

	/**
	 * delimiter
	 *
	 * @var string
	 */

	protected $_delimiter = '@@@';

	/**
	 * tags
	 *
	 * @var array
	 */

	protected $_tags = array(
		'break' => array(
			'function' => '_parseBreak',
			'position' => ''
		),
		'code' => array(
			'function' => '_parseCode',
			'position' => ''
		),
		'function' => array(
			'function' => '_parseFunction',
			'position' => ''
		)
	);

	/**
	 * classes
	 *
	 * @var array
	 */

	protected $_classes = array(
		'break' => 'link_read_more',
		'code' => 'box_code'
	);

	/**
	 * forbiddenFunctions
	 *
	 * @var array
	 */

	protected $_forbiddenFunctions = array(
		'curl',
		'curl_exec',
		'curl_multi_exec',
		'exec',
		'eval',
		'fopen',
		'include',
		'include_once',
		'mysql',
		'passthru',
		'popen',
		'proc_open',
		'shell',
		'shell_exec',
		'system',
		'require',
		'require_once'
	);

	/**
	 * construct
	 *
	 * @since 2.0.0
	 *
	 * @param string $input
	 * @param string $route
	 */

	public function __construct($input = null, $route = null)
	{
		$this->_output = $input;
		$this->_route = $route;

		/* call init */

		$this->init();
	}

	/**
	 * init
	 *
	 * @since 2.0.0
	 */

	public function init()
	{
		foreach($this->_tags as $key => $value)
		{
			/* save tag related position */

			$position = $this->_tags[$key]['position'] = strpos($this->_output, '<' . $key . '>');

			/* call related function if tag found */

			if ($position > -1)
			{
				$function = $value['function'];
				$this->_output = $this->$function($this->_output);
			}
		}
	}

	/**
	 * getOutput
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */

	public function getOutput()
	{
		return $this->_output;
	}

	/**
	 * parseBreak
	 *
	 * @since 2.0.0
	 *
	 * @param string $input
	 * @return string
	 */

	protected function _parseBreak($input = null)
	{
		$output = str_replace('<break>', '', $input);
		if (LAST_TABLE === 'categories' || FULL_ROUTE === '' || check_alias(FIRST_PARAMETER, 1) === 1)
		{
			$output = substr($output, 0, $this->_tags['break']['position']);

			/* add read more */

			if ($this->_route)
			{
				$output .= anchor_element('internal', '', $this->_classes['break'], l('read_more'), $this->_route);
			}
		}
		return $output;
	}

	/**
	 * parseCode
	 *
	 * @since 2.0.0
	 *
	 * @param string $input
	 * @return string
	 */

	protected function _parseCode($input = null)
	{
		$output = str_replace(array(
			'<code>',
			'</code>'
		), $this->_delimiter, $input);
		$parts = explode($this->_delimiter, $output);

		/* parse needed parts */

		foreach ($parts as $key => $value)
		{
			if ($key % 2)
			{
				$parts[$key] = '<code class="' . $this->_classes['code'] . '">' . trim(htmlspecialchars($value)) . '</code>';
			}
		}
		$output = implode($parts);
		return $output;
	}

	/**
	 * parseFunction
	 *
	 * @since 2.0.0
	 *
	 * @param string $input
	 * @return string
	 */

	protected function _parseFunction($input = null)
	{
		$output = str_replace(array(
			'<function>',
			'</function>'
		), $this->_delimiter, $input);
		$parts = explode($this->_delimiter, $output);

		/* parse needed parts */

		foreach ($parts as $key => $value)
		{
			if ($key % 2)
			{
				/* validate allowed functions */

				if (!in_array($value, $this->_forbiddenFunctions))
				{
					/* decode to array */

					$json = json_decode($value, true);
					ob_start();

					/* multiple calls with parameter */

					if (is_array($json))
					{
						foreach ($json as $function => $parameter)
						{
							echo call_user_func_array($function, $parameter);
						}
					}

					/* else single call */

					else
					{
						echo call_user_func($value);
					}
					$parts[$key] = ob_get_clean();
				}
			}
		}
		$output = implode($parts);
		return $output;
	}
}
?>