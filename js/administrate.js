function hideAllTabs()
{
	$("#tab1").hide();
	$("#tab2").hide();
	$("#tab3").hide();
	$("#tab4").hide();
	$("#tab5").hide();
	$("#tab6").hide();
	
	$("#tab1").removeClass("activeTab");
	$("#tab2").removeClass("activeTab");
	$("#tab3").removeClass("activeTab");
	$("#tab4").removeClass("activeTab");
	$("#tab5").removeClass("activeTab");
	$("#tab6").removeClass("activeTab");
}
function firstTab()
{
	hideAllTabs();
	
	$("#tab1").show();
	$("#tab1").addClass("activeTab");
}
function secondTab()
{
	hideAllTabs();
	
	$("#tab2").show();
	$("#tab2").addClass("activeTab");
}
function thirdTab()
{
	hideAllTabs();
	
	$("#tab3").show();
	$("#tab3").addClass("activeTab");
}
function fourthTab()
{
	hideAllTabs();
	
	$("#tab4").show();
	$("#tab4").addClass("activeTab");
}
function fifthTab()
{
	hideAllTabs();
	
	$("#tab5").show();
	$("#tab5").addClass("activeTab");
}
function sixthTab()
{
	hideAllTabs();
	
	$("#tab6").show();
	$("#tab6").addClass("activeTab");
}

/*-------------------------------------------------------*/
/*-----------------------Všeobecné-----------------------*/
function startMail(addressee)
{
	$("#emailAddressee").val(addressee)	//Nastav adresu
	fifthTab();	//Zobraz formulář
}
/*-------------------------Tab 1-------------------------*/
var currentUserValues = new Array(4);
function editUser(event)
{
	//Dočasné znemožnění ostatních akcí u všech uživatelů
	$(".userAction:not(.grayscale)").addClass("grayscale_temp_user");
	$(".userAction").addClass("grayscale");
	$(".userAction").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editableUserRow");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 3; i++)
	{
		currentUserValues[i] = $("#editableUserRow .userField:eq("+ i +")").val();
	}
	
	$("#editableUserRow .userAction").hide();					//Skrytí ostatních tlačítek akcí
	$("#editableUserRow .userEditButtons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editableUserRow .userField").addClass("editableField");	//Obarvení políček (//TODO)
	$("#editableUserRow .userField").removeAttr("readonly");	//Umožnění editace (pro <input>)
	$("#editableUserRow .userField").removeAttr("disabled");	//Umožnění editace (pro <select>)
}
function cancelUserEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale_temp_user").removeAttr("disabled");
	$(".grayscale_temp_user").removeClass("grayscale grayscale_temp_user");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 3; i++)
	{
		$("#editableUserRow .userField:eq("+ i +")").val(currentUserValues[i]);
	}
	
	$("#editableUserRow .userAction").show();						//Znovuzobrazení ostatních tlačítek akcí
	$("#editableUserRow .userEditButtons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editableUserRow .userField").removeClass("editableField");	//Odbarvení políček
	$("#editableUserRow input.userField").attr("readonly", "");		//Znemožnění editace (pro <input>)
	$("#editableUserRow select.userField").attr("disabled", "");	//Znemožnění editace (pro <select>)

	$("#editableUserRow").removeAttr("id");
}
function confirmUserEdit(userId)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 3; i++)
	{
		currentUserValues[i] = $("#editableUserRow .userField:eq("+ i +")").val();
	}
	
	//Odeslat data na server
	$.post("administrate-action",
		{
			action: 'update user',
			userId: userId,
			addedPics: currentUserValues[0],
			guessedPics: currentUserValues[1],
			karma: currentUserValues[2],
			status: currentUserValues[3],
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Reset DOM
				cancelUserEdit();
				//TODO - zobraz (možná) nějak úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
				//alert(response["message"]);
			}
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
}
function deleteUser(userId)
{
	if (!confirm("Opravdu chcete odstranit tohoto uživatele?\nTato akce je nevratná!"))
	{
		return;
	}
	$.post('administrate-action',
		{
			action: 'delete user',
			userId: userId
		},
		function(response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error" || response["messageType"] === "success")
			{
				//TODO - zobraz nějak chybovou nebo úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
	//Odebrání uživatele z DOM
	event.target.parentNode.parentNode.parentNode.remove();
}
/*-------------------------Tab 2-------------------------*/
function deleteClass(classId)
{
	if (!confirm("Opravdu chcete odstranit tuto třídu?\nTato akce je nevratná!"))
	{
		return;
	}
	$.post('administrate-action',
		{
			action: 'delete class',
			classId: classId
		},
		function(response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error" || response["messageType"] === "success")
			{
				//TODO - zobraz nějak chybovou nebo úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
	//Odebrání třídy z DOM
	event.target.parentNode.parentNode.parentNode.remove();
}
/*-------------------------Tab 3-------------------------*/
/*-------------------------Tab 4-------------------------*/
function acceptNameChange(event, objectType, requestId)
{
	let action = (objectType === "user") ? "accept user name change" : "accept class name change";
	$.post('administrate-action',
		{
			action:action,
			reqId:requestId
		},
		function(response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
	//Odebrání žádosti z DOM
	event.target.parentNode.parentNode.parentNode.remove();
}
function declineNameChange(event, objectType, requestId)
{
	let reason = prompt("Zadejte prosím důvod zamítnutí žádosti (uživatel jej obdrží e-mailem, pokud jej zadal). Nevyplnění tohoto pole bude mít za následek zrušení zamítnutí.");
	if (reason === false || reason.length === 0){ return; }
	let action = (objectType === "user") ? "decline user name change" : "decline class name change";
	$.post('administrate-action',
		{
			action:action,
			reqId:requestId,
			reason: reason
		},
		function(response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
	//Odebrání žádosti z DOM
	event.target.parentNode.parentNode.parentNode.remove();
}
/*-------------------------Tab 5-------------------------*/
var emailModified = true;	//Proměnná uchovávající informaci o tom, zda byl formulář pro odeslání e-mailu od posledního odeslání modifikován
function emailModification()
{
	emailModified = true;
}
function previewEmailMessage()
{
	let rawHTMLbody = $("#emailMessage").val();
	let rawHTMLfooter = $("#emailFooter").val();
	$.post('administrate-action',
		{
			action:"preview email",
			htmlMessage:rawHTMLbody,
			htmlFooter:rawHTMLfooter
		},
		function(response)
		{
			let result = JSON.parse(response)['content'];
			$("#emailEditor").hide();
			$("#emailPreviewButton").hide();
			
			$("#emailPreview").html(result);
			$("#emailPreview").show();
			$("#emailEditButton").show();
		}
	);
}
function editEmailMessage()
{
	$("#emailEditButton").hide();
	$("#emailPreview").hide();
	
	$("#emailEditor").show();
	$("#emailPreviewButton").show();
}
function sendMail()
{
	//Ochrana před odesíláním duplicitních e-mailů
	if (!emailModified)
	{
		if (!confirm("Opravdu chcete odeslat ten samý e-mail znovu?"))
		{
			return;
		}
	}
	
	let sender = $("#emailSender").val();
	let fromAddress = $("#emailSenderAddress").val();
	let addressee = $("#emailAddressee").val();
	let subject = $("#emailSubject").val();
	let rawHTMLbody = $("#emailMessage").val();
	let rawHTMLfooter = $("#emailFooter").val();
	
	$("#statusInfo").show();
	$("#emailSendButton").attr("disabled", true);
	
	$.post('administrate-action',
		{
			action:"send email",
			addressee:addressee,
			subject:subject,
			htmlMessage:rawHTMLbody,
			htmlFooter:rawHTMLfooter,
			sender:sender,
			fromAddress:fromAddress
		},
		function(response)
		{
			$("#statusInfo").hide();
			$("#emailSendButton").removeAttr("disabled");
			
			emailModified = false;
			
			response = JSON.parse(response)['message']
			{
				if (response["messageType"] === "error" || response["messageType"] === "success")
				{
					//TODO - zobraz nějak chybovou nebo úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
					alert(response["message"]);
				}
			}
			alert(result);
		}
	);
}
/*-------------------------Tab 6-------------------------*/
function sendSqlQuery()
{
	let query = $("#sqlQueryInput").val();
	$.post('administrate-action',
		{
			action:"execute sql query",
			query:query
		},
		function(response)
		{
			let result = JSON.parse(response)['dbResult'];
			$("#sqlResult").html(result);
		}
	);
}