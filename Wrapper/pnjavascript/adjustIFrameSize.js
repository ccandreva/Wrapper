
// This is for browsing within a frame where the contained page calls the script through the BODY tag:
// <body onload=\"if (parent.adjustIFrameSize)
//                parent.adjustIFrameSize(window);\">

function adjustIFrameSize (iframeWindow, WrapDebug) { 
	if (iframeWindow.document.height) { 
		var iframeElement = parent.document.getElementById (iframeWindow.name); 
		iframeElement.style.height = iframeWindow.document.height + '16px'; 
	  if (WrapDebug) {
		// Display height & width in document
		document.getElementById('Height').firstChild.nodeValue = iframeWindow.document.height + 'px'; 
		document.getElementById('Width').firstChild.nodeValue = iframeWindow.document.width + 'px'; "
	  }
	} else if (document.all) { 
		var iframeElement = parent.document.all[iframeWindow.name]; 
		if (iframeWindow.document.compatMode && iframeWindow.document.compatMode != 'BackCompat') { 
			h = iframeWindow.document.documentElement.scrollHeight + 5;
			w = iframeWindow.document.documentElement.scrollWidth + 5;
		} else { 
			h = iframeWindow.document.body.scrollHeight + 5; 
			w = iframeWindow.document.body.scrollWidth + 5;
		}
		iframeElement.style.height = h + 'px'; 
	  if (WrapDebug) {
		// Display height & width in document
		document.getElementById('Height').innerText = h + 'px'; 
		document.getElementById('Width').innerText = w + 'px';" 
	  }
	} 
}
