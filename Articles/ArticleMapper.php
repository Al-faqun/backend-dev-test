<?php
namespace Articles;

/**
 * Class ArticleMapper
 * @package Articles
 */
class ArticleMapper
{
	private $pdo;
	
	/**
	 * ArticleMapper constructor.
	 * @param \PDO $pdo
	 */
	function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
	/**
	 * Получить массив с данными статьи по айди.
	 *
	 * @param int $id
	 * @return array|false
	 */
	function getArticle($id)
	{
		$query = 'SELECT `id`, `name`, `text_filepath`, `parent_id` FROM `articles` WHERE `id` = :id LIMIT 1';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute(['id' => $id]);
		$node = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $node;
	}
	
	/**
	 * Получить массив с данными о детях-статьях, принадлежащих указанной секции.
	 *
	 * @param int $sectionID
	 * @return array|false
	 */
	function getArticleChildList($sectionID)
	{
		$query = 'SELECT `id`, `name`, `text_filepath`, `parent_id` FROM `articles` WHERE `parent_id` = :id';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute(['id' => $sectionID]);
		//получаем ассоциативный массив
		$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($data)) {
			$data = false;
		}
		return $data;
	}
	
	/**
	 * Получить класс Секции и рекурсивно всех её потмков.
	 *
	 * @param $sectionID
	 * @param SectionMapper $sectionMapper
	 * @return Section|bool
	 */
	function fetchFromSection($sectionID, SectionMapper $sectionMapper)
	{
		//рекурсивно получаем всех потомков выбранной Секции
		$fetchChildRecursive = function($parent) use (&$fetchChildRecursive, $sectionMapper) {
			//превращаем массив в класс Section
			$parent = $sectionMapper->toSection($parent);
			//получаем список детей-статей
			$articles = $this->getArticleChildList($parent->ID);
			if ($articles !== false) {
				foreach ($articles as $article) {
					//каждую статью добавляем в список детей
					$parent->addChild($this->toArticle($article));
				}
			}
			//получаем список детей-секций
			$sections = $sectionMapper->getSectionChildList($parent->ID);
			if ($sections !== false) {
				foreach ($sections as $section) {
					//каждую секцию (и всех её детей) добавляем рекурсивно как ребенка
					$parent->addChild($fetchChildRecursive($section));
				}
			}
			return $parent;
		};
		
		try {
			//получаем массив с данными родительской секции
			$root = $sectionMapper->getSection($sectionID);
			if ($root !== false) {
				//получаем класс Section со всеми потомками
				$result = $fetchChildRecursive($root);
			} else {
				$result = false;
			}
		} catch (\PDOException $e) {
			//возможная обработка ошибок
		}
		
		return $result;
	}
	
	/**
	 * Добавляем статью в базу данных
	 * (обратите внимание, айди в объекте значения не имеет).
	 *
	 * @param Article $article
	 * @return bool
	 * @throws \Exception
	 */
	function addArticle(Article $article)
	{
		try {
			$stmt = $this->toStatement($article, 'insert');
			$result = $stmt->execute();
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении данных', 0, $e);
		}
		return $result;
	}
	
	/**
	 * @return int
	 */
	function lastInsertedId()
	{
		return (int)$this->pdo->lastInsertId();
	}
	
	/**
	 * @param Article $article
	 * @return bool
	 * @throws \Exception
	 */
	function updateArticle(Article $article)
	{
		try {
			$stmt = $this->toStatement($article, 'update');
			$result = $stmt->execute();
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении данных', 0, $e);
		}
		return $result;
	}
	
	/**
	 * @param $id
	 * @return bool
	 * @throws \Exception
	 */
	function deleteArticle($id)
	{
		try {
			$sql = 'DELETE FROM `articles` WHERE `id` = :id';
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute(['id' => $id]);
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при удалении данных', 0, $e);
		}
		return $result;
	}
	
	/**
	 * Преобразовать массив с данными в класс Article.
	 *
	 * @param $row
	 * @return Article
	 */
	public function toArticle($row)
	{
		$result = new Article($row['id'], $row['name'], $row['parent_id'], $row['text_filepath']);
		return $result;
	}
	
	/**
	 * Получить pdoStatement для сущности.
	 *
	 * @param Article $object
	 * @param string $typeOfStatement
	 * @return \PDOStatement
	 * @throws \Exception
	 */
	private function toStatement(Article $object, $typeOfStatement = 'insert')
	{
		try {
			$typeOfStatement = strtolower($typeOfStatement);
			switch ( $typeOfStatement ) {
				case 'insert':
					$sql = 'INSERT INTO `articles`(`name`, `parent_id`, `text_filepath`)
					        VALUES (:name, :parent_id, :text_filepath)';
					break;
				case 'update':
					$sql = 'UPDATE `articles`
					        SET `name`= :name,
					        `parent_id`= :parent_id,
					        `text_filepath`= :text_filepath
 					        WHERE `id` = :id';
					break;
				default:
					throw new \Exception('Incorrect type of statement');
			}
			$stmt = $this->pdo->prepare($sql);
			if ($typeOfStatement === 'update') {
				$stmt->bindParam(':id', $object->ID, \PDO::PARAM_INT);
			}
			$stmt->bindParam(':name', $object->name);
			$stmt->bindParam(':parent_id', $object->parentID, \PDO::PARAM_INT);
			$stmt->bindParam(':text_filepath', $object->textFilepath);
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении данных.', 0, $e);
		}
		return $stmt;
		
	}
}