<?php
namespace FileSystem;

	/**
	 * Class FilesEntity
	 * @package FileSystem
	 */
	class FilesEntity
	{
		public $ID;
		public $name;
		public $parentID;
		
		/**
		 * FilesEntity constructor.
		 * @param $ID
		 * @param $name
		 * @param $parentID
		 */
		function __construct($ID, $name, $parentID)
		{
			$this->ID = $ID;
			$this->name = $name;
			$this->parentID = $parentID;
		}
	}