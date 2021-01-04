
/*--------------------------------------------------------------------------*/
/* Funkce upravující viditelný obsah stránky */

//zpracování eventů
$(function() { 
	//zobrazení cookies alertu
	setTimeout(() => {
		$("#cookies-alert").addClass("show");
	}, 1000);

	//event listenery tlačítek
	$("#hide-login-section-button").click(function(){hideLoginSection()})
	$("#hide-cookies-alert-button").click(function(){hideCookiesAlert()})
	$(".show-login-section-login-button, .show-login-section-register-button, .show-login-section-password-recovery-button").click(function(event){showLoginSection(event)});
	
	//event listener kliknutí myši
	$(document).mouseup(function(e) {mouseUpChecker(e)})

	//event listener scrollování
	$(window).scroll(function(e) {showScrollButton(e)})
})


//zasunutí cookies alertu
function hideCookiesAlert(){
	$("#cookies-alert").removeClass("show");
}

//zobrazení/skrytí back-to-top tlačítka podle toho, kolik je odscrollováno
var documentHeight = $(window).height();
var scrollOffset = 50;
function showScrollButton(event) {
	var scrolled = $(window).scrollTop();
	if (scrolled > (documentHeight + scrollOffset)) {
		$("#back-to-top").addClass("show");
	}
	else if (scrolled <= (documentHeight + scrollOffset)) {
		$("#back-to-top").removeClass("show");
	}
}

//zobrazení login sekce
function showLoginSection(e) {
	if(!$("#index-login-section").hasClass("show")) {
		$("#index-login-section").addClass("show");
		$(".overlay").addClass("show");
		$("body").css("overflowY", "hidden");	
	}
	if ($(e.target).hasClass("show-login-section-login-button"))
		showLoginDiv('login');
	else if ($(e.target).hasClass("show-login-section-register-button"))
		showLoginDiv('register');
	else if ($(e.target).hasClass("show-login-section-password-recovery-button"))
		showLoginDiv('password-recovery');
}

//zobrazení požadované části v login sekci
function showLoginDiv(divId) {
	$("#register").hide();
	$("#login").hide();
	$("#password-recovery").hide();
	$("#" + divId).show();
}

//skrytí login sekce
function hideLoginSection() {
	$("#index-login-section").removeClass("show");
	$(".overlay").removeClass("show");
	$("body").css("overflowY", "auto");
	emptyForms(".user-data input.text-field");
}

//vymaže obsah textových polí ve formuláři
function emptyForms(fields) {
	var formTextFields = [];
	formTextFields = $(fields);
	formTextFields.val('');
}

//detekce kliknutí mimo login sekci
function mouseUpChecker(e) {
	var container = $("#index-login-section");
	var cookiesAlert = $("#cookies-alert");

	if (!container.is(e.target) && !cookiesAlert.is(e.target) && container.has(e.target).length === 0 && cookiesAlert.has(e.target).length === 0) 
	{
		hideLoginSection();
	}
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
			email = $("#password-recovery-email").val();
			break;
		default:
			return;
	}
	
	//Odeslání dat
	$.post("index-forms",
		{
			type: type,
			name: name,
			pass: pass,
			repass: repass,
			email: email,
			stayLogged: stayLogged
		},
		function (response, status) { ajaxCallback(response, status, serverResponse); },
		"json"
	);
}

function serverResponse(messageType, message, data)
{
	//var messageType == //success / info / warning / error
	//var message == //Chybová hláška
	//var form == //Formulář z něhož byla odeslána data - login / register / passRecovery
	
	//TODO - zobrazení chybové nebo úspěchové hlášky
}

