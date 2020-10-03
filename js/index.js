
/*--------------------------------------------------------------------------*/
/* Funkce upravující viditelný obsah stránky */

//zpracování eventů
$(function() { 
	setTimeout(() => {
		$("#cookies-alert").addClass("show");
	}, 1000);

	//zasunutí cookies alertu
	$("#hide-cookies-alert-button").click(function(){
		$("#cookies-alert").removeClass("show");
	})

	//skrytí login sekce kliknutím na tlačítko
	$("#hide-login-section-button").click(function(){hideLoginSection();})
	
	//skrytí login sekce tím, že se klikne mimo
	$(document).mouseup(function(e) 
	{
		var container = $("#index-login-section");
		var cookiesAlert = $("#cookies-alert");

		if (!container.is(e.target) && !cookiesAlert.is(e.target) && container.has(e.target).length === 0 && cookiesAlert.has(e.target).length === 0) 
		{
			hideLoginSection();
		}
	});

	//zobrazení login sekce
	$(".show-login-section-login-button, .show-login-section-register-button, .show-login-section-password-recovery-button").click(function(event){
		if(!$("#index-login-section").hasClass("show")) {
			$("#index-login-section").addClass("show");
			$(".overlay").addClass("show");
			$("body").css("overflowY", "hidden");	
		}
		if ($(event.target).hasClass("show-login-section-login-button"))
			showLoginDiv('login');
		else if ($(event.target).hasClass("show-login-section-register-button"))
			showLoginDiv('register');
		else if ($(event.target).hasClass("show-login-section-password-recovery-button"))
			showLoginDiv('password-recovery');
	})

	//zobrazení/skrytí back-to-top tlačítka podle toho, kolik je odscrollováno
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

});

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

//zobrazení požadované části v login sekci
function showLoginDiv(divId)
{
	$("#index-login-section").css("height", "auto");
	$("#" + divId).css("height", "auto");
	let loginDivHeight = $("#" + divId).outerHeight() + $("#hide-login-section-button").outerHeight();
	if (loginDivHeight > (0.9*$(window).height()-64)) {
		$("#index-login-section").css("height", "90vh");
		$("#" + divId).css("height", "100%");
	}
	$("#register").hide();
	$("#login").hide();
	$("#password-recovery").hide();
	$("#" + divId).show();
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

