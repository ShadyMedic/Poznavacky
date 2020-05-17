
/*-----------------------------------------------------------------------------------------------------------------------------------------*/

$(function() { //až po načtení stránky

	//přidává třídu na zpracování úvodních animací
	if (getCookie("recentLogin") == 1) { //Je aktivní cookie, že se uživatel nedávno přihlásil nebo se právě odhlásil? --> přeskoč animace
		$("body").addClass("loaded");
	}
	else {
		$("body").addClass("load");
	}
	setTimeout(() => {
		$("#cookies-alert").css("transform", "translateY(0)");
	}, 4000);
});

//zasunutí elementu dolů
function hideDown(elementId)
{	
	$("#" + elementId).css("transform", "translateY(100%)");
}

//vysunutí sekce s přihlašováním, registrací a obnovou hesla
function showLoginSection(specification)
{
	$("#index-login-section").css("transform", "translateX(0)");
	$("body").css("overflowY", "hidden");
	let divId = specification;
	showLoginDiv(divId);
}

//zobrazení požadované části v přihlašovací sekci
function showLoginDiv(divId)
{
	$("#register").hide();
	$("#login").hide();
	$("#password-recovery").hide();
	$("#" + divId).show();
}

function hideLoginSection() 
{
	$("#index-login-section").css("transform", "translateX(-100%)");
	$("body").css("overflowY", "auto");
}

/*--------------------------------------------------------------------------*/
/* Odesílání dat z formulářů na server */
function formSubmitted(event)
{
	event.preventDefault();
	
	var formId = event.target.id;
	var type = $("#"+formId).find('*').filter(':input:first').val();	//Hodnota prvního <input> prvku (identifikátor formuláře)
	var name = "";
	var pass = "";
	var repass = "";
	var email = "";
	var stayLogged = "";
	switch (type)
	{
		//Přihlašovací formulář
		case 'l':
			name = $("#login-name").val();
			pass = $("#login-pass").val();
			stayLogged = $("#login-persist").is(":checked");
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

