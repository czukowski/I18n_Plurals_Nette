<?php
namespace I18n\Nette;
use I18n\Core,
	I18n\Reader\ReaderInterface,
	InvalidArgumentException,
	Nette\Http\IRequest,
	Nette\Localization\ITranslator;

/**
 * Nette Translator adapter class
 * 
 * @package    I18n
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 */
class NetteTranslator implements ITranslator
{
	/**
	 * @var  Core
	 */
	private $i18n;
	/**
	 * @var  array
	 */
	protected $available_langs;
	/**
	 * @var  string
	 */
	protected $default_lang;
	/**
	 * @var  boolean
	 */
	protected $use_neon_style_params;
	/**
	 * @var  array  List of known classes that translator can be set to.
	 */
	private static $translatable_classes = array(
		'Nette\Bridges\ApplicationLatte\Template',
		'Nette\Forms\Controls\BaseControl',
		'Nette\Forms\Form',
		'Nette\Templating\Template',
	);

	/**
	 * Instanciates a new I18n\Core object.
	 * 
	 * @param  string   $default_lang
	 * @param  boolean  $use_neon_style_params
	 */
	public function __construct($default_lang = 'x', $use_neon_style_params = FALSE)
	{
		if (is_object($default_lang))
		{
			// For backward compatibility get the default lang value from context object.
			// Do not use this way if you can avoid it.
			$default_lang = isset($default_lang->parameters['defaultLocale'])
				? $default_lang->parameters['defaultLocale']
				: 'x';
		}
		$this->default_lang = $default_lang;
		$this->i18n = new Core;
		$this->use_neon_style_params = $use_neon_style_params;
	}

	/**
	 * Attach an i18n reader to a core object.
	 * 
	 * @param  I18n\Reader\ReaderInterface  $reader
	 */
	public function attach(ReaderInterface $reader)
	{
		$this->i18n->attach($reader);
	}

	/**
	 * @return  array
	 */
	public function getAvailableLanguages()
	{
		return $this->available_langs ? : array();
	}

	/**
	 * @param   array  $langs
	 * @return  $this
	 */
	public function setAvailableLanguages($langs)
	{
		$this->available_langs = $langs;
		return $this;
	}

	/**
	 * @return  string
	 */
	public function getDefaultLanguage()
	{
		return $this->default_lang;
	}

	/**
	 * @param   string  $lang
	 * @return  $this
	 */
	public function setDefaultLanguage($lang)
	{
		$this->default_lang = $lang;
		return $this;
	}

	/**
	 * @param   IRequest  $httpRequest
	 * @return  $this
	 */
	public function setLanguageFromHeaders(IRequest $httpRequest)
	{
		$accepted = explode(',', $httpRequest->getHeader('Accept-Language'));
		$available = $this->getAvailableLanguages();
		foreach ($accepted as $accept)
		{
			if ($accept === '*')
			{
				break;
			}
			$parts = explode(';', $accept);
			$acceptedLang = strtolower($parts[0]);
			foreach ($available as $availableLang)
			{
				if ($acceptedLang === strtolower($availableLang))
				{
					return $this->setDefaultLanguage($acceptedLang);
				}
			}
		}
		return $this->setDefaultLanguage($this->default_lang);
	}

	/**
	 * Nette localization interface adapter.
	 * 
	 * @param   string  $string
	 * @param   mixed   $count
	 * @return  string
	 */
	public function translate($string, $count = NULL)
	{
		// The func_num_args/func_get_arg jiggling is to overcome the ITranslator's limitation
		if (func_num_args() > 2)
		{
			$parameters = func_get_arg(2);
		}
		if ( ! isset($parameters))
		{
			$parameters = array();
		}
		if (func_num_args() > 3)
		{
			$lang = func_get_arg(3);
		}
		if ($count && is_array($count))
		{
			// If there's no context and parameters are in its place, shift the 3rd and 4th arguments.
			$lang = $parameters ? : NULL;
			$parameters = $count;
			$count = NULL;
		}
		if ( ! isset($lang))
		{
			$lang = $this->default_lang;
		}
		return $this->call_translate($string, $count, $parameters, $lang);
	}

	/**
	 * Set translator to objects.
	 * 
	 * @param  object  $object
	 */
	public function setTranslator($object)
	{
		foreach (self::$translatable_classes as $class_name)
		{
			if ($object instanceof $class_name)
			{
				$object->setTranslator($this);
				return;
			}
		}
		throw new InvalidArgumentException('Bad argument type. See `NetteTranslator::$translatable_classes` for list of allowed types.');
	}

	/**
	 * This method can be used by descendant classes to eg. modify substitution parameters.
	 * 
	 * @param   string  $string   String to translate
	 * @param   mixed   $count    String form or numeric count
	 * @param   array   $params   Param values to substitute
	 * @param   string  $lang     Target language
	 * @return  string
	 */
	protected function call_translate($string, $count, $params, $lang)
	{
		if ($this->use_neon_style_params)
		{
			$modifiedParams = array();
			foreach ($params as $key => $value)
			{
				$modifiedParams['%'.$key.'%'] = $value;
			}
			$params = $modifiedParams;
		}
		return $this->i18n->translate($string, $count, $params, $lang);
	}

	/**
	 * Retrieves the core object instance for direct usage.
	 * 
	 * @return  Core
	 */
	public function getService()
	{
		return $this->i18n;
	}
}