<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

class XMLHelper
{
	public static function addDomElement($data, $root, $domObj){
	    if(is_array($data) || is_object($data)){
		$element = $domObj->createElement($root);
		foreach($data as $key=>$value){
		    if(is_numeric($key)) $key = 'item';
		    $element->appendChild(self::addDomElement($value, $key, $domObj));
		}
		return $element;
	    }else{
		if(!preg_match('|^[\w]+$|', $data)){
		    $element = $domObj->createElement($root);
		    $element->appendChild($domObj->createCDATASection($data));
		}else{
		    $element = $domObj->createElement($root, $data);
		}
		return $element;
	    }
	}
	
	public static function domToObject($domNode){
	    $resultObj = new stdClass();
	    for ($i = 0; $i < $domNode->childNodes->length; $i++){
		  $item = $domNode->childNodes->item($i);
		  if ($item->nodeType == XML_ELEMENT_NODE){
		      $children =self::domToObject($item);
		      if($item->nodeName == 'blog' || $item->nodeName == 'item'){
			  if(!empty($children)){
			    if(empty($resultObj)) $resultObj = $children;
			    else{
				if(is_array($resultObj)) $resultObj[] = $children;
				else $resultObj = array($children);
			    }
			  }
		      }else{
			    if(empty($resultObj->{$item->nodeName})) $resultObj->{$item->nodeName} = $children;
			    else{
				if(is_array($resultObj->{$item->nodeName})) $resultObj->{$item->nodeName}[] = $children;
				else{
				    $temp = $resultObj->{$item->nodeName};
				    unset($resultObj->{$item->nodeName});
				    $resultObj->{$item->nodeName} = array($temp, $children);
				}
			    }
		      }
		  }else if ($item->nodeType == XML_CDATA_SECTION_NODE || ($item->nodeType == XML_TEXT_NODE && trim($item->nodeValue) != '')){
		      return $item->nodeValue;
		  }
	    }
	    return $resultObj;
	}
}
?>