<?php
namespace I18n\Nette;
use ArrayAccess,
	Nette\Caching\Cache;

/**
 * I18n Reader Cache Wrapper
 * 
 * This is a wrapper for the Nette cache service. Experimental!
 * 
 * @package    I18n
 * @category   Nette
 * @author     Korney Czukowski
 * @copyright  (c) 2015 Korney Czukowski
 * @license    MIT License
 */
class NetteCacheWrapper implements ArrayAccess
{
	/**
	 * @var  Cache  Nette cache service.
	 */
	private $_cache;
	/**
	 * @var  array  In-memory copy in order to avoid repeated cache storage reads.
	 */
	private $_memory = array();
	/**
	 * @var  array  Wrapper options
	 */
	private $_options;

	const DIRECTORIES = 'directories';

	/**
	 * @param  Cache  $cache    Nette cache service
	 * @param  array  $options  Wrapper options
	 */
	public function __construct(Cache $cache, array $options = array())
	{
		$this->_cache = $cache;
		$this->setOptions($options);
	}

	/**
	 * @param  array  $options
	 */
	private function setOptions($options)
	{
		$this->_options = $options;
		if (isset($options[self::DIRECTORIES]) && is_array($options[self::DIRECTORIES]))
		{
			foreach ($options[self::DIRECTORIES] as $path => $file_mask)
			{
				$this->addDirectoryOption($path, $file_mask);
			}
			unset($this->_options['directories']);
		}
	}

	/**
	 * @param  string  $path
	 * @param  string  $file_mask
	 */
	public function addDirectoryOption($path, $file_mask)
	{
		$found = $this->_find_files($path, $file_mask);
		if ($found)
		{
			if ( ! array_key_exists(Cache::FILES, $this->_options))
			{
				$this->_options[Cache::FILES] = array();
			}
			elseif ( ! is_array($this->_options[Cache::FILES]))
			{
				$this->_options[Cache::FILES] = array($this->_options[Cache::FILES]);
			}
			$this->_options[Cache::FILES] = array_merge($this->_options[Cache::FILES], $found);
		}
	}

	/**
	 * Based on:
	 * 
	 * @see  http://php.net/manual/en/function.glob.php#106595
	 * 
	 * @param   string   $directory
	 * @param   string   $filename_mask
	 * @param   integer  $flags
	 * @return  array
	 */
	private function _find_files($directory, $filename_mask, $flags = 0)
	{
		$files = glob($directory.'/'.$filename_mask, $flags);
		if (($subdirectories = glob($directory.'/*', GLOB_ONLYDIR|GLOB_NOSORT))) // intentionally assigned
		{
			foreach ($subdirectories as $subdirectory)
			{
				$files = array_merge($files, $this->_find_files($subdirectory, $filename_mask, $flags));
			}
		}
		return $files;
	}

	/**
	 * @param   string  $key
	 * @reutrn  mixed
	 */
	private function _cache_load($key)
	{
		if ( ! isset($this->_memory[$key]))
		{
			$this->_memory[$key] = $this->_cache->load($key);
		}
		return $this->_memory[$key];
	}

	/**
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  array   $options
	 */
	private function _cache_save($key, $value, array $options = NULL)
	{
		$this->_cache->save($key, $value, $options);
		unset($this->_memory[$key]);
	}

	/**
	 * @param   string  $key
	 * @return  boolean
	 */
	public function offsetExists($key)
	{
		return $this->_cache_load($key) !== NULL;
	}

	/**
	 * @param   string  $key
	 * @return  mixed
	 */
	public function offsetGet($key)
	{
		return $this->_cache_load($key);
	}

	/**
	 * @param  string  $key
	 * @param  mixed   $value
	 */
	public function offsetSet($key, $value)
	{
		$this->_cache_save($key, $value, $this->_options);
	}

	/**
	 * @param  string  $key
	 */
	public function offsetUnset($key)
	{
		$this->_cache_save($key, NULL);
	}
}
