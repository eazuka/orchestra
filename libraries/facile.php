<?php namespace Orchestra;

use InvalidArgumentException,
	RuntimeException,
	IoC,
	Response;

class Facile {

	/**
	 * Lists of templates
	 */
	public static $templates = array();

	/**
	 * Create a new Facile instance.
	 *
	 * @static
	 * @access public			
	 * @param  string   $name   Name of template
	 * @param  array    $data
	 * @param  string   $format
	 * @return self
	 */
	public static function make($name, $data = array(), $format = null) 
	{
		return new static($name, $data, $format);
	}

	/**
	 * Register a template.
	 *
	 * @static
	 * @access public
	 * @param  string           $name
	 * @param  Facile\Template  $template
	 * @return void
	 */
	public static function template($name, $template) 
	{
		$resolve = IoC::resolve($template);

		if ( ! ($resolve instanceof Facile\Driver))
		{
			throw new RuntimeException(
				"Expected \$template to be instanceof Orchestra\Facile\Driver."
			);
		}

		static::$templates[$name] = $resolve;
	}

	/**
	 * Create a new Facile instance.
	 *
	 * @access protected	
	 * @param  string   $name   Name of template
	 * @param  array    $data
	 * @param  string   $format
	 * @return self
	 */
	protected function __construct($name, $data = array(), $format = null) 
	{
		$this->name   = $name;
		$this->data   = $data;
		$this->format = $format;

		if ( ! isset(static::$templates[$name]))
		{
			throw new InvalidArgumentException(
				"Template [{$name}] is not available."
			);
		}

		$this->template = static::$templates[$name];
	}

	/**
	 * Name of template.
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * View template.
	 *
	 * @var Facile\Template
	 */
	protected $template = null;

	/**
	 * View format.
	 *
	 * @var string
	 */
	protected $format = null;

	/**
	 * View data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Render facile.
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Render facile by format.
	 *
	 * @access public
	 * @return mixed
	 */
	public function render()
	{
		$format   = $this->format;
		$template = $this->template;

		// Get expected response format.
		is_null($format) and $format = $template->format();

		return $template->compose($format, $this->data);
	}
}