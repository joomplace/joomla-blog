<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if (!defined('JB_CACHE_PATH'))
	define('JB_CACHE_PATH', JPATH_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'joomblog');

class JoomblogTemplate
{
	var $vars;

	function __construct($file = null)
	{
		$this->file = $file;
		@ini_set('short_open_tag', 'On');
	}

	function set($name, $value, $object = false)
	{
		if (!$object) $this->vars[$name] = is_object($value) ? $value->fetch() : $value;
		else $this->vars[$name] = $value;
	}


	function fetch($file = null)
	{
		if (!$file) $file = $this->file;

		if ($this->vars)
			extract($this->vars);

		ob_start();
		include($file);
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	function object_to_array($obj)
	{
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = array();
		foreach ($_arr as $key => $val)
		{
			$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
			$arr[$key] = $val;
		}
		return $arr;
	}
}

class JoomblogCachedTemplate extends JoomblogTemplate
{
	var $cache_id;
	var $expire;
	var $cached;
	var $file;

	function __construct($cache_id = "", $cache_timeout = 0)
	{
		parent::__construct();
		/* cache filename */
		$this->cache_id = JB_CACHE_PATH . "/cache__" . md5($cache_id);
		/* cached false by default to allow rewrite */
		$this->cached = false;
		/* cached experation (Joomla settings by default) */
		$this->expire = ($cache_timeout)?$cache_timeout:JFactory::getConfig()->get('cachetime')*60;
	}

	function is_cached()
	{
		if(!JFactory::getConfig()->get('caching')){
			return false; //cache off
		}
		/* already checked by self */
		if ($this->cached) return true;
		/* no filename */
		if (!$this->cache_id) return false;
		/* no file */
		if (!file_exists($this->cache_id)) return false;
		/* couldn`t get modified time */
		if (!($mtime = filemtime($this->cache_id))) return false;
		/* cache is expired (no need to check time zone because cache is created with servertime) */
		if (($mtime + $this->expire) < time()){
			@unlink($this->cache_id);
			return false;
		}else
		{
			$this->cached = true;
			return true;
		}
	}

	function fetch_cache($file, $processFunc = '')
	{
		$contents = "";

		if ($this->is_cached())
		{
			/* read cache */
			$fp = fopen($this->cache_id, 'r');
			if ($fp)
			{
				$filesize = filesize($this->cache_id);
				if ($filesize > 0)
				{
					$contents = fread($fp, $filesize);
				}
				fclose($fp);
			}
			else
			{
				$contents = $this->fetch($file);
			}
		}
		else
		{
			/* create cache */
			$contents = $this->fetch($file);

			if ($processFunc)
				$contents = $processFunc($contents);

			if (!empty($contents) && JFactory::getConfig()->get('caching'))
			{
				if ($fp = fopen($this->cache_id, 'w'))
				{
					fwrite($fp, $contents);
					fclose($fp);
					$this->cached = true;
				}
				else
				{
					$this->cached = false;
				}
			}
		}

		return $contents;
	}
}

