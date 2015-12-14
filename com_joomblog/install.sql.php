<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDBO();

$query ="CREATE TABLE IF NOT EXISTS `#__joomblog_privacy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(11) NOT NULL,
  `posts` int(10) unsigned NOT NULL,
  `comments` int(10) unsigned NOT NULL,
  `isblog` tinyint(3) unsigned NOT NULL,
  `jsviewgroup` int(10) unsigned NOT NULL,
  `jspostgroup` int(10) unsigned NOT NULL,
  `isnew` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `default` int(3) unsigned NOT NULL DEFAULT '0',
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `default` (`default`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0"; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_admin` (
  `sid` varchar(128) NOT NULL,
  `cid` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `type` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`)
) DEFAULT CHARSET=utf8"; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_content_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contentid` int(10) unsigned NOT NULL DEFAULT '0',
  `tag` int(10) unsigned NOT NULL DEFAULT '0',
  `isnew` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0"; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_user` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `title` text NOT NULL,
  `feedburner` text NOT NULL,
  `style` text NOT NULL,
  `params` text NOT NULL,
  `about` text NOT NULL,
  `site` varchar(255) NOT NULL DEFAULT '',
  `twitter` varchar(255) NOT NULL DEFAULT '',
  `birthday` date DEFAULT '0000-00-00',
  `avatar` text NOT NULL,
  `post_count` int(5) NOT NULL DEFAULT '5',
  `reading_list` varchar(255) NOT NULL,
  `facebook` varchar(255) NOT NULL,
  `google_plus` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8"; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_list_blogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `published` int(3) NOT NULL,
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL,
  `private` int(3) NOT NULL,
  `title` varchar(250) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `header` text NOT NULL,
  `metadata` text NOT NULL,
  `metadesc` text NOT NULL,
  `metakey` text NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `approved` tinyint(3) unsigned NOT NULL,
  `ordering` int(10) NOT NULL DEFAULT '0',
  `access` int(10) NOT NULL DEFAULT '1',
  `access_gr` int(10) NOT NULL DEFAULT '0',
  `waccess` int(10) NOT NULL DEFAULT '0',
  `waccess_gr` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_multicats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `isnew` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `cid` (`cid`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_blogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `blog_id` int(10) unsigned NOT NULL,
  `isnew` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `blog_id` (`blog_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `published` int(1) unsigned NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0"; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `contentid` int(11) DEFAULT NULL,
  `vote` int(3) DEFAULT '0',
  `isnew` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_comment_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `commentid` int(11) DEFAULT NULL,
  `vote` int(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 "; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_plugins` (
  `published` int(1) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8"; 
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parentid` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `contentid` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `name` varchar(200) DEFAULT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `voted` int(11) NOT NULL DEFAULT '0',
  `isnew` tinyint(3) unsigned NOT NULL,
  `spam` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contentid` (`contentid`),
  KEY `published` (`published`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `attribs` varchar(5120) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(10) NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `metadata` text NOT NULL,
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `sectionid` int(10) unsigned NOT NULL DEFAULT '0',
  `access_gr` int(10) NOT NULL DEFAULT '0',
  `caccess` int(10) NOT NULL DEFAULT '1',
  `caccess_gr` int(10) NOT NULL DEFAULT '0',
  `defaultimage` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `access` (`access`),
  KEY `state` (`state`),
  KEY `catid` (`catid`),
  KEY `createdby` (`created_by`),
  KEY `language` (`language`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
$db->setQuery($query);
$db->execute();

$query = "CREATE TABLE IF NOT EXISTS `#__joomblog_posts_rating` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`content_id`)
) DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->execute();

//////////////////////////////////////////////////////////////////////////////
///////				Create Uncategorised for JoomBlog					//////
//////////////////////////////////////////////////////////////////////////////

$db->setQuery("SELECT id FROM #__categories WHERE `extension` = 'com_joomblog' AND `title` = 'Uncategorised'");
$exists = $db->loadResult();
if(!$exists)
{
	$date = JFactory::getDate();
	$rules_array = array();
	$rules_array['core.edit.state'] = array();
	$rules_array['core.edit'] = array();
	$rules_array['core.delete'] = array();
		
	$db->setQuery("SELECT MAX(id) from #__categories WHERE extension = 'com_joomblog' AND level = 1 ");
	$max_cat_id = $db->loadResult();
		
	if($max_cat_id){
		$cat_alias = "uncategorised".($max_cat_id+1);
	}else{
		$cat_alias = "uncategorised";
	}
	$user  = JFactory::getUser();
  $user_id = "'".$user->id."'";	
	$data = array( 
		'extension'=> "com_joomblog",
		'title'=> "Uncategorised",
		'published'=> "1",
		'access'=> "1",
		'parent_id'=> "0",
		'level'=> "0",
		'path'=> "joomblog",
		'alias'=> $cat_alias,
		'params'=> "{\"category_layout\":\"\",\"image\":\"\"}",
		'metadata'=> "{\"author\":\"\",\"robots\":\"\"}",
		'created_user_id'=> $user_id,
		'created_time'=> ''.$date->toSql().'',
		'modified_user_id'=> "0",
		'modified_time'=> "0000-00-00 00:00:00",
		'language'=> "*",
		'rules'=>$rules_array 
	);
		
	$row = JTable::getInstance("Category");
		
	if ($row->parent_id != $data['parent_id'] || $data['id'] == 0) {
		$row->setLocation($data['parent_id'], 'last-child');
	}
		
	$row->bind($data);
	$row->store(true);
		
	$category_id = $row->id;
}

//////////////////////////////////////////////////////////////////////////////
///////				Create JoomBlog Users From Joomla Users				//////
//////////////////////////////////////////////////////////////////////////////

$db->setQuery("SELECT id FROM #__users");
$users = $db->loadObjectList();

$jbuser = array();
$db->setQuery("SELECT user_id FROM #__joomblog_user");
$jbuser = $db->loadColumn();

$insert = array();
if (!empty($users)){
  if ($jbuser)
  {
    foreach($users as $user)
    {
      if (!in_array($user->id, $jbuser))
      {
        $insert[] = "(".$user->id.", '', '', '', '', '', '', '', '', '0000-00-00', '', '', '','','')";
      }
    }
  }
  else
  {
    foreach($users as $user)
    {
       $insert[] = "(".$user->id.", '', '', '', '', '', '', '', '', '0000-00-00', '', '', '','','')";
    }
  } 
  
  
  $insert_val = implode(',', $insert);
  if ($insert_val)
  {
    $query = "INSERT INTO #__joomblog_user VALUES ".$insert_val;
    
    $db->setQuery($query);
    $db->execute();
  }

}

//add new fields to old version of component
try
    {
      $query = "ALTER TABLE  `#__joomblog_list_blogs` ADD  `header` TEXT NOT NULL AFTER  `description`";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_user ADD `post_count` INT(5) NOT NULL DEFAULT '5'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_user ADD `reading_list` VARCHAR(255) NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_comment ADD `spam` TINYINT(3) NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_tags ADD  UNIQUE `name` (`name`)";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_list_blogs ADD `ordering` INT(10) NOT NULL DEFAULT '0'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_list_blogs ADD `access` INT(10) NOT NULL DEFAULT '1'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_list_blogs ADD `access_gr` INT(10) NOT NULL DEFAULT '0'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_list_blogs ADD `waccess` INT(10) NOT NULL DEFAULT '0'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }    
try
    {
      $query = "ALTER TABLE #__joomblog_list_blogs ADD `waccess_gr` INT(10) NOT NULL DEFAULT '0'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_posts ADD `access_gr` INT(10) NOT NULL DEFAULT '0'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_posts ADD `caccess` INT(10) NOT NULL DEFAULT '1'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_posts ADD `caccess_gr` INT(10) NOT NULL DEFAULT '0'";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_posts ADD `defaultimage` VARCHAR(255) NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_privacy ADD `isnew` TINYINT(3) UNSIGNED NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
try
    {
      $query = "ALTER TABLE #__joomblog_content_tags ADD `isnew` TINYINT(3) UNSIGNED NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_multicats ADD `isnew` TINYINT(3) UNSIGNED NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_blogs ADD `isnew` TINYINT(3) UNSIGNED NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_votes ADD `isnew` TINYINT(3) UNSIGNED NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }     
try
    {
      $query = "ALTER TABLE #__joomblog_comment ADD `isnew` TINYINT(3) UNSIGNED NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_user ADD `facebook` VARCHAR(255) NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE #__joomblog_user ADD `google_plus` VARCHAR(255) NOT NULL";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { } 
try
    {
      $query = "ALTER TABLE `#__joomblog_posts` ADD `custom_metatags` TEXT NOT NULL DEFAULT ''";
      $db->setQuery($query);
      $db->execute();
    }
    catch (RuntimeException $e)
    { }
