Introduction
============

This package will help you to do grammatically accurate translations in your Nette application
(framework version 2.2+ supported).

The _suggested_ translation sources for Nette application are actually Neon files which may be
located virtually anywhere in the application, for example, `app/i18n` folder, this will be used
for the examples. Note that you are not limited in where you store your translations: also pure
PHP files, databases and even all of them together may be used!

Installation and setup
----------------------

Add to your application using Composer:

`composer require czukowski/i18n-nette`

Add I18n service to your configuration file:

```yaml
extensions:
    i18n: I18n\Nette\NetteExtension

i18n:
    defaultLang: cs       # Default fallback language
    directories:          # List of directories containing i18n files
        - %appDir%/i18n
    languages:            # List of languages available in the application,
        - cs              # it is useful for choosing default language from
        - en              # HTTP request headers
```

Place your translations into the i18n directory, like this:

 * `en.neon` - English translations,
 * `cs.neon` - Czech translations,
 * `fr.neon` - General French translations,
 * `fr/be.neon` - Belgium French translations that are different from general French,
 * `fr/ch.neon` - Swiss French translations that are different from general French.

If you request the translation for 'fr-CH' locale, it'll look in the `fr/ch.neon` first, and failing
that in the general `fr.neon`. If the translation wasn't found even there, the untranslated input string
is returned.

The translation data structure is very similar to what you're used to in Neon configuration:

```yaml
string: řetězec
section:
	string: 'řetězec v podsekci'
```

Some of the Nette controls are ready for translations, you just need to set the translator instance
to them, for example (this is assuming you've named your `NetteTranslator` service 'i18n'):

```php
// Set translator to control (Nette\Forms\Controls\BaseControl):
$control->setTranslator($this->context->getService('i18n'));
// Set translator to form (Nette\Forms\Form):
$form->setTranslator($this->context->getService('i18n'));
// Set translator to template (Nette\Templating\Template or Nette\Bridges\ApplicationLatte\Template):
$template->setTranslator($this->context->getService('i18n'));
```

After setting the translator to the templates, you'll be able to use the translation macro:
`{_'translate this'}`. We'll get into the details on its usage later on.

Configuration options
---------------------

Configuration options available for this Nette extension:

 - `defaultLang` - Default application language, ie translate to this language if no target
   language specified in translate function call (default value: 'en-us').
 - `directories` - Directories containing application translations. May be more than one if
   application contains multiple modules, each adding to the translations list. Paths may contain
   template keys from config parameters section, eg `%appDir%/i18n` (no default value).
 - `languages` - List of available languages. Useful when default language is set automatically
    from HTTP Request headers (no default value).
 - `useNeonStyleParams` - If set to TRUE, wraps replacement parameter names into percent signs
   (eg. `param` becomes `%param%`), so that translation keys can look similarly to the template
   parameters in neon configuration file, while using bare parameter keys in the translate calls.
   Example: `{_'I have %count% strings to translate', $count, ['count' => $count]}`. Default value
   is FALSE, but if your application uses only neon files as translation sources, using this may
   look nicer. Changing this parameter mid-way will require to review all translate calls across
   your application, so choose wisely.
 - `setLangFromRequestHeaders` - If set to TRUE, will automatically set default language from HTTP
   Request (the respective function can also be called manually). Only languages existing in the
   `languages` list will be set, according to priorities in the headers.
 - `replaceLatteFactory` - If set to TRUE, replaces `latte.templateFactory` service with a new one,
   that implements a callback on template create. This callback may be used to inject translator to
   templates automatically (default value is FALSE, but if you use custom Latte Template Factory
   replacement, you may set it to TRUE safely).
 - `latteFactoryClass` - This is a class name that will be used for the replacement template factory.
   This setting allows to override it and use another class that implements the same functionality, if
   needed (default value: `'I18n\Nette\TemplateFactory'`). This setting will have no effect, unless
   the `replaceLatteFactory` parameter is set to TRUE.
 - `autoSetTranslatorToTemplates` - If set to TRUE, will inject translator to templates automatically,
   using the replacement Latte Template Factory. If `replaceLatteFactory` is set to TRUE, this parameter
   is also set to TRUE implicitly. The only valid use case to set this parameter is when another template
   factory is already replaced by another class, and it is still desired to auto-set translator to
   templates using `onCreateTemplate` callback.

How to make the translations work
---------------------------------

You can find the information about the translation contexts, plural forms and even more in
[the base package readme](https://github.com/czukowski/I18n_Plural#translation-contexts). *It is
omitted here in order to avoid duplication*.

Translating in Nette templates
------------------------------

Here are some examples, those are pretty self-explanatory:

	// Basic translation.
	{_'Welcome!'}
	// Translation with context.
	{_'New customer has been saved.', $customer->gender}
	// Translation with parameters and context skipped.
	{_'Hi, my name is :name', [':name' => $name]}
	// All arguments present, including target language.
	{_'You have :count messages', $count, [':count' => $count], 'cs'}

Note: if you use `useNeonStyleParams`, the translation could look like:

	{_'You have %count% messages', $count, ['count' => $count], 'cs'}

API
---

The base package API is covered in its own readme. 

### class I18n\NetteTranslator

You are not required to use core object directly. This class is a Nette-compatible wrapper and it's the
suggested usage in Nette applications. See above for an example on how to setup a trasnlation service.

#### public function __construct($default_lang = 'x', $use_neon_style_params = FALSE)

  * @param  string   $default_lang
  * @param  boolean  $use_neon_style_params

Translator constructor takes default language to use when none is specified explicitly. Initializes a Core
object instance internally.

#### public function attach(I18n\Reader\ReaderInterface $reader)

  * @param  I18n\Reader\ReaderInterface  $reader

Attaches a Reader object to the Core object (see below). `I18n\Nette\NeonReader` is a suggested default
reader for Nette application, although there's `I18n\Nette\NetteReader` that gets translations from raw PHP
files placed similarly into the Nette application, and you may of course also implement your own readers to
provide translations from any source of your choice.

#### public function getAvailableLanguages()

Returns list of available languages.

#### public function setAvailableLanguages($langs)

  * @param   array  $langs
  * @return  $this

Sets list of available languages.

#### public function getDefaultLanguage()

Returns default language, used when no target language passed to translate function call.

#### public function setDefaultLanguage($lang)

  * @param   string  $lang
  * @return  $this

Sets default language.

#### public function setLanguageFromHeaders(Nette\Http\IRequest $httpRequest)

  * @param   Nette\Http\IRequest  $httpRequest
  * @return  $this

Sets default language from HTTP Request headers, if at least one of accepted language is
contained in the available languages list. The one with the highest priority is chosen.

#### public function setTranslator($object)

  * @param  object  $object

Sets Nette translator (`$this` object instance) to the compatible objects:

 - `'Nette\Bridges\ApplicationLatte\Template'`
 - `'Nette\Forms\Controls\BaseControl'`
 - `'Nette\Forms\Form'`
 - `'Nette\Templating\Template'`

#### public function translate($string, $count, $parameters, $lang)

  * @param   string  $string      String to translate
  * @param   mixed   $count       String form or numeric count (optional)
  * @param   array   $parameters  Param values to replace (optional)
  * @param   string  $lang        Target language (optional)
  * @return  string

The `$parameters` values (array) may be passed as 2nd argument, in that case `$count` is considered `NULL`
and `$lang` is the 3rd argument.

#### public function getService()

  * @return  I18n\Core

Returns the internal Core object reference if needed for some reason.
