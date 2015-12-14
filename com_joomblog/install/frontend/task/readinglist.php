<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php' );

class JbblogReadingListTask extends JbblogBaseController{

	function display(){

		global $_JB_CONFIGURATION;
		$option = 'com_joomblog';
		$mainframe	= JFactory::getApplication();
		$db			= JFactory::getDBO();
		$user	= JFactory::getUser();
		$user_id = (int)$user->id;
		$query = JFactory::getDbo()->getQuery(true);
		$query->select(" `u`.post_count, `u`.reading_list");
		$query->from("#__joomblog_user AS u");
		$query->where('u.user_id ='.$user_id);
		$db->setQuery($query);
		$itemId = JFactory::getApplication()->input->get('itemid');
		$user_settings = $db->loadObject();
		if (!empty($user_settings) AND !empty($user_settings->reading_list))
		{
			$limitstart = 0;
			$blog_id = JFactory::getApplication()->input->get('blog_id');
			$limit = JFactory::getApplication()->input->get('limit');
			if (!$limit) $limit = $user_settings->post_count;
			$query = JFactory::getDbo()->getQuery(true);
			$query->select(" `ps`.id, `ps`.title, `ps`.introtext, `ps`.created, `ps`.defaultimage, `lb`.title as blog_title, `lb`.id as blog_id, `u`.id as user_id, `u`.username, `u`.name ");
			$query->from("#__joomblog_posts AS ps");
			$query->join("LEFT", "#__joomblog_blogs AS jb ON jb.content_id = ps.id");
			$query->join("LEFT", "#__joomblog_list_blogs AS lb ON jb.blog_id = lb.id");
			$query->join("LEFT", "#__users as u ON u.id = ps.created_by");
			$query->where('ps.state = 1');
			if ($blog_id) $query->where('lb.id IN ('.$blog_id.')');
			else $query->where('lb.id IN ('.$user_settings->reading_list.')');
			$query->order('ps.created DESC');
			$db->setQuery($query,$limitstart,$limit);
			$posts = $db->loadObjectList();
			if (!empty($posts))
			{
				foreach ($posts as $post)
				{
					//Get created string
					$date_dif = date_diff(new DateTime(), new DateTime($post->created))->days;
					switch($date_dif) {
						case ($date_dif<1):
							$published = Jtext::_('COM_JOOMBLOG_TODAY');
							break;
						case ($date_dif==1):
							$published = Jtext::_('COM_JOOMBLOG_YESTERDAY');
							break;
						case ($date_dif<7 AND $date_dif>1):
							$published = $date_dif.' '.Jtext::_('COM_JOOMBLOG_DAYS_AGO');
							break;
						case ($date_dif<14 AND $date_dif>=7):
							$published = '1 '.Jtext::_('COM_JOOMBLOG_WEEK_AGO');
							break;
						case ($date_dif<21 AND $date_dif>=14):
							$published = '2 '.Jtext::_('COM_JOOMBLOG_WEEKS_AGO');
							break;
						case ($date_dif<30 AND $date_dif>=21):
							$published = '3 '.Jtext::_('COM_JOOMBLOG_WEEKS_AGO');
							break;
						case ($date_dif<60 AND $date_dif>=30):
							$published = '1 '.Jtext::_('COM_JOOMBLOG_MONTH_AGO');
							break;
						case ($date_dif<90 AND $date_dif>=60):
							$published = '2 '.Jtext::_('COM_JOOMBLOG_MONTHS_AGO');
							break;
						default:
							$date = JFactory::getDate($post->created);
							$published = $date->format('M d, Y');
							break;
					}
					echo '<div class="jb-posts-in-blog">';
                        echo '<div class="jb-blog-contouter">';
                            echo '<div class="jb-blog-continner">';
                                echo '<h4><a href="'.JRoute::_(Juri::root().'index.php?option=com_joomblog&blogid='.$post->blog_id.'&show='.$post->id.'&Itemid='.$itemId, false).'">'.$post->title.'</a></h4>';
                                echo '<div class="jb-post-stat">';
                                    echo '<span>'.$post->name.' at </span><a href="'.JRoute::_(Juri::root().'index.php?option=com_joomblog&blogid='.$post->blog_id.'&view=blogger&Itemid='.$itemId, false).'">'.$post->blog_title.'</a><span> - '.$published.'</span>';
                                echo '</div>';
                                echo '<div class="jb-readinglist-postcontent clearfix">';
                                    if(!empty($post->defaultimage)) {
                                    	echo '<div  class="jb-readinglist-postimgcont">';
	                                    	if (strpos($post->defaultimage,'http:') === false) { echo '<img class="jb-readinglist-postimg" src="'.Juri::root().$post->defaultimage.'" alt=""/>'; } 
    	                                	else { echo '<img class="jb-readinglist-postimg" src="'.$post->defaultimage.'" alt=""/>'; }
                                    	echo '</div>';
                                    }
                                   
	                                echo '<div class="jb-readinglist-text">';
	                                    $post_text = preg_replace("/<img[^>]+\>/i", " ", $post->introtext);
	                                    $ending = JString::strpos($post_text, '</p>');
										$pos=-1;
										$pos_array = array();
										while ( ($pos=JString::strpos($post_text,'</p>',$pos+2)) !== false ) 
											$pos_array[]=$pos;
										$pNum = $_JB_CONFIGURATION->get('autoReadmorePCount');
										if (count($pos_array) <= $pNum) { $post_text = $post_text; } 
										else {
											$ending = $pos_array[$pNum-1];
											$post_text = JString::substr($post_text, 0, $ending + 4);
											$post_text = jbCloseTags(preg_replace('#\s*<[^>]+>?\s*$#','',$post_text));
										}
										//check div in post text
										$div_matches_count = 0;
										$closed_div_count = 0;
										preg_match_all ("/<div/i",$post_text,$div_matches);
										if ($div_matches[0]) $div_matches_count = count($div_matches[0]);
										preg_match_all ("/<\/div>/i",$post_text,$closed_div_matches);
										if ($closed_div_matches[0]) $closed_div_count = count($closed_div_matches[0]);
										if ((int)$closed_div_count < (int)$div_matches_count)
										{
											$k = (int)$div_matches_count - (int)$closed_div_count;
											for ($j=0; $j<$k; $j++)
											{
												$post_text .= '</div>';
											}											
										}
	                                    echo $post_text;
	                                echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
				}

				if ( sizeof($posts) < $limit )
				{
					echo '<script type="text/javascript">document.getElementById("more_posts_button").innerHTML= "";</script>';
				}
			}
			else
			{
				echo '<div class="jb-posts-in-blog">';
				  echo '<div class="jb-blog-contouter">';
                    echo '<div class="jb-blog-continner">';
                    echo '</div>';
                  echo '</div>';
                echo '</div>';
                echo '<script type="text/javascript">
	            document.getElementById("current_blog_title").innerHTML= "";
	            document.getElementById("more_posts_button").innerHTML= "";
	            document.getElementById("jb-title-seporator").innerHTML= "";
	            </script>';
			}
		}
		else
		{
			echo '<div class="jb-posts-in-blog">';
			  echo '<div class="jb-blog-contouter">';
                echo '<div class="jb-blog-continner">';
                echo '</div>';
              echo '</div>';
            echo '</div>';
            echo '<script type="text/javascript">
            document.getElementById("current_blog_title").innerHTML= "";
            document.getElementById("more_posts_button").innerHTML= "";
            document.getElementById("jb-title-seporator").innerHTML= "";
            </script>';
		}

		die;
	}
}