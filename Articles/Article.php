<?php
namespace Articles;


class Article
{
	public $ID;
	public $name;
	public $parentID;
	public $textFilepath;
	
	function __construct($ID, $name, $parentID, $textFilepath)
	{
		$this->ID = $ID;
		$this->name = $name;
		$this->parentID = $parentID;
		$this->textFilepath = $textFilepath;
	}
}