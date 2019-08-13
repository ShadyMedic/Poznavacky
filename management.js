var userTr;	//Používá se při změně uživatelských údajů - ukládá se sem innerHTML řádku uživatele
function firstTab()
{
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	document.getElementById("tab6").style.display = "none";
	
	document.getElementById("tab1").style.display = "block";
}
function secondTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	document.getElementById("tab6").style.display = "none";
	
	document.getElementById("tab2").style.display = "block";
}
function thirdTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	document.getElementById("tab6").style.display = "none";
	
	document.getElementById("tab3").style.display = "block";
}
function fourthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	document.getElementById("tab6").style.display = "none";
	
	document.getElementById("tab4").style.display = "block";
}
function fifthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab6").style.display = "none";
	
	document.getElementById("tab5").style.display = "block";
}
function sixthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab6").style.display = "block";
}
/*------------------------------------------------------------*/
function editUser(event)
{
	//Uložit současný stav
	userTr = event.target.parentNode.parentNode.parentNode.innerHTML;
	
	//Dočasně znemožnit editaci jiných uživatelů
	var buttons = document.getElementsByClassName("editButton");
	for (var i = 0; i < buttons.length; i++)
	{
	    buttons[i].setAttribute("class", "userAction grayscale editButton");
	    buttons[i].removeAttribute("title");
	    buttons[i].setAttribute("disabled", "true");
	}
	
	//Povolit editaci polí
	event.target.parentNode.parentNode.parentNode.childNodes[4].childNodes[0].removeAttribute("readonly");
	event.target.parentNode.parentNode.parentNode.childNodes[5].childNodes[0].removeAttribute("readonly");
	event.target.parentNode.parentNode.parentNode.childNodes[6].childNodes[0].removeAttribute("readonly");
	event.target.parentNode.parentNode.parentNode.childNodes[7].childNodes[0].removeAttribute("disabled");
	
	//Obarvit upravitelná políčka
	event.target.parentNode.parentNode.parentNode.childNodes[4].setAttribute("class", "editableField");
	event.target.parentNode.parentNode.parentNode.childNodes[5].setAttribute("class", "editableField");
	event.target.parentNode.parentNode.parentNode.childNodes[6].setAttribute("class", "editableField");
	event.target.parentNode.parentNode.parentNode.childNodes[7].setAttribute("class", "editableField");
	
	//Změnit tlačítka akcí
	event.target.parentNode.parentNode.innerHTML = "<button class='nameChangeAction activeBtn' onclick='confirmUserEdit(event)' title='Uložit'><img src='tick.gif'/></button><button class='nameChangeAction activeBtn' onclick='cancelUserEdit(event)' title='Zrušit'><img src='cross.gif'/></button>";
}
function confirmUserEdit(event)
{
	var username = event.target.parentNode.parentNode.parentNode.childNodes[1].innerHTML;
	var newAddedPics = event.target.parentNode.parentNode.parentNode.childNodes[4].childNodes[0].value;
	var newGuessedPics = event.target.parentNode.parentNode.parentNode.childNodes[5].childNodes[0].value;
	var newKarma = event.target.parentNode.parentNode.parentNode.childNodes[6].childNodes[0].value;
	var newStatus = event.target.parentNode.parentNode.parentNode.childNodes[7].childNodes[0].value;

	//Reset tlačítek a stylů
	var userRow = event.target.parentNode.parentNode.parentNode;
	event.target.parentNode.parentNode.parentNode.innerHTML = userTr;
	
	//Aktualizace hodnot v DOM
	userRow.childNodes[4].childNodes[0].value = newAddedPics;
	userRow.childNodes[5].childNodes[0].value = newGuessedPics;
	userRow.childNodes[6].childNodes[0].value = newKarma;
	userRow.childNodes[7].childNodes[0].value = newStatus;
	
	//Znovu umožnit editaci jiných uživatelů
	var buttons = document.getElementsByClassName("editButton");
	for (var i = 0; i < buttons.length; i++)
	{
	    buttons[i].setAttribute("class", "userAction activeBtn editButton");
	    buttons[i].setAttribute("title", "upravit");
	    buttons[i].removeAttribute("disabled");
	}
	
	//Upravit data v databázi
	postRequest("editUser.php", responseFunc, responseFunc, null, username, null, null, null, null, newAddedPics, newGuessedPics, newKarma, newStatus);
}
function cancelUserEdit(event)
{
	event.target.parentNode.parentNode.parentNode.innerHTML = userTr;
	userTr = "";
	
	//Znovu umožnit editaci jiných uživatelů
	var buttons = document.getElementsByClassName("editButton");
	for (var i = 0; i < buttons.length; i++)
	{
	    buttons[i].setAttribute("class", "userAction activeBtn editButton");
	    buttons[i].setAttribute("title", "upravit");
	    buttons[i].removeAttribute("disabled");
	}
}
function deleteUser(event)
{
	var confirmation = confirm("Opravdu chcete odstranit tohoto uživatele?\nTato akce je nevratná!");
	
	if (confirmation === true)
	{
		//Ovlivnění databáze
		var username = event.target.parentNode.parentNode.parentNode.childNodes[1].innerHTML;
		postRequest("deleteUser.php", responseFunc, responseFunc, null, username);
		
		//Odstranění účtu z DOM
		event.target.parentNode.parentNode.parentNode.parentNode.removeChild(event.target.parentNode.parentNode.parentNode);
	}
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
function sendSqlQuery()
{
	var query = document.getElementById("sqlQueryInput").value;
	
	postRequest("executeSqlQuery.php", printSqlResponse, responseFunc, null, null, null, null, null, query);
}
function printSqlResponse(response)
{
	document.getElementById("sqlResult").innerHTML = response;
}
/*------------------------------------------------------------*/
function postRequest(url, success = null, error = null, accepted = null, oldName = null, newName = null, emailAddressee = null, emailSubject = null, emailMessage = null, addedPics = null, guessedPics = null, karma = null, status = null)
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
	req.send("acc="+accepted+"&oldName="+oldName+"&newName="+newName+"&to="+emailAddressee+"&sub="+emailSubject+"&msg="+emailMessage+"&aPics="+addedPics+"&gPics="+guessedPics+"&karma="+karma+"&status="+status);
	return req;
}
function responseFunc(response)
{
	eval(response);
}