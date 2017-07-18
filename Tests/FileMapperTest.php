<?php
use FileSystem\FileMapper;
use PHPUnit\Framework\TestCase;

class FileMapperTest extends TestCase
{
	protected $pdo;
	protected $mapper;
	
	function setUp()
	{
		$this->pdo = $GLOBALS['test_pdo'];
		$this->mapper = new FileMapper($this->pdo);
		$this->pdo->beginTransaction();
	}
	
	function tearDown()
	{
		$this->pdo->rollback();
	}
	
	function testGetNode()
	{
		$result = $this->mapper->getNode(1);
		$this->assertNotFalse($result);
	}
	
	function testGetNodeFail()
	{
		$result = $this->mapper->getNode(9000);
		$this->assertFalse($result);
	}
	
	function testGetChildList()
	{
		$result = $this->mapper->getChildList(1);
		$this->assertNotFalse($result);
	}
	
	function testGetChildListFail()
	{
		$result = $this->mapper->getChildList(9000);
		$this->assertFalse($result);
	}
	
	function testFetchFromNode()
	{
		$result = $this->mapper->fetchFromNode(1);
		$this->assertNotFalse($result);
	}
	
	function testFetchFromNodeFalse()
	{
		$result = $this->mapper->fetchFromNode(9000);
		$this->assertFalse($result);
	}
	
	function testAddNode()
	{
		$child = new \FileSystem\File(null, 'youMustNotSeeThisFile', 1);
		$result = $this->mapper->addNode($child);
		$this->assertNotFalse($result);
	}
	
	function testUpdateNode()
	{
		$parent = new \FileSystem\File(null, 'youMustNotSeeThisFile', 1);
		$result = $this->mapper->addNode($parent);
		if ($result !== false) {
			$child = new \FileSystem\File($this->mapper->lastInsertedID(), 'thisFileWasUpdatedAndMustNotExist', 2);
			$result = $this->mapper->updateNode($child);
		}
		$this->assertNotFalse($result);
	}
	
	function testDeleteNode()
	{
		$parent = new \FileSystem\File(null, 'youMustNotSeeThisFile', 1);
		$result = $this->mapper->addNode($parent);
		if ($result !== false) {
			$result = $this->mapper->deleteNode($this->mapper->lastInsertedID());
		}
		$this->assertNotFalse($result);
	}
	
	function testDeleteEveryChild()
	{
		$first = new \FileSystem\File(null, 'last to be deleted', 1);
		$result = $this->mapper->addNode($first);
		$parentID =  $this->mapper->lastInsertedID();
		$second = new \FileSystem\File(null, 'second to be deleted', $this->mapper->lastInsertedID());
		$result = $this->mapper->addNode($second);
		$third = new \FileSystem\File(null, 'first to be deleted', $this->mapper->lastInsertedID());
		$result = $this->mapper->addNode($third);
		if (($result !== false) && ($parentID !== 0)) {
			$result = $this->mapper->DeleteEveryChild($parentID);
		}
		$this->assertNotFalse($result);
	}
	
	function testDeleteEveryChildFail()
	{
		$first = new \FileSystem\Folder(null, 'last to be deleted', 1);
		$result = $this->mapper->addNode($first);
		$parentID =  $this->mapper->lastInsertedID();
		$second = new \FileSystem\Folder(null, 'second to be deleted', $this->mapper->lastInsertedID());
		$result = $this->mapper->addNode($second);
		$third = new \FileSystem\File(null, 'first to be deleted', $this->mapper->lastInsertedID());
		$result = $this->mapper->addNode($third);
		if (($result !== false) && ($parentID !== 0)) {
			$result = $this->mapper->deleteEveryChild($parentID);
		}
		$this->assertNotFalse($result);
	}
}
