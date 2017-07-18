<?php
namespace Articles;

/**
 * Class Section
 * @package Articles
 */
class Section
{
	public $ID;
	public $name;
	public $parentID;
	public $children = array();
	
	/**
	 * Section constructor.
	 * @param int $ID
	 * @param string $name
	 * @param int $parentID
	 */
	function __construct($ID, $name, $parentID)
	{
		$this->ID = $ID;
		$this->name = $name;
		$this->parentID = $parentID;
	}
	
	/**
	 * Сохраняет ссылку на объект-ребёнка
	 * @param Section|Article $child
	 */
	function addChild($child)
	{
		$this->children[$child->ID] = $child;
	}
}