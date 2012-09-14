<?php

use Orchestra\Resources;

class Orchestra_Resources_Controller extends Orchestra\Controller
{
	public $restful = true;

	/**
	 * Construct Pages Controller, only authenticated user should 
	 * be able to access this controller.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->filter('before', 'orchestra::auth');
		$this->filter('before', 'orchestra::manage');
		
		Event::fire('orchestra.started: backend');
	}

	/**
	 * Add a drop-in page anywhere on Orchestra
	 *
	 * @access public
	 * @param  string $request
	 * @param  array  $arguments
	 * @return Response
	 */
	public function __call($request, $arguments = array())
	{
		list($method, $name) = explode('_', $request, 2);

		$action  = array_shift($arguments) ?: 'index';
		$content = Resources::call($name, $action, $arguments);

		if (false === $content) return Response::error('404');

		return View::make('orchestra::resources.pages', array('content' => $content));
	}	
}