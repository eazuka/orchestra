<?php

Bundle::start('orchestra');

class RoutingSettingsTest extends Orchestra\Testable\TestCase {
	
	/**
	 * User instance.
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

		parent::tearDown();
	}

	/**
	 * Test Request GET (orchestra)/settings without auth
	 *
	 * @test
	 */
	public function testGetSettingsPageWithoutAuth()
	{
		$response = $this->call('orchestra::settings@index');

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::login'), 
			$response->foundation->headers->get('location'));
	}

	/**
	 * Test Request GET (orchestra)/settings
	 *
	 * @test
	 */
	public function testGetSettingsPage()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::settings@index');

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::settings.index', $response->content->view);
	}

	/**
	 * Test Request POST (orchestra)/settings
	 *
	 * @test
	 */
	public function testPostSettingsPageIsSuccessful()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::settings@index', array(), 'POST', array(
			'site_name'              => 'Foo',
			'site_description'       => 'Foobar',
			'site_user_registration' => 'no',

			'email_default'          => 'mail',
			'email_from'             => 'admin@codenitive.com',
			'email_smtp_host'        => '',
			'email_smtp_port'        => '',
			'email_smtp_username'    => '',
			'email_smtp_password'    => '',
			'email_smtp_encryption'  => '',
			'email_sendmail_command' => '',
			'stmp_change_password'   => 'no',
		));

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::settings'), 
			$response->foundation->headers->get('location'));

		$this->assertEquals('Foo', memorize('site.name'));
		$this->assertEquals('Foobar', memorize('site.description'));
	}

	/**
	 * Test Request POST (orchestra)/settings failed
	 *
	 * @test
	 */
	public function testPostSettingsPageFailed()
	{
		$this->be($this->user);

		$response = $this->call('orchestra::settings@index', array(), 'POST', array(
			'site_name' => "Hello"
		));

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals(302, $response->foundation->getStatusCode());
		$this->assertEquals(handles('orchestra::settings'), 
			$response->foundation->headers->get('location'));

		$this->assertFalse('Hello' === memorize('site.name'));
	}
}