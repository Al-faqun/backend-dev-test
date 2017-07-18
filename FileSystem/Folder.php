<?php
namespace FileSystem;

	class Folder extends FilesEntity 
	{
		public $children = array();

		function addChild($child)
		{
			$this->children[$child->ID] = $child;
		}

	}