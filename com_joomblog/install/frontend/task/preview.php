<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'show.base.php' );
require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'plugins.php' );

class JbblogPreviewTask extends JbblogShowBase
{
	var $_plugins	= null;
	var $row = null;
	var $uid = null;
	var $prev = null;
	var $next = null;
	
	function __construct()
	{	
		$this->_plugins	= new JBPlugins();
		$this->toolbar = JB_TOOLBAR_HOME;
		$db	= JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$preview	= $jinput->get( 'preview' , '' , 'GET' );
		$id	= $jinput->get( 'id' , '' , 'GET' );
		$this->uid	= (!empty( $preview ) ) ? $preview : $id;
		$uid	= $this->uid;
		if (is_numeric($uid)){
			$row	= JTable::getInstance( 'Posts' , 'Table' );

			if (!$row->load( $uid )) {return;}
			if (!empty($_SESSION['entrySession']))
			{
				$key = array_search($uid, $_SESSION['entrySession']);
				$this->prev = ($key) ? $_SESSION['entrySession'][$key - 1] : '';
				$this->next = ($key < count($_SESSION['entrySession']) - 1) ? $_SESSION['entrySession'][$key + 1] : '';
			}
		}else{
		    JError::raiseError(404, JText::_('COM_JOOMBLOG_POST_NOT_FOUND'));
		    return false;
		}

		$this->row = &$row;
	}
	
	function _header()
	{
		echo parent::_header();
		
		if($this->isJbEntry()){
      $doc = JFactory::getDocument();
      $doc->addScript(rtrim( JURI::root() , '/' ).'/components/com_joomblog/js/joomblog.js');
		}
	}
	
	function isJbEntry()
	{
		$my		= JFactory::getUser();
		if (isset($this->row->created_by))
		return( $this->row->created_by == $my->id ); 
		else return false; 
	}

	function display($styleid = '', $wrapTag = 'div')
	{
		global $JBBLOG_LANG, $_JB_CONFIGURATION;
		$mainframe	= JFactory::getApplication();
		$doc = JFactory::getDocument();
		$my	= JFactory::getUser();

		if(!jbAllowedGuestView('entry'))
		{
			$template		= new JoomblogTemplate();
			$content		= $template->fetch($this->_getTemplateName('permissions'));
			return $content;
		}
		
		$Itemid		= jbGetItemId();
		$row		= null;
		$task		= '';
		$task_url	= "";

		if ($task!="")
		{
			$task_url = "&task=$task";
		}
		
		// Load plugins
		$this->_plugins->load();
		
		if (isset($this->row)) $row = &$this->row; else $row=null;
		if (!isset($row->id)) 
		{
			return JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_ENTRIES');
		}
		
		$row->permalink = jbGetPermalinkUrl($row->id);
		$row->comments = "";
		
		$row->addPosition = $_JB_CONFIGURATION->get('addThisPosition');
		
		$date			= JFactory::getDate( $row->created );
		$row->createdFormatted	= $date->format( $_JB_CONFIGURATION->get('dateFormat', 'Y-m-d') );
		$row->created			= $date->format('Y-m-d H:i:s');

		$row->title = jbUnhtmlspecialchars($row->title);
		$row->title = htmlspecialchars($row->title); 
		if (empty ($row->title)) $row->title = JText::_('COM_JOOMBLOG_UNTITLED');
		if (!($row->id)) $row->id = JFactory::getApplication()->input->get('preview');
		$db			= JFactory::getDBO();
		$date		= JFactory::getDate();

		jbAddPageTitle(jbUnhtmlspecialchars($row->title));
		jbAddPathway($row->title);
		$tags	= jbGetTags($row->id);
		//looking for old metadata
		$metadata = json_decode ($row->metadata);
		if(empty($metadata)){
		    $metadata = new stdClass ();
		    $metadata->metakey = '';
		    $metadata->metadesc = '';
		}
		$keywords	= ''.$metadata->metakey;
		if (empty( $keywords ))
		{
			foreach($tags as $tag)
			{
				if ( !empty ($tag->name))
				{
					if ( empty( $keywords ) )
					{
						$keywords	.= $tag->name ;
					}
					else $keywords	.= ', '.$tag->name ;
				}
			}
		}
		$metadescription = $row->metadesc.' '.$metadata->metadesc;
		if($doc->getDescription() == '' || $row->metadesc != '')
			$doc->setDescription( $metadescription );
		if( !empty( $row->metakey ) )
		{
			$keywords	.= ' ' . $row->metakey;
		}
		$doc->setMetaData( 'keywords' , $doc->getMetaData('keywords').' '.$keywords );
		$tpl	= new JoomblogCachedTemplate(serialize($row) . $_JB_CONFIGURATION->get('template') . $task);

		if (!$tpl->is_cached())
		{
			if ($_JB_CONFIGURATION->get('useAddThis')){
				if ($_JB_CONFIGURATION->get('addThisName')!=''){
					$row->introtext = preg_replace('/{social}/', jbGetAddThis(), $row->introtext);
					$row->fulltext = preg_replace('/{social}/', jbGetAddThis(), $row->fulltext);
				} else {
					global $raiseDisqusNotice;
					if(!$raiseDisqusNotice){
						$raiseDisqusNotice=1;
						JError::raiseNotice('',JText::_('COM_JOOMBLOG_PLEASE_ENTER_YOUR_ADDTHIS_PROFILE'));
					}
					$row->introtext = str_replace('{social}', '', $row->introtext);
					$row->fulltext = str_replace('{social}', '', $row->fulltext);
				}
			} else {
					$row->introtext = str_replace('{social}', '', $row->introtext);
					$row->fulltext = str_replace('{social}', '', $row->fulltext);
			}
			
			$row->text	= '';
			
			if($row->introtext && trim($row->introtext) != '')
			{
				$row->text	.= $row->introtext;
			}
			
			if($row->fulltext && trim($row->fulltext) != '')
			{
				if($_JB_CONFIGURATION->get('anchorReadmore'))
				{
					$row->text	.= '<a name="readmore"></a>'; 
				}
				$row->text	.= $row->fulltext;
			}
		//Search for social media images
		preg_match_all('/<img[^>]+\>/i',$row->text,$matches);
		if ($matches)
		{
			$embed_scripts='function show_social(embed_id,social_id)
								{						
									if(social_id.style.display=="none")
									{ 
										social_id.style.display="block";
										embed_id.style.display="none"; 
										FB.XFBML.parse();
									} 
									else
									{ 
										social_id.style.display="none";
									} 
								}
							function show_embed(embed_id,input_id,social_id)
								{						
									if(embed_id.style.display=="none")
									{ 
										embed_id.style.display="block";
										social_id.style.display="none";
										$(input_id).select();
									} 
									else
									{ 
										embed_id.style.display="none"; 
									} 
								}
							function show_only_embed(embed_id,input_id)
								{						
									if(embed_id.style.display=="none")
									{ 
										embed_id.style.display="block";
										$(input_id).select();
									} 
									else
									{ 
										embed_id.style.display="none"; 
									} 
								}
							function show_only_social(social_id)
								{						
									if(social_id.style.display=="none")
									{ 
										social_id.style.display="block";
										FB.XFBML.parse(); 
									} 
									else
									{ 
										social_id.style.display="none";
									} 
								}';
			$doc->addScriptDeclaration( $embed_scripts );

			$j=1;
			foreach ($matches[0] as $match)
			{
				if (strpos($match,'data-embed="1"')) $img_embed = true;
				if (strpos($match,'data-facebook="1"')) $img_facebook = true;
				if (strpos($match,'data-twitter="1"')) $img_twitter = true;
				if (strpos($match,'data-pinterest="1"')) $img_pinterest = true;
				if (strpos($match,'data-linkedin="1"')) $img_linkedin = true;
				if (strpos($match,'data-googleplus="1"')) $img_googleplus = true;
				if (@$img_embed OR @$img_facebook OR @$img_twitter OR @$img_pinterest OR @$img_linkedin OR @$img_googleplus)
				{
					preg_match('/<img[^>]+src=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/', $match, $src);
					$image_src = $src[2];
					$social_media_image = '<div class="jb-social-media-image">'.$match;
					$embed_content = str_replace('data-twitter="1"','',$match);
					$embed_content = str_replace('data-embed="1"','',$embed_content);
					$embed_content = str_replace('data-facebook="1"','',$embed_content);
					$embed_content = str_replace('data-pinterest="1"','',$embed_content);
					$embed_content = str_replace('data-googleplus="1"','',$embed_content);
					$embed_content = str_replace('data-linkedin="1"','',$embed_content);
					$embed_content = '<a target="_blank" href="'.$row->permalink.'">' . $embed_content . '</a>';
					$embed_content = str_replace('<', '&lt;', $embed_content);
					$embed_content = str_replace('>', '&gt;', $embed_content);

					if ($img_embed AND($img_facebook OR $img_twitter OR $img_pinterest OR $img_linkedin OR $img_googleplus)) {
						$social_media_image .= '<div class="jb-img-embed"><button onclick="show_embed(jb_embed_item_'.$j.',jb_embed_input_'.$j.',jb_share_item_'.$j.');" class="jb-embed-button">'.JText::_("COM_JOOMBLOG_EMBED").' 
						<i class="jb-icon-code"></i></button><button onclick="show_social(jb_embed_item_'.$j.',jb_share_item_'.$j.');" class="jb-social-button">'.JText::_("COM_JOOMBLOG_SHARE").' <i class="jb-icon-share"></i></button>';
					}
					if ($img_embed AND !($img_facebook OR $img_twitter OR $img_pinterest OR $img_linkedin OR $img_googleplus)) {
						$social_media_image .= '<div class="jb-img-embed"><button onclick="show_only_embed(jb_embed_item_'.$j.',jb_embed_input_'.$j.');" class="jb-embed-button">'.JText::_("COM_JOOMBLOG_EMBED").' 
						<i class="jb-icon-code"></i></button>';
					}
					if (!$img_embed AND ($img_facebook OR $img_twitter OR $img_pinterest OR $img_linkedin OR $img_googleplus)) {
						$social_media_image .= '<div class="jb-img-embed"><button onclick="show_only_social(jb_share_item_'.$j.');" class="jb-social-button">'.JText::_("COM_JOOMBLOG_SHARE").' <i class="jb-icon-share"></i></button>';
					}							
					if ($img_embed)	$social_media_image .=	'<div style="display:none;" class="jb-embed-input" id="jb_embed_item_'.$j.'"><input style="cursor:text;" id="jb_embed_input_'.$j.'" type="text" readonly value=\''.$embed_content.'\'></div></div>';
					if ($img_facebook OR $img_twitter OR $img_pinterest OR $img_linkedin OR $img_googleplus) $social_media_image .= '<div class="jb-img-social-share" id="jb_share_item_'.$j.'" style="display:none;">';
					//add pinterest button
					if ($img_pinterest) 
					{
						$post_link = urlencode($row->permalink);
						$image_link = urlencode($image_src);
						$title = urlencode($row->title);
						$social_media_image .= '<div class="jb-img-pinterest">
						<a href="//pinterest.com/pin/create/button/?url='.$post_link.'&media='.$image_link.'&description='.$title.'" data-pin-do="buttonPin" data-pin-config="above"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>
						</div>';
					}
					//add twitter button
					if ($img_twitter) $social_media_image .= '<div class="jb-img-twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$image_src.'" data-count="horizontal">Tweet</a></div>';					
					//add linkedin button
					if ($img_linkedin) $social_media_image .= '<div class="jb-img-linkedin"><script type="IN/Share" data-url="'.$image_src.'" data-counter="right"></script></div>';
					//add google+ button
					if ($img_googleplus) $social_media_image .= '<div class="jb-img-googleplus"><g:plusone size="medium" href="'.$image_src.'"></g:plusone></div>';
					//add facebook button
					if ($img_facebook) $social_media_image .= '<div class="jb-img-facebook"><div id="fb-root"></div>
						<div class="fb-like" data-href="'.$image_src.'" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div></div>';
					if ($img_facebook OR $img_twitter OR $img_pinterest OR $img_linkedin OR $img_googleplus) $social_media_image .= '</div>';
					$social_media_image .= '</div>';
					$row->text = str_replace($match,$social_media_image,$row->text);
					unset($img_embed);
					unset($img_facebook);
					unset($img_twitter);
					unset($img_pinterest);
					unset($img_linkedin);
					unset($img_googleplus);
					unset($image_src);
					unset($embed_content);
					unset($social_media_image);
				}
				$j++;

			}	
			unset($j);
		}	

		$enablecomments=true;
		$privecycomments = $row->caccess;
		$usr	= JFactory::getUser();
		if(!in_array('8', $usr->getAuthorisedGroups()) && $usr->id != $row->created_by){
		    if($privecycomments != -2 && $privecycomments != -4){
			if(!in_array($privecycomments, $usr->getAuthorisedGroups())){
			    $enablecomments = false;
			}
		    }else{
			if($privecycomments == -2){
			    if(!in_array($row->created_by, JbblogBaseController::getJSFriends($usr->id))){
				$enablecomments = false;
			    }
			}
			if($privecycomments == -4){
			    if(!in_array($row->caccess_gr, JbblogBaseController::getJSGroups($usr->id))){
				$enablecomments = false;
			    }
			}
		    }
		}


			if ($enablecomments)
			{
			if ($_JB_CONFIGURATION->get('useComment') && $_JB_CONFIGURATION->get('useJomComment'))
			{
				jimport( 'joomla.filesystem.file');
				$file	= JPATH_PLUGINS . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'jom_comment_bot.php';
				if (JFile::exists( $file ) )
				{
					require_once( $file );
					
 					if($_JB_CONFIGURATION->get('enableJCDashboard'))
					{
                        if(preg_match('/{!jomcomment}/i',$row->text))
						{
 							$row->text	= str_replace('{!jomcomment}','',$row->text);
 						}
                        else if(preg_match('/{jomcomment}/i',$row->text))
						{
 							$row->text	= str_replace('{jomcomment}','',$row->text);
 							$row->comments	= "";
 							$row->comments 	= jomcomment($row->id, "com_joomblog");
 						}
                        else if(preg_match('/{jomcomment lock/}/i', $row->text) )
 						{
 							$row->text	= str_replace('{jomcomment lock}','',$row->text);
 							$row->comments	= "";
 							$row->comments 	= jomcomment($row->id, "com_joomblog" , '' , '' , true );
						}
						else
						{
 							$row->comments	= "";
 							$row->comments 	= jomcomment($row->id, "com_joomblog");
 						}
 					}
					else
					{
 						$row->comments	= "";
 						$row->comments 	= jomcomment($row->id, "com_joomblog");
 					}
				}
			}elseif($_JB_CONFIGURATION->get('useComment') == 1){
				$row->comments = jbGetComments($row->id);
			} else if ($_JB_CONFIGURATION->get('useComment') == 2){
				$row->comments = jbGetDisqusComments($row);
			}
			}else $row->comments ="";
						
			
			$row->author = jbUserGetName($row->created_by, $_JB_CONFIGURATION->get('useFullName'));
			if($_JB_CONFIGURATION->get('integrJoomSoc') && file_exists(JPATH_ROOT.'/components/com_community/libraries/core.php')){
			    include_once JPATH_ROOT.'/components/com_community/libraries/core.php';
			    // Get CUser object
			    $row->authorLink = CRoute::_('index.php?option=com_community&view=profile&userid='.$row->created_by);
			}else{
			    $row->authorLink	= JRoute::_("index.php?option=com_joomblog&task=profile&id=".$row->created_by."&Itemid=$Itemid");
			}

			$row->categories = jbCategoriesURLGet($row->id, true, $task);
			$row->jcategory	= '<a href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $row->catid.'&Itemid='.$Itemid ) . '">' . jbGetJoomlaCategoryName( $row->catid ) . '</a>';
			$row->emailLink = JRoute::_("index.php?option=com_content&task=emailform&id={$this->uid}");

			$avatar	= 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
			$avatar	= new $avatar($row->created_by);	
			$row->avatar	= $avatar->get();
			$post_owner = JTable::getInstance( 'BlogUsers' , 'Table' );
			$post_owner->load($row->created_by);

			if ($post_owner->avatar!=='')
			{
				$row->avatar = JUri::root().'images/joomblog/avatar/'.$post_owner->avatar;
			}
			else 
			{
				$row->avatar = JUri::root().'components/com_joomblog/images/user.png';
			}
			$row->avatar = '<img src="'.$row->avatar.'"/>';
			
			$row->afterContent = '';
			$row->beforeContent = '';
			

			$params	= $this->_buildParams();
			/*$row->beforeContent		= $this->_plugins->trigger('onBeforeDisplayContent', $row, $params, 0);
			$row->onPrepareContent	= $this->_plugins->trigger('onPrepareContent', $row, $params, 0);
			//$row->onContentPrepare  = $this->_plugins->trigger('onContentPrepare', $row, $params, 0);
			$row->afterContent		= "<br />". $this->_plugins->trigger('onAfterDisplayContent', $row, $params, 0);*/
			$row->beforeContent = '';
			$row->onPrepareContent	= '';
			$row->afterContent	= '';
			$row->text = $this->_plugins->onContentPrepare($row->text);
			$db	= JFactory::getDBO();
			$query = "SELECT `blog_id` FROM #__joomblog_blogs WHERE content_id = '".(int)$row->id."' ";
			$db->setQuery( $query );
			$blogid=$db->loadResult();

			$query = "SELECT `twitter`,	`site`, `reading_list`, `facebook`, `google_plus`, `about`  FROM #__joomblog_user WHERE user_id = '".(int)$row->created_by."' LIMIT 1";
			$db->setQuery( $query );
			$author_info=$db->loadObject();
			$reading_list_array = explode(',',$author_info->reading_list);
			$not_in_reading_list = !in_array($row->blog_id,$reading_list_array);
			$row->not_in_reading_list = $not_in_reading_list;
			$row->author_twitter = $author_info->twitter;
			$row->author_site = $author_info->site;
			$row->author_facebook = $author_info->facebook;
			$row->author_googleplus = $author_info->google_plus;
			$row->author_about = $author_info->about;
			
			$row->editLink = '<span class="editLink"><a href="index.php?option=com_joomblog&task=write&id='.$row->id.'&blogid='.$blogid.'&Itemid='.$Itemid.'"><img border="0" src="'.JURI::base().'media/system/images/edit.png" alt="Edit" name="Edit"></a></span>';

			if($_JB_CONFIGURATION->get('enableBackLink'))
				$row->afterContent .= jbGetBackLink();

			$tpl->set( 'enablePdfLink' 	,	( boolean ) $_JB_CONFIGURATION->get('enablePdfLink') );
			$tpl->set( 'enablePrintLink',	( boolean ) $_JB_CONFIGURATION->get( 'enablePrintLink' ) );
			$tpl->set( 'enableHits',		( boolean ) $_JB_CONFIGURATION->get( 'enableHits' ) );

			unset($row->_table);
			unset($row->_key);

			$my		= JFactory::getUser();
			
			$tpl->set('userId', $my->id);
			$tpl->set('categoryDisplay' , $_JB_CONFIGURATION->get('categoryDisplay') );
			
			
			
	//SMT social int
		
		/*** Twitter ***/
		$usetwitter = $_JB_CONFIGURATION->get('usetwitter');
		$row->twitter_button = null;
		$row->twposition = $_JB_CONFIGURATION->get('positiontwitterInPost');
		
		if ($usetwitter)
		{
			$showTwInPost = $_JB_CONFIGURATION->get('showtwitterInPost');
			
			
			if ($showTwInPost)
			{
				$twitStyle 	= $_JB_CONFIGURATION->get('twitterpoststyle');
				$twitFlSt 	= $_JB_CONFIGURATION->get('twitterfollowpoststyle');
				$twitLang 	= $_JB_CONFIGURATION->get('twitterlang');
				$twitName 	= $_JB_CONFIGURATION->get('twitterName');
				$twitflName = $twitName;
				$twitUrl 	= $row->permalink;	
				
				if (isset($twitLang)) $twitLang='data-lang="'.$twitLang.'"'; else $twitLang='';
				if (isset($twitName)) $twitName='data-via="'.$twitName.'"'; else $twitName='';
				if (isset($twitUrl)) $twitUrl='data-url="'.$twitUrl.'"'; else $twitUrl='';
				if (isset($row->title)) $twitText='data-text="'.$row->title.'"'; else $twitText='';
					switch ( $twitStyle ) 
					{
						case 'none': 
							$row->twitter_button='<a href="http://twitter.com/share" class="twitter-share-button" '.$twitUrl.' data-count="none" '.$twitText.' '.$twitName.' '.$twitLang.'>Tweet</a>';
						break;
						case 'horizontal': 
							$row->twitter_button='<a href="http://twitter.com/share" class="twitter-share-button" '.$twitUrl.' data-count="horizontal" '.$twitText.' '.$twitName.' '.$twitLang.'>Tweet</a>';
						break;
						case 'vertical': 
							$row->twitter_button='<a href="http://twitter.com/share" class="twitter-share-button" '.$twitUrl.' data-count="vertical" '.$twitText.' '.$twitName.' '.$twitLang.'>Tweet</a>';
						break;
					}
										
					switch ( $twitFlSt ) 
					{
						case 'twf1': 
							$row->twitter_button.='<a href="http://twitter.com/'.$twitflName.'" class="twitter-follow-button" data-show-count="false" '.$twitLang.' >@'.$twitflName.'</a>';
						break;
						case 'twf2': 
							$row->twitter_button.='<a href="http://twitter.com/'.$twitflName.'" class="twitter-follow-button" data-show-count="true" '.$twitLang.' >'.$twitflName.'</a>';
						break;
					}
					
					//$row->twitter_button.='<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
					
			}
								
		}
		
		/*** Facebook ***/
		$usefacebook = $_JB_CONFIGURATION->get('usefacebook');
		$row->fb_button = null;
		$row->fbposition = $_JB_CONFIGURATION->get('positionfbInPost');
		
		if ($usefacebook)
		{
			$showFbInPost = $_JB_CONFIGURATION->get('showfbInPost');
			
			if ($showFbInPost)
			{
				$fbStyle 		= $_JB_CONFIGURATION->get('fb_style_post');
				$fbSendButton 	= $_JB_CONFIGURATION->get('fb`_sendbutton');
				$fbwidth		= (int)$_JB_CONFIGURATION->get('fbwidth',400);
				$fbUrl 	= $row->permalink;	
				
				$row->fb_button='<script src="http://connect.facebook.net/en_US/all.js#appId=259018057462154&amp;xfbml=1"></script>';

					switch ( $fbStyle ) 
					{
						case 'none': 
							$row->fb_button.='<div id="fb-root"></div><fb:like href="'.$fbUrl.'" send="'.($fbSendButton?true:false).'" width="'.$fbwidth.'" show_faces="false" font=""></fb:like>';
						break;
						case 'horizontal': 
							$row->fb_button.='<div id="fb-root"></div><fb:like layout="button_count" href="'.$fbUrl.'" send="'.($fbSendButton?true:false).'" width="'.$fbwidth.'" show_faces="false" font=""></fb:like>';
						break;
						case 'vertical': 
							$row->fb_button.='<div id="fb-root"></div><fb:like layout="box_count" href="'.$fbUrl.'" send="'.($fbSendButton?true:false).'" width="'.$fbwidth.'" show_faces="false" font=""></fb:like>';
						break;
					}					
									
			}
								
		}
		
		/*** Google + ***/
		$usegp = $_JB_CONFIGURATION->get('usegp');
		$row->gp_button = null;
		$row->gpposition = $_JB_CONFIGURATION->get('positiongpInPost');
		
		if ($usegp)
		{
			$showGpInPost = $_JB_CONFIGURATION->get('showgpInPost');
			$gpLang 	= $_JB_CONFIGURATION->get('gp_language');
			if (isset($gpLang)) $gpLang="{lang: '" . $gpLang . "'}"; else $gpLang='';
			
			if ($showGpInPost)
			{
				$gpStyle 		= $_JB_CONFIGURATION->get('gp_style_post');
				$gpUrl 	= $row->permalink;	
				
				//$row->gp_button='<script type="text/javascript" src="https://apis.google.com/js/plusone.js">'.$gpLang.'</script>';

					switch ( $gpStyle ) 
					{
						case 'none': 
							$row->gp_button.='<g:plusone size="medium" href="'.$gpUrl.'"></g:plusone>';
						break;
						case 'horizontal': 
							$row->gp_button.='<g:plusone count="false" href="'.$gpUrl.'"></g:plusone>';
						break;
						case 'vertical': 
							$row->gp_button.='<g:plusone size="tall" href="'.$gpUrl.'"></g:plusone>';
						break;
					}					
									
			}
								
		}


		/*** StumbleUpon ***/

		$usesu = $_JB_CONFIGURATION->get('usesu');
		$row->su_button = null;
		$row->suposition = $_JB_CONFIGURATION->get('positionsuInPost');

		if ($usesu)
		{
			$showSuInList = $_JB_CONFIGURATION->get('showsuInPost');
			if ($showSuInList)
			{
				$suStyle 		= preg_replace('/[^\d]+/i', '', $_JB_CONFIGURATION->get('su_style_post'));
				$row->su_button.='<su:badge layout="'.$suStyle.'" location="'.$row->permalink.'"></su:badge>';
			}

		}

        /*** Pinterest ***/

        $usepi = $_JB_CONFIGURATION->get('usepi');
        $row->pi_button = null;
        $row->piposition = $_JB_CONFIGURATION->get('positionpiInPost');

        if ($usepi) {
            $showPiInPost = $_JB_CONFIGURATION->get('showpiInPost');
            $piLang 	= $_JB_CONFIGURATION->get('pi_language');
            $piLang = (isset($piLang)) ? "{lang: '" . $piLang . "'}" : '';

            if ($showPiInPost)
            {
                $piStyle        = $_JB_CONFIGURATION->get('pi_style_post');
                $piUrl          = $row->permalink;
                $piDescription  = $row->title;
                preg_match_all('#<img.*?src=["\']*([\S]+)["\'].*?>#', $row->introtext . $row->fulltext, $piImageTemp);
                /*'#<img.*?src=["\']*([\S]+)["\'].*?>#si'*/
                $piImage        = ($piImageTemp == '') ? 'none' : '&media=' . @$piImageTemp[1][0];
                $pi_url_text    = '<a href="http://pinterest.com/pin/create/button/?url=' . $piUrl . $piImage . '&description=' . $piDescription . '" class="pin-it-button" count-layout="' . $piStyle . '"><img border="0" src="http://assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>';
                $row->pi_button .= $pi_url_text;
                $row->pi_button .= '<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js">' . $piLang . '</script>';
            }
        }

        //

		/*** Linkedin ***/
		$useln = $_JB_CONFIGURATION->get('useln');
		$row->ln_button = null;
		$row->lnposition = $_JB_CONFIGURATION->get('positionlnInPost');
		
		if ($useln)
		{
			$showLnInPost = $_JB_CONFIGURATION->get('showlnInPost');
			
			if ($showLnInPost)
			{
				$lnStyle 		= $_JB_CONFIGURATION->get('ln_style_post');
				$lnUrl 	= $row->permalink;	
				
				//$row->ln_button='<script src="http://platform.linkedin.com/in.js" type="text/javascript"></script>';
				
					switch ( $lnStyle ) 
					{
						case 'none': 
							$row->ln_button.='<script type="IN/Share" data-url="'.$lnUrl.'"></script>';
						break;
						case 'horizontal': 
							$row->ln_button.='<script type="IN/Share" data-url="'.$lnUrl.'" data-counter="right"></script>';
						break;
						case 'vertical': 
							$row->ln_button.='<script type="IN/Share" data-url="'.$lnUrl.'" data-counter="top"></script>';
						break;
					}					
									
			}
								
		}
		
		/*** AddThis ***/
		$useat = $_JB_CONFIGURATION->get('useAddThis');
		$row->at_button = null;
		$row->atposition = $_JB_CONFIGURATION->get('addThisPostPosition');
		
			if ($useat)
				if ($_JB_CONFIGURATION->get('showAddThisInPost') == 1)
				{						
				$sefUrl = $row->permalink;
				$host = $_JB_CONFIGURATION->get('addThisName');
				$button_style = $_JB_CONFIGURATION->get('addthis_post_button_style');

					$services = array();
					
					if ($button_style == 'style2'){	$add = '<!-- AddThis Button BEGIN --><div class="addthis_toolbox addthis_default_style addthis_32x32_style">';} 
					else if ($button_style == 'style9' || $button_style == 'style10'){ $add = '';} else {$add = '<!-- AddThis Button BEGIN --><div class="addthis_toolbox addthis_default_style ">';}
					
					$add .= socialDefaultAdd($button_style, $sefUrl, $host);
					$row->at_button = $add;
				}
	//

			
			$tpl->set('entry', $tpl->object_to_array($row));
			$this->fbOpengraphMeta($tpl->object_to_array($row));
			$prevHTML = ''; $nextHTML = '';
			if ($this->prev)
			{
				$temp	= JTable::getInstance( 'Posts' , 'Table' );
				$temp->load( $this->prev );
				$prevHTML .= '<a href="'.JRoute::_('index.php?option=com_joomblog&show='.$this->prev.'&Itemid='.$Itemid).'" title="View previous post: '.$temp->title.'">'.$temp->title.'</a>';
				$tpl->set( 'prevURI' , JRoute::_('index.php?option=com_joomblog&show='.$this->prev.'&Itemid='.$Itemid) );
				$tpl->set( 'prevTitle' , $temp->title );
			}
			
			$tpl->set( 'prev' 	, $prevHTML );
			unset($temp);
			
			if ($this->next)
			{
				$temp	= JTable::getInstance( 'Posts' , 'Table' );
				$temp->load( $this->next );
				$nextHTML .= '<a href="'.JRoute::_('index.php?option=com_joomblog&show='.$this->next.'&Itemid='.$Itemid).'" title="View next post: '.$temp->title.'">'.$temp->title.'</a>';
				$tpl->set( 'nextURI' , JRoute::_('index.php?option=com_joomblog&show='.$this->next.'&Itemid='.$Itemid) );
				$tpl->set( 'nextTitle' , $temp->title );
			}
			
			$tpl->set( 'next' 	, $nextHTML );
			if($_JB_CONFIGURATION->get('showRandomPost') == '1'){
			    $postModel = $this->getModel('Post');
			    $rand = $postModel->getRandomItem($row->id);
			    if(!empty($rand) && count($rand)>0){
				$tpl->set( 'randURI' , JRoute::_('index.php?option=com_joomblog&show=' . $rand[0] . '&Itemid='.$Itemid) );
			    }
			}

		}
		
		$content	= '';
		
		$path	= $this->_getTemplateName( 'entry' );
		$content .= $tpl->fetch_cache( $path );
		return $content;		
	}
	
	protected function isFriends($id1=0,$id2=0)
	{
		$db	= JFactory::getDBO();
		$db->setQuery(	" SELECT `connection_id` FROM `#__community_connection` " .
						" WHERE connect_from=".(int)$id1." AND connect_to=".(int)$id2." AND `status`=1 ");
		$frindic = $db->loadResult();
		if ($frindic) return true; else return false;				
	}
	private function fbOpengraphMeta($e){
	    global $JBBLOG_LANG, $_JB_CONFIGURATION;
	    $fbadmin = $_JB_CONFIGURATION->get('fbadmin');
	    $fbappid = $_JB_CONFIGURATION->get('fbappid');
	    $permalink = $e['permalink'];
	    $meta_title = $e['title'];
	    $metadata_json = json_decode($e['metadata']);
	    if(empty($metadata_json)){
		$metadata_json = new stdClass();
		$metadata_json->page_image = '';
		$metadata_json->ogdesc = '';
	    }
	    if(empty($metadata_json->page_image) && !empty($e['defaultimage'])) $metadata_json->page_image = JURI::root().$e['defaultimage'];
	    $page_image = $metadata_json->page_image;
	    $meta_description = $metadata_json->ogdesc;
	    $meta_author = 'http://' . $_SERVER['HTTP_HOST'] . $e['authorLink'];
	    $meta_articletags = array ();
	    $publish_time_array = explode(" ", $e['publish_up']); 
	    $meta_articlepublish = $publish_time_array[0];
	    foreach ($e['tagobj'] as $article_tag)
	    {
		    array_push ($meta_articletags, $article_tag['name']);
	    }
	    $default_blogpost_image = $_JB_CONFIGURATION->get('page_image');
	    if (empty($page_image) AND !empty($default_blogpost_image))
	    {
	    $page_image = JURI::base().'images/'.$default_blogpost_image ;
	    }
	    if (!empty($page_image))
	    {
		    list($width, $height) = @getimagesize($page_image);
	    }else{
		$width = 0;
		$height = 0;
	    }

	    if ($width > 199 AND $height > 199)
	    {
		    $meta_image = $page_image;
	    }
	    else
	    {
		    $meta_image = JURI::root() . 'media/com_joomblog/images/spacer.png';	
	    }
	    
	    $meta_updated_time = $e['modified'];

	    $doc = JFactory::getDocument();
	    $doc->addCustomTag($this->getMetatags($permalink, $meta_title, $meta_image, $meta_description, $meta_updated_time, $fbadmin, $fbappid, $meta_author, $meta_articletags, $meta_articlepublish));
	}
	
	private function getMetatags($permalink, $meta_title, $meta_image, $meta_description, $meta_updated_time, $fbadmin, $fbappid, $meta_author, $meta_articletags, $meta_articlepublish) {
	    $metatags = '';
	    if ($fbadmin !=='') $metatags = "<meta property=\"fb:admins\" content=\"$fbadmin\" />\n";
	    if ($fbappid !=='') $metatags .= "<meta property=\"fb:app_id\" content=\"$fbappid\" />\n";
	    $metatags .= "<meta property=\"og:url\" content=\"$permalink\" />\n";
	    $metatags .= "<meta property=\"og:title\" content=\"$meta_title\" />\n";
	    $metatags .= "<meta property=\"og:image\" content=\"$meta_image\" />\n";
	    $metatags .= "<meta property=\"og:description\" content=\"$meta_description\" />\n";
	    $metatags .= "<meta property=\"og:updated_time\" content=\"$meta_updated_time\" />\n";
	    $metatags .= "<meta property=\"og:type\" content=\"article\" />\n";
	    $metatags .= "<meta property=\"article:author\" content=\"$meta_author\" />\n";
	    $metatags .= "<meta property=\"article:published_time\" content=\"$meta_articlepublish\" />\n";
	    if (!empty($meta_articletags))
	    {
		    for ($i = 0; $i < count($meta_articletags); $i++)
		      {
			$metatags .= "<meta property=\"article:tag\" content=\"$meta_articletags[$i]\" />\n";
		      } 
	    }
	    return $metatags;
	}
}
