<?php
namespace FileSystem;
	class FilesEntity 
	{
		public $ID;
		public $name;
		public $parentID;

		function __construct($ID, $name, $parentID)
		{
			$this->ID = $ID;
			$this->name = $name;
			$this->parentID = $parentID;
		}
	}