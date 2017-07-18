<?php
namespace Serializing;


class User
{
	private static $instance;
	private static $mapper;
	private $storage;
	private $id;
	
	private function __construct($userID)
	{
		if (!isset(self::$mapper)) {
			throw new \Exception('Cannot initialize User without datamapper');
		}
		$this->storage = self::unserialize($this->fetchStorage($userID));
		$this->id = $userID;
	}
	
	static function getInstance($userID)
	{
		if (isset(self::$instance)) {
			return self::$instance;
		} else {
			self::$instance = new User($userID);
		}
		return self::$instance;
	}
	
	static function addMapper(UserMapper $mapper)
	{
		self::$mapper = $mapper;
	}
	
	function is_set($address)
	{
		try {
			$value = $this->referenceTo($address);
		} catch (\Exception $e) {
			$result = false;
		}
		if (isset($value)) {
			$result = true;
		} else $result = false;
		return $result;
	}
	
	function get($address)
	{
		$value = $this->referenceTo($address);
		return $value;
	}
	
	function set($address, $value)
	{
		$reference = &$this->referenceTo($address);
		$reference = $value;
		$result = $this->replaceStorage();
		return $result;
	}
	
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
	
	private function fetchStorage($id)
	{
		return self::$mapper->fetchStorage($id);
	}
	
	private function replaceStorage()
	{
		return self::$mapper->replaceStorage($this->id, self::serialize($this->storage));
	}
	static function serialize($variable)
	{
		return serialize($variable);
	}
	
	static function unserialize($variable)
	{
		return unserialize($variable);
	}
	
}

