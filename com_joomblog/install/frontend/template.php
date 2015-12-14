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

	function JoomblogTemplate($file = null)
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

	function JoomblogCachedTemplate($cache_id = "", $cache_timeout = 1000)
	{
		$this->JoomblogTemplate();
		$this->cache_id = JB_CACHE_PATH . "/cache__" . md5($cache_id);
		$this->cached = false;
		$this->expire = $cache_timeout;
	}

	function is_cached()
	{

		return false; //cache is always off
		if ($this->cached) return true;
		if (!$this->cache_id) return false;
		if (!file_exists($this->cache_id)) return false;
		if (!($mtime = filemtime($this->cache_id))) return false;
		if (($mtime + $this->expire) < time())
		{
			@unlink($this->cache_id);
			return false;
		}

		else
		{
			$this->cached = true;
			return true;
		}
	}

	function fetch_cache($file, $processFunc = '')
	{
		//cache is always off
		// $contents = $this->fetch($file);
		//if($processFunc) $contents = $processFunc($contents);
		//return $contents;
		//end always off

		$contents = "";

		if ($this->is_cached())
		{
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
			$contents = $this->fetch($file);

			if ($processFunc)
				$contents = $processFunc($contents);

			if (!empty($contents))
			{
				if ($fp = fopen($this->cache_id, 'w'))
				{
					fwrite($fp, $contents);
					fclose($fp);
				}
				else
				{

				}
			}
		}

		return $contents;
	}
}

