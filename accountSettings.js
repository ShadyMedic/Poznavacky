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

function changePasswordStage2()
{
	document.getElementById("changePasswordInput1").style.display = "none";
	document.getElementById("changePasswordInput3").style.display = "none";
	document.getElementById("changePasswordInput2").style.display = "block";
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