<?php
namespace I18n\Nette;
use ReflectionMethod;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 * @group      plurals
 */
class NeonReaderTest extends ReaderTestcase
{
	private $tempFile;
	protected $i18n = array(
		'cs.neon' => <<<NEON
test: test
locale: 'locale (cs)'
section:
	test: 'section test'
NEON
		,
		'cs/cz.neon' => <<<NEON
'locale': 'locale (cs-cz)'
'exclusive': 'only in cs-cz'
NEON
	);

	/**
	 * @dataProvider  provideLoadFile
	 */
	public function testLoadFile($neon, $expected)
	{
		$this->setupObject();
		$load_file = new ReflectionMethod($this->object, 'load_file');
		$load_file->setAccessible(TRUE);
		if ($neon)
		{
			// Write neon content to temporary file.
			$this->tempFile = tempnam(sys_get_temp_dir(), 'i18n');
			file_put_contents($this->tempFile, $neon);
		}
		// Load neon from temporary file.
		$actual = $load_file->invoke($this->object, $this->tempFile);
		$this->assertSame($expected, $actual);
	}

	public function provideLoadFile()
	{
		return array(
			array(
				FALSE,
				array(),
			),
			array(
				$this->i18n['cs.neon'],
				array(
					'test' => 'test',
					'locale' => 'locale (cs)',
					'section' => array(
						'test' => 'section test'
					),
				),
			),
			array(
				$this->i18n['cs/cz.neon'],
				array(
					'locale' => 'locale (cs-cz)',
					'exclusive' => 'only in cs-cz',
				),
			),
		);
	}

	protected function loadFile($content)
	{
		$decode = new ReflectionMethod($this->object, 'decode');
		$decode->setAccessible(TRUE);
		return $decode->invoke($this->object, $content);
	}

	protected function getObjectConstructorArguments()
	{
		return array('callback://app/');
	}

	public function tearDown()
	{
		if ($this->tempFile)
		{
			unlink($this->tempFile);
		}
	}
}
