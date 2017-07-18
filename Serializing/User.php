<?php
namespace Serializing;

/**
 * Class User
 * @package Serializing
 */
class User
{
	/**
	 * @var User
	 */
	private static $instance;
	/**
	 * @var UserMapper
	 */
	private static $mapper;
	/**
	 * @var array
	 */
	private $storage;
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * Закрытый конструктор.
	 *
	 * @param int $userID
	 * @throws \Exception
	 */
	private function __construct($userID)
	{
		if (!isset(self::$mapper)) {
			throw new \Exception('Cannot initialize User without datamapper');
		}
		$this->storage = self::unserialize($this->fetchStorage($userID));
		$this->id = $userID;
	}
	
	/**
	 * Метод получить единственный экземпляр класса;
	 * (обратите внимание, перед этим нужно вызвать метод User::addMapper().
	 *
	 * @param int $userID
	 * @return User
	 */
	static function getInstance($userID)
	{
		if (isset(self::$instance)) {
			return self::$instance;
		} else {
			self::$instance = new User($userID);
		}
		return self::$instance;
	}
	
	/**
	 * Добавляет в класс маппер для работы с бд.
	 *
	 * @param UserMapper $mapper
	 */
	static function addMapper(UserMapper $mapper)
	{
		self::$mapper = $mapper;
	}
	
	/**
	 * Проверяет, сохранена ли в объекте User переменная по адресу.
	 * Адрес формируется по типу a\b\c...
	 *
	 * @param string $address
	 * @return bool
	 */
	function is_set($address)
	{
		try {
			//получаем ссылку на значение в переменной storage
			$value = $this->referenceTo($address);
		} catch (\Exception $e) {
			//если исключение, значит, такой путь в storage не определен
			$result = false;
		}
		//проверяем дополнительно значение на null
		if (isset($value)) {
			$result = true;
		} else $result = false;
		return $result;
	}
	
	/**
	 * Возвращает значение переменной, которая сохранена в объекте по указанному адресу.
	 * Адрес формируется по типу a\b\c...
	 *
	 * @param string $address
	 * @return array|mixed
	 */
	function get($address)
	{
		$value = $this->referenceTo($address);
		return $value;
	}
	
	/**
	 * Меняет значение переменной,
	 * @param $address
	 * @param $value
	 * @return bool
	 */
	function set($address, $value)
	{
		//если такая переменная уже есть
		if ($this->is_set($address)) {
			//береём её адрес и присваиваем новое значение
			$reference = &$this->referenceTo($address);
			$reference = $value;
		} else {
			//time to go the hard way
			//парсим путь
			$reference = &$this->storage;
			$levels = explode('\\', $address);
			//счётчик нужен для определения последнего элемента пути - которому присваивается значение
			$count = count($levels);
			$counter = 1;
			//проходим весь адрес
			foreach ($levels as $key => $level) {
				//что-то не так - выходим
				if (empty($level)) {
					throw new \Exception('Incorrect address.');
				}
				//сохраняем ссылку на прошлую итерацию в новую переменную
				$stored = &$reference;
				unset($reference);  //чтобы не заменить значение приватной переменной storage
				if (isset($stored[$level])) {
					//если элемент существует - возвращаем его в следующую итерацию
					$reference = &$stored[$level];
				} else {
					//если не существует, и если последний элемент -
					if ($counter === $count) {
						//ставим ему нужное значение
						$stored[$level] = $value;
					} else {
						//если не последний - инцииализируем как пустой массив для следующей итерации
						$stored[$level] = array();
					}
					//возвращаем ссылку на полученное для дальнейших итераций
					$reference = &$stored[$level];
				}
				$counter++;
			}
		}
		$result = $this->replaceStorage();
		
		return $result;
	}
	
	/**
	 * Возвращает ссылку на существующую часть переменной  storage по адресу.
	 *
	 * @param $address
	 * @return array|mixed
	 * @throws \Exception
	 */
	private function &referenceTo($address)
	{
		$reference = &$this->storage;
		$levels = explode('\\', $address);
		foreach ($levels as $level) {
			if (empty($level)) {
				break;
			}
			$stored = &$reference;
			unset($reference);  //чтобы не заменить значение приватной переменной
			if (isset($stored[$level])) {
				$reference = &$stored[$level];
			} else throw new \Exception('Trying to access wrong array index.');
		}
		
		return $reference;
	}
	
	/**
	 * Получить новый storage.
	 *
	 * @param int $id
	 * @return bool
	 */
	private function fetchStorage($id)
	{
		return self::$mapper->fetchStorage($id);
	}
	
	/**
	 * Отослать приватную переменную в бд.
	 *
	 * @return bool
	 */
	private function replaceStorage()
	{
		return self::$mapper->replaceStorage($this->id, self::serialize($this->storage));
	}
	
	/**
	 * Закодировать в строку.
	 *
	 * @param $variable
	 * @return string
	 */
	static function serialize($variable)
	{
		return serialize($variable);
	}
	
	/**
	 * Вернуть серилизированной переменной первоначальный вид.
	 *
	 * @param $variable
	 * @return mixed
	 */
	static function unserialize($variable)
	{
		return unserialize($variable);
	}
	
}

