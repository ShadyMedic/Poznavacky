
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

/*--------------------------------------------------------------------------*/
/* Odesílání dat z formulářů na server */
function formSumbitted(event)
{
	event.preventDefault();
	
	var formId = event.target.id;
	var type = $("#"+formId).find('*').filter(':input:first').val();	//Hodnota prvního <input> prvku (identifikátor formuláře)
	var name = "";
	var pass = "";
	var repass = "";
	var email = "";
	switch (type)
	{
		//Přihlašovací formulář
		case 'l':
			name = $("#login-name").val();
			pass = $("#login-pass").val();
			stayLogged = $("#login-persist").val();
			break;
		//Registrační formulář
		case 'r':
			name = $("#register-name").val();
			pass = $("#register-pass").val();
			repass = $("#register-repass").val();
			email = $("#register-email").val();
			break;
		//Formulář pro obnovu hesla
		case 'p':
			email = $("#password-recovery-email");
			break;
	}
	
	//Odeslání dat
	$.post("index-forms", {
		type: type,
		name: name,
		pass: pass,
		repass: repass,
		email: email,
		stayLogged: stayLogged
	}, serverResponse);
}

function serverResponse(data, status)
{
	var response = JSON.parse(data);
	//Přesměrování
	if (response.hasOwnProperty("redirect"))
	{
		window.location = response.redirect;
		return;
	}
	
	//Zobrazení hlášky
	var messageType = response.messageType;	//success / info / warning / error
	var message = response.message; //Chybová hláška
	var form = response.origin; //Formulář z něhož byla odeslána data - login / register / passRecovery
	
	//TODO - zobrazení chybové nebo úspěchové hlášky
}