<?php

use Laravel\Fluent,
	Orchestra\Core,
	Orchestra\Extension,
	Orchestra\Extension\Publisher,
	Orchestra\Form,
	Orchestra\HTML,
	Orchestra\Messages,
	Orchestra\View;

class Orchestra_Extensions_Controller extends Orchestra\Controller {

	/**
	 * Construct Extensions Controller, only authenticated user should be
	 * able to access this controller.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->filter('before', 'orchestra::auth');
		$this->filter('before', 'orchestra::manage');
	}

	/**
	 * List all available extensions
	 *
	 * GET (:bundle)/extensions
	 *
	 * @access public
	 * @return Response
	 */
	public function get_index()
	{
		$data = array(
			'extensions' => Extension::detect(),
			'_title_' => __("orchestra::title.extensions.list"),
		);

		foreach($data['extensions'] as $name => & $extension)
		{
			isset($extension->require) or $extension->require = array();

			$extension->require    = (array) $extension->require;
			$extension->unresolved = Extension::not_activatable($name);
		}

		return View::make('orchestra::extensions.index', $data);
	}

	/**
	 * Activate an extension
	 *
	 * GET (:bundle)/extensions/activate/(:name)
	 *
	 * @access public
	 * @param  string   $name name of the extension
	 * @return Response
	 */
	public function get_activate($name = null)
	{
		if (is_null($name) or Extension::started($name)) return Response::error('404');

		$msg = new Messages;

		try
		{
			Extension::activate($name);

			$msg->add('success', __('orchestra::response.extensions.activate', array(
				'name' => $name,
			)));
		}
		catch (Orchestra\Extension\FilePermissionException $e)
		{
			Publisher::queue($name);

			// In events where extension can't be activated due to 
			// bundle:publish we need to put this under queue.
			return Redirect::to(handles('orchestra::publisher'));
		}
		catch (Orchestra\Extension\UnresolvedException $e)
		{
			// In events where extension may require other extension, we 
			// should notify them to such issues.
			$get_name_version = function($dep)
			{
				return $dep['name'].' '.$dep['version'];
			};

			$dependencies = array_map($get_name_version, $e->getDependencies());
			$msg->add('error', __('orchestra::response.extensions.depends-on', array(
				'name'         => $name,
				'dependencies' => implode(', ', $dependencies)
			)));
		}

		return Redirect::to(handles('orchestra::extensions'))
				->with('message', $msg->serialize());
	}

	/**
	 * Deactivate an extension
	 *
	 * GET (:bundle)/extensions/deactivate/(:name)
	 *
	 * @access public
	 * @param  string   $name name of the extension
	 * @return Response
	 */
	public function get_deactivate($name = null)
	{
		if (is_null($name) or ! Extension::started($name)) return Response::error('404');

		$msg = new Messages;

		try
		{
			Extension::deactivate($name);
			$msg->add('success', __('orchestra::response.extensions.deactivate', array(
				'name' => $name,
			)));
		}
		catch (Orchestra\Extension\UnresolvedException $e)
		{
			$msg->add('error', __('orchestra::response.extensions.other-depends-on', array(
				'name'         => $name,
				'dependencies' => implode(', ', $e->getDependencies())
			)));
		}

		return Redirect::to(handles('orchestra::extensions'))
				->with('message', $msg->serialize());
	}

	/**
	 * Configure an extension
	 *
	 * GET (:bundle)/extensions/configure/(:name)
	 *
	 * @access public
	 * @param  string   $name name of the extension
	 * @return Response
	 */
	public function get_configure($name = null)
	{
		if (is_null($name) or ! Extension::started($name)) return Response::error('404');

		if (Extension::option($name, 'configurable') === false) return Response::error('404');

		// Load configuration from memory.
		$memory = Core::memory();
		$config = new Fluent((array) $memory->get("extension_{$name}", array()));

		$extension_name = $memory->get("extensions.available.{$name}.name", $name);

		// Add basic form, allow extension to add custom configuration field
		// to this form using events.
		$form = Form::of("orchestra.extension: {$name}", function ($form) use ($name, $config)
		{
			$form->row($config);

			$form->attr(array(
				'action' => handles("orchestra::extensions/configure/{$name}"),
				'method' => "POST",
			));

			$handles = Extension::option($name, 'handles');

			$form->fieldset(function ($fieldset) use ($handles, $name)
			{
				// We should only cater for custom URL handles for a route.
				if ( ! is_null($handles) and Extension::option($name, 'configurable') !== false)
				{
					$fieldset->control('input:text', 'handles', function ($control) use ($handles)
					{
						$control->label = 'Handle URL';
						$control->value = $handles;
					});
				}

				$fieldset->control('input:text', 'migrate', function ($control) use ($handles, $name)
				{
					$control->label = __('orchestra::label.extensions.update');

					$control->field = function($row, $self) use ($name)
					{
						return HTML::link(
							handles('orchestra::extensions/update/'.$name),
							__('orchestra::label.extensions.actions.update'),
							array('class' => 'btn btn-info')
						);
					};

				});

			});
		});

		// Now lets the extension do their magic.
		Event::fire("orchestra.form: extension.{$name}", array($config, $form));

		$data = array(
			'eloquent'      => $config,
			'form'          => Form::of("orchestra.extension: {$name}"),
			'_title_'       => $extension_name,
			'_description_' => __("orchestra::title.extensions.configure"),
		);

		return View::make('orchestra::extensions.configure', $data);
	}

	/**
	 * Update extension configuration
	 *
	 * POST (:bundle)/extensions/configure/(:name)
	 *
	 * @access public
	 * @param  string   $name name of the extension
	 * @return Response
	 */
	public function post_configure($name = null)
	{
		if (is_null($name) or ! Extension::started($name)) return Response::error('404');

		$input  = Input::all();
		$memory = Core::memory();
		$config = new Fluent((array) $memory->get("extension_{$name}", array()));
		$loader = (array) $memory->get("extensions.active.{$name}", array());
		$msg    = new Messages;

		// This part should be part of extension loader configuration. What
		// saved here wouldn't be part of extension configuration.
		if (isset($input['handles']) and ! empty($input['handles']))
		{
			$loader['handles'] = $input['handles'];
		}

		$memory->put("extensions.active.{$name}", $loader);

		// In any event where extension need to do some custom handling.
		Event::fire("orchestra.saving: extension.{$name}", array($config));

		$memory->put("extension_{$name}", $input);

		Event::fire("orchestra.saved: extension.{$name}", array($config));

		$msg->add('success', __("orchestra::response.extensions.configure", compact('name')));

		return Redirect::to(handles('orchestra::extensions'))
			->with('message', $msg->serialize());
	}

	/**
	 * Update an extension, run migration and bundle publish command.
	 *
	 * GET (:bundle)/extensions/update/(:name)
	 *
	 * @access public
	 * @param  string   $name name of the extension
	 * @return Response
	 */
	public function get_update($name)
	{
		// we should only be able to upgrade extension which is already
		// started
		if ( ! Extension::started($name))
		{
			return Response::error('404');
		}

		$msg = new Messages;

		try
		{
			Extension::publish($name);
		}
		catch (Orchestra\Extension\FilePermissionException $e)
		{
			Publisher::queue($name);
			
			// In events where extension can't be activated due to 
			// bundle:publish we need to put this under queue.
			return Redirect::to(handles('orchestra::publisher'));
		}

		$msg->add('success', __('orchestra::response.extensions.update', compact('name')));

		return Redirect::to(handles('orchestra::extensions'))
				->with('message', $msg->serialize());
	}
}
