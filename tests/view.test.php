<?php

Bundle::start('orchestra');

class ViewTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$_SERVER['view.started'] = null;
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($_SERVER['view.started']);
	}

	/**
	 * Test construct a Orchestra\View
	 *
	 * @test
	 */
	public function testConstructAView()
	{
		Event::listen('orchestra.started: view', function ()
		{
			$_SERVER['view.started'] = 'foo';
		});

		$this->assertTrue(is_null($_SERVER['view.started']));

		$view = new Orchestra\View('orchestra::layout.main');

		$this->assertInstanceOf('Laravel\View', $view);
		$this->assertEquals('frontend', Orchestra\View::$theme);
		$this->assertEquals('foo', $_SERVER['view.started']);

		$refl = new \ReflectionObject($view);
		$file = $refl->getProperty('view');
		$file->setAccessible(true);

		$this->assertEquals('orchestra::layout.main', $file->getValue($view));
	}

	/**
	 * Test Orchestra\View::exists()
	 *
	 * @test
	 */
	public function testExists()
	{
		$result = Orchestra\View::exists('orchestra::layout.main');

		$this->assertTrue(is_bool($result));

		$path = Orchestra\View::exists('orchestra::layout.main', true);
		$expected = Bundle::path('orchestra').'views'.DS.'layout'.DS.'main.blade.php';

		$this->assertEquals($expected, $path);
	}
}