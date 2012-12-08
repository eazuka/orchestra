<?php

Bundle::start('orchestra');

class RoutingInstallerTest extends Orchestra\Testable\TestCase {
	
	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();

		$this->removeApplication();

		Session::load();
	}

	/**
	 * Test Request GET (orchestra)/installer/index
	 *
	 * @test
	 */
	public function testGetInstallerPage()
	{
		$response = $this->call('orchestra::installer@index', array());

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::installer.index', $response->content->view);

		$response = $this->call('orchestra::installer@steps', array(1));

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::installer.step1', $response->content->view);

		$response = $this->call('orchestra::installer@steps', array(2), 'POST', array(
			'site_name' => 'Orchestra',
			'email'     => 'example@test.com',
			'password'  => '123456',
			'fullname'  => 'Orchestra TestRunner',
		));

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::installer.step2', $response->content->view);
	}
}