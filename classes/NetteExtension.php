<?php
namespace I18n\Nette;
use Nette\DI\CompilerExtension,
    Nette\DI\Statement;

/**
 * NetteExtension
 * 
 * @package    I18n
 * @category   DI
 * @author     Korney Czukowski
 * @copyright  (c) 2016 Korney Czukowski
 * @license    MIT License
 */
class NetteExtension extends CompilerExtension
{
	/**
	 * @var  array  Default config.
	 */
	protected $defaults = array(
		// Default application language.
		'defaultLang' => 'en-us',
		// Directories containing application translations.
		// May be more than one if application contains multiple modules.
		'directories' => array('%appDir%/i18n'),
		// List of available languages.
		// Useful when default language is set automatiaclly from Request headers.
		'languages' => NULL,
		// If set to TRUE, replaces `latte.templateFactory` service with a new one,
		// that implements a callback on template create. This callback may be used
		// to inject translator to templates automatically.
		'replaceLatteFactory' => FALSE,
		// This is a class name that will be used for the replacement template factory.
		// This setting allows to override it and use another class that implements the
		// same functionality, if needed. The setting will have no effect, unless the
		// `replaceLatteFactory` parameter is set to TRUE.
		'latteFactoryClass' => 'I18n\Nette\TemplateFactory',
		// If set to TRUE, will try to inject translator to templates automatically.
		// If `replaceLatteFactory` is set to TRUE, this parameter is also set to TRUE
		// implicitly. The only valid use case to set this parameter is when another
		// the template factory is already replaced by another class, and it is still
		// desired to auto-set translator to templates using `onCreateTemplate` callback.
		'autoSetTranslatorToTemplates' => FALSE,
	);

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$container->addDefinition($this->prefix('translator'))
			->setClass('I18n\Nette\NetteTranslator')
			->setArguments(array($config['defaultLang']))
			->addSetup('$service->attach(?)', array($this->prefix('@reader')))
			->addSetup('$service->setAvailableLanguages(?)', array($config['languages']))
			->addSetup('$service->setLanguageFromHeaders(?)', array('@httpRequest'));
		$reader = $container->addDefinition($this->prefix('reader'))
			->setClass('I18n\Reader\PrefetchingReader')
			->setArguments(array($this->prefix('@cache')));
		$cache = $container->addDefinition($this->prefix('cache'))
			->setClass('I18n\Reader\NetteCacheWrapper')
			->setArguments(array($this->prefix('@cacheService')));

		foreach ($config['directories'] as $directory)
		{
			if (is_dir($directory))
			{
				$source = new Statement('I18n\Reader\NeonReader', array($directory));
				$reader->addSetup('$service->attach(?)', array($source));
				$cache->addSetup('$service->add_directory_option(?, ?)', array($directory, '*.neon'));
			}
		}

		$container->addDefinition($this->prefix('cacheService'))
			->setClass('Nette\Caching\Cache')
			->setArguments(array('@cache.storage', 'i18n'))
			->setAutowired(FALSE);
	}

	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if ($config['replaceLatteFactory'])
		{
			$this->getContainerBuilder()
				->getDefinition('latte.templateFactory')
				->setFactory($config['latteFactoryClass']);
			$config['autoSetTranslatorToTemplates'] = TRUE;
		}
		if ($config['autoSetTranslatorToTemplates']) {
			$container->getDefinition('latte.templateFactory')
				->addSetup('$service->onCreateTemplate[] = ?', array(array($this->prefix('@translator'), 'setTranslator')));
		}
	}
}
