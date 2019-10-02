function selected1()
{
	document.getElementById("field2").style.display = "block";
	
	var final = "";
	var arr = document.getElementById("dropList").value;
	for (var i = arr.length - 1; arr[i] != '('; i--){}
	for (var j = 0; j < i - 1; j++){final += arr[j];}
	document.getElementById("duckLink").href = "https://duckduckgo.com/?q=" + final + "&iax=images&ia=images";
	
	document.getElementById("previewImg").style.display = "block";
}
function urlTyped()
{
	var value = document.getElementById("urlInput").value;
	
	//Část kódu zkopírována z https://stackoverflow.com/questions/5717093/check-if-a-javascript-string-is-a-url
	var pattern = new RegExp('((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
			  				'((\\d{1,3}\\.){3}\\d{1,3}))'); // OR ip (v4) address
	//Konec zkopírované části
	
	if (pattern.test(value))
	{
		document.getElementById("urlConfirm").removeAttribute("disabled");
		document.getElementById("urlConfirm").removeAttribute("class");
		document.getElementById("urlConfirm").setAttribute("class","button");
	}
	else
	{
		document.getElementById("urlConfirm").setAttribute("disabled", true);
		document.getElementById("urlConfirm").setAttribute("class","buttonDisabled");
	}
	
	document.getElementById("previewImg").src = "images/imagePreview.png";
}
function selected2(event)
{
	event.preventDefault();
	document.getElementById("previewImg").src = document.getElementById("urlInput").value;
	document.getElementById("sendButton").removeAttribute("disabled");
	document.getElementById("sendButton").removeAttribute("class");
	document.getElementById("sendButton").setAttribute("class","button");
}
function resetForm(event)
{
	try{event.preventDefault();}catch(e){firstLoad = true;}
	
	document.getElementById("sendButton").setAttribute("disabled", true);
	document.getElementById("sendButton").setAttribute("class","buttonDisabled");
	document.getElementById("previewImg").src = "images/imagePreview.png";
	document.getElementById("previewImg").style.display = "none";
	document.getElementById("urlConfirm").setAttribute("disabled", true);
	document.getElementById("urlConfirm").setAttribute("class","buttonDisabled");
	document.getElementById("urlInput").value = "";
	document.getElementById("field2").style.display = "none";
	document.getElementById("dropList").value = "";
}
function add(event)
{
	event.preventDefault();
	var name = document.getElementById("dropList").value;
	var URL = document.getElementById("previewImg").src;
	
	name = encodeURI(name);
	URL = encodeURI(URL);
	
	getRequest("newPicture.php?name=" + name + "&url=" + URL, responseFunc, responseFunc);
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
	if (response === "swal('Obrázek úspěšně přidán', '', 'success');")
	{
		var options = document.getElementById("dropList").options;
		for (var i = 0; i < options.length; i++)
		{
			if (options[i].innerHTML == document.getElementById("dropList").value){break;}
		}
		var option = options[i].innerHTML;
		var newValue = Number(option.split("(")[1].split(")")[0]) + 1;
		var name = option.split("(")[0];
		var newOption = name + "(" + newValue + ")";
		document.getElementById("dropList").options[i].innerHTML = newOption;//TODO
		
		document.getElementById("sendButton").setAttribute("disabled", true);
		document.getElementById("sendButton").setAttribute("class","buttonDisabled");
		document.getElementById("previewImg").src = "images/imagePreview.png";
		document.getElementById("urlConfirm").setAttribute("disabled", true);
		document.getElementById("urlConfirm").setAttribute("class","buttonDisabled");
		document.getElementById("urlInput").value = "";
	}
}
