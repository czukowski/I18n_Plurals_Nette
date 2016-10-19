<?php
namespace I18n\Nette;
use PHPUnit_Framework_TestCase,
	ReflectionClass;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 */
abstract class Testcase extends PHPUnit_Framework_TestCase
{
	protected $object;

	public function setupObject()
	{
		$class = new ReflectionClass($this->getClassName());
		$this->object = $class->newInstanceArgs($this->getObjectConstructorArguments());
	}

	public function getClassName()
	{
		return preg_replace('#Test$#', '', get_class($this));
	}

	protected function getObjectConstructorArguments()
	{
		return array();
	}
}
