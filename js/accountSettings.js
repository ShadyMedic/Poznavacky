//vše, co se děje po načtení stránky
$(function()
{
	//event listenery tlačítek
	$("#change-name-button").click(function() {changeName()})
	$("#change-name-confirm-button").click(function() {changeNameConfirm()})
	$("#change-name-cancel-button").click(function() {changeNameCancel()})
	$("#change-password-button").click(function() {changePassword()})
	$("#change-password-confirm-button").click(function() {changePasswordConfirm()})
	$("#change-password-cancel-button").click(function() {changePasswordCancel()})
	$("#change-email-button").click(function() {changeEmail()})
	$("#change-email-confirm-button").click(function() {changeEmailConfirm()})
	$("#change-email-cancel-button").click(function() {changeEmailCancel()})
	$("#delete-account-button").click(function() {deleteAccount()})
	$("#delete-account-confirm-button").click(function() {deleteAccountVerify()})
	$("#delete-account-final-confirm-button").click(function() {deleteAccountFinal()})
	$("#delete-account-cancel-button, #delete-account-final-cancel-button").click(function() {deleteAccountCancel()})
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
})

function changeNameCancel() {
	$("#change-name-button").show()
	$("#change-name").closest(".user-data-item").find(".user-property-value").show();
	$("#change-name").hide();
	$("#change-name-new").val("");
}

function changePasswordCancel() {
	$("#change-password-button").show()
	$("#change-password").closest(".user-data-item").find(".user-property-value").show();
	$("#change-password").hide();
	$("#change-password-old").val("");
	$("#change-password-new").val("");
	$("#change-password-re-new").val("");
}

function changeEmailCancel() {
	$("#change-email-button").show()
	$("#change-email").closest(".user-data-item").find(".user-property-value").show();
	$("#change-email").hide();
	$("#change-email-password").val("");
	$("#chabge-email-new").val("");
}

function changeName()
{
	$("#change-name-button").hide()
	$("#change-name").closest(".user-data-item").find(".user-property-value").hide();
	$("#change-name").show();
	$("#change-name-new").focus();
	changePasswordCancel();
	changeEmailCancel();
	deleteAccountCancel();
}

function changeNameConfirm()
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
	$("#change-password-old").focus();
	changeNameCancel();
	changeEmailCancel();
	deleteAccountCancel();
}

function changePasswordConfirm()
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
						changePasswordCancel();
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
	$("#change-email-password").focus();
	changeNameCancel();
	changePasswordCancel();
	deleteAccountCancel();
}

function changeEmailCOnfirm()
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
						changeEmailCancel();
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
	$("#delete-account").show();
	$("#delete-account1").show();
	$("#delete-account-password").focus();
	$("#delete-account")[0].scrollIntoView({ 
		behavior: 'smooth',
		block: "start" 
	});
	changeNameCancel();
	changePasswordCancel();
	changeEmailCancel();
}

function deleteAccountVerify()
{
	var password = $("#delete-account-password").val();
	
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
		$("#delete-account2").show();
		$("#delete-account1").hide();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		console.log("["+messageType+" - " + data.origin + "] " + message);
		alert("["+messageType+" - " + data.origin + "] " + message);
		
		$("#delete-account-password").val("");
	}
}

function deleteAccountFinal()
{
	var password = $("#delete-account-password").val();
	
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
	$("#delete-account-password").val("");
	$("#delete-account-button").show();
	$("#delete-account").hide();
	$("#delete-account2").hide();
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