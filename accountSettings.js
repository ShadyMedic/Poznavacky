function changeName()
{
	document.getElementById("changeNameButton").style.display = "none";
	document.getElementById("changeNameInput").style.display = "block";
}

function confirmNameChange()
{
	var newName = document.getElementById("changeNameInputField").value;
	
	getRequest("changeUsername.php?new=" + newName, checkNameChange)
	
	//Reset HTML
	document.getElementById("changeNameInputField").value = "";
	document.getElementById("changeNameInput").style.display = "none";
	document.getElementById("changeNameButton").style.display = "block";
}

function checkNameChange(response)
{
	alert(response);
}
/*-----------------------------------------------------------------------------*/
function changePassword()
{
	
}
/*-----------------------------------------------------------------------------*/
function changeEmail()
{
	
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