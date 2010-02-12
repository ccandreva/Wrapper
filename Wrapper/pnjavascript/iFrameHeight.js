
function iFrameHeight(WrapDebug) { 
	var ua = navigator.userAgent.toLowerCase();
	if(document.getElementById && (ua.indexOf('msie')==-1)) { // Mozilla, Opera & DOM
		var objFrame = document.getElementById('ContentFrame');
		var objDoc = (objFrame.contentDocument) ? objFrame.contentDocument // DOM, Moz 1.0+, Opera
			: (objFrame.contentWindow) ? objFrame.contentWindow.document //IE5.5+
			: (window.frames && window.frames['ContentFrame']) ? window.frames['ContentFrame'].document //IE5, Konqueror, Safari
			: (objFrame.document) ? objFrame.document 
			: null;
	// 	Konqueror/Safari doesn't like ComputedStyle
	//	var ComputedHeight = document.defaultView ? document.defaultView.getComputedStyle(objFrame.contentDocument.documentElement, '').getPropertyValue('height') : 0;
		if (ua.indexOf('gecko')) objFrame.style.height = '500px'; // Mozilla fix
 		var h = objDoc.body.scrollHeight; // find height of internal page
		if (h==0) return; // Opera fix
	//	if (parseInt(ComputedHeight) > h) { h = parseInt(ComputedHeight); }
		if (h<500) {  h = 500; } 
 		objFrame.style.height = h + 16 + 'px'; // change height of iFrame, +16 for scrollbars
		
	    if (WrapDebug) {
		// Display height & width in document
		var w = objDoc.body.scrollWidth; // find width of internal page
		document.getElementById('Height').firstChild.nodeValue = objDoc.body.scrollHeight + 'px'; 
		document.getElementById('Width').firstChild.nodeValue = w + 'px'; // obj.contentDocument.
	//	document.getElementById('CompHeight').firstChild.nodeValue = ComputedHeight;
	    }
	} else if(document.all) { 
		// document.all.ContentFrame.style.width = document.frames('ContentFrame').document.body.scrollWidth + 'px';
 		var h = document.frames('ContentFrame').document.body.scrollHeight;
		if (h<500) { h = 500; }
 		document.all.ContentFrame.style.height = h + 18 + 'px'; // +16 to compensate for scrollbars, plus 2px extra
	    if (WrapDebug) {
		// Display height & width in document
		var w = document.frames('ContentFrame').document.body.scrollWidth;
		document.getElementById('Height').innerText = h + 16 + 'px'; 
		document.getElementById('Width').innerText = w + 'px';
	    }
	}
}

onload = iFrameHeight();

