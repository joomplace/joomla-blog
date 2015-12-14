var jb_JQuery = jQuery.noConflict();
(function($){
$(document).ready(function(){
    
    
    
    /*add post blocks slideDown*/
    $('.jb-nav-tabs>li>a').click(function(){
                               
        if($(this).next().css('display') == 'none'){
            var index = $('.jb-nav-tabs>li>a').index($(this));
            $('.jb-nav-tabs>li>a').removeClass('jb-btn-primary');
            $(this).addClass('jb-btn-primary');
            $('.jb-tab-pane').slideUp('3000');
            $('.jb-nav-tabs>li>a>span.jb-caret').removeClass('jb-caret-up');
            $('.jb-nav-tabs>li>a>span.jb-caret').addClass('jb-caret-down');
            $(this).next().slideDown('3000');
            //console.log($(this).next());
            $(this).children('span.jb-caret').removeClass('jb-caret-down');
            $(this).children('span.jb-caret').addClass('jb-caret-up');
        }
        else{
            $(this).removeClass('jb-btn-primary');
            $(this).next().slideUp('3000');
            $(this).children('span.jb-caret').removeClass('jb-caret-up');
            $(this).children('span.jb-caret').addClass('jb-caret-down');
        }
        return false;
    });
           
           
  
   /*show-more link on dashboard page*/
   if ($('.jb-author-desc-short').height() < 48){
       $('.jb-read-more-link').addClass('jb-read-more-link-hide');
       $('.jb-author-desc-inner').removeClass('jb-author-desc-inner');
   }
   
        $('.jb-read-more-link').click(function(){
           if($(this).parents('.jb-author-desc').hasClass('jb-author-desc-short')){
                $(this).parents('.jb-author-desc').removeClass('jb-author-desc-short');
                $(this).parents('.jb-author-desc').addClass('jb-author-desc-long');
                $(this).html('hide').css({'display': 'block', 'width': '99%', 'text-align': 'right' });                
                return (false);  
           }
            else {
                $(this).parents('.jb-author-desc').removeClass('jb-author-desc-long');
                $(this).parents('.jb-author-desc').addClass('jb-author-desc-short');
                $(this).html('more ...').css({'display': 'inline-block', 'width': 'auto'});
                return (false);
            }

            
        });
        /*show-more link on dashboard page end*/
       
        
        /*drop-down menu blog list in JB control pannel*/          
           $('#dashboard .jb-btns-dropdown>.jb-dropdown-toggle').click(function(){
              
              switch($(this).parent().find('.jb-dropdown-menu').css('display')){
                  case 'none':
                $(this).parent().find('.jb-dropdown-menu').css({ 'display':'block' })                
                break
                  case 'block':
                $(this).parent().find('.jb-dropdown-menu').css({ 'display':'none' })
                break
                    default:
                $(this).parent().find('.jb-dropdown-menu').css({ 'display':'none' })

              }                   
           });    
                 
           $('.jb-btns-dropdown > .jb-show-info').click(function(){
              switch($('.jb-btns-dropdown').find('.jb-dropdown-menu').css('display')){
                  case 'none':
                      
                $('.jb-btns-dropdown').find('.jb-dropdown-menu').css({ 'display':'block' })
                break;
                  case 'block':
                $('.jb-btns-dropdown').find('.jb-dropdown-menu').css({ 'display':'none' })
                break;
                    default:
                $('.jb-btns-dropdown').find('.jb-dropdown-menu').css({ 'display':'none' })
              } 
				return(false);
           });
           
           
           
           /*checking of post in JB control pannel - posts*/
           
		   $('.jb-outer-postslist').click(function(){
               if($(this).hasClass('jb-outer-postslist-active')){
                   $(this).removeClass('jb-outer-postslist-active');
                   $(this).find('.jb-check-post').attr('checked', false);
                   $(this).find('.jb-icon-checkbox-checked').removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
                   $('.jb-check-all').attr('checked', false);
                   $('.jb-check-all').removeClass('jb-check-all-on');
                   $('.jb-check-all').find('.jb-icon-checkbox-checked').removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
				   return;
               }
               else{
                   $(this).addClass('jb-outer-postslist-active');
                   $(this).find('.jb-check-post').attr('checked', true);
                   $(this).find('.jb-icon-checkbox-unchecked').removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
                   
                   var allTrue = 1;
                   $('.jb-checkpost-list').each(function(){
                       if($(this).is(':checked') == false){
                           allTrue = allTrue * 0;
                       }
                   });
                   if (allTrue == 1){
                       $('.jb-check-all').attr('checked', true);
                       $('.jb-check-all').addClass('jb-check-all-on');
                       $('.jb-check-all > .jb-icon-checkbox-unchecked').removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
                   }
                   return;
                  
               }
           });  
		   
		   
		   $('.comment-label').click(function(){
               if($(this).parents('.jb-outer-postslist').hasClass('jb-outer-postslist-active')){			   
                   $(this).parents('.jb-outer-postslist').removeClass('jb-outer-postslist-active');
                   $(this).parents('.jb-outer-postslist').find('.jb-check-post').attr('checked', false);
                   $(this).parents('.jb-outer-postslist').find('.jb-icon-checkbox-checked').removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
                   $('.jb-check-all').attr('checked', false);
                   $('.jb-check-all').removeClass('jb-check-all-on');
                   $('.jb-check-all').find('.jb-icon-checkbox-checked').removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
				   return;
               }
               else{
                   $(this).parents('.jb-outer-postslist').addClass('jb-outer-postslist-active');
                   $(this).parents('.jb-outer-postslist').find('.jb-check-post').attr('checked', true);
                   $(this).parents('.jb-outer-postslist').find('.jb-icon-checkbox-unchecked').removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
                   
                   var allTrue = 1;
                   $('.jb-checkpost-list').each(function(){
                       if($(this).is(':checked') == false){
                           allTrue = allTrue * 0;
                       }
                   });
                   if (allTrue == 1){
                       $('.jb-check-all').attr('checked', true);
                       $('.jb-check-all').addClass('jb-check-all-on');
                       $('.jb-check-all > .jb-icon-checkbox-unchecked').removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
                   }
                   return;
                  
               }
           });
		   
           $('.jb-check-all').click(function(){
              
               if($(this).hasClass('jb-check-all-on')){
                   
                   $(this).removeClass('jb-check-all-on');
                   $(this).find('.jb-check-all').attr('checked', false);
                   $(this).find('.jb-icon-checkbox-checked').removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
                                       
                    $('.jb-postslist-container').find('.jb-icon-checkbox-checked').each(function(){
                        $(this).removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
                    });

                    $('.jb-postslist-container').find('.jb-outer-postslist').each(function(){
                        $(this).removeClass('jb-outer-postslist-active');
                        $(this).find('.jb-check-post').attr('checked', false);
                    });
                  
               }
                else{   
                    
                    $(this).addClass('jb-check-all-on');
                    $(this).find('.jb-check-all').attr('checked', true);
                    $(this).find('.jb-icon-checkbox-unchecked').removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
                                       
                    $('.jb-postslist-container').find('.jb-icon-checkbox-unchecked').each(function(){
                    $(this).removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
                    });
                    
                     $('.jb-postslist-container').find('.jb-outer-postslist').each(function(){
                        $(this).addClass('jb-outer-postslist-active');
                        $(this).find('.jb-check-post').attr('checked', true);
                    });
                }  
           });
           
           
           
           /*checking blogs in popup*/
          $('#blogs_list').find('.jb-blogs-list').each(function(){             
               if ($(this).attr('checked')){
                   
                   $(this).next().removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');
               }
               else{
                   $(this).next().removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');
               }
           });
          
          $('.jb-blogs-list-container').click(function(){             
               if($(this).find('.jb-blogs-list').attr('checked')){                  
                   $(this).find('i').removeClass('jb-icon-checkbox-checked').addClass('jb-icon-checkbox-unchecked');                   
                   $(this).find('.jb-blogs-list').attr('checked', false)
               }
               else{
                   $(this).find('i').removeClass('jb-icon-checkbox-unchecked').addClass('jb-icon-checkbox-checked');                    
                   $(this).find('.jb-blogs-list').attr('checked', true)
               }
           });
           /*checking blogs in popup end*/
           
           
           
           
           /*z-index for dropdown-menu on blog-panel page in ie7*/
           $(function() {
                var zIndexNumber = 1000;
                // Put your target element(s) in the selector below!
                $(".with-js .jb-blog-contouter").each(function() {
                        $(this).css('zIndex', zIndexNumber);
                        zIndexNumber -= 10;
                });
         });
           /*z-index for dropdown-menu on blog-panel page in ie7 end*/
       
       
       
       
  
  
           
    /*comment blocks slideDown*/
/**
@todo: Show comment block smooth. Set focus to comments textarea when shown.
*/    
    //for unregistered users
    $('#jb-instead-editor').click(function(){ 
        $('.jb-unreg-fields').css('display', 'block');
        $(this).css('display', 'none');
        $('.jb-editor').css('display', 'block');
        $('.comment-texeditor-container .wysihtml5-sandbox > body').click(function(){
        console.log($(this));
        //$(this).next().css({'z-index':'5', 'margin-top':'-15px', 'transition':'border 0.2s linear 0s,'});
    });
    });
   
    
    
    
    /*comment-collapse*/
    $('.jb-post-comment').hover(function(){
        $(this).find('.jb-comcollapse').css('display','block');
    },
    function(){        
        $(this).find('.jb-comcollapse').css('display','none');
    });
    $('.jb-comcollapse > a').click(function(){
	if($(this).html() == '-'){
	    hideComment(this);
	}else{
	    showComment(this);
	}
	return false;
    });
    hideComment = function(el){
	$(el).html('+');
  $(el).attr('title','Expand');
	$(el).parents('.jb-comment-info').find('.jb-avatar').css('margin-right', '27px');                                
	$(el).parents('.jb-comment-info').find('.jb-avatar img').css({'width': '24px',
				'height':'24px'});
	$(el).parents('.jb-post-comment').find('.jb-commenttext').css('display','none');
	$(el).parents('.jb-post-comment').find('.jb-edit-share').css('display','none');
    };
    showComment = function(el){
	$(el).html('-');
	$(el).parents('.jb-comment-info').find('.jb-avatar').css('margin-right', '15px');
	$(el).parents('.jb-comment-info').find('.jb-avatar img').css({'width': '36px',
				'height':'36px'});
	$(el).parents('.jb-post-comment').find('.jb-commenttext').css('display','block');
	$(el).parents('.jb-post-comment').find('.jb-edit-share').css('display','block');
    };
    
    
    /* Placeholder for IE */
    if($.browser.msie) { // Condition for IE only
        $("form").find("input[type='text']").each(function() {
            var tp = $(this).attr("placeholder");
            $(this).attr('value',tp).css('color','#ccc');
        }).focusin(function() {
            var val = $(this).attr('placeholder');
            if($(this).val() == val) {
                $(this).attr('value','').css('color','#303030');
            }
        }).focusout(function() {
            var val = $(this).attr('placeholder');
            if($(this).val() == "") {
                $(this).attr('value', val).css('color','#ccc');
            }
        });

        /* Protected send form */
        $("form").submit(function() {
            $(this).find("input[type='text']").each(function() {
                var val = $(this).attr('placeholder');
                if($(this).val() == val) {
                    $(this).attr('value','');
                }
            })
        });
        $("form").find("textarea").each(function() {
            var tp = $(this).attr("placeholder");
            $(this).attr('value',tp).css('color','#ccc');
        }).focusin(function() {
            var val = $(this).attr('placeholder');
            if($(this).val() == val) {
                $(this).attr('value','').css('color','#303030');
            }
        }).focusout(function() {
            var val = $(this).attr('placeholder');
            if($(this).val() == "") {
                $(this).attr('value', val).css('color','#ccc');
            }
        });

        /* Protected send form */
        $("form").submit(function() {
            $(this).find("textarea").each(function() {
                var val = $(this).attr('placeholder');
                if($(this).val() == val) {
                    $(this).attr('value','');
                }
            })
        });
        
        
    }
   
   
    var menu = $('#joomBlog-toolbar').width();
	menu = Math.floor(menu)-54;
	//var li_array = new Array(0);
	li_array = $('#joomBlog-toolbar li');
	
	var num = 0;
	var li_sum = 0;
	if (li_array.length)
	for (var i=0; i<li_array.length;i++)
	{
		li_sum = li_sum + li_array[i].offsetWidth;
		if (li_sum + 110 > menu) break;
		num++;
	}

	if (num){
				
		$.each(li_array, function(i, val){
				if ( i == num )
				{
					$(li_array[i-1]).after('<li class="tools-dropdown-menu"><a href="javascript:void(0);" id="dropdown-menu-click"><!--x--></a></li>');
				}
				if ( i >= num ){
					$('.hidden-menu').append($(li_array[i]));
				}
			
		});		
	}

	
	$('#dropdown-menu-click').bind('click',switchMenu);
	function switchMenu(){
		var hidden = $('.hidden-menu');
		
		if(hidden.css('display') == 'none')
		{
			hidden.fadeIn(100);
			return;
		}
		if(hidden.css('display') == 'block')
		{
			hidden.fadeOut(100);
			return;
		}
	}
		
	$(window).resize(function(){
		var menu = $('#joomBlog-toolbar').width();
		menu = Math.floor(menu)-54;
		var li_array = $('#joomBlog-toolbar li').not('.tools-dropdown-menu');
		var li = li_array[0].offsetWidth + 20;
		var li_sum = (li_array.length * li) + (15 * li_array.length) + 40;
				
		if (menu < li_sum){
			
			var num = Math.floor(menu/li);		
			$.each(li_array, function(i, val){
				if ( i >= num - 1 ){
					$('.hidden-menu').prepend($(li_array[i])).css('display', 'none');
				}
			});
			
			var last = $('#joomBlog-toolbar li:last').attr('class');
			if (last != 'tools-dropdown-menu'){
				$('#joomBlog-toolbar li:last').after('<li class="tools-dropdown-menu"><a href="javascript:void(0);" id="dropdown-menu-click"><!--x--></a></li>');
				$('#dropdown-menu-click').bind('click',switchMenu);
			}
			
			return true;
		}	
		
		
		if (menu > (li_sum + li + 10)){
		
			var num = Math.floor(menu/li);
			var menu_li = $('.hidden-menu li');
			
			$.each(li_array, function(i, val){
				
				if (i <= num ){
					$(menu_li[0]).insertBefore('.tools-dropdown-menu');
				}
			});
			if (menu_li.length == 1) $('.tools-dropdown-menu').remove();
			return true;
		}
		
		
	});
        
        
});

closeTag = function(text1, text2, textarea){
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange){
		var caretPos = textarea.caretPos, temp_length = caretPos.text.length;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;

		if (temp_length == 0)
		{
			caretPos.moveStart("character", -text2.length);
			caretPos.moveEnd("character", -text2.length);
			caretPos.select();
		}
		else
			textarea.focus(caretPos);
	}else if (typeof(textarea.selectionStart) != "undefined"){
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var selection = textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var newCursorPos = textarea.selectionStart;
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text1 + selection + text2 + end;

		if (textarea.setSelectionRange)
		{
			if (selection.length == 0)
				textarea.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
			else
				textarea.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
			textarea.focus();
		}
		textarea.scrollTop = scrollPos;
	}else{
		textarea.value += text1 + text2;
		textarea.focus(textarea.value.length - 1);
	}
};

getdom = function(id){
  return document.getElementById(id);
};

validateComment = function(f){
	if(!f.name.value){
		alert('"Name" field must not be empty.');
		return false;
	}
	
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

	if(!f.email.value){
		alert('Please provide a valid email address.');
		return false;
	}

	if(reg.test(f.email.value)  == false){
		alert('The address \''+f.email.value+'\' does not appear to be a valid email address.');
		return false;
	}
	
	if(!f.comment.value){
		alert('"Comment" field must not be empty.');
		return false;
	}
	
	return true;
};

Addcomment = function(f){
	if(validateComment(f)){
		f.submit();
	}
};

Savecomment = function(){
	document.commentListForm.task.value = "savecomment";
	document.commentListForm.submit();
};

Publishedcomment = function(id,s){
	document.commentListForm.task.value = "publishedcomment";
	document.commentListForm.params.value = s;
	document.commentListForm.id.value = id;
	document.commentListForm.submit();
};

Editcomment = function(id){
	// var bbcode = '<br/><div id="comment_codes">'+
 //              '<a href="javascript:void(0);"  title="Bold" onclick="closeTag(\'[b]\', \'[/b]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                '<span class="code_b">B</span>'+
 //              '</a>'+
 //              '<a href="javascript:void(0);"  title="Italicize" onclick="closeTag(\'[i]\', \'[/i]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                '<span class="code_i">I</span>'+
 //              '</a>'+
 //              '<a href="javascript:void(0);"  title="Underline" onclick="closeTag(\'[u]\', \'[/u]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                '<span class="code_u">U</span>'+
 //              '</a>'+
 //              '<a href="javascript:void(0);"  title="Strikethrough" onclick="closeTag(\'[s]\', \'[/s]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                 '<span class="code_s">S</span>'+
 //              '</a>'+
 //              '<a href="javascript:void(0);"  title="URL" onclick="closeTag(\'[url]\', \'[/url]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                '<span class="code_url">URL</span>'+
 //              '</a>'+
 //              '<a href="javascript:void(0);"  title="Image" onclick="closeTag(\'[img]\', \'[/img]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                '<span class="code_image">Image</span>'+
 //              '</a>'+
 //              '<a href="javascript:void(0);"  title="Quote" onclick="closeTag(\'[quote]\', \'[/quote]\', getdom(\'editcomment\')); return false;" class="code">'+
 //                '<span class="code_quote">Quote</span>'+
 //              '</a>'+
 //            '</div>';
  
	// document.getElementById('desc-comment-'+id).innerHTML = bbcode+'<textarea class="inputbox" id="editcomment" name="editcomment['+id+']" cols="40" rows="5" >'+jQuery.trim(document.getElementById('desc-comment-'+id).innerHTML)+'</textarea>';
	document.getElementById('desc-comment-'+id).innerHTML = '<textarea class="inputbox" id="editcomment" name="editcomment['+id+']" cols="40" rows="5" >'+jQuery.trim(document.getElementById('desc-comment-'+id).innerHTML)+'</textarea>';
  document.getElementById('edit-comment-'+id).style['display'] = 'none';
	document.getElementById('save-comment-'+id).style['display'] = 'inline';
};

sendVote = function(id,vote){
	$.ajax({
		url: baseurl+'index.php?option=com_joomblog&task=addvote&id='+id+'&format=raw&vote='+vote,
		dataType : "json",                    
		success: function (data, textStatus) {
			if(data.msg){
				alert(data.msg);
			}

			if(data.sumvote !== undefined){
				
				if(data.sumvote>0){
					//sumvote = "+"+data.sumvote;
					sumvote = data.sumvote;
				}else{
					sumvote = data.sumvote;
				}
				
				
				if(data.sumvote>0){
					$("#post-"+id+" .sumvote").addClass("green");
				}else{
					if(data.sumvote<0){
						$("#post-"+id+" .sumvote").addClass("red");
					}else{
						$("#post-"+id+" .sumvote").removeClass("green red");
					}
				}
				
				$("#post-"+id+" .sumvote").text(sumvote);
			}
		}
	});
};

sendCommentVote = function(id,vote){
	$.ajax({
		url: baseurl+'index.php?option=com_joomblog&task=addcommentvote&id='+id+'&format=raw&vote='+vote,
		dataType : "json",                    
		success: function (data, textStatus) {
			if(data.msg){
				alert(data.msg);
			}

			if(data.sumcommentvote !== undefined){
				
				if(data.sumcommentvote>0){
					//sumcommentvote = "+"+data.sumcommentvote;
					sumcommentvote = data.sumcommentvote;
				}else{
					sumcommentvote = data.sumcommentvote;
				}
				
				
				if(data.sumcommentvote>0){
					$("#comment"+id+" .sumcommentvote").addClass("green");
				}else{
					if(data.sumcommentvote<0){
						$("#comment"+id+" .sumcommentvote").addClass("red");
					}else{
						$("#comment"+id+" .sumcommentvote").removeClass("green red");
					}
				}

				$("#comment"+id+" .sumcommentvote").text(sumcommentvote);
			}
		}
	});
};





})(jQuery.noConflict());

    
            
