<?php
namespace Articles;

/**
 * Class Article
 * @package Articles
 */
class Article
{
	public $ID;
	public $name;
	public $parentID;
	public $textFilepath;
	
	/**
	 * Article constructor.
	 * @param int $ID
	 * @param string $name
	 * @param int $parentID
	 * @param string $textFilepath
	 */
	function __construct($ID, $name, $parentID, $textFilepath)
	{
		$this->ID = $ID;
		$this->name = $name;
		$this->parentID = $parentID;
		$this->textFilepath = $textFilepath;
	}
}