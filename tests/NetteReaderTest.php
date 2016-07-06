<?php
namespace I18n\Nette;

/**
 * @package    Plurals
 * @category   Unit tests
 * @author     Korney Czukowski
 * @copyright  (c) 2013 Korney Czukowski
 * @license    MIT License
 * @group      plurals
 */
class NetteReaderTest extends ReaderTestcase
{
	protected $i18n = array(
		'cs.php' => array(
			'test' => 'test',
			'locale' => 'locale (cs)',
			'section' => array(
				'test' => 'section test'
			),
		),
		'cs/cz.php' => array(
			'locale' => 'locale (cs-cz)',
			'exclusive' => 'only in cs-cz',
		),
	);

	protected function _load_file($content)
	{
		return $content;
	}
}