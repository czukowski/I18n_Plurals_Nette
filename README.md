Introduction
============

This package will help you to do grammatically accurate translations in your Nette application (framework version 2.2+ supported).

The _suggested_ translation sources for Nette application are actually Neon files which may be located virtually anywhere in the application, for example, `app/i18n` folder, this will be used for the examples. Note that you are not limited in where you store your translations: also pure PHP files, databases and even all of them together may be used!

Installation and setup
----------------------

Add to your application using Composer:

`composer require czukowski/i18n-nette`

In your application code you'll need to make sure the i18n directory is known to the Configurator, perhaps in `index.php` or bootstrap:

```php
$parameters['i18nDir'] = $parameters['appDir'].'/i18n'
```

Services configuration example (supposing 'en-us' is the default language):

```yaml
services:
  # This is the Translation service.
  i18n:
    class: I18n\Nette\NetteTranslator('en-us')
    setup:
      - attach(@i18n.reader)
  # This is the Reader service that is the source of translation strings.
  # It is possible to attach multiple readers to the translator.
  i18n.reader: I18n\Nette\NeonReader(%i18nDir%)
```

Alternatively this may be put into a Nette extension.

Place your translations into the i18n directory, like this:

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
// Set translator to template (Nette\Templating\Template):
$template->setTranslator($this->context->getService('i18n'));
```

After setting the translator to the templates, you'll be able to use the translation macro:
`{_'translate this'}`. We'll get into the details on its usage later on.

How to make the translations work
---------------------------------

You can find the information about the translation contexts, plural forms and even more in [the base package readme](https://github.com/czukowski/I18n_Plural#translation-contexts). *It is omitted here in order to avoid duplication*.

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


API
---

The base package API is covered in its own readme. 

### class I18n\NetteTranslator

You are not required to use core object directly. This class is a Nette-compatible wrapper and it's the
suggested usage in Nette applications. See above for an example on how to setup a trasnlation service.

#### public function __construct($default_lang = 'x')

  * @param  string  $default_lang

Translator constructor takes default language to use when none is specified explicitly. Initializes a Core
object instance internally.

#### public function attach(I18n\Reader\ReaderInterface $reader)

  * @param  I18n\Reader\ReaderInterface  $reader

Attaches a Reader object to the Core object (see below). `I18n\NeonReader` is a suggested default reader
for Nette application, although there's `I18n\NetteReader` that gets translations from raw PHP files and
you may of course also implement your own readers to provide translations from any source of your choice.

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
