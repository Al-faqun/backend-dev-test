<?php
namespace Articles;


class SectionMapper
{
	private $pdo;
	
	function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
	/**
	 * @param $id
	 * @return array|false
	 */
	function getSection($id)
	{
		$query = 'SELECT `id`, `name`, `parent_id` FROM `sections` WHERE `id` = :id LIMIT 1';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute(['id' => $id]);
		$node = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $node;
	}
	
	/**
	 *@return array|false
	 */
	function getSectionChildList($sectionID)
	{
		$query = 'SELECT `id`, `name`, `parent_id` FROM `sections` WHERE `parent_id` = :id';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute(['id' => $sectionID]);
		$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($data)) {
			$data = false;
		}
		return $data;
	}
	
	function addSection(Section $section)
	{
		try {
			$stmt = $this->toStatement($section, 'insert');
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
	 * @param Section $section
	 * @return bool
	 * @throws \Exception
	 */
	function updateSection(Section $section)
	{
		try {
			$stmt = $this->toStatement($section, 'update');
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
	function deleteSection($id)
	{
		try {
			$sql = 'DELETE FROM `sections` WHERE `id` = :id';
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute(['id' => $id]);
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при удалении данных', 0, $e);
		}
		return $result;
	}
	
	/**
	 * @param $sectionID
	 * @param ArticleMapper $articleMapper
	 * @return bool
	 */
	function deleteEveryChild($sectionID, ArticleMapper $articleMapper)
	{
		$deleteChildRecursive = function($child) use (&$deleteChildRecursive, $articleMapper) {
			$articles = $articleMapper->getArticleChildList($child['id']);
			if ($articles !== false) {
				foreach ($articles as $article) {
					$articlesSuccess = $articleMapper->deleteArticle($article['id']);
				}
			}
			$sections = $this->getSectionChildList($child['id']);
			if ($sections !== false) {
				foreach ($sections as $section) {
					$sectionsSuccess = $deleteChildRecursive($section);
				}
			}
			$result = $this->deleteSection($child['id']);
			if (isset($articlesSuccess))  {
				if (isset($sectionsSuccess)) {
					$result = (($articlesSuccess  === true) AND ($sectionsSuccess  === true)) ? $result : false;
				} else {
					$result = ($articlesSuccess  === true) ? $result : false;
				}
				
			}
			return $result;
		};
		$result = true; //значение по умолчанию (в отстутствие предмета для удаления)
		try {
			$sectionsChildren = $this->getSectionChildList($sectionID);
			if ($sectionsChildren !== false) {
				foreach ($sectionsChildren as $child) {
					$result = $deleteChildRecursive($child);
				}
			}
		} catch (\PDOException $e) {
			//возможная обработка ошибок
		}
		return $result;
	}
	
	/**
	 * @param $row
	 * @return Section
	 */
	public function toSection($row)
	{
		$result = new Section($row['id'], $row['name'], $row['parent_id']);
		return $result;
	}
	
	/**
	 * @param Section $object
	 * @param string $typeOfStatement
	 * @return \PDOStatement
	 * @throws \Exception
	 */
	private function toStatement(Section $object, $typeOfStatement = 'insert')
	{
		try {
			$typeOfStatement = strtolower($typeOfStatement);
			switch ( $typeOfStatement ) {
				case 'insert':
					$sql = 'INSERT INTO `sections`(`name`, `parent_id`)
					        VALUES (:name, :parent_id)';
					break;
				case 'update':
					$sql = 'UPDATE `articles`
					        SET `name`= :name,
					        `parent_id`= :parent_id
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
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении данных.', 0, $e);
		}
		return $stmt;
		
	}
}