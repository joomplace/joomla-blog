<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JBOptionSetup
{
	var $data;
	var $current;

	function create()
	{
		$this->current = 0;
		$this->data = array();
	}

	function add($data)
	{
		$this->data[$this->current][] = $data;
	}

	function add_section($title, $desc='')
	{
    $this->current++;
		$this->data[$this->current][0] = array('title' => $title, 'desc' => $desc);
	}


	function get_html()
	{
		$cfgcount	= 0;
		$i        = 1;      
      
		$trclass = '';
		  
		$html = '';  
		$html .= '<div class="width-100">';

		foreach($this->data  as $cfg)
		{
		    $anchor = strtolower(str_replace(" ","_",$cfg[0]['title']));
			
			$html .= '<fieldset class="adminform"><legend>'.$cfg[0]['title'].'</legend><ul class="adminformlist">';
			
			for($i = 1; $i < count($cfg); $i++)
			{
				$text = "";

				$html .= '<li>';
				
				$onclick = isset($cfg[$i]['onclick']) ? ' onclick="'.$cfg[$i]['onclick'].'" ' : '';

				$title = @$cfg[$i]['desc']?$cfg[$i]['title'].'::'.htmlentities($cfg[$i]['desc']):"";
				
				if($title){
					$img = "<img style='padding:0;margin:0px 5px 0px 0px;' src = '".JURI::base()."components/com_joomblog/images/icon-16-info.png' />";
				}else{
					$img = "";
				}

				$html .= '<label title="'.$title.'" class="'.($title?"hasTip":"").' required" for="jform_'.$cfg[$i]['name'].'" id="jform_'.$cfg[$i]['name'].'-lbl">'.$img.$cfg[$i]['title'].'</label>';
				
				switch($cfg[$i]['type'])
				{
					case 'checkbox':
						$html.= '<input '.$onclick.'class="cfgdesc" type="checkbox" name="'.$cfg[$i]['name'].'" value="1" id="'.$cfg[$i]['name'].'" ';

						if($cfg[$i]['value'])
							$html.= ' checked="checked" ';

						$html.= '/>';
						break;

					case 'text':

						$html.= '<input '.$onclick.'type="text" value="'.$cfg[$i]['value'].'" name="'.$cfg[$i]['name'].'" id="'.$cfg[$i]['name'].'" class="inputbox" ';

						$html .= isset($cfg[$i]['size']) ? ' size="'.$cfg[$i]['size'].'" ' : '';
						$html .= isset($cfg[$i]['maxlength']) ? ' maxlength="'.$cfg[$i]['maxlength'].'" ' : '';

						$html .= '/>';

						break;

					case 'textarea':
						$html.= '<textarea '.$onclick.'name="'.$cfg[$i]['name'].'" id="'.$cfg[$i]['name'].'" ';

						$html .= isset($cfg[$i]['cols']) ? ' cols="'.$cfg[$i]['cols'].'" ' : '';
						$html .= isset($cfg[$i]['rows']) ? ' rows="'.$cfg[$i]['rows'].'" ' : '';

						$html .= '>'.stripslashes($cfg[$i]['value']).'</textarea>';
						break;
					case 'select':
					    if(is_array($cfg[$i]['value'])){
						    $html   .= '<select name="' . $cfg[$i]['name'] . '" id="' . $cfg[$i]['name'] . '" class="inputbox"';
							$html   .= ($cfg[$i]['size'] > 1) ? ' multiple="multiple"': '';
							$html   .= ' size="' . $cfg[$i]['size'] . '">';

							foreach($cfg[$i]['value'] as $key => $val){
							    if(is_array($cfg[$i]['selected'])){

							        if(in_array($key,$cfg[$i]['selected'])){
										$html   .= '<option value="' . $key .'" selected="selected">' . $val . '</option>';
									}
									else{
									    $html   .= '<option value="' . $key .'">' . $val . '</option>';
									}
								}
								else{

									if($key == $cfg[$i]['selected']){
									    $html   .= '<option value="' . $key .'" selected="selected">' . $val . '</option>';
									}
									else{
									    $html   .= '<option value="' . $key .'">' . $val . '</option>';
									}
								}
							}
							
							$html .= '</select>';

						}
						else{
						    $html   .= '<font color="red">Need associative array input of values</font>';
						}
					    break;
					    
					case 'radio':
						break;
					case 'custom':
					    $html   .=  $cfg[$i]['value'];
					    break;
				}

				$html .= '</li>';
			}
			$html .= '</ul></fieldset>';
			
			$cfgcount ++;
		}

		$html .= '</div>';

		return $html;
	}
}