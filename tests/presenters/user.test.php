<?php

Bundle::start('orchestra');

class PresentersUserTest extends Orchestra\Testable\TestCase {

	/**
	 * User instance.
	 *
	 * @var Orchestra\Model\User
	 */
	protected $user = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();

		$this->user = Orchestra\Model\User::find(1);
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($this->user);

		parent::tearDown();
	}

	/**
	 * Test instanceof Orchestra\Presenter\User::table()
	 *
	 * @test
	 */
	public function testInstanceOfUserTable()
	{
		$this->be($this->user);

		$user = Orchestra\Model\User::paginate(5);
		$stub = Orchestra\Presenter\User::table($user);

		$refl = new \ReflectionObject($stub);
		$grid = $refl->getProperty('grid');
		$grid->setAccessible(true);
		$grid = $grid->getValue($stub);

		$this->assertInstanceOf('Orchestra\Table', $stub);
		$this->assertEquals(Orchestra\Table::of('orchestra.users'), $stub);
		$this->assertInstanceOf('Hybrid\Table\Grid', $grid);
	}

	/**
	 * Test instanceof Orchestra\Presenter\User::table_action()
	 *
	 * @test
	 */
	public function testInstanceOfUserTableAction()
	{
		$user   = Orchestra\Model\User::paginate(5);
		$stub   = Orchestra\Presenter\User::table($user); 
		$output = Orchestra\Presenter\User::table_actions($stub);

		$this->assertTrue(is_null($output));

		$refl = new \ReflectionObject($stub);
		$grid = $refl->getProperty('grid');
		$grid->setAccessible(true);
		$grid = $grid->getValue($stub);

		$this->assertInstanceOf('Orchestra\Table', $stub);
		$this->assertEquals(Orchestra\Table::of('orchestra.users'), $stub);
		$this->assertInstanceOf('Hybrid\Table\Grid', $grid);
	}

	/**
	 * Test instanceof Orchestra\Presenter\User::form()
	 *
	 * @test
	 */
	public function testInstanceOfUserForm()
	{
		$user = new Orchestra\Model\User;
		$stub = Orchestra\Presenter\User::form($user);

		$refl = new \ReflectionObject($stub);
		$grid = $refl->getProperty('grid');
		$grid->setAccessible(true);
		$grid = $grid->getValue($stub);

		$this->assertInstanceOf('Orchestra\Form', $stub);
		$this->assertEquals(Orchestra\Form::of('orchestra.users'), $stub);
		$this->assertInstanceOf('Hybrid\Form\Grid', $grid);
	}
	
}