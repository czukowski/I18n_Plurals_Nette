<?php
namespace I18n\Nette;
use Nette\Caching\Cache;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 * @group      plurals
 */
class NetteCacheWrapperTest extends Testcase
{
	private $cacheData;
	private $cacheOptions;

	private function createFilesList($directory)
	{
		// Will return list of PHP files in the tests directory.
		// This is needed for the caching 'files' option test that should normally work with
		// the source translation files, but since there aren't any shipped with this package,
		// we only have the PHP files to work with.
		return array(
			$directory.'/NeonReaderTest.php',
			$directory.'/NetteCacheWrapperTest.php',
			$directory.'/NetteReaderTest.php',
			$directory.'/NetteTranslatorTest.php',
			$directory.'/ReaderTestcase.php',
			$directory.'/Testcase.php',
		);
	}

	/**
	 * @dataProvider  provideAddFirectoryOption
	 */
	public function testAddFirectoryOption($path, $mask, $expected)
	{
		$object = new NetteCacheWrapper($this->createCacheMock());
		$object->addDirectoryOption($path, $mask);
		$object['any key'] = 'any data';
		$this->assertEquals($expected, $this->cacheOptions);
	}

	public function provideAddFirectoryOption()
	{
		$directory = realpath(__DIR__);
		return array(
			array(
				$directory,
				'*.php',
				array(
					Cache::FILES => $this->createFilesList($directory),
				),
			),
			array(
				$directory,
				'*.dat',
				array(),
			),
		);
	}

	/**
	 * @dataProvider  provideConstructorOptions
	 */
	public function testConstructorOptions($options, $expected)
	{
		$object = new NetteCacheWrapper($this->createCacheMock(), $options);
		$object['any key'] = 'any data';
		$this->assertEquals($expected, $this->cacheOptions);
	}

	public function provideConstructorOptions()
	{
		$directory = realpath(__DIR__);
		return array(
			array(
				array(Cache::FILES => array('en-us.neon')),
				array(Cache::FILES => array('en-us.neon')),
			),
			array(
				array(
					NetteCacheWrapper::DIRECTORIES => array(
						$directory => '*.php',
					),
				),
				array(
					Cache::FILES => $this->createFilesList($directory),
				),
			),
			array(
				array(
					NetteCacheWrapper::DIRECTORIES => array(
						$directory => '*.php',
					),
					Cache::FILES => 'en-us.neon',
				),
				array(
					Cache::FILES => array_merge(
						array('en-us.neon'),
						$this->createFilesList($directory)
					),
				),
			),
		);
	}

	/**
	 * @dataProvider  provideArrayAccess
	 */
	public function testArrayAccess($key, $value)
	{
		$this->setupObject();
		$this->assertFalse(isset($this->object[$key]));
		$this->object[$key] = $value;
		$this->assertTrue(isset($this->object[$key]));
		$this->assertEquals($value, $this->object[$key]);
		unset($this->object[$key]);
		$this->assertFalse(isset($this->object[$key]));
	}

	public function provideArrayAccess()
	{
		return array(
			array('en', array('key1' => 'value1')),
		);
	}

	public function setUp()
	{
		$this->cacheData = array();
		parent::setUp();
	}

	protected function getObjectConstructorArguments()
	{
		return array($this->createCacheMock());
	}

	/**
	 * @return  Nette\Caching\Cache
	 */
	protected function createCacheMock()
	{
		$cache = $this->getMock('Nette\Caching\Cache', array(), array(), '', FALSE);
		$cache->expects($this->any())
			->method('load')
			->will($this->returnCallback(array($this, 'callbackCacheLoad')));
		$cache->expects($this->any())
			->method('save')
			->will($this->returnCallback(array($this, 'callbackCacheSave')));
		return $cache;
	}

	public function callbackCacheLoad($key)
	{
		return isset($this->cacheData[$key]) ? $this->cacheData[$key] : NULL;
	}

	public function callbackCacheSave($key, $value, $options = array())
	{
		$this->cacheData[$key] = $value;
		$this->cacheOptions = $options;
	}
}
