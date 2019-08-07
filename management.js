function firstTab()
{
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab1").style.display = "block";
}
function secondTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab2").style.display = "block";
}
function thirdTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab3").style.display = "block";
}
function fourthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab4").style.display = "block";
}
function fifthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	
	document.getElementById("tab5").style.display = "block";
}
/*------------------------------------------------------------*/
function editUser(event)
{
	//TODO implementovat funkci pro úpravy uživatelů
}
function deleteUser(event)
{
	//TODO implementovat funkci pro odstraňování uživatelů
}
/*------------------------------------------------------------*/
function acceptNameChange(event)
{
	//Získání současného jména
	var oldName = event.target.parentNode.parentNode.parentNode.childNodes[0].innerHTML;
	
	//Získání nového jména
	var newName = event.target.parentNode.parentNode.parentNode.childNodes[1].innerHTML;
	
	//Posílání požadavku na ovlivnění databáze
	postRequest("resolveNameChange.php", null, null, true, oldName, newName);
	
	//Odstranění požadavku z DOM
	event.target.parentNode.parentNode.parentNode.parentNode.removeChild(event.target.parentNode.parentNode.parentNode);
}
function declineNameChange(event)
{
	//Získání současného jména
	var oldName = event.target.parentNode.parentNode.parentNode.childNodes[0].innerHTML;
	
	//Posílání požadavku na ovlivnění databáze
	postRequest("resolveNameChange.php", null, null, false, oldName);
	
	//Odstranění požadavku z DOM
	event.target.parentNode.parentNode.parentNode.parentNode.removeChild(event.target.parentNode.parentNode.parentNode);
}
function sendMailNameChange(email)
{
	fifthTab();
	document.getElementById("emailAddressee").value = email;
}
/*------------------------------------------------------------*/
function updateEmailPreview()
{
	var msg = document.getElementById("emailMessage").value;
	if (msg !== "")
	{
		document.getElementById("emailPreview").innerHTML = msg;
	}
	else
	{
		msg = msg.replace("\n", "<br>");
		document.getElementById("emailPreview").innerHTML = "Náhled e-mailu se zobrazí zde";
	}
}
function sendMail()
{
	var to = document.getElementById("emailAddressee").value;
	var subject = document.getElementById("emailSubject").value;
	var message = document.getElementById("emailMessage").value;
	
	postRequest("emailSender.php", responseFunc, responseFunc, null, null, null, to, subject, message);
}
/*------------------------------------------------------------*/
function postRequest(url, success = null, error = null, accepted = null, oldName = null, newName = null, emailAddressee = null, emailSubject = null, emailMessage = null)
{
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
	req.send("acc="+accepted+"&oldName="+oldName+"&newName="+newName+"&to="+emailAddressee+"&sub="+emailSubject+"&msg="+emailMessage);
	return req;
}
function responseFunc(response)
{
	eval(response);
}