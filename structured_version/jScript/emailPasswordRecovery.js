function changePassword()
{	
	var newPass = document.getElementById("pass").value;
	var rePass = document.getElementById("repass").value;
	var token = window.location.search.substr(1).split("=")[1];	//Získání kódu z URL
	
	newPass = encodeURIComponent(newPass);
	rePass = encodeURIComponent(rePass);
	
	postRequest("php/ajax/emailPasswordChange.php", responseFunc, responseFunc, newPass, rePass, token);
	
	//Reset HTML
	document.getElementById("pass").value="";
	document.getElementById("repass").value="";
}

function postRequest(url, success = null, error = null, newPass, rePass, token){
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
	req.send("new="+newPass+"&reNew="+rePass+"&token=" + token);
	return req;
}

function responseFunc(response)
{
	eval(response);
}
