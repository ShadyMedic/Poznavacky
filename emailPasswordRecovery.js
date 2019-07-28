function changePassword()
{
	var newPass = document.getElementById("pass").value;
	var rePass = document.getElementById("repass").value;
	var token = window.location.search.substr(1).split("=")[1];
	
	getRequest("emailPasswordChange.php?new=" + newPass + "&reNew=" + rePass + "&token=" + token, responseFunc)
	
	//Reset HTML
	document.getElementById("pass").value = "";
	document.getElementById("repass").value = "";
}

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

function responseFunc(response)
{
	eval(response);
}