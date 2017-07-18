<?php
namespace Articles;


class Section
{
	public $ID;
	public $name;
	public $parentID;
	public $children = array();
	
	function __construct($ID, $name, $parentID)
	{
		$this->ID = $ID;
		$this->name = $name;
		$this->parentID = $parentID;
	}
	
	function addChild($child)
	{
		$this->children[$child->ID] = $child;
	}
}