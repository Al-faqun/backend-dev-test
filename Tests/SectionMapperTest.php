<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 13.07.2017
 * Time: 18:38
 */

use Articles\SectionMapper;
use PHPUnit\Framework\TestCase;

class SectionMapperTest extends TestCase
{
	protected $pdo;
	protected $mapper;
	
	function setUp()
	{
		$this->pdo = $GLOBALS['test_pdo'];
		$this->mapper = new SectionMapper($this->pdo);
		$this->pdo->beginTransaction();
	}
	
	function tearDown()
	{
		$this->pdo->rollback();
	}
	
	function testGetSection()
	{
		$result = $this->mapper->getSection(1);
		$this->assertNotFalse($result);
	}
	
	function testGetSectionFail()
	{
		$result = $this->mapper->getSection(9000);
		$this->assertFalse($result);
	}
	
	function testGetSectionChildList()
	{
		$result = $this->mapper->getSectionChildList(1);
		$this->assertNotFalse($result);
	}
	
	function testGetSectionChildListFail()
	{
		$result = $this->mapper->getSectionChildList(9000);
		$this->assertFalse($result);
	}
	
	
	function testAddSection()
	{
		$child = new \Articles\Section(null, 'youMustNotSeeThisFile', 1);
		$result = $this->mapper->addSection($child);
		$this->assertNotFalse($result);
	}
	
	function testUpdateSection()
	{
		$parent = new \Articles\Section(null, 'youMustNotSeeThisFile', 1);
		$result = $this->mapper->addSection($parent);
		if ($result !== false) {
			$child = $parent = new \Articles\Section(null, 'youMustNotSeeThisFile', 2);
			$result = $this->mapper->updateSection($child);
		}
		$this->assertNotFalse($result);
	}
	
	function testDeleteSection()
	{
		$parent = new \Articles\Section(null, 'youMustNotSeeThisFile', 1);
		$result = $this->mapper->addSection($parent);
		if ($result !== false) {
			$result = $this->mapper->deleteSection($this->mapper->lastInsertedID());
		}
		$this->assertNotFalse($result);
	}
	
	function testDeleteEveryChildFail()
	{
		$articleMapper = new \Articles\ArticleMapper($this->pdo);
		$first =  new \Articles\Section(null, 'last to be deleted', 1);
		$result = $this->mapper->addSection($first);
		$parentID =  $this->mapper->lastInsertedID();
		
		$second = new \Articles\Section(null, 'second to be deleted', $parentID);
		$result = $this->mapper->addSection($second);
		
		$third = new \Articles\Article(null, 'first to be deleted', $this->mapper->lastInsertedID(), 'somepath');
		$result = $articleMapper->addArticle($third);
		
		if (($result !== false) && ($parentID !== 0)) {
			$result = $this->mapper->deleteEveryChild($parentID, $articleMapper);
		}
		$this->assertNotFalse($result);
	}
}
