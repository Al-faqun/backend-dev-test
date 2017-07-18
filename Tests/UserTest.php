<?php
use Serializing\User;
use PHPUnit\Framework\TestCase;
use Serializing\UserMapper;

class UserTest extends TestCase
{
	protected $pdo;
	protected $mapper;
	
	function setUp()
	{
		$this->pdo = $GLOBALS['test_pdo'];
		$this->mapper = new UserMapper($this->pdo);
		$this->pdo->beginTransaction();
	}
	
	function tearDown()
	{
		$this->pdo->rollback();
	}
	
	function testGetInstance()
	{
		User::addMapper($this->mapper);
		$result = User::getInstance(1);
		$this->assertInstanceOf(User::class, $result);
	}
	
	function testIs_set()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$result = $user->is_set('home\location');
		$this->assertTrue($result);
	}
	
	function testIs_setFail()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$result = $user->is_set('home\location\DoesnNotExist');
		$this->assertFalse($result);
	}
	
	function testGet()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$result = $user->get('home\location');
		$this->assertInternalType('array', $result);
	}
	
	function testGetFail()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$this->expectException(\Exception::class);
		$result = $user->get('home\location\doesntexist');
	}
	
	function testSet()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$user->set('home\location\lat', 'newvalue');
		$result = $user->get('home\location\lat');
		$this->assertEquals('newvalue', $result);
	}
	
	function testSetNewKey()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$user->set('home\location\newkey', 'newvalue');
		$result = $user->get('home\location\newkey');
		$this->assertEquals('newvalue', $result);
	}
	function testSetFail()
	{
		User::addMapper($this->mapper);
		$user = User::getInstance(1);
		$this->expectException(\Exception::class);
		$result = $user->get('home\location\doesntexist');
	}
	
	function testSerializeUnserialize()
	{
		$array = [
			'home' => [
				'city' => 'Miami',
				'state' => 'FL',
				'location' => [
					'lat' => 40.00,
					'long' => 50.00
				]
			],
			'work' => [
				'main' => [
					'role' => 'cheif',
					'address' => 'Miami, FL'
				],
				'hobby' => (object) [
					'role' => 'painter',
					'address' => null
				],
				'age' => 64
			]
		];
		$coded = User::serialize($array);
		$decoded = User::unserialize($coded);
		$this->assertEquals($array, $decoded);
	}
}
