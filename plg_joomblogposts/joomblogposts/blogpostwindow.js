// JavaScript Document
function Browser() {

  var ua, s, i;

  this.isIE    = false;
  this.isNS    = false;
  this.version = null;

  ua = navigator.userAgent;

  s = "MSIE";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  // Treat any other "Gecko" browser as NS 6.1.

  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
}
// redirect to list view
function blogWindowSaveClose(){
	window.location.reload();
}

function editWindowTitle(nt){
	//jax.$('blogEditorMessage').innerHTML = nt;
}

// Global object to hold drag information.
var load_method = (window.ie ? 'load' : 'domready');

// Must re-initialize window position
function blogShowWindow(windowUrl){
	
	Obj = document.getElementById("blogWindow");
	if(!Obj){
		Obj    = document.createElement('div');
		
		var html  = '';
		html += '<div id="blogWindow" return false;" onmousedown="dragOBJ(this, event);" style="top: 0px;">';
		html += '	<!-- top section -->';
    	html += '	<div id="sf_tl"></div>';
    	html += '	<div id="sf_tm"></div>';
    	html += '	<div id="sf_tr"></div>';
    	html += '	<div style="clear: both;"></div>';
    	html += '	<!-- middle section -->';
    	html += '	<div id="sf_ml"></div>';
    	html += '	<div id="blogWindowContentOuter">';
    	html += '		<div id="blogWindowContentTop">';
		html += '			<a href="javascript:void(0);" onclick="myblogHideWindow();" id="sf_close_btn">Close</a>';
		html += '			<div id="sf_logo"></div>';
    	html += '		</div>';			
    	html += '		<div id="blogWindowContent">';
    	html += '		</div>';		
    	html += '	</div>';
    	html += '	<div id="sf_mr"></div>';
    	html += '	<div style="clear: both;"></div>';
    	html += '	<!-- bottom section -->';
    	html += '	<div id="sf_bl"></div>';
    	html += '	<div id="sf_bm"></div>';
    	html += '	<div id="sf_br"></div>';
    	html += '	<div style="clear: both;"></div>';
		html += '</div>';
		
		
		Obj.innerHTML = html;
		document.body.appendChild(Obj);
	}
	
	
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	}
	else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight - 20+'px';
	}
	else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	
	var yPos;
	if (window.innerHeight != null)
	{
		yPos = window.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight)
	{
		yPos = document.documentElement.clientHeight;
	}
	else
	{
		yPos = document.body.clientHeight;
	}
	
	yPos=yPos-60  ;
	var leftPos = (myWidth - 837)/2;
	
	document.getElementById('blogWindow').style.visibility	= "visible";
	document.getElementById('blogWindow').style.zIndex = myGetZIndexMax() + 1;
    document.getElementById('blogWindowContent').innerHTML	= '<iframe id="blogContentFrame" src="' + windowUrl + '" frameborder="0" style="width: 770px; height: 530px;" scrolling="auto"></iframe>';
    
	// change the iframe source
	document.getElementById('blogContentFrame').setAttribute("src", '');
	document.getElementById('blogContentFrame').setAttribute("src", windowUrl);

	//if (browser.isIE) {
		//jQuery('#sf_tl, #sf_tm, #sf_tr, #sf_ml, #sf_mr, #sf_bl, #sf_bm, #sf_br, #sf_logo').pngfix();
	//}
	
	/*
	Set editor position, center it in screen regardless of the scroll position
	*/
	// In ie 7, pageYOffset is null
	var iframe = document.getElementById("blogWindow");
	if (window.pageYOffset)
		iframe.style.marginTop = (window.pageYOffset + 10) + 'px';
	else
	    iframe.style.marginTop = (document.body.scrollTop + 10) + 'px';
	iframe.style.height = (yPos) + 'px';
	
	
	/*
    Set height and width for transparent window
	*/

	var m_s = yPos + 'px';
    document.getElementById("blogWindow").style.height = m_s
    document.getElementById('blogWindow').style.left = leftPos + 'px';
	document.getElementById("blogWindowContent").style.height = (yPos - 30) + 'px';
	document.getElementById("blogContentFrame").style.height = (yPos - 30) + 'px';
	document.getElementById("blogWindowContentOuter").style.height = m_s;
	document.getElementById("sf_ml").style.height = m_s;
	document.getElementById("sf_mr").style.height = m_s;    
	
	 
}

function myblogHideWindow(){
	document.getElementById('blogWindowContent').innerHTML     = "";
	document.getElementById('blogWindow').style.visibility		= "hidden";
}

function dragOBJ(d,e) {

    function drag(e) {
		if(!stop) {
			d.style.top=(tX=xy(e,1)+oY-eY+'px');
			d.style.left=(tY=xy(e)+oX-eX+'px');
		}
	}
	
	function agent(v) {
		return(Math.max(navigator.userAgent.toLowerCase().indexOf(v),0));
	}
	function xy(e,v) {
		return(v?(agent('msie')?event.clientY+document.body.scrollTop:e.pageY):(agent('msie')?event.clientX+document.body.scrollTop:e.pageX));
	}

    var oX=parseInt(d.style.left);
	var oY=parseInt(d.style.top);
	var eX=xy(e);
	var eY=xy(e,1);
	var tX,tY,stop;

    document.onmousemove=drag;
	document.onmouseup=function(){
		stop=1; document.onmousemove=''; document.onmouseup='';
	};

}

function myGetZIndexMax(){
	var allElems = document.getElementsByTagName?
	document.getElementsByTagName("*"):
	document.all; // or test for that too
	var maxZIndex = 0;

	for(var i=0;i<allElems.length;i++) {
		var elem = allElems[i];
		var cStyle = null;
		if (elem.currentStyle) {cStyle = elem.currentStyle;}
		else if (document.defaultView && document.defaultView.getComputedStyle) {
			cStyle = document.defaultView.getComputedStyle(elem,"");
		}

		var sNum;
		if (cStyle) {
			sNum = Number(cStyle.zIndex);
		} else {
			sNum = Number(elem.style.zIndex);
		}
		if (!isNaN(sNum)) {
			maxZIndex = Math.max(maxZIndex,sNum);
		}
	}
	return maxZIndex;
}
