<?php

use Orchestra\Resources;

class Orchestra_Resources_Controller extends Orchestra\Controller
{
	public $restful = true;

	/**
	 * Construct Resources Controller, only authenticated user should 
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
	 * Add a drop-in resource anywhere on Orchestra
	 *
	 * @access public
	 * @param  string $request
	 * @param  array  $arguments
	 * @return Response
	 */
	public function __call($request, $arguments = array())
	{
		list($method, $name) = explode('_', $request, 2);

		$action    = array_shift($arguments) ?: 'index';
		$page_name = '';
		$page_desc = '';
		$content   = "";

		switch (true) 
		{
			case ($name === 'index' and $name === $action) :
				$page_name = __("orchestra::title.resources.list")->get();
				break;
			default :
				$content = Resources::call($name, $action, $arguments);
				break;
		}

		$resources = Resources::all();

		if ($content instanceof Response)
		{
			$status_code = $content->foundation->getStatusCode();

			if ( ! $content->foundation->isSuccessful())
			{
				return Response::error($status_code);
			}
		}

		return View::make('orchestra::resources.resources', array(
			'content'        => $content,
			'resources_list' => $resources,
			'page_name'      => $page_name,
			'page_desc'      => $page_desc,
		));
	}	
}