<?php

use Orchestra\Presenter\Resource as ResourcePresenter,
	Orchestra\Resources,
	Orchestra\View;

class Orchestra_Resources_Controller extends Orchestra\Controller {

	/**
	 * Construct Resources Controller, only authenticated user should be able
	 * to access this controller.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->filter('before', 'orchestra::auth');
	}

	/**
	 * Route to Resources List
	 *
	 * @access private
	 * @param  array    $resources
	 * @return Response
	 */
	private function index_page($resources)
	{
		return View::make('orchestra::resources.index', array(
			'table'         => ResourcePresenter::table($resources),
			'_title_'       => __('orchestra::title.resources.list'),
			'_description_' => __('orchestra::title.resources.list-detail'),
		));
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

		unset($method);

		$action    = array_shift($arguments) ?: 'index';
		$content   = null;
		$resources = Resources::all();

		switch (true)
		{
			case ($name === 'index' and $name === $action) :
				return $this->index_page($resources);
				break;
			default :
				$content = Resources::call($name, $action, $arguments);
				break;
		}

		if ( ! $content) return Response::error('404');
		elseif ($content instanceof Redirect) return $content;
		elseif ($content instanceof Response)
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
		));
	}
}
