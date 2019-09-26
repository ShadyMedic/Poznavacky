function changeName()
{
	document.getElementById("changeNameButton").style.display = "none";
	document.getElementById("changeNameInput").style.display = "block";
}

function confirmNameChange()
{
	var newName = document.getElementById("changeNameInputField").value;
	newName = encodeURIComponent(newName);
	
	postRequest("changeUsername.php", responseFunc, responseFunc, newName);
	
	//Reset HTML
	document.getElementById("changeNameInputField").value = "";
	document.getElementById("changeNameInput").style.display = "none";
	document.getElementById("changeNameButton").style.display = "inline-block";
}

/*-----------------------------------------------------------------------------*/

function changePassword()
{
	document.getElementById("changePasswordButton").style.display = "none";
	document.getElementById("changePasswordInput2").style.display = "none";
	document.getElementById("changePasswordInput1").style.display = "block";
}

function changePasswordVerify()
{
	var password = document.getElementById("changePasswordInputFieldOld").value;
	
	postRequest("checkPassword.php", changePasswordStage2, responseFunc, null, password);
}

function changePasswordStage2(response)
{
	if (response === "ok")
	{
		document.getElementById("changePasswordInput1").style.display = "none";
		document.getElementById("changePasswordInput3").style.display = "none";
		document.getElementById("changePasswordInput2").style.display = "block";
	}
	else
	{
		swal("Špatné heslo.","","error");
		document.getElementById("changePasswordInputFieldOld").value = "";
	}
}

function changePasswordStage3()
{
	document.getElementById("changePasswordInput2").style.display = "none";
	document.getElementById("changePasswordInput3").style.display = "block";
}

function confirmPasswordChange()
{
	var oldPass = document.getElementById("changePasswordInputFieldOld").value;
	var newPass = document.getElementById("changePasswordInputFieldNew").value;
	var rePass = document.getElementById("changePasswordInputFieldReNew").value;
	
	oldPass = encodeURIComponent(oldPass);
	newPass = encodeURIComponent(newPass);
	rePass = encodeURIComponent(rePass);
	
	postRequest("changePassword.php", responseFunc, responseFunc, null, oldPass, newPass, rePass);
	
	//Reset HTML
	document.getElementById("changePasswordInputFieldOld").value = "";
	document.getElementById("changePasswordInputFieldNew").value = "";
	document.getElementById("changePasswordInputFieldReNew").value = "";
	document.getElementById("changePasswordInput3").style.display = "none";
	document.getElementById("changePasswordButton").style.display = "inline-block";
}

/*-----------------------------------------------------------------------------*/

function changeEmail()
{
	document.getElementById("changeEmailButton").style.display = "none";
	document.getElementById("changeEmailInput").style.display = "block";
}

function confirmEmailChange()
{
	var newEmail = document.getElementById("changeEmailInputField").value;
	
	if (newEmail.length == 0)
	{
		if (!confirm("Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.")){return;}
		/*
		swal({
			title: "Odebrat e-mail",
			text: "Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.",
			icon: "question",
			buttons: {
				no:
				{
					text: "Ponechat",
					value: "no",
				},
			    yes:
			    {
			      text: "Odebrat",
			      value: "yes",
			    }
			  },
			})
			.then((value) => {
				switch (value)
				{
			  		case "no":
			    	return;
			    	break;
				}
			});
		*/
	}
	
	newEmail = encodeURIComponent(newEmail);
	
	postRequest("changeEmail.php", responseFunc, responseFunc, null, null, null, null, newEmail);
	
	//Reset HTML
	document.getElementById("changeEmailInputField").value = "";
	document.getElementById("changeEmailInput").style.display = "none";
	document.getElementById("changeEmailButton").style.display = "inline-block";
}

function updateEmail(newEmail)
{
	document.getElementById("emailAddress").innerHTML = newEmail;
}

/*-----------------------------------------------------------------------------*/

function deleteAccount()
{
	document.getElementById("deleteAccountButton").style.display = "none";
	document.getElementById("deleteAccountInput1").style.display = "inline-block";
}

function deleteAccountVerify()
{
	var password = document.getElementById("deleteAccountInputField").value;
	
	postRequest("checkPassword.php", deleteAccountConfirm, responseFunc, null, password);
}

function deleteAccountConfirm(response)
{
	if (response === "ok")
	{
	document.getElementById("deleteAccountInput2").style.display = "inline-block";
	document.getElementById("deleteAccountInput1").style.display = "none";
	}
	else
	{
		swal("Špatné heslo.","","error");
		document.getElementById("deleteAccountInputField").value = "";
	}
}

function deleteAccountFinal()
{
	var pass = document.getElementById("deleteAccountInputField").value;
	var username = document.getElementById("username").innerText;
	postRequest("deleteAccount.php", responseFunc, null, username, pass);
}

function deleteAccountCancel()
{
	document.getElementById("deleteAccountButton").style.display = "inline-block";
	document.getElementById("deleteAccountInput2").style.display = "none";
}

/*-----------------------------------------------------------------------------*/

function getRequest(url, success = null, error = null){
	var req = false;
	//Creating request
	try
	{
		//Most broswers
		req = new XMLHttpRequest();
	} catch (e)
	{
		//Interned Explorer
		try
		{
			req = new ActiveXObject("Msxml2.XMLHTTP");
		}catch(e)
		{
			//Older version of IE
			try
			{
				req = new ActiveXObject("Microsoft.XMLHTTP");
			}catch(e)
			{
				return false;
			}
		}
	}
	
	//Checking request
	if (!req) return false;
	
	//Checking function parameters and setting intial values in case they aren´t specified
	if (typeof success != 'function') success = function () {};
	if (typeof error!= 'function') error = function () {};
	
	//Waiting for server response
	req.onreadystatechange = function()
	{
		if(req.readyState == 4)
		{
			return req.status === 200 ? success(req.responseText) : error(req.status);
		}
	}
	req.open("GET", url, true);
	req.send();
	return req;
}

function postRequest(url, success = null, error = null, newName = null, oldPass = null, newPass = null, rePass = null, newEmail = null){
	var req = false;
	//Creating request
	try
	{
		//Most broswers
		req = new XMLHttpRequest();
	} catch (e)
	{
		//Interned Explorer
		try
		{
			req = new ActiveXObject("Msxml2.XMLHTTP");
		}catch(e)
		{
			//Older version of IE
			try
			{
				req = new ActiveXObject("Microsoft.XMLHTTP");
			}catch(e)
			{
				return false;
			}
		}
	}
	
	//Checking request
	if (!req) return false;
	
	//Checking function parameters and setting intial values in case they aren´t specified
	if (typeof success != 'function') success = function () {};
	if (typeof error!= 'function') error = function () {};
	
	//Waiting for server response
	req.onreadystatechange = function()
	{
		if(req.readyState == 4)
		{
			return req.status === 200 ? success(req.responseText) : error(req.status);
		}
	}
	req.open("POST", url, true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send("newName="+newName+"&oldPass="+oldPass+"&newPass="+newPass+"&reNewPass="+rePass+"&newEmail="+newEmail);
	return req;
}

function responseFunc(response)
{
	eval(response);
}