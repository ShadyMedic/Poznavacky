//vše, co se děje po načtení stránky
$(function()
{
	//event listenery tlačítek
	$("#change-name-button").click(function() {changeName()})
	$("#change-name-confirm-button").click(function() {confirmNameChange()})
	$("#change-name-abort-button").click(function() {abortNameChange()})
	$("#change-password-button").click(function() {changePassword()})
	$("#change-password-confirm-button").click(function() {confirmPasswordChange()})
	$("#change-password-abort-button").click(function() {abortPasswordChange()})
	$("#change-email-button").click(function() {changeEmail()})
	$("#change-email-confirm-button").click(function() {confirmEmailChange()})
	$("#change-email-abort-button").click(function() {abortEmailChange()})
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
	resizeMainImg();
})

function abortNameChange() {
	$("#change-name-button").show()
	$("#change-name").closest(".user-data-item").find(".user-property-value").show();
	$("#change-name").hide();
}

function abortPasswordChange() {
	$("#change-password-button").show()
	$("#change-password").closest(".user-data-item").find(".user-property-value").show();
	$("#change-password").hide();
}

function abortEmailChange() {
	$("#change-email-button").show()
	$("#change-email").closest(".user-data-item").find(".user-property-value").show();
	$("#change-email").hide();
}

function changeName()
{
	$("#change-name-button").hide()
	$("#change-name").closest(".user-data-item").find(".user-property-value").hide();
	$("#change-name").show();
}

function confirmNameChange()
{
	var newName = $("#change-name-new").val();
	newName = encodeURIComponent(newName);
	
	$.post("account-update",
		{
			action: "request name change",
			name: newName
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "success")
					{
						//Reset HTML
						$("#change-name-new").val("");
						$("#change-name").hide();
						$("#change-name-button").show();
					}
					
					evaluateResponse(messageType, message, data);
				}
			);
		},
		"json"
	);
}

/*-----------------------------------------------------------------------------*/

function changePassword()
{
	$("#change-password-button").hide()
	$("#change-password").closest(".user-data-item").find(".user-property-value").hide();
	$("#change-password").show();
}

function confirmPasswordChange()
{
	var oldPass = $("#change-password-old").val();
	var newPass = $("#change-password-new").val();
	var rePass = $("#change-password-re-new").val();
	
	oldPass = encodeURIComponent(oldPass);
	newPass = encodeURIComponent(newPass);
	rePass = encodeURIComponent(rePass);
	
	$.post("account-update",
		{
			action: "change password",
			oldPassword: oldPass,
			newPassword: newPass,
			rePassword: rePass
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "success")
					{
						//Reset HTML
						$("#change-password-old").val("");
						$("#change-password-new").val("");
						$("#change-password-re-new").val("");
						$("#change-password").hide();
						$("#change-password-button").show();
					}
					else if (messageType === "error")
					{
						//Výmaz nového hesla a zobrazení pole pro nové heslo poprvé
						$("#change-password-new").val("");
						$("#change-password-re-new").val("");
					}
					
					evaluateResponse(messageType, message, data);
				}
			);
		},
	"json"
	);
}

/*-----------------------------------------------------------------------------*/

function changeEmail()
{
	$("#change-email-button").hide()
	$("#change-email").closest(".user-data-item").find(".user-property-value").hide();
	$("#change-email").show();
}

function confirmEmailChange()
{
	var password = $("#change-email-password").val();
	var newEmail = $("#change-email-new").val();
	
	if (newEmail.length == 0)
	{
		//TODO - zkus vymyslet, jak tohle provést bez popupu
		if (!confirm("Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.")){return;}
	}
	
	newEmail = encodeURIComponent(newEmail);
	var pass = $("#change-email-password").val();
	
	$.post("account-update",
		{
			action: "change email",
			password: password,
			newEmail: newEmail
		},
		function (response, code){
			ajaxCallback(response, code,
				function (messageType, message, data) {
					//Funkce zajišťující změnu e-mailu v DOM v případě úspěšné změny
					if (messageType === 'success')
					{
						$("#email-address").text(decodeURIComponent(newEmail));
						
						//Reset HTML
						$("#change-email-password").val("");
						$("#change-email-new").val("");
						$("#change-email-input1").hide();
						$("#change-email-input2").hide();
						$("#change-email-button").show();
					}
					evaluateResponse(messageType, message, data);
				}
			);
		},
		"json"
	);
}

function updateEmail(newEmail)
{
	$("#email-address").innerHTML = newEmail;
}

/*-----------------------------------------------------------------------------*/

function deleteAccount()
{
	$("#delete-account-button").hide();
	$("#delete-account-input1").show();
}

function deleteAccountVerify()
{
	var password = $("#delete-account-input-field").val();
	
	$.post("account-update",
		{
			action: "verify password",
			password: password
		},
		function (response, status) { ajaxCallback(response, status, deleteAccountConfirm); },
		"json"
	);
}

function deleteAccountConfirm(messageType, message, data)
{
	if (data.verified === true)
	{
		$("#delete-account-input2").show();
		$("#delete-account-input1").hide();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		console.log("["+messageType+" - " + data.origin + "] " + message);
		alert("["+messageType+" - " + data.origin + "] " + message);
		
		$("#delete-account-input-field").val("");
	}
}

function deleteAccountFinal()
{
	var password = $("#delete-account-input-field").val();
	
	$.post("account-update",
		{
			action: "delete account",
			password: password
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "error")
					{
						//Uvedení HTML do původního stavu (má smysl pouze v případě selhání)
						deleteAccountCancel();
					}
					
					evaluateResponse(messageType, message, data);
				}
			)
		},
		"json"
	);
}

function deleteAccountCancel()
{
	$("#delete-account-input-field").val("");
	$("#delete-account-button").show();
	$("#delete-account-input2").hide();
}

/**
 * Funkce vyhodocující odpověď serveru
 */
function evaluateResponse(messageType, message, data)
{	
	//Zobrazení hlášky
	//messageType = success / info / warning / error
	//message Chybová nebo úspěchová hláška
	//data = Další informace, pod data.origin je název akce, která vyvolala AJAX požadavek
	
	//TODO - zobrazení chybové nebo úspěchové hlášky
	console.log("["+messageType+" - " + data.origin + "] " + message);
	alert("["+messageType+" - " + data.origin + "] " + message);
}