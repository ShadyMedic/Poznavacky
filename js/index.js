
/*-----------------------------------------------------------------------------------------------------------------------------------------*/

//přidává třídu na zpracování úvodních animací
window.addEventListener("load", () => {
	if (1==1) { // pokud je aktivní cookie, že se stránka načetla po odhlášení - PŘIDAT
		document.body.classList.add("loaded");
		document.body.style.overflowY="auto";
	}
	else {
		document.body.classList.add("load"); 
		setTimeout(() => {
			document.body.style.overflowY="auto";
		}, 3400);
	}
	setTimeout(() => {
		document.getElementById("cookies-alert").style.transform = "translateY(0)"
	}, 4000);
});

//zasunutí elementu dolů
function hideDown(elementId)
{	
	document.getElementById(elementId).style.transform = "translateY(100%)";
}

//vysunutí sekce s přihlašováním, registrací a obnovou hesla
function showLoginSection(specification)
{
	document.getElementById('index-login-section').style.transform = "translateX(0)";
	document.body.style.overflowY="hidden";
	let divId = specification;
	showLoginDiv(divId);
}

//zobrazení požadované části v přihlašovací sekci
function showLoginDiv(divId)
{
	document.getElementById('register').classList.add("hidden");
	document.getElementById('login').classList.add("hidden");
	document.getElementById('password-recovery').classList.add("hidden");
	document.getElementById(divId).classList.remove("hidden");
}

function hideLoginSection() 
{
	document.getElementById('index-login-section').style.transform = "translateX(-100%)";
	document.body.style.overflowY="auto";
}