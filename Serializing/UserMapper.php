<?php
namespace Serializing;

/**
 * Class UserMapper
 * @package Serializing
 */
class UserMapper
{
	private $pdo;
	
	/**
	 * UserMapper constructor.
	 * @param \PDO $pdo
	 */
	function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
	/**
	 * Добавить в таблицу новую строку о юзере.
	 *
	 * @param $storage
	 * @return bool|int
	 * @throws \Exception
	 */
	function newUserRecord($storage)
	{
		try {
			$sql = 'INSERT INTO `users` SET `storage` = :storage';
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute(['storage' => $storage]);
			if ($result === true) {
				$result = $this->lastInsertedId();
			}
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при добавлении User', 0, $e);
		}
		return $result;
	}
	
	/**
	 * Вернуть последний результат INSERT.
	 *
	 * @return int
	 */
	function lastInsertedId()
	{
		return (int)$this->pdo->lastInsertId();
	}
	
	/**
	 * Получить переменную storage из бд по айди пользователя.
	 *
	 * @param int $id
	 * @return bool
	 * @throws \Exception
	 */
	function fetchStorage($id)
	{
		try {
			$query = 'SELECT `storage` FROM `users` WHERE `id` = :id LIMIT 1';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute(['id' => $id]);
			$queryResult = $stmt->fetch(\PDO::FETCH_ASSOC);
			if ($queryResult === false) {
				$result = false;
			} else {
				$result = $queryResult['storage'];
			}
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при получении переменной storage', 0, $e);
		}
		return $result;
	}
	
	/**
	 * Заменить переменную storage существующего пользователя.
	 * 
	 * @param int $id
	 * @param $storage
	 * @return bool
	 * @throws \Exception
	 */
	function replaceStorage($id, $storage)
	{
		try {
			$sql = 'UPDATE `users` SET `storage` = :storage WHERE `id` = :id';
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute(['id' => $id, 'storage' => $storage]);
		} catch (\PDOException $e) {
			throw new \Exception('Ошибка при замене переменной storage', 0, $e);
		}
		return $result;
	}
}