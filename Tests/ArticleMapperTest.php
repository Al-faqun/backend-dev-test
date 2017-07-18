<?php

use Articles\ArticleMapper;
use PHPUnit\Framework\TestCase;

class ArticleMapperTest extends TestCase
{
	protected $pdo;
	protected $mapper;
	
	function setUp()
	{
		$this->pdo = $GLOBALS['test_pdo'];
		$this->mapper = new ArticleMapper($this->pdo);
		$this->pdo->beginTransaction();
	}
	
	function tearDown()
	{
		$this->pdo->rollback();
	}
	
	function testGetArticle()
	{
		$result = $this->mapper->getArticle(1);
		$this->assertNotFalse($result);
	}
	
	function testGetArticleFail()
	{
		$result = $this->mapper->getArticle(9000);
		$this->assertFalse($result);
	}
	
	
	function testGetArticleChildList()
	{
		$result = $this->mapper->getArticleChildList(1);
		$this->assertNotFalse($result);
	}
	
	function testGetArticleChildListFail()
	{
		$result = $this->mapper->getArticleChildList(9000);
		$this->assertFalse($result);
	}
	
	function testFetchFromSection()
	{
		$sectionMapper = new \Articles\SectionMapper($this->pdo);
		$result = $this->mapper->fetchFromSection(1, $sectionMapper);
		$this->assertNotFalse($result);
	}
	
	function testAddArticle()
	{
		$child = new \Articles\Article(null, 'youMustNotSeeThisFile', 1, 'some path');
		$result = $this->mapper->addArticle($child);
		$this->assertNotFalse($result);
	}
	
	function testUpdateArticle()
	{
		$parent = new \Articles\Article(null, 'youMustNotSeeThisFile', 1, 'some path');
		$result = $this->mapper->addArticle($parent);
		if ($result !== false) {
			$child = new \Articles\Article(
				$this->mapper->lastInsertedID(),
				'thisFileWasUpdatedAndMustNotExist',
				2,
				'some path');
			$result = $this->mapper->updateArticle($child);
		}
		$this->assertNotFalse($result);
	}
	
	function testDeleteArticle()
	{
		$parent = new \Articles\Article(null, 'youMustNotSeeThisFile', 1, 'some path');
		$result = $this->mapper->addArticle($parent);
		if ($result !== false) {
			$result = $this->mapper->deleteArticle($this->mapper->lastInsertedID());
		}
		$this->assertNotFalse($result);
	}
}