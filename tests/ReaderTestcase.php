<?php
namespace I18n\Nette;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 */
abstract class ReaderTestcase extends Testcase
{
	protected $appPath = 'callback://app/';
	protected $callbackCounters = array();
	protected $i18n = array();

	/**
	 * @dataProvider  provideGet
	 */
	public function testGet($expected, $string, $lang, $default_lang)
	{
		$this->setupGet($default_lang);
		$actual = $this->object->get($string, $lang);
		$this->assertSame($expected, $actual);
		// Call the method again to make sure the translation table has been read from cache this time.
		$this->object->get($string, $lang);
		foreach ($this->callbackCounters as $key => $counter)
		{
			if ($counter > 1)
			{
				$this->fail('load_file() called '.$counter.' times for '.$key.', expected 0 or 1.');
			}
		}
	}

	public function provideGet()
	{
		// [expected, string, lang, default_lang]
		return array(
			// Strings existing in multiple locales with only target locale specified.
			array('locale (cs-cz)', 'locale', 'cs-cz', NULL),
			array('locale (cs)', 'locale', 'cs', NULL),
			// Strings existing in multiple locales with only default locale specified.
			array('locale (cs-cz)', 'locale', NULL, 'cs-cz'),
			array('locale (cs)', 'locale', NULL, 'cs'),
			// Test strings existing in multiple locales with target and default locales specified.
			array('locale (cs-cz)', 'locale', 'cs-cz', 'cs'),
			array('locale (cs)', 'locale', 'cs', 'cs-cz'),
			// Fallback to less specific locale.
			array(NULL, 'test', 'cs-cz', NULL),
			array('test', 'test', 'cs', NULL),
			// Structured translations (dot-delimited).
			array(NULL, 'section.test', 'cs-cz', NULL),
			array('section test', 'section.test', 'cs', NULL),
			// Strings existing in most specific locale only.
			array('only in cs-cz', 'exclusive', 'cs-cz', NULL),
			array(NULL, 'exclusive', 'cs', NULL),
		);
	}

	public function setUp()
	{
		foreach (array_keys($this->i18n) as $key)
		{
			$this->callbackCounters[$key] = 0;
		}
	}

	protected function setupGet($default_lang)
	{
		$arguments = array(
			$this->appPath,
			$default_lang,
		);
		$this->object = $this->getMock($this->getClassName(), array('load_file'), $arguments);
		$this->object->expects($this->any())
			->method('load_file')
			->will($this->returnCallback(array($this, 'callbackLoadFile')));
	}

	public function callbackLoadFile($path)
	{
		$file = preg_replace('#^'.preg_quote($this->appPath, '#').'#', '', str_replace(DIRECTORY_SEPARATOR, '/', $path));
		if (isset($this->i18n[$file]))
		{
			$this->callbackCounters[$file]++;
			return $this->loadFile($this->i18n[$file]);
		}
		return array();
	}

	abstract protected function loadFile($content);
}
