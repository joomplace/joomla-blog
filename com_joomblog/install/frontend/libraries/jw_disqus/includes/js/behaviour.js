/*
* JoomBlog component for Joomla
* @version $Id: behaviour.js 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage behaviour.js
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
window.addEvent('domready',function(){
		// Disqus Comments Counter
		if(typeof disqusSubDomain != "undefined"){
			var links = document.getElementsByTagName('a');
			var query = '?';
			for(var i = 0; i < links.length; i++) {
				if(links[i].href.indexOf('#disqus_thread') >= 0) query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
			}
			var disqusScript = document.createElement('script');
			disqusScript.setAttribute('charset','utf-8');
			disqusScript.setAttribute('type','text/javascript');
			disqusScript.setAttribute('src','http://disqus.com/forums/' + disqusSubDomain + '/get_num_replies.js' + query + '');
			var b = document.getElementsByTagName('body')[0];
			b.appendChild(disqusScript);
		}
    
    // Smooth Scroll
    new SmoothScroll({
        duration: 500
    });
    
		// End
    
});
