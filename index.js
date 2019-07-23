function hideCookies()
{
	document.getElementById("cookiesAlert").style.visibility = "hidden"
}

function register(event)
{
	/*
	 * TODO
    try{event.preventDefault();}catch(e){}
    var code = document.getElementsByClassName("text")[0].value;
    var result = getRequest("auth.php?token=" + code, responseFunc);
	 */
}

function login()
{
	/*
	 * TODO
	try{event.preventDefault();}catch(e){}
    var code = document.getElementsByClassName("text")[0].value;
    var result = getRequest("auth.php?token=" + code, responseFunc);
	 */
}

function responseFunc(response)
{
	eval(response);
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