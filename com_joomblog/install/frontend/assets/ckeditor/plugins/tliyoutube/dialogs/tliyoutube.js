﻿/*
 Modifica e usa come vuoi

 Creato da TurboLab.it - 01/01/2014 (buon anno!)
*/
CKEDITOR.dialog.add("tliyoutubeDialog",function(c){return{title:"YouTube movie",minWidth:400,minHeight:75,contents:[{id:"tab-basic",label:"Basic Settings",elements:[{type:"text",id:"youtubeURL",label:"Insert URL into YouTube movie."}]}],onOk:function(){var b=this.getValueOf("tab-basic","youtubeURL").trim().match(/v=([^&$]+)/i);if(null==b||""==b||""==b[0]||""==b[1])return alert("URL is invalid. Sample: a\n\n\t http://www.youtube.com/watch?v=abcdef"),
!1;var a=c.document.createElement("iframe");a.setAttribute("width","560");a.setAttribute("height","315");a.setAttribute("src","//www.youtube.com/embed/"+b[1]+"?rel=0");a.setAttribute("frameborder","0");a.setAttribute("allowfullscreen","1");c.insertElement(a)}}});