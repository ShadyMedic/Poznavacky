var userTr;		//Používá se při změně uživatelských údajů - ukládá se sem innerHTML řádku uživatele
var constantTr;	//Používá se při změně konstant - ukládá se sem innerHTML řádku konstanty
/*------------------------------------------------------------*/
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
function reevaluateMoveButtons()
{
	try		//Pro případ, že by nebyla přítomna již žádná konstanta
	{
		var buttons = document.getElementsByClassName("moveDownButton");
		for (var i = 0; i < buttons.length; i++)
		{
		    buttons[i].setAttribute("class", "moveDownButton activeBtn");
		    buttons[i].setAttribute("title", "Posunout dolů");
		    buttons[i].setAttribute("onclick", "moveConstantDown(event)");
		    buttons[i].removeAttribute("disabled");
		}
		
		var buttons = document.getElementsByClassName("moveUpButton");
		for (var i = 0; i < buttons.length; i++)
		{
		    buttons[i].setAttribute("class", "moveUpButton activeBtn");
		    buttons[i].setAttribute("title", "Posunout nahoru");
		    buttons[i].setAttribute("onclick", "moveConstantUp(event)");
		    buttons[i].removeAttribute("disabled");
		}
		
		document.getElementById("constantsTable").childNodes[0].childNodes[0].childNodes[2].childNodes[1].setAttribute("class","moveUpButton grayscale");
	    document.getElementById("constantsTable").childNodes[0].childNodes[0].childNodes[2].childNodes[1].removeAttribute("onclick");
	    document.getElementById("constantsTable").childNodes[0].childNodes[0].childNodes[2].childNodes[1].removeAttribute("title");
	    document.getElementById("constantsTable").childNodes[0].childNodes[0].childNodes[2].childNodes[1].setAttribute("disabled", "true");
	
	    document.getElementById("constantsTable").childNodes[0].childNodes[document.getElementById("constantsTable").childNodes[0].childNodes.length - 1].childNodes[2].childNodes[2].setAttribute("class","moveDownButton grayscale");
	    document.getElementById("constantsTable").childNodes[0].childNodes[document.getElementById("constantsTable").childNodes[0].childNodes.length - 1].childNodes[2].childNodes[2].removeAttribute("onclick");
	    document.getElementById("constantsTable").childNodes[0].childNodes[document.getElementById("constantsTable").childNodes[0].childNodes.length - 1].childNodes[2].childNodes[2].removeAttribute("title");
	    document.getElementById("constantsTable").childNodes[0].childNodes[document.getElementById("constantsTable").childNodes[0].childNodes.length - 1].childNodes[2].childNodes[2].setAttribute("disabled", "true");
	}catch(e){}
}
function editConstant(event)
{
	//Uložit současný stav
	constantTr = event.target.parentNode.parentNode.parentNode.innerHTML;
	
	//Povolit editaci hodnoty
	event.target.parentNode.parentNode.parentNode.childNodes[1].childNodes[0].removeAttribute("readonly");
	
	//Obarvit upravitelné políčko
	event.target.parentNode.parentNode.parentNode.childNodes[0].setAttribute("class", "editableField");
	
	//Změnit tlačítka akcí
	event.target.parentNode.parentNode.innerHTML = "<button class='activeBtn' onclick='confirmConstEdit(event)' title='Uložit'><img src='tick.gif'/></button><button class='activeBtn' onclick='cancelConstEdit(event)' title='Zrušit'><img src='cross.gif'/></button>";
}
function confirmConstEdit(event)
{
	var newValue = event.target.parentNode.parentNode.parentNode.childNodes[1].childNodes[0].value;

	//Reset tlačítek a stylů
	var constantRow = event.target.parentNode.parentNode.parentNode;
	event.target.parentNode.parentNode.parentNode.innerHTML = constantTr;
	constantTr = "";
	
	//Aktualizace hodnot v DOM
	constantRow.childNodes[1].childNodes[0].value = newValue;
}
function cancelConstEdit(event)
{
	event.target.parentNode.parentNode.parentNode.innerHTML = constantTr;
	constantTr = "";
}
function moveConstantUp(event)
{
	var movedHTML = event.target.parentNode.parentNode.parentNode.innerHTML;
	var replacedHTML = event.target.parentNode.parentNode.parentNode.previousSibling.innerHTML;
	
	var movedNode = event.target.parentNode.parentNode.parentNode;
	var replacedNode = event.target.parentNode.parentNode.parentNode.previousSibling;
	
	movedNode.innerHTML = replacedHTML;
	replacedNode.innerHTML = movedHTML;
	
	reevaluateMoveButtons();
}
function moveConstantDown(event)
{
	var movedHTML = event.target.parentNode.parentNode.parentNode.innerHTML;
	var replacedHTML = event.target.parentNode.parentNode.parentNode.nextSibling.innerHTML;
	
	var movedNode = event.target.parentNode.parentNode.parentNode;
	var replacedNode = event.target.parentNode.parentNode.parentNode.nextSibling;
	
	movedNode.innerHTML = replacedHTML;
	replacedNode.innerHTML = movedHTML;
	
	reevaluateMoveButtons();
}
function deleteConstant(event)
{
	//Odstranit řádek tabulky z DOM
	event.target.parentNode.parentNode.parentNode.parentNode.removeChild(event.target.parentNode.parentNode.parentNode);
	
	//Znovu propočítat vypnutí tlačítek
	reevaluateMoveButtons();
}
function addConstant()
{
	var cName = prompt("Zadejte jméno konstanty.\n\nJméno by se MĚLO skládat pouze z velkých písmen a podtržítek.\nJméno NESMÍ obsahovat jiné znaky než písmena, číslice a podtržítka.\nJméno NESMÍ začína číslicí.")
	if (cName === null || cName.length === 0){return;}
	var pattern = new RegExp("^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$");	//RegEx zkopírováno z https://www.php.net/manual/en/language.constants.php
	if (!pattern.test(cName))
	{
		alert("Neplatné jméno konstanty.");
		return;
	}
	
	//Tvorba a zobrazení nového řádku
	var newTr = document.createElement("tr");
	newTr.innerHTML = "<td>"+cName+"</td><td class='editableField'><input type='text' value='' class='userField'></td><td><button class='activeBtn' onclick='confirmConstEdit(event)' title='Uložit'><img src='tick.gif'></button><button class='activeBtn' onclick='deleteConstant(event)' title='Odstranit'><img src='cross.gif'></button></td>";
	document.getElementById("constantsTable").childNodes[0].appendChild(newTr);
	document.getElementById("constantsTable").childNodes[0].childNodes[document.getElementById("constantsTable").childNodes[0].childNodes.length - 1].childNodes[1].childNodes[0].focus();
	
	constantTr = "<td>"+cName+"</td><td><input type='text' readonly value=''></td><td><button class='activeBtn' onclick='editConstant(event)' title='Upravit konstantu'><img src='pencil.gif'></button><button class='moveUpButton activeBtn' onclick='moveConstantUp(event)' title='Posunout nahoru'><img src='up.gif'></button><button class='moveDownButton grayscale' disabled='true'><img src='down.gif'></button><button class='activeBtn' onclick='deleteConstant(event)' title='Odstranit konstantu'><img src='cross.gif'></button></td>"
	
	reevaluateMoveButtons();
}
function saveConstants()
{
	//Formát: KONSTANTA¶KONSTANTA¶KONSTANTA
	//KONSTANTA = JMÉNO¤HODNOTA
	// --> A¤1B¤2¶C¤3
	var requestString = "";
	var constantList = document.getElementById("constantsTable").childNodes[0].childNodes;
	for (var i = 0; i < constantList.length; i++)
	{
		var constant = constantList[i];
		
		var cName = constant.childNodes[0].innerHTML;
		var cValue = constant.childNodes[1].childNodes[0].value;
		
		requestString += ("¶"+cName+"¤"+cValue);
	}
	requestString = requestString.substring(1);	//Odstraňování prvního oddělovače konstant
	
	//Odeslat konstanty na server
	postRequest("updateConstants.php", responseFunc, responseFunc, null, null, null, null, null, requestString);
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
//TODO Funkce pro správu hlášení
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
	//Získat důvod zamítnutí.
	var reason = prompt("Zadejte prosím důvod zamítnutí.\nTento důvod obdrží žadatel e-mailem (pokud jej zadal).")
	if (reason === null || reason.length === 0){return;}	//Zrušit odmítnutí v případě nezadání důvodu či kliknutí na "Zrušit"
	
	//Získání současného jména
	var oldName = event.target.parentNode.parentNode.parentNode.childNodes[0].innerHTML;
	
	//Posílání požadavku na ovlivnění databáze
	postRequest("resolveNameChange.php", null, null, false, oldName, null, null, null, reason);
	
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