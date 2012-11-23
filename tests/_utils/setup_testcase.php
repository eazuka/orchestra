<?php

use Symfony\Component\HttpFoundation\LaravelRequest;

include_once "controller_testcase.php";

function setup_orchestra_env()
{
	Config::set('auth.driver', 'eloquent');
	Config::set('auth.model', 'Orchestra\Model\User');
	Config::set('database.default', 'sqlite');
	Config::set('database.connections.sqlite', array(
		'driver'   => 'sqlite',
		'database' => ':memory:',
		'prefix'   => '',
	));

	DB::$connections = array();

	Laravel\Session::load();
}

function teardown_orchestra_env()
{
	Config::set('auth.driver', 'eloquent');
	Config::set('auth.model', 'User');
}

function setup_orchestra_fixture()
{
	if ( ! Orchestra\Installer::installed())
	{
		Request::$foundation = LaravelRequest::createFromGlobals();

		Request::foundation()->server->add(array(
			'REQUEST_METHOD' => 'POST',
		));

		Orchestra\Installer\Runner::install();

		Orchestra\Installer\Runner::create_user(array(
			'site_name' => 'Orchestra',
			'email'     => 'example@test.com',
			'password'  => '123456',
			'fullname'  => 'Orchestra TestRunner',
		));

		Orchestra\Core::shutdown();
		Orchestra\Memory::shutdown();
		Orchestra\Acl::shutdown();

		Orchestra\Core::start();
	}
}
