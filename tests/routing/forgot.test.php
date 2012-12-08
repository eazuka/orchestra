<?php

Bundle::start('orchestra');

class RoutingForgotTest extends Orchestra\Testable\TestCase {

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
	 * Test Request GET (orchestra)/forgot
	 *
	 * @test
	 */
	public function testGetForgotPage()
	{
		$response = $this->call('orchestra::forgot@index');

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::forgot.index', $response->content->view);
	}

	/**
	 * Test Request GET (orchestra)/forgot with auth
	 *
	 * @test
	 */
	public function testGetForgotPageWithAuth()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::forgot@index');

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra'), 
			$response->foundation->headers->get('location'));
	}

	/**
	 * Test Request POST (orchestra)/forgot with invalid csrf
	 *
	 * @test
	 */
	public function testPostForgotPageFailedInvalidCsrf()
	{
		$response = $this->call('orchestra::forgot@index', array(), 'POST', array(
			'email' => 'example@test.com',
		));

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(500, $response->foundation->getStatusCode());
	}

	/**
	 * Test Request POST (orchestra)/forgot
	 *
	 * @test
	 */
	public function testPostForgotPage()
	{
		$this->markTestIncomplete(
			"This would require Mockery library to be installed."
		);
	}

	/**
	 * Test Request GET (orchestra)/forgot/reset/(id)/(hash)
	 *
	 * @test
	 */
	public function testGetResetPasswordPage()
	{
		$this->markTestIncomplete(
			"This would require Mockery library to be installed."
		);
	}
}