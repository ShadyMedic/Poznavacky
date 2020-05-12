function changeName()
{
	$("#changeNameButton").hide()
	$("#changeNameInput").show();
}

function confirmNameChange()
{
	var newName = $("#changeNameInputField").val();
	newName = encodeURIComponent(newName);
	
	$.post("account-update",{
		action: "request name change",
		name: newName
	}, evaluateResponse);
	
	//Reset HTML
	$("#changeNameInputField").val("");
	$("#changeNameInput").hide();
	$("#changeNameButton").show();
}

/*-----------------------------------------------------------------------------*/

function changePassword()
{
	$("#changePasswordButton").hide();
	$("#changePasswordInput2").hide();
	$("#changePasswordInput1").show();
}

function changePasswordVerify()
{
	var password = $("#changePasswordInputFieldOld").val();
	
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
		$("#changePasswordInput1").hide();
		$("#changePasswordInput3").hide();
		$("#changePasswordInput2").show();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		//swal("Špatné heslo.","","error");
		
		$("#changePasswordInputFieldOld").val("");
	}
}

function changePasswordStage3()
{
	$("#changePasswordInput2").hide();
	$("#changePasswordInput3").show();
}

function confirmPasswordChange()
{
	var oldPass = $("#changePasswordInputFieldOld").val();
	var newPass = $("#changePasswordInputFieldNew").val();
	var rePass = $("#changePasswordInputFieldReNew").val();
	
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
	$("#changePasswordInputFieldOld").val("");
	$("#changePasswordInputFieldNew").val("");
	$("#changePasswordInputFieldReNew").val("");
	$("#changePasswordInput3").hide();
	$("#changePasswordButton").show();
}

/*-----------------------------------------------------------------------------*/

function changeEmail()
{
	$("#changeEmailButton").hide();
	$("#changeEmailInput2").hide();
	$("#changeEmailInput1").show();
}

function changeEmailVerify()
{
	var password = $("#changeEmailPasswordInputField").val();
	
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
		$("#changeEmailButton").hide();
		$("#changeEmailInput1").hide();
		$("#changeEmailInput2").show();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		//swal("Špatné heslo.","","error");
		
		$("#changeEmailPasswordInputField").val("");
	}
}

function confirmEmailChange()
{
	var newEmail = $("#changeEmailInputField").val();
	
	if (newEmail.length == 0)
	{
		//TODO - zkus vymyslet, jak tohle provést bez popupu
		if (!confirm("Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.")){return;}
	}
	
	newEmail = encodeURIComponent(newEmail);
	var pass = $("#changeEmailPasswordInputField").val();
	
	$.post("account-update",{
		action: "change email",
		password: password,
		newEmail: newEmail
	}, evaluateResponse);
	
	//Reset HTML
	$("#changeEmailPasswordInputField").val("");
	$("#changeEmailInputField").val("");
	$("#changeEmailInput1").hide();
	$("#changeEmailInput2").hide();
	$("#changeEmailButton").show();
}

function updateEmail(newEmail)
{
	$("#emailAddress").innerHTML = newEmail;
}

/*-----------------------------------------------------------------------------*/

function deleteAccount()
{
	$("#deleteAccountButton").hide();
	$("#deleteAccountInput1").show();
}

function deleteAccountVerify()
{
	var password = $("#deleteAccountInputField").val();
	
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
		$("#deleteAccountInput2").show();
		$("#deleteAccountInput1").hide();
	}
	else
	{
		//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
		//swal("Špatné heslo.","","error");
		
		$("#deleteAccountInputField").val("");
	}
}

function deleteAccountFinal()
{
	var password = $("#deleteAccountInputField").val();
	
	$.post("account-update",{
		action: "delete account",
		password: password
	}, evaluateResponse);
}

function deleteAccountCancel()
{
	$("#deleteAccountInputField").val("");
	$("#deleteAccountButton").show();
	$("#deleteAccountInput2").hide();
}

/**
 * Funkce vyhodocující odpověď serveru
 */
function evaluateResponse(response, status)
{
	//TODO
	alert(response);
}