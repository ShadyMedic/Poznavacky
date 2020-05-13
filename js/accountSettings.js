function changeName()
{
	$("#change-name-button").hide()
	$("#change-name-input").show();
}

function confirmNameChange()
{
	var newName = $("#change-name-input-field").val();
	newName = encodeURIComponent(newName);
	
	$.post("account-update",{
		action: "request name change",
		name: newName
	}, evaluateResponse);
	
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
	
	$.post("account-update",{
		action: "verify password",
		password: password
	}, changePasswordStage2);
}

function changePasswordStage2(response)
{
	response = JSON.parse(response);
	if (response.verified === true)
	{
		$("#change-password-input1").hide();
		$("#change-password-input3").hide();
		$("#change-password-input2").show();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		//swal("Špatné heslo.","","error");
		
		$("#change-password-input-field-old").val("");
	}
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
	
	$.post("account-update", {
		action: "change password",
		oldPassword: oldPass,
		newPassword: newPass,
		rePassword: repass
	}, evaluateResponse);
	
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
	
	$.post("account-update",{
		action: "verify password",
		password: password
	}, changeEmailStage2);
}

function changeEmailStage2(response)
{
	response = JSON.parse(response);
	if (response.verified === true)
	{
		$("#change-email-button").hide();
		$("#change-email-input1").hide();
		$("#change-email-input2").show();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		//swal("Špatné heslo.","","error");
		
		$("#change-email-password-input-field").val("");
	}
}

function confirmEmailChange()
{
	var newEmail = $("#change-email-input-field").val();
	
	if (newEmail.length == 0)
	{
		//TODO - zkus vymyslet, jak tohle provést bez popupu
		if (!confirm("Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.")){return;}
	}
	
	newEmail = encodeURIComponent(newEmail);
	var pass = $("#change-email-password-input-field").val();
	
	$.post("account-update",{
		action: "change email",
		password: password,
		newEmail: newEmail
	}, evaluateResponse);
	
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
	
	$.post("account-update",{
		action: "verify password",
		password: password
	}, deleteAccountConfirm);
}

function deleteAccountConfirm(response)
{
	response = JSON.parse(response);
	if (response.verified === true)
	{
		$("#delete-account-input2").show();
		$("#delete-account-input1").hide();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		//swal("Špatné heslo.","","error");
		
		$("#delete-account-input-field").val("");
	}
}

function deleteAccountFinal()
{
	var password = $("#delete-account-input-field").val();
	
	$.post("account-update",{
		action: "delete account",
		password: password
	}, evaluateResponse);
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
function evaluateResponse(response, status)
{
	//TODO
	alert(response);
}