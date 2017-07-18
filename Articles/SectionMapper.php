<?php
namespace Articles;

/**
 * Class SectionMapper
 * @package Articles
 */
class SectionMapper
{
	private $pdo;
	
	/**
	 * SectionMapper constructor.
	 * @param \PDO $pdo
	 */
	function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
	/**
	 * Получить массив с данными раздела по его айди.
	 * @param int $id
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
	 * Получить массив с данными о детях типа "Секция" конкретной секции.
	 * @param int $sectionID
	 * @return array|false
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
	
	/**
	 * Добавить в базу данных секцию (параметр айди объекта роли не играет).
	 * @param Section $section
	 * @return bool
	 * @throws \Exception
	 */
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
	 * Получить ID результата последней команды INSERT
	 * (имеет смысл вызывать только следующей же командой после этого INSERT).
	 * @return int
	 */
	function lastInsertedId()
	{
		return (int)$this->pdo->lastInsertId();
	}
	
	/**
	 * Изменить данные раздела.
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
	 * Удалить раздел по его айди
	 * (внимание, дочерние ноды, если они есть, не трогаются!).
	 * @param int $id
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
	 * Удалить каждого ребёнка раздела
	 * (внимание, сам раздел не удаляется!).
	 * @param int $sectionID
	 * @param ArticleMapper $articleMapper
	 * @return bool
	 */
	function deleteEveryChild($sectionID, ArticleMapper $articleMapper)
	{
		//функция, которая рекурсивно удаляет всех детей ноды и её саму
		$deleteChildRecursive = function($child) use (&$deleteChildRecursive, $articleMapper) {
			//получаем список и удаляем детей типа "Статьи"
			$articles = $articleMapper->getArticleChildList($child['id']);
			if ($articles !== false) {
				foreach ($articles as $article) {
					$articlesSuccess = $articleMapper->deleteArticle($article['id']);
				}
			}
			//получаем список и удаляем детей типа "Разделы", а также всех их детей
			$sections = $this->getSectionChildList($child['id']);
			if ($sections !== false) {
				foreach ($sections as $section) {
					$sectionsSuccess = $deleteChildRecursive($section);
				}
			}
			$result = $this->deleteSection($child['id']);
			//возвращаем true, только если все предыдущие sql-команды вернули нам true,
			//в противном случае возвращаем false
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
			//получаем список детей секции
			$sectionsChildren = $this->getSectionChildList($sectionID);
			if ($sectionsChildren !== false) {
				//удаляем каждого из них и всех его детей
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
	 * @param array $row
	 * @return Section
	 */
	public function toSection($row)
	{
		$result = new Section($row['id'], $row['name'], $row['parent_id']);
		return $result;
	}
	
	/**
	 * Этот метод преобразует объект в нужную sql.
	 *
	 * @param Section $object
	 * @param string $typeOfStatement 'insert' или 'update'
	 * @return \PDOStatement
	 * @throws \Exception
	 */
	private function toStatement(Section $object, $typeOfStatement = 'insert')
	{
		try {
			//в зависимости от переданного типа sql
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
			//создаём нужный pdo-statment
			$stmt = $this->pdo->prepare($sql);
			//привязываем переменные к placeholder'ам
			//остаётся только исполнить
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