<?php
use Serializing\UserMapper;
use PHPUnit\Framework\TestCase;

class UserMapperTest extends TestCase
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
	
	function testNewUserRecord()
	{
		$storage = 'ciphered';
		$result = $this->mapper->newUserRecord($storage);
		$this->assertInternalType('integer', $result);
	}
	
	function testFetchStorage()
	{
		$storage = $this->mapper->fetchStorage(1);
		$this->assertNotFalse($storage);
	}
	
	function testFetchStorageFail()
	{
		$storage = $this->mapper->fetchStorage(9000);
		$this->assertFalse($storage);
	}
	
	function testReplaceStorage()
	{
		$storage = 'ciphered';
		$result = $this->mapper->newUserRecord($storage);
		$newstorage = 'second ciphered';
		$result = $this->mapper->replaceStorage($this->pdo->lastInsertId(), $newstorage);
		$this->assertNotFalse($result);
	}
}
