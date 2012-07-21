<?php namespace Orchestra;

use \Config, \Exception, \Event, 
	Hybrid\Acl, Hybrid\Memory;

class Core
{
	/**
	 * Core initiated status
	 *
	 * @static
	 * @access  protected
	 * @var     boolean
	 */
	protected static $initiated = false;

	/**
	 * Cached instances for Orchestra
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $cached = array();

	/**
	 * Start Orchestra\Core
	 *
	 * @static
	 * @access public
	 * @return void
	 * @throws Exception If memory instance is not available (database not set yet)
	 */
	public static function start()
	{
		// avoid current method from being called more than once.
		if (true === static::$initiated) return ;

		// Make Menu instance
		static::$cached['orchestra_menu'] = Widget::make('menu.orchestra');
		
		// Make Menu instance for frontend application
		static::$cached['app_menu'] = Widget::make('menu.application');

		// Make ACL instance
		static::$cached['acl'] = Acl::make('orchestra');

		// First, we need to ensure that Hybrid\Acl is compliance with 
		// our Eloquent Model, This would overwrite the default configuration
		Config::set('hybrid::auth.roles', function ($user, $roles)
		{
			foreach ($user->roles()->get() as $role)
			{
				array_push($roles, $role->name);
			}

			return $roles;
		});

		try 
		{
			// Initiate Memory class
			static::$cached['memory'] = Memory::make('fluent.orchestra_options');

			if (is_null(static::$cached['memory']->get('site.name')))
			{
				throw new Exception('Installation is not completed');
			}

			// In event where we reach this point, we can consider no 
			// exception has occur, we should be able to compile acl and menu 
			// configuration
			static::$cached['acl']->attach(static::$cached['memory']);

			// In any event where Memory failed to load, we should set 
			// Installation status to false routing for installation is 
			// enabled.
			Installer::$status = true;

			static::extensions();
			static::listeners();
		}
		catch (Exception $e) 
		{
			// In any case where Exception is catched, we can be assure that
			// Installation is not done/completed, in this case we should use 
			// runtime/in-memory setup
			static::$cached['memory'] = Memory::make('runtime.orchestra');

			static::$cached['orchestra_menu']->add('install')->title('Install')->link(handles('orchestra::installer'));
		}

		static::$initiated = true;
	}

	/**
	 * Get memory instance for Orchestra
	 *
	 * @static
	 * @access public
	 * @return Hybrid\Memory
	 */
	public static function memory()
	{
		return isset(static::$cached['memory']) ? static::$cached['memory'] : null;
	}

	/**
	 * Get Acl instance for Orchestra
	 *
	 * @static
	 * @access public
	 * @return Hybrid\Acl
	 */
	public static function acl()
	{
		return isset(static::$cached['acl']) ? static::$cached['acl'] : null;
	}

	/**
	 * Get Menu instance for Orchestra
	 *
	 * @static
	 * @access public
	 * @return Hybrid\Acl
	 */
	public static function menu($type = 'orchestra')
	{
		return static::$cached["{$type}_menu"] ?: null;
	}

	/**
	 * Load Extensions for Orchestra
	 *
	 * @static
	 * @access protected
	 * @return void
	 */
	protected static function extensions()
	{
		$memory     = Core::memory();
		$availables = (array) $memory->get('extensions.available', array());
		$actives    = (array) $memory->get('extensions.active', array());

		foreach ($memory->get('extensions.active', array()) as $name)
		{
			Extension::start($name, (array) $availables[$name]['config']);
		}
	}

	/**
	 * Listeners for Orchestra
	 *
	 * @static
	 * @access protected
	 * @return void
	 */
	protected static function listeners()
	{
		Event::listen('orchestra.started: manage', function ()
		{
			Extension\Pane::make('orchestra.welcome', function ($pane)
			{
				$pane->attr = array('class' => 'hero-unit');
				$pane->html = '<h2>Welcome to your new Orchestra site!</h2>
				<p>If you need help getting started, check out our documentation on First Steps with Orchestra. If you’d rather dive right in, here are a few things most people do first when they set up a new Orchestra site. 
				<!-- If you need help, use the Help tabs in the upper right corner to get information on how to use your current screen and where to go for more assistance.--></p>';

			});

			// localize the variable, and ensure it by references.
			$menu = Core::menu('orchestra');
			$acl  = Core::acl();

			// Add basic menu.
			$menu->add('home')
				->title(__('orchestra::title.home.list')->get())
				->link(handles('orchestra'));

			// Add menu when user can manage users
			if ($acl->can('manage-users'))
			{
				$menu->add('users')
					->title(__('orchestra::title.users.list')->get())
					->link(handles('orchestra::users'));

				$menu->add('add-users', 'childof:users')
					->title(__('orchestra::title.users.create')->get())
					->link(handles('orchestra::users/view'));
			}

			// Add menu when user can manage orchestra
			if ($acl->can('manage-orchestra'))
			{
				$menu->add('extensions', 'after:home')
					->title(__('orchestra::title.extensions.list')->get())
					->link(handles('orchestra::extensions'));

				$menu->add('settings')
					->title(__('orchestra::title.settings.list')->get())
					->link(handles('orchestra::settings'));

				/*
				$menu->add('menus', 'childof:settings')->title('Menus')->link(handles('orchestra::menus'));
				$menu->add('widgets', 'childof:settings')->title('Widgets')->link(handles('orchestra::widgets'));
				 */
			}
		});
	}
}