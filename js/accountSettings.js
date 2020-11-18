function changeName()
{
	$("#change-name-button").hide()
	$("#change-name-input").show();
}

function confirmNameChange()
{
	var newName = $("#change-name-input-field").val();
	newName = encodeURIComponent(newName);
	
	$.post("account-update",
		{
			action: "request name change",
			name: newName
		},
		function (response, status) { ajaxCallback(response, status, evaluateResponse); },
		"json"
	);
	
	//Reset HTML
	$("#change-name-input-field").val("");
	$("#change-name-input").hide();
	$("#change-name-button").show();
}

/*-----------------------------------------------------------------------------*/

function changePassword()
{
	$("#change-password-button").hide();
	$("#change-password-input2").hide();
	$("#change-password-input1").show();
}

function changePasswordVerify()
{
	var password = $("#change-password-input-field-old").val();
	
	$.post("account-update",
		{
			action: "verify password",
			password: password
		},
		function (response, status) { ajaxCallback(response, status, changePasswordStage2); },
		"json"
	);
}

function changePasswordStage2(messageType, message, data)
{
	if (data.verified === true)
	{
		displayChangePasswordStage2();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		console.log("["+messageType+" - " + data.origin + "] " + message);
		alert("["+messageType+" - " + data.origin + "] " + message);
		
		$("#change-password-input-field-old").val("");
	}
}

function displayChangePasswordStage2()
{
	$("#change-password-input1").hide();
	$("#change-password-input3").hide();
	$("#change-password-input2").show();
}

function changePasswordStage3()
{
	$("#change-password-input2").hide();
	$("#change-password-input3").show();
}

function confirmPasswordChange()
{
	var oldPass = $("#change-password-input-field-old").val();
	var newPass = $("#change-password-input-field-new").val();
	var rePass = $("#change-password-input-field-re-new").val();
	
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
		function (response, status) { ajaxCallback(response, status, evaluateResponse); },
		"json"
		);
	
	//Reset HTML
	$("#change-password-input-field-old").val("");
	$("#change-password-input-field-new").val("");
	$("#change-password-input-field-re-new").val("");
	$("#change-password-input3").hide();
	$("#change-password-button").show();
}

/*-----------------------------------------------------------------------------*/

function changeEmail()
{
	$("#change-email-button").hide();
	$("#change-email-input2").hide();
	$("#change-email-input1").show();
}

function changeEmailVerify()
{
	var password = $("#change-email-password-input-field").val();
	
	$.post("account-update",
		{
			action: "verify password",
			password: password
		},
		function (response, status) { ajaxCallback(response, status, changeEmailStage2); },
		"json"
	);
}

function changeEmailStage2(messageType, message, data)
{
	if (data.verified === true)
	{
		$("#change-email-button").hide();
		$("#change-email-input1").hide();
		$("#change-email-input2").show();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		console.log("["+messageType+" - " + data.origin + "] " + message);
		alert("["+messageType+" - " + data.origin + "] " + message);
		
		$("#change-email-password-input-field").val("");
	}
}

function confirmEmailChange()
{
	var password = $("#change-email-password-input-field").val();
	var newEmail = $("#change-email-input-field").val();
	
	if (newEmail.length == 0)
	{
		//TODO - zkus vymyslet, jak tohle provést bez popupu
		if (!confirm("Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.")){return;}
	}
	
	newEmail = encodeURIComponent(newEmail);
	var pass = $("#change-email-password-input-field").val();
	
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
					}
					evaluateResponse(messageType, message, data);
				}
			);
		},
		"json"
	);
	
	//Reset HTML
	$("#change-email-password-input-field").val("");
	$("#change-email-input-field").val("");
	$("#change-email-input1").hide();
	$("#change-email-input2").hide();
	$("#change-email-button").show();
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
		function (response, status) { ajaxCallback(response, status, evaluateResponse) },
		"json"
	);
	
	//Uvedení HTML do původního stavu (má smysl pouze v případě selhání)
	deleteAccountCancel();
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