<?php
namespace I18n\Nette;
use Nette\Caching\Cache;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2015 Korney Czukowski
 * @license    MIT License
 * @group      plurals
 */
class NetteCacheWrapperTest extends Testcase
{
	private $_cache_data;
	private $_cache_options;

	private function _create_files_list($directory) {
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
	 * @dataProvider  provide_add_directory_option
	 */
	public function test_add_directory_option($path, $mask, $expected)
	{
		$object = new NetteCacheWrapper($this->create_cache_mock());
		$object->add_directory_option($path, $mask);
		$object['any key'] = 'any data';
		$this->assertEquals($expected, $this->_cache_options);
	}

	public function provide_add_directory_option()
	{
		$directory = realpath(__DIR__);
		return array(
			array(
				$directory,
				'*.php',
				array(
					Cache::FILES => $this->_create_files_list($directory),
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
	 * @dataProvider  provide_constructor_options
	 */
	public function test_constructor_options($options, $expected)
	{
		$object = new NetteCacheWrapper($this->create_cache_mock(), $options);
		$object['any key'] = 'any data';
		$this->assertEquals($expected, $this->_cache_options);
	}

	public function provide_constructor_options()
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
					Cache::FILES => $this->_create_files_list($directory),
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
						$this->_create_files_list($directory)
					),
				),
			),
		);
	}

	/**
	 * @dataProvider  provide_array_access
	 */
	public function test_array_access($key, $value)
	{
		$this->setup_object();
		$this->assertFalse(isset($this->object[$key]));
		$this->object[$key] = $value;
		$this->assertTrue(isset($this->object[$key]));
		$this->assertEquals($value, $this->object[$key]);
		unset($this->object[$key]);
		$this->assertFalse(isset($this->object[$key]));
	}

	public function provide_array_access()
	{
		return array(
			array('en', array('key1' => 'value1')),
		);
	}

	public function setUp()
	{
		$this->_cache_data = array();
		parent::setUp();
	}

	protected function _object_constructor_arguments()
	{
		return array($this->create_cache_mock());
	}

	/**
	 * @return  Nette\Caching\Cache
	 */
	protected function create_cache_mock()
	{
		$cache = $this->getMock('Nette\Caching\Cache', array(), array(), '', FALSE);
		$cache->expects($this->any())
			->method('load')
			->will($this->returnCallback(array($this, 'callback_cache_load')));
		$cache->expects($this->any())
			->method('save')
			->will($this->returnCallback(array($this, 'callback_cache_save')));
		return $cache;
	}

	public function callback_cache_load($key)
	{
		return isset($this->_cache_data[$key]) ? $this->_cache_data[$key] : NULL;
	}

	public function callback_cache_save($key, $value, $options = array())
	{
		$this->_cache_data[$key] = $value;
		$this->_cache_options = $options;
	}
}
