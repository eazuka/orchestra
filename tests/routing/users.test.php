<?php

Bundle::start('orchestra');

class RoutingUsersTest extends Orchestra\Testable\TestCase {

	/**
	 * User instance
	 *
	 * @var Orchestra\Model\User
	 */
	private $user = null;

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
		$this->be(null);

		parent::tearDown();
	}

	/**
	 * Test Request GET (orchestra)/users without auth
	 *
	 * @test
	 */
	public function testGetUsersPageWithoutAuth()
	{
		$response = $this->call('orchestra::users@index');
		
		$this->assertInstanceOf('Laravel\Redirect', $response);	
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::login'), 
			$response->foundation->headers->get('location'));
	}

	/**
	 * Test Request GET (orchestra)/users
	 *
	 * @test
	 */
	public function testGetUsersPage()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::users@index');
		
		$this->assertInstanceOf('Laravel\Response', $response);	
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::users.index', $response->content->view);
	}

	/**
	 * Test Request GET (orchestra)/users/view/1 without auth
	 *
	 * @test
	 */
	public function testGetSingleUserPageWithoutAuth()
	{
		$response = $this->call('orchestra::users@view', array(1));
		
		$this->assertInstanceOf('Laravel\Redirect', $response);	
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::login'), 
			$response->foundation->headers->get('location'));
	}

	/**
	 * Test Request GET (orchestra)/users/view/1
	 *
	 * @test
	 */
	public function testGetSingleUserPage()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::users@view', array(1));
		
		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::users.edit', $response->content->view);
	}

	/**
	 * Test Request POST (orchestra)/users/view
	 *
	 * @test
	 */
	public function testPostCreateNewUserPage()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::users@view', array(), 'POST', array(
			'email'    => 'crynobone@gmail.com',
			'fullname' => 'Mior Muhammad Zaki',
			'password' => '123456',
			'roles'    => array(2),
		));

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::users'), 
			$response->foundation->headers->get('location'));

		$user = Orchestra\Model\User::find(2);

		$this->assertEquals('crynobone@gmail.com', $user->email);
		$this->assertEquals('Mior Muhammad Zaki', $user->fullname);
		$this->assertTrue(Hash::check('123456', $user->password));
	}

	/**
	 * Test Request POST (orchestra)/users/view/1
	 *
	 * @test
	 */
	public function testPostUpdateUserPage()
	{
		$this->be($this->user);

		$this->call('orchestra::users@view', array(), 'POST', array(
			'email'    => 'crynobone@gmail.com',
			'fullname' => 'Mior Muhammad Zaki',
			'password' => '123456',
			'roles'    => array(2),
		));

		$response = $this->call('orchestra::users@view', array(2), 'POST', array(
			'email'    => 'crynobone@gmail.com',
			'fullname' => 'crynobone',
			'password' => '345678',
			'roles'    => array(2),
		));

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::users'), 
			$response->foundation->headers->get('location'));

		$user = Orchestra\Model\User::find(2);

		$this->assertEquals('crynobone@gmail.com', $user->email);
		$this->assertEquals('crynobone', $user->fullname);
		$this->assertTrue(Hash::check('345678', $user->password));
	}
	
	/**
	 * Test Request POST (orchestra)/users/view/1
	 *
	 * @test
	 */
	public function testPostDeleteUserPage()
	{
		$this->be($this->user);

		$this->call('orchestra::users@view', array(), 'POST', array(
			'email'    => 'crynobone@gmail.com',
			'fullname' => 'Mior Muhammad Zaki',
			'password' => '123456',
			'roles'    => array(2),
		));
		
		$response = $this->call('orchestra::users@delete', array(2));

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::users'), 
			$response->foundation->headers->get('location'));

		$user = Orchestra\Model\User::find(2);

		$this->assertTrue(is_null($user));
	}
}