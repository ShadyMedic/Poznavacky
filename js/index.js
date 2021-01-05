
/*--------------------------------------------------------------------------*/
/* Funkce upravující viditelný obsah stránky */

//zpracování eventů
$(function() { 
	setTimeout(() => {
		$("#cookies-alert").addClass("show");
	}, 1000);

	$(window).resize(function() {
		checkLoginSize();
	})

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

	//Odeslání AJAX požadavku pro kontrolu existence uživatele při přihlašování
	$("#login-name").blur(function(){ isStringUnique($("#login-name").val(), true, $("#login-name").get(), false); });

	//Odeslání AJAX požadavku pro kontrolu neexistence uživatele při registraci
	$("#register-name").blur(function(){ isStringUnique($("#register-name").val(), true, $("#register-name").get(), true); });

	//Odeslání AJAX poýadavku pro kontrolu neexistence e-mailu při registraci
	$("#register-email").blur(function(){ isStringUnique($("#register-email").val(), false, $("#register-email").get(), true); });

	//Odeslání AJAX poýadavku pro kontrolu existence e-mailu při obnově hesla
	$("#password-recovery-email").blur(function(){ isStringUnique($("#password-recovery-email").val(), false, $("#password-recovery-email").get(), false); });
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
function showLoginDiv(divId) {
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
	$("#register").removeClass("show");
	$("#login").removeClass("show");
	$("#password-recovery").removeClass("show");
	$("#" + divId).show();
	$("#" + divId).addClass("show");
}

//
function checkLoginSize() {
	let divId = $("#index-login-section > .show").attr("id");
	let loginDivHeight = $("#" + divId).outerHeight() + $("#hide-login-section-button").outerHeight();
	if ($("#index-login-section").hasClass("show")) {
		if (loginDivHeight >= (0.9*$(window).height()-64)) {
			if ($("#index-login-section").css("height") != "90vh") {
				$("#index-login-section").css("height", "90vh");
				$("#" + divId).css("height", "100%");
			}
		}
		else {
			if ($("#index-login-section").css("height") != "auto") {
				$("#index-login-section").css("height", "auto");
				$("#" + divId).css("height", "auto");
			}
		}
	}
}
/*--------------------------------------------------------------------------*/
/* Odesílání dat z formulářů na server */

function isStringUnique(string, isName, inputElement, shouldBeUnique)
{
	//Odeslání dat
	let type = (isName) ? 'u' : 'e';
	$.post("index-forms",
		{
			type: type,
			text: string
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function(messageType, message, data)
				{
					if (messageType === "success")
					{
						if ((data.unique ^ shouldBeUnique))
						{
							//TODO - nějak upravit inputElement tak, aby se ukázala chyba
							$(inputElement).css("backgroundColor", "red");
						}
						else
						{
							//TODO - nějak upravit inputElement tak, aby se ukázalo potvrzení
							$(inputElement).css("backgroundColor", "green");
						}
					}
				}
			);
		},
		"json"
	);
}

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

	var errors = message.split("|"); //V případě, že bylo nalezeno více chyb, jsou odděleny svislítkem
	console.log(errors);

	//TODO - zobrazení chybové nebo úspěchové hlášky
}

