<?php
namespace I18n\Nette;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2012 Korney Czukowski
 * @license    MIT License
 */
abstract class Testcase extends \PHPUnit_Framework_TestCase
{
	protected $object;

	public function setup_object()
	{
		$class = new \ReflectionClass($this->class_name());
		$this->object = $class->newInstanceArgs($this->_object_constructor_arguments());
	}

	public function class_name()
	{
		return preg_replace('#Test$#', '', get_class($this));
	}

	protected function _object_constructor_arguments()
	{
		return array();
	}
}