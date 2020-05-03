
/*-----------------------------------------------------------------------------------------------------------------------------------------*/

//přidává třídu na zpracování úvodních animací
window.addEventListener("load", () => {
	if (1==2) { // pokud je aktivní cookie, že se stránka načetla po odhlášení - PŘIDAT
		document.body.classList.add("load");
	}
	else {
		document.body.classList.add("loaded"); 
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