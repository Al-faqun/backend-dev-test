<?php
namespace FileSystem;

	/**
	 * Class Folder
	 * @package FileSystem
	 */
	class Folder extends FilesEntity 
	{
		public $children = array();
		
		/**
		 * @param $child
		 */
		function addChild($child)
		{
			$this->children[$child->ID] = $child;
		}

	}