<?php
namespace FileSystem;

class FileMapper
{
	private $pdo;
	private $requiredFields;

	function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
		$this->requiredFields = array('id', 'name', 'type');
	}

	/**
	*@return array|false
	*/
	function getNode($id)
	{
		$query = 'SELECT `id`, `name`, `type`, `parent_id` FROM `files` WHERE `id` = :id LIMIT 1';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute(['id' => $id]);
		$node = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $node;
	}

	/**
	*@return array|false
	*/
	function getChildList($id)
	{
		$query = 'SELECT `id`, `name`, `type`, `parent_id` FROM `files` WHERE `parent_id` = :id';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute(['id' => $id]);
		$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($data)) {
			$data = false;
		}
		return $data;
	}

	/**
	*@return File|Folder|false
	*/
	function fetchFromNode($id)
	{
		try { 
			$fetchChildRecursive = function($child) use (&$fetchChildRecursive) {
				if ($child['type'] === 'folder') {
					$entity = new Folder($child['id'], $child['name'], $child['parent_id']);
					$children = $this->getChildList($child['id']);
					if (!empty($children)) {
						foreach ($children as $element) {
							$entity->addChild($fetchChildRecursive($element));
						}
						
					}
				} elseif ($child['type'] === 'file') {
					$entity = new File($child['id'], $child['name'], $child['parent_id']);
				}
				return $entity;
			};

			$root = $this->toObject($this->getNode($id)); 
			if ($root instanceOf Folder) {
				$children = $this->getChildList($id);
				if (!empty($children)) {
					foreach ($children as $child) {
						$root->addChild($fetchChildRecursive($child));
					}
				}
			} 
		} catch (\PDOException $e) {
			//возможная обработка ошибок
		}

		return $root;
	}
	
	/**
	 * Object's ID is ignored and auto-generated.
	 * @param FilesEntity $child
	 * @return bool
	 * @throws \Exception
	 */
	function addNode(FilesEntity $child)
	{
		try {
			$stmt = $this->toStatement($child, 'insert');
			$result = $stmt->execute();
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении данных', 0, $e);
		}
		return $result;

	}

	/**
	* @return int id of last inserted ID or 0 if cannot retrieve
	 * Only has a meaning if used [u]directly[/u] after the insert statement.
	*/
	public function lastInsertedId()
	{
		return (int)$this->pdo->lastInsertId();
	}
	
	/**
	 *@return bool
	 */
	function updateNode(FilesEntity $child)
	{
		try {
			$stmt = $this->toStatement($child, 'update');
			$result = $stmt->execute();
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при обновлении данных', 0, $e);
		}
		return $result;
	}
	
	/**
	 *@return bool
	 */
	function deleteNode($id)
	{
		try {
			$sql = 'DELETE FROM `files` WHERE `id` = :id';
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute(['id' => $id]);
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при удалении данных', 0, $e);
		}
		return $result;
	}
	
	function deleteEveryChild($id)
	{
		$deleteChildRecursive = function($child) use (&$deleteChildRecursive) {
			if ($child['type'] === 'folder') {
				$children = $this->getChildList($child['id']);
				if (!empty($children)) {
					foreach ($children as $element) {
						$returned = $deleteChildRecursive($element);
					}
				}
			}
			$result = $this->deleteNode($child['id']);
			if (isset($returned)) {
				$result = ($returned  === true) ? $result : false;
			}
			return $result;
		};
		$result = true;
		$children = $this->getChildList($id);
		if ($children !== false) {
			foreach ($children as $child) {
				$result = $deleteChildRecursive($child);
			}
		}
		return $result;
	}
	
	private function toObject($row)
	{
		if ($this->checkIntegrity($row) === true) {
			if ($row['type'] === 'folder') {
				$result = new Folder($row['id'], $row['name'], $row['parent_id']);
			} elseif($row['type'] === 'file') {
				$result = new File($row['id'], $row['name'], $row['parent_id']);
			}
		} else {
			$result = false;
		}
		return $result;
	}
	
	/**
	 * @param FilesEntity $object
	 * @param string $typeOfStatement
	 * @return \PDOStatement
	 */
	private function toStatement(FilesEntity $object, $typeOfStatement = 'insert')
	{
		try {
			$typeOfStatement = strtolower($typeOfStatement);
			switch ( $typeOfStatement ) {
				case 'insert':
					$sql = 'INSERT INTO `files`(`type`, `name`, `parent_id`) VALUES (:type, :name, :parent_id)';
					break;
				case 'update':
					$sql = 'UPDATE `files` SET `type`= :type,`name`= :name,`parent_id`= :parent_id WHERE `id` = :id';
					break;
				default:
					throw new \Exception('Incorrect type of statement');
			}
			$stmt = $this->pdo->prepare($sql);
			$type = ($object instanceOf Folder) ? 'folder' : 'file';
			if ($typeOfStatement === 'update') {
				$stmt->bindParam(':id', $object->ID, \PDO::PARAM_INT);
			}
			$stmt->bindParam(':type', $type);
			$stmt->bindParam(':name', $object->name);
			$stmt->bindParam(':parent_id', $object->parentID, \PDO::PARAM_INT);
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении данных.', 0, $e);
		}
		return $stmt;

	}
	
	/**
	 * @param array $row
	 * @return bool
	 */
	private function checkIntegrity($row)
	{
		$result = false;
		foreach ($this->requiredFields as $field) {
			if (!isset($row[$field])) {
				$result = false;
				break;
			} else {
				$result = true;
			}
		}
		return $result;
	}
}