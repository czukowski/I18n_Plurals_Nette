<?php
namespace I18n\Nette;
use Latte\Helpers,
    Nette\Application\UI,
    Nette\Bridges\ApplicationLatte;

/**
 * TemplateFactory
 *
 * @author  ksustov
 */
class TemplateFactory extends ApplicationLatte\TemplateFactory
{
	/**
	 * @var  array  callable[]
	 **/
	public $onCreateTemplate = array();

	/**
	 * @param   UI\Control  $control
	 * @return  ApplicationLatte\Template
	 */
	public function createTemplate(UI\Control $control = NULL) {
		$template = parent::createTemplate($control);

		foreach ($this->onCreateTemplate ? : array() as $cb) {
			call_user_func(Helpers::checkCallback($cb), $template);
		}

		return $template;
	}
}
