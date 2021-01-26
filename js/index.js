
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
    $("#demo-button").click(function(){demoLogin()})

	//event listener kliknutí myši
	$(document).mouseup(function(e) {mouseUpChecker(e)})

	//event listener scrollování
	$(window).scroll(function(e) {showScrollButton(e)})

	//event listenery inputů
	$("#login-name").on("input", function() {checkLoginName()})
	$("#login-pass").on("input", function() {checkLoginPassword()})
	$("#register-name").on("input", function() {checkRegisterName()})
	$("#register-pass").on("input", function() {checkRegisterPassword()})
	$("#register-repass").on("input", function() {checkRegisterRePassword()})
	$("#register-email").on("input", function() {checkRegisterEmail()})
	$("#password-recovery-email").on("input", function() {checkRecoveryEmail()})
  
	//Odeslání AJAX požadavku pro kontrolu neexistence uživatele při registraci
	$("#register-name").blur(function()
  {
    if (!($("#register-name").val() != "" && checkRegisterName())) { return; }
    enqueueAjaxRequest
    (
      new ajaxRequest
      (
        'index-forms',
        {
          text: $("#register-name").val(),
          type: 'u'
        },
        function(messageType, message, data){ isStringUniqueCallback(messageType, message, data, true, $("#register-name")); }
      )
    );
  });

	//Odeslání AJAX poýadavku pro kontrolu neexistence e-mailu při registraci
	$("#register-email").blur(function()
  {
    if (!($("#register-email").val() != "" && checkRegisterEmail())) { return; }
    enqueueAjaxRequest
    (
      new ajaxRequest
      (
        'index-forms',
        {
          text: $("#register-email").val(),
          type: 'e'
        },
        function(messageType, message, data){ isStringUniqueCallback(messageType, message, data, true, $("#register-email")); }
      )
    );
  });

	$("#register-form, #login-form, #pass-recovery-form").on("submit", function(e) {formSubmitted(e)})
})

//funkce kontrolující správně zadané jméno při přihlašování
function checkLoginName() {
	var loginNameMessage;
	if($("#login-name").val().length == 0)
		loginNameMessage = "Jméno musí být vyplněno.";
	else loginNameMessage = "";
	$("#login-name-message").text(loginNameMessage);
}

//funkce kontrolující správně zadané heslo při přihlašování
function checkLoginPassword() {
	var loginPasswordMessage;
	if($("#login-pass").val().length == 0)
		loginPasswordMessage = "Heslo musí být vyplněno.";
	else loginPasswordMessage = "";
	$("#login-pass-message").text(loginPasswordMessage);
}

//funkce kontrolující správně zadané jméno při registraci
function checkRegisterName() {
	var nameAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ";
	var registerNameMessage;
	if ($("#register-name").val().length == 0)
		registerNameMessage = "Jméno musí být vyplněno."
	else if ($("#register-name").val().length < 4)
		registerNameMessage = "Jméno musí být alespoň 4 znaky dlouhé."
	else if ($("#register-name").val().length > 15)
		registerNameMessage = "Jméno může být nejvíce 15 znaků dlouhé."
	else registerNameMessage = "";
	for (let i = 0; i < $("#register-name").val().length; i++ ) {
		if (!nameAllowedChars.includes($("#register-name").val()[i]))
			registerNameMessage = "Jméno obsahuje nepovolené znaky."
	}
	$("#register-name-message").text(registerNameMessage);

	//používá jako podmínka zavolání funkce na kontrolu jedinečnosti jména
	if (registerNameMessage == "") return true;
	else {
		$("#register-name").removeClass("checked");
		return false;
	}
}

//funkce kontrolující správně zadané heslo při registraci
function checkRegisterPassword() {
	var passwordAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\''";
	var registerPasswordMessage;
	if ($("#register-pass").val().length == 0)
		registerPasswordMessage = "Heslo musí být vyplněno."
	else if ($("#register-pass").val().length < 6)
		registerPasswordMessage = "Heslo musí být alespoň 6 znaků dlouhé."
	else if ($("#register-pass").val().length > 31)
		registerPasswordMessage = "Heslo může být nejvíce 31 znaků dlouhé."
	else registerPasswordMessage = "";
	for (let i = 0; i < $("#register-pass").val().length; i++ ) {
		if (!passwordAllowedChars.includes($("#register-pass").val()[i]))
			registerPasswordMessage = "Heslo obsahuje nepovolené znaky."
	}
	$("#register-pass-message").text(registerPasswordMessage);

	if (registerPasswordMessage == "")
		$("#register-pass").addClass("checked");
	else $("#register-pass").removeClass("checked");

	checkRegisterRePassword();
}

//funkce kontrolující správně zadané heslo znovu při registraci
function checkRegisterRePassword() {
	var registerRePasswordMessage;
	if ($("#register-repass").val().length == 0)
		registerRePasswordMessage = "Heslo znovu musí být vyplněno."
	else if ($("#register-repass").val() != $("#register-pass").val())
		registerRePasswordMessage = "Zadaná hesla se neshodují."
	else registerRePasswordMessage = "";

	if (registerRePasswordMessage == "")
		$("#register-repass").addClass("checked");
	else $("#register-repass").removeClass("checked");

	$("#register-repass-message").text(registerRePasswordMessage);
}

//funkce kontrolující správně zadaný email při registraci
function checkRegisterEmail() {
	var registerEmailMessage;
  	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if ($("#register-email").val() != "" && !regex.test($("#register-email").val()))
		registerEmailMessage = "Zadaný email má nesprávný tvar."
	else registerEmailMessage= "";
	$("#register-email-message").text(registerEmailMessage);

	//používá jako podmínka zavolání funkce na kontrolu jedinečnosti jména
	if (registerEmailMessage == "") return true;
	else {
		$("#register-email").removeClass("checked");
		return false;
	}
}

//funkce kontrolující správně zadaný email při obnově hesla
function checkRecoveryEmail() {
	var recoveryEmailMessage;
  	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if ($("#password-recovery-email").val().length == 0)
		recoveryEmailMessage = "Email musí být vyplněn."
	else if ($("#password-recovery-email").val() != "" && !regex.test($("#password-recovery-email").val()))
		recoveryEmailMessage = "Zadaný email má nesprávný tvar."
	else recoveryEmailMessage= "";
	$("#password-recovery-email-message").text(recoveryEmailMessage);
}

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
	emptyForms(".user-data input.text-field, .message");
}

//skrytí login sekce
function hideLoginSection() {
	$("#index-login-section").removeClass("show");
	$(".overlay").removeClass("show");
	$("body").css("overflowY", "auto");
	emptyForms(".user-data input.text-field, .message");
}

//přihlášení pod demo účtem (kliknutí na tlačítko "Vyzkoušet demo")
function demoLogin() {
    $("#login-name").val("Demo");
    $("#login-pass").val("6F{1NPL#/p[O-y25JkKeOp2N7MLN@p}"); 
    $("#login-persist").prop("checked", false);
    $("#login-form").submit();
}

//vymaže obsah textových polí ve formuláři
function emptyForms(fields) {
	var formTextFields = [];
	formTextFields = $(fields);
	formTextFields.val('');
	formTextFields.text('');
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

var ajaxRequestsQueue = [];

//Objekt obsahující data o AJAX požadavku k odeslání
function ajaxRequest(url, data, callback)
{
	this.url = url;
	this.data = data;
	this.callback = callback;
}

/**
 * Funkce zařazující nový AJAX požadavek do fronty k odeslání / vyřízení
 * Pokud zatím ve frontě není žádný požadavek, je tento zařazený okamžitě odeslán
*/
function enqueueAjaxRequest(request)
{
	ajaxRequestsQueue.push(request);
	if (ajaxRequestsQueue.length === 1) //Ve frontě je pouze aktuální požadavek --> okamžitě jej odešli
	{
		sendAjaxRequest();
	}
}

//Funkce odesílající první AJAX požadavek ve frontě, tato funkce by se neměla volat přímo
function sendAjaxRequest()
{
	let request = ajaxRequestsQueue[0]; //Načti údaje o požadavku
	$.post(
		request.url,
		request.data,
		function (response, status)
		{
			ajaxCallback(response, status, request.callback);
			ajaxRequestsQueue.shift(); //Odstraň vyřešený požadavek z fronty
			if (ajaxRequestsQueue.length > 0) { sendAjaxRequest(); } //Mezitím byl zařazen další požadavek
		}
	);
}

//Funkce zpracovávající odpověď na AJAX požadavek pro zjištění, zda je dané jméno nebo e-mail unikátní
function isStringUniqueCallback(messageType, message, data, shouldBeUnique, $inputElement)
{
	if (messageType === "success")
	{
		if ((data.unique ^ shouldBeUnique))
    {
      //zobrazení chybové hlášky
      $(inputElement).removeClass("checked");
      if (inputElement[0] === $("#register-name")[0])
        $("#register-name-message").text("Toto jméno už používá jiný uživatel.")
      else if (inputElement[0] === $("#register-email")[0])
        $("#register-email-message").text("Tento email už používá jiný uživatel.")
    }
    else
    {
      //zobrazení potvrzovací ikony - jedinečný input splňující všechny podmínky (zkontrolováno předtím)
      $(inputElement).addClass("checked");
    }
	}
}

//Funkce volaná při odeslání jakéhokoli formuláře, která z něj načte data a zařadí AJAX požadavek, který je odešle
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

	enqueueAjaxRequest(
		new ajaxRequest
		(
			'index-forms',
			{
				type: type,
				name: name,
				pass: pass,
				repass: repass,
				email: email,
				stayLogged: stayLogged
			},
			serverResponse
		)
	);
}

//Funkce zpracovávající odpověď na AJAX požadavek odesílající data z odeslaného formuláře
function serverResponse(messageType, message, data)
{
	var errors = message.replaceAll("|", ". ");
	if (!errors.endsWith(".")) {
		errors = errors.concat(".");
	}

	switch(data.origin) {
		case "login":
			$("#login-server-message").text(errors);
			break;
		case "register":
			$("#register-server-message").text(errors);
			break;
		case "passRecovery":
			$("#password-recovery-server-message").text(errors);
			break;
	}
}

