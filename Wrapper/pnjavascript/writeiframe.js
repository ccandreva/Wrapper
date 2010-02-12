
var wrapnum=0;
function writeiframe() { 
	if (wrapnum!=0) return;
	else wrapnum=1;
	var iFrameDoc, content;
	var ScriptFrame =  document.getElementById('ContentFrame'); // parent.frames.ContentFrame; 
	iFrameDoc = (window.frames && window.frames['ContentFrame']) ? window.frames['ContentFrame'].document //IE5, Konqueror, Safari
		: ScriptFrame.contentDocument ? ScriptFrame.contentDocument // Dom, Moz 1.0+, Opera
		: ScriptFrame.contentWindow ? ScriptFrame.contentWindow.document // IE5.5+
		: document.all('ContentFrame').contentWindow.document; // IE 4
	content = document.getElementById ? document.getElementById('buffer').innerHTML // firstChild.nodeValue
		: document.all('buffer').innerHTML; // IE 4
	var pattern1 = /&amp;l2;/g;
	var pattern2 = /&amp;g2;/g;
	var pattern3 = /&amp;q2;/g;
	content = content.replace(pattern1, '<');
	content = content.replace(pattern2, '>');
	content = content.replace(pattern3, '\"');
	iFrameDoc.open();
	iFrameDoc.write(content);
	iFrameDoc.close();
} 

onload = function() {  writeiframe();  }

