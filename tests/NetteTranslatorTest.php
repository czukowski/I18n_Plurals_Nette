<?php
namespace I18n\Nette;
use I18n\Tests;

/**
 * NetteTranslatorTest test
 * 
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 * @group      plurals
 */
class NetteTranslatorTest extends Testcase
{
	/**
	 * @var  array
	 */
	private $constructorArguments = array();

	/**
	 * @dataProvider  provideConstruct
	 */
	public function testConstruct($arguments, $expected)
	{
		$this->constructorArguments = $arguments;
		$this->setupObject();
		$default_lang = new \ReflectionProperty($this->object, 'default_lang');
		$default_lang->setAccessible(TRUE);
		$actual = $default_lang->getValue($this->object);
		$this->assertSame($expected, $actual);
	}

	public function provideConstruct()
	{
		// [constructor arguments, expcted default lang]
		return array(
			array(array(), 'x'),
			array(array('cs'), 'cs'),
			array(array('cs-cz'), 'cs-cz'),
			array(array($this->createContext(NULL)), 'x'),
			array(array($this->createContext('cs')), 'cs'),
		);
	}

	private function createContext($defaultLocale)
	{
		$context = new \stdClass;
		$context->parameters['defaultLocale'] = $defaultLocale;
		return $context;
	}

	/**
	 * @dataProvider  provideAttach
	 */
	public function testAttach($reader)
	{
		$this->setupObject();
		$this->object->attach($reader);
		$core = $this->object->getService();
		$readers = new \ReflectionProperty($core, '_readers');
		$readers->setAccessible(TRUE);
		$actual = $readers->getValue($core);
		$this->assertSame(1, count($actual));
		$this->assertSame($reader, reset($actual));
	}

	public function provideAttach()
	{
		// [reader object]
		return array(
			array(
				$this->getMock('I18n\Reader\ReaderInterface', array('get')),
			),
			array(
				new Tests\DefaultReader,
			),
			array(
				new NetteReader('app://'),
			),
			array(
				new NeonReader('app://'),
			),
		);
	}

	/**
	 * @dataProvider  provideTranslate
	 */
	public function testTranslate($arguments, $defaultLang, $expected)
	{
		$this->constructorArguments = array($defaultLang);
		$this->setupObject();
		$this->object->attach(new Tests\DefaultReader);
		$translate = new \ReflectionMethod($this->object, 'translate');
		$actual = $translate->invokeArgs($this->object, $arguments);
		$this->assertSame($expected, $actual);
	}

	public function provideTranslate()
	{
		// [arguments, default lang, expected]
		return array(
			// 'Normal' usage.
			array(array('Spanish'), 'x', 'Spanish'),
			array(array('Spanish'), 'es', 'Español'),
			array(array(':title person', 'mr'), 'cs', ':title muž'),
			array(array(':title person', 'ms', array(':title' => 'tato')), 'cs', 'tato žena'),
			array(array(':count things', 1), 'en', ':count thing'),
			array(array(':count things', 1, array(':count' => 1)), 'en', '1 thing'),
			array(array(':count things', 1, array(':count' => 1), 'cs'), 'en', '1 věc'),
			array(array(':count things', 2, array(':count' => 2), 'en'), 'cs', '2 things'),
			array(array(':count things', 3, array(':count' => 3), 'cs'), 'cs', '3 věci'),
			array(array(':count things', 10, array(':count' => 'ten')), 'en', 'ten things'),
			array(array(':count things', 10, array(':count' => 'deset'), 'cs'), 'en', 'deset věcí'),
			array(array(':title person', 'ms', array(':title' => 'some')), 'zh', 'some person'),
			// Context parameter may be missing and arguments shifted.
			array(array(':title person', NULL, array(':title' => 'some')), 'zh', 'some person'),
			array(array(':title person', array(':title' => 'some')), 'zh', 'some person'),
			array(array(':title person', array(':title' => 'nějaký'), 'cs'), 'zh', 'nějaký člověk'),
		);
	}

	protected function getObjectConstructorArguments()
	{
		return $this->constructorArguments;
	}
}
