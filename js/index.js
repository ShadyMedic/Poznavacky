// Opera 8.0+
var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
// Firefox 1.0+
var isFirefox = typeof InstallTrigger !== 'undefined';
// Safari 3.0+ "[object HTMLElementConstructor]" 
var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));
// Internet Explorer 6-11
var isIE = /*@cc_on!@*/false || !!document.documentMode;
// Edge 20+
var isEdge = !isIE && !!window.StyleMedia;
// Chrome 1 - 79
var isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);
// Edge (based on chromium) detection
var isEdgeChromium = isChrome && (navigator.userAgent.indexOf("Edg") != -1);
// Blink engine detection
var isBlink = (isChrome || isOpera) && !!window.CSS;
/*-----------------------------------------------------------------------------------------------------------------------------------------*/
window.addEventListener("load", () => {
	if (isIE) { //nebo pokud je aktivní cookie, že se stránka načetla po odhlášení - PŘIDAT
		document.body.classList.add("loaded");
	}
	else {
		document.body.classList.add("load"); 
	}
	setTimeout(() => {
		document.body.style.overflowY="auto";
	}, 3400);
	setTimeout(() => {
		document.getElementById("cookies-alert").style.transform = "translateY(0)"
	}, 4000);
});

function hide(elementId)
{	
	document.getElementById(elementId).style.transform = "translateY(100%)";
}