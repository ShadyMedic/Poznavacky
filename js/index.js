
/*-----------------------------------------------------------------------------------------------------------------------------------------*/

$(function() { //až po načtení stránky

	//přidává třídu na zpracování úvodních animací
	/*if (getCookie("recentLogin") == 1) { //Je aktivní cookie, že se uživatel nedávno přihlásil nebo se právě odhlásil? --> přeskoč animace
		$("body").addClass("loaded");
	}
	else {
		$("body").addClass("load");
	}*/
	setTimeout(() => {
		$("#cookies-alert").addClass("show");
	}, 1000);
});

var documentHeight = $(window).height();
var scrollOffset = 50;
$(window).scroll(function(event) {
	var scrolled = $(window).scrollTop();
	console.log(scrolled);
	if (scrolled > (documentHeight + scrollOffset)) {
		$("#back-to-top").addClass("show");
	}
	else if (scrolled <= (documentHeight + scrollOffset)) {
		$("#back-to-top").removeClass("show");
	}
})

//zasunutí elementu dolů
function hideCookiesAlert()
{	
	$("#cookies-alert").removeClass("show");
}

//vysunutí sekce s přihlašováním, registrací a obnovou hesla
function showLoginSection(spec)
{
	$("#index-login-section").addClass("show");
	$(".overlay").addClass("show");
	$("body").css("overflowY", "hidden");
	let divId = spec;
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
	$("#index-login-section").removeClass("show");
	$(".overlay").removeClass("show");
	$("body").css("overflowY", "auto");
}

$(document).mouseup(function(e) 
{
    var container = $("#index-login-section");
	var cookiesAlert = $("#cookies-alert");

    if (!container.is(e.target) && !cookiesAlert.is(e.target) && container.has(e.target).length === 0 && cookiesAlert.has(e.target).length === 0) 
    {
        hideLoginSection();
    }
});

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
			email = $("#password-recovery-email").val();
			break;
		default:
			return;
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

