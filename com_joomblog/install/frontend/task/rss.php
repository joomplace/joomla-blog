<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'task'.DIRECTORY_SEPARATOR.'base.php' );
require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'feedcreator.php');
require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'datamanager.php');

class JbblogRssTask extends JbblogBaseController
{
 	
	function display()
	{
		header('Content-type:application/xml');
		$this->_rss();
		exit;
	}

	function _rss($bloggerID = "", $tags = "", $category = '',  $keywords = "", $archive="")
	{

		global $JBBLOG_LANG, $_JB_CONFIGURATION, $Itemid;

		$mainframe	= JFactory::getApplication();
		$db			= JFactory::getDBO();
        $desc_length_max = 0;
        $feedType = $_JB_CONFIGURATION->get('showFeed');
        $countOfChars = $_JB_CONFIGURATION->get('countOfChars');

		if (!$_JB_CONFIGURATION->get('useRSSFeed') or $_JB_CONFIGURATION->get('useRSSFeed') == "0")
		{
			echo '<error>';
			echo JText::_('COM_JOOMBLOG_RSS_FEED_NOT_ENABLED');
			echo '</error>';
			return;
		}

		$blogger		= JFactory::getApplication()->input->get( 'user' , '' , 'REQUEST' );
		$category		= JFactory::getApplication()->input->get( 'category' , '' , 'REQUEST' );
		$tag		= JFactory::getApplication()->input->get( 'tag' , '' , 'REQUEST' );
		$blog		= JFactory::getApplication()->input->get( 'blogid' , 0 , 'REQUEST' );
		$archive		= JFactory::getApplication()->input->get( 'archive' , '' , 'REQUEST' );
		if ($blogger != "")
		{
			$bloggerID = is_string($blogger) ? jbGetAuthorId(urldecode($blogger)) : intval($blogger);
			$query	= "SELECT * from #__users WHERE id='$bloggerID'";
			$db->setQuery( $query );
			$blogger	= $db->loadObjectList();
			if ($blogger){
				$blogger = $blogger[0];
				$blogger_username = ($_JB_CONFIGURATION->get('useFullName')=="1" ? $blogger->name :$blogger->username);
			}else{
				$blogger_username = "";
			}
		}else $bloggerID = 0;

		
		if( !empty( $archive ) )
		{
			$archive = urldecode($archive);
			$archive = date("Y-m-d 00:00:00", strtotime($archive."-01"));
		}
		
		$rss = new RSSCreator20();

		$rssLimit	= ( $_JB_CONFIGURATION->get('rssFeedLimit') != 0 ) ? (int) $_JB_CONFIGURATION->get('rssFeedLimit') : 20;

		$searchby = array('limit' => $rssLimit,
				'limitstart' => 0,
				);
	
		$entries = mb_get_entries($searchby, false, false);
		$total = count($entries);

		
		if(!class_exists('JoomblogTemplate'))
		{
			require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'template.php' );
		}
		    
		$tpl = new JoomblogCachedTemplate(serialize($entries) . "_rss" . strval($bloggerID) . strval($category) . strval($tag) . strval($blog) . strval($archive));
		if (!$tpl->is_cached()) 
		{
			$title	= JText::_('COM_JOOMBLOG_RSS_FEED_PAGE_TITLE');
			
			if( isset( $blogger_username ) && !empty( $blogger_username ) )
			{
				$title	.= ' ' . JText::sprintf( 'COM_JOOMBLOG_RSS_FEED_PAGE_TITLE_BY' , $blogger_username );
			}

			if( isset( $tag ) && !empty( $tag ) )
			{
				$title	.= ' ' . JText::sprintf( 'COM_JOOMBLOG_RSS_FEED_PAGE_TITLE_TAGGED' , $tag );
			}

			if( isset( $category ) && !empty( $category ) )
			{
				$title	.= ' ' . JText::sprintf('COM_JOOMBLOG_RSS_FEED_PAGE_TITLE_CATEGORY', jbGetJoomlaCategoryName( $category ) );
			}
			
			if( isset( $blog ) && !empty( $blog ) )
			{
			    $query = $db->getQuery(true);
			    $query->select('lb.*');
			    $query->select('lb.*,u.username, u.name');
			    $query->from('#__joomblog_list_blogs lb');
			    $query->join('LEFT','#__users u ON u.id=lb.user_id');
			    $query->join('LEFT','#__joomblog_user ub ON ub.user_id=lb.user_id');
			    $query->where("lb.id=".$db->quote($blog));
			    $db->setQuery($query);
			    $blogData = $db->loadObject();
			    if($blogData){
				$blogData->title = $blogData->title?$blogData->title:$blogData->username."'s blog";
				$title	.= ' ' . JText::sprintf( 'COM_JOOMBLOG_RSS_FEED_PAGE_TITLE_FROM' , $blogData->title );
			    }
			}
			if ($archive and $archive != "")
			{
				$archive_display	= date("F Y", strtotime($archive));
				$title 				.= " - $archive_display";
			}
			
			$db->setQuery("SELECT description from #__joomblog_user WHERE user_id='$bloggerID'");
			$description = $db->loadResult();
			
			if (!$description or $description == "")
				$description = "$title";
			
			// remove readmore tag
			$description = str_replace('{readmore}', '', $description);	
				
			$rss->title 		= $title;
			$rss->description	= $description;
			$rss->encoding		= 'UTF-8';
			$rss->link			= rtrim( JURI::base() , '/' );
			$rss->cssStyleSheet = NULL;

			if ($entries)
			{
				$count = 0;
				
				foreach($entries as $row)
				{

					$count++;

					if ($count > $rssLimit)
					{
						break;
					}

					$item = new FeedItem();
					$item->title = $row->title != "" ? $row->title : "...";
					$item->title = jbUnhtmlspecialchars($item->title);
                    $itemDesc = '';

                    switch($feedType)
                    {
                        case 0:
                            if(!empty($row->introtext)){
                                $itemDesc = $row->introtext;
                                if(!empty($row->fulltext)){
                                    $itemDesc = $itemDesc . $row->fulltext;
                                }
                                $itemDesc = str_replace('&nbsp;', ' ', $itemDesc);
                            }
                        break;

                        case 1:
                            if(!empty($row->introtext)){
                                $itemDesc = $row->introtext;
                                $itemDesc = str_replace('&nbsp;', ' ', $itemDesc);
                            }
                        break;

                        case 2:
                            $desc_length_max = $countOfChars;
                            $dots="...";
                            $spacebar= ' ';
                            $text = '';
                            if(!empty($row->introtext)){
                                $text = strip_tags($row->introtext);
                                if(!empty($row->fulltext)){
                                    $text = $text . strip_tags($row->fulltext);
                                }
                                $text = str_replace('&nbsp;', $spacebar, $text);
                                if(strlen($text)>$desc_length_max)
                                {
                                    $part = substr($text, 0 , $desc_length_max);
                                    if(strpos($part,$spacebar)){
                                        while(substr($part, -1)!=$spacebar)
                                        {
                                            $part = substr($part, 0, strlen($part)-1);
                                        }
                                        $itemDesc = rtrim($part).$dots;
                                    }
                                    else{
                                        $itemDesc = $part;
                                    }
                                }
                            }
                        break;

                        default:
                            if(!empty($row->introtext)){
                                $itemDesc = $row->introtext;
                                $itemDesc = str_replace('&nbsp;', ' ', $itemDesc);
                            }
                    }

					if(!$_JB_CONFIGURATION->get('rssFeedLimit')){
					    $itemDesc = preg_replace ('/<[^>]*>/', '', $itemDesc);
					}

					$user = JFactory::getUser();
					$actualDescLength	= JString::strlen($itemDesc);
					$itemDesc			= preg_replace("/\r\n|\n|\r/", "", $itemDesc);
					$itemDesc = str_replace(array('{jomcomment lock}','{!jomcomment}','{jomcomment}'),'',$itemDesc);

					if ($actualDescLength > $desc_length_max)
					{
						$itemDesc .= JText::_('COM_JOOMBLOG_READMORE');
					}

					$itemDesc			= str_replace('{readmore}', '', $itemDesc);
					$itemDesc			= str_replace('Read More...', '', $itemDesc);
					$itemDesc			= str_replace('alt=&quot;Listenin', '', $itemDesc);

/*					$metadata_json = json_decode($row->metadata);
					if(empty($metadata_json)){
					    $metadata_json = new stdClass();
					    $metadata_json->page_image = '';
					}*/

                    $page_image = '';
                    if(!empty($row->defaultimage)){
                        $page_image = $row->defaultimage;
                    }
                    if($page_image) {
                        $itemDesc = '<img height="72" align="left" src="' . JUri::root() . substr($page_image, 1) . '">' . $itemDesc;
                    }

                    $itemDesc = jbCloseTags($itemDesc);
					$item->description	= $itemDesc;

					$item->link			= html_entity_decode(  $row->permalink  );
					$date				= new JDate( $row->created );
					
					$mainframe = JFactory::getApplication();
					$date->setTimezone(new DateTimeZone( $mainframe->getCfg( 'offset' ) ));

					$item->date			= $date->toRFC822( true );
					$item->author		= null;// jbGetAuthorName($row->created_by , '1');
					
					$categoriesList 	= jbGetTags($row->id);
					
					$extraElements 		= array ();
					
					if ($categoriesList)
					{
						$categories = "";
						$indentString = " ";
						
						foreach ($categoriesList as $category)
						{
							$categoryName = htmlentities($category->name) ;
							
							if ($categories != "")
							{
								$categories .= "</category>\n$indentString<category>";
							}
								
							$categories .= $categoryName;
						}
						
						$extraElements['category'] = $categories;
						//$item->author = '<dc:creator>'.$item->author.'</dc:creator>';
						$item->additionalElements = $extraElements;
					}

					$rss->addItem($item);
				}
			}
			
			$tpl->set('rss', $rss->createFeed());
		}
		
		$rsscontent = $tpl->fetch_cache(JB_TEMPLATE_PATH . "/admin/rss.tmpl.html");
		
		$trans = array_map('utf8_encode', array_flip(array_diff(get_html_translation_table(HTML_ENTITIES), get_html_translation_table(HTML_SPECIALCHARS))));
		$rsscontent = strtr($rsscontent, $trans);

		echo $rsscontent;
	}
}

