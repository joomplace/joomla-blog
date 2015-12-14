<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

class JFormFieldAjaxavatar extends JFormField
{
	protected $type = 'Ajaxavatar';
	
	public function getInput() {
	    JHtml::script(JURI::base().'components/com_joomblog/assets/jplace.jquery.js');
	    JHTML::script(JURI::root().'components/com_joomblog/assets/file-upload/js/vendor/jquery.ui.widget.js');
	    JHTML::script(JURI::root().'components/com_joomblog/assets/file-upload/js/jquery.iframe-transport.js');
	    JHTML::script(JURI::root().'components/com_joomblog/assets/file-upload/js/jquery.fileupload.js');
	    JFactory::getDocument()->addStyleDeclaration('
		.file-input-button {
		    cursor: pointer;
		    direction: ltr;
		    font-size: 50px !important;
		    height: 100% !important;
		    margin: 0 !important;
		    opacity: 0;
		    padding: 0 !important;
		    position: relative;
		    right: 0;
		    top: 0;
		    transform: translate(-300px, 0px) scale(4);
		    width: 100% !important;
		}
		.avatarProgressContainer{
		    border: 1px solid #CCCCCC;
		    color: #CCCCCC !important;
		    height: 100px;
		    width: 100px;
		    display: block;
		    text-decoration: none;
		    overflow: hidden;
		    margin: 0 !important;
		    padding: 0 !important;
		    cursor: hand;
		    cursor: pointer;
		    text-align: center;
		    vertical-align: middle;
		    background: url(\''.JURI::root().'components/com_joomblog/assets/file-upload/img/loading.gif\') no-repeat 50% 50%;
		    background-size: 100% Auto;
		}
		');

	    JFactory::getDocument()->addCustomTag('<!--[if gte IE 8]><script src="'.JURI::root().'components/com_joomblog/assets/file-upload/js/cors/jquery.xdr-transport.js"></script><![endif]-->');
	    ob_start();
	?>
    (function($) {
     $(document).ready(function () {
     deleteAvatar = function(el){
	$.ajax({
	    url: '<?php echo(JRoute::_('index.php?option=com_joomblog&task=bloggerpref')); ?>',
	    type: 'POST',
	    data: { removeAvatar: 'true'}
	});
	$('#avatarUploadButton').css('display','block');
	$('#avatarUploadImage').css('display','none');
	$('#jform_photo').val('');
     };
     
     $('#avatarUpload').fileupload({
	sequentialUploads: false,
        dataType: 'json',
	submit: function (e, data) {
		$('#avatarUploadButton').css('display','none');
		$('#avatarProgressContainer').css('display','block');
	},
        done: function (e, data) {
	    if(data.result.status == 'ok'){	   
		$('#avatarUploadImage').html('<img src="<?php echo JURI::root().'images/joomblog/avatar/';?>'+data.result.image+'" alt="'+data.result.image+'"/><span class="jb-del-img" image="'+data.result.image+'" onclick="deleteAvatar(this);"></span>');
		$('#avatarProgressContainer').css('display','none');
		$('#avatarUploadImage').css('display','block');
		$('#jform_photo').val('images/testimonials/'+data.result.image);
		$('#avatarUploadImage > img').load(function(){
		    $('#avatarUploadImage > img').css('height','100%');
		    $('#avatarUploadImage > img').css('width','100%');
		});
	    }
	    if(data.result.status == 'bad'){
		if(data.result.message != '') alert(data.result.message);
		$('#avatarProgressContainer').css('display','none');
		$('#avatarUploadButton').css('display','block');
	    }
        } 
    });
   });
   })(jplace.jQuery);
	    <?php
		$jQuery = ob_get_contents();
		ob_end_clean();
		JFactory::getDocument()->addScriptDeclaration($jQuery);
		$input = '';
		
		if ($this->value) {
			$input .= '
			<div class="jb-add-image jb-profdet-item">                            
			    <div id="avatarProgressContainer" class="avatarProgressContainer" style="display: none;"><div id="imageProgress" class="imageProgress"></div></div>
                            <a href="javascript:void(0)" class="jb-img" onclick="deleteAvatar(this);" id="avatarUploadImage"><img src="'.JURI::root().'images/joomblog/avatar/'.$this->value.'" alt="" style="width: 100%; height: 100%;" /><span class="jb-del-img"></span></a>
			    <div class="jb-add-img" id="avatarUploadButton" style="display: none;"><span class="jb-add-img-label">'.JText::_('COM_JOOMBLOG_BLOG_ADMIN_ADD_AVATAR').'</span><input type="file" name="Filedata" id="avatarUpload" data-url="'.JRoute::_('index.php?option=com_joomblog&task=bloggerpref').'" class="jb-file-input-button" /></div>
                        </div>';
		} else {
			$input .= '
			<div class="jb-add-image jb-profdet-item">
				<div id="avatarProgressContainer" class="avatarProgressContainer" style="display: none;"><div id="imageProgress" class="imageProgress"></div></div>
				<a href="javascript:void(0)" class="jb-img" onclick="deleteAvatar(this);" id="avatarUploadImage" style="display:none"></a>
				<div class="jb-add-img" id="avatarUploadButton"><span class="jb-add-img-label">'.JText::_('COM_JOOMBLOG_BLOG_ADMIN_ADD_AVATAR').'</span><input type="file" name="Filedata" id="avatarUpload" data-url="'.JRoute::_('index.php?option=com_joomblog&task=bloggerpref').'" class="jb-file-input-button" /></div>
                        </div>';
		}
		
		$input .= '<input type="hidden" name="jform[photo]" id="jform_photo" value="'.(!empty($item->value) ? $item->value : '').'" />';
		
		return $input;
	}
	
	public function setValue($value){
	    $this->value = $value;
	}
}
