<?php

Bundle::start('orchestra');

class ExtensionPublisherFTPTest extends Orchestra\Testable\TestCase {

	/**
	 * User instance.
	 *
	 * @var Orchestra\Model\User
	 */
	private $user = null;

	/**
	 * Stub instance.
	 *
	 * @var Orchestra\Extension\Publisher\FTP
	 */
	private $stub = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();

		$this->user = Orchestra\Model\User::find(1);
		$this->stub = new Orchestra\Extension\Publisher\FTP(
			new Orchestra\Testable\FTP
		);
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
	 * Test instanceof stub
	 */
	public function testInstanceOfStub()
	{
		$this->assertInstanceOf('Orchestra\Extension\Publisher\Driver', $this->stub);
		$this->assertFalse($this->stub->connected());
	}

	/**
	 * Test Orchestra\Extension\Publisher\FTP::connect()
	 *
	 * @test
	 */
	public function testConnectUsingFTP()
	{
		$this->stub->connect();

		$refl       = new \ReflectionObject($this->stub);
		$connection = $refl->getProperty('connection');
		$connection->setAccessible(true);

		$this->assertEquals($connection->getValue($this->stub), $this->stub->connection());
		$this->assertTrue($this->stub->connected());
	}
}