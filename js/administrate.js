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
var currentClassValues = new Array(2);
function editClass(event)
{
	//Dočasné znemožnění ostatních akcí u všech tříd
	$(".classAction:not(.grayscale)").addClass("grayscale_temp_class");
	$(".classAction").addClass("grayscale");
	$(".classAction").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editableClassRow");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentClassValues[i] = $("#editableClassRow .classField:eq("+ i +")").val();
	}
	
	$("#editableClassRow .classAction").hide();						//Skrytí ostatních tlačítek akcí
	$("#editableClassRow .classEditButtons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editableClassRow .classField").addClass("editableField");	//Obarvení políček (//TODO)
	$("#editableClassRow .classField").removeAttr("disabled");		//Umožnění editace (pro <select>)
	classStatusEdited();		//Umožnění nastavení kódu třídy, pokud je současný stav nastaven na "private" a kód tak má smysl
}
function classStatusEdited()
{
	let newStatus = $("#editableClassRow select.classField").val();
	if (newStatus !== "private")
	{
		//Kód nemá smysl --> vymazat jej
		$("#editableClassRow input.classField").val("");
		$("#editableClassRow input.classField").attr("readonly", "");
	}
	else
	{
		//Je potřeba nastavit kód --> umožnit editaci
		if (currentClassValues[1] === "")
		{
			$("#editableClassRow input.classField").val("0000");
		}
		else
		{
			$("#editableClassRow input.classField").val(currentClassValues[1]);
		}
		
		$("#editableClassRow input.classField").removeAttr("readonly");
	}
}
function cancelClassEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale_temp_class").removeAttr("disabled");
	$(".grayscale_temp_class").removeClass("grayscale grayscale_temp_class");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editableClassRow .classField:eq("+ i +")").val(currentClassValues[i]);
	}
	
	$("#editableClassRow .classAction").show();							//Znovuzobrazení ostatních tlačítek akcí
	$("#editableClassRow .classEditButtons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editableClassRow .classField").removeClass("editableField");	//Odbarvení políček
	$("#editableClassRow input.classField").attr("readonly", "");		//Znemožnit editaci (pro <input>)
	$("#editableClassRow select.classField").attr("disabled", "");		//Znemožnit editaci (pro <select>)

	$("#editableClassRow").removeAttr("id");
}
function confirmClassEdit(classId)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentClassValues[i] = $("#editableClassRow .classField:eq("+ i +")").val();
	}
	
	//Odeslat data na server
	$.post("administrate-action",
		{
			action: 'update class',
			classId: classId,
			code: currentClassValues[1],
			status: currentClassValues[0]
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Reset DOM
				cancelClassEdit();
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
var currentClassAdminValues = new Array(2);
var changedIdentifier;
function changeClassAdmin(event)
{
	//Dočasné znemožnění ostatních akcí u všech tříd
	$(".classAction:not(.grayscale)").addClass("grayscale_temp_class");
	$(".classAction").addClass("grayscale");
	$(".classAction").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editableClassAdminRow");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentClassAdminValues[i] = $("#editableClassAdminRow .classAdminTable .classAdminField:eq("+ i +")").val();
	}
	
	$("#editableClassAdminRow .classAction").hide();											//Skrytí ostatních tlačítek akcí
	$("#editableClassAdminRow .classEditAdminButtons").show();									//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editableClassAdminRow .classAdminTable .classAdminField").addClass("editableField");	//Obarvení políček (//TODO)
	$("#editableClassAdminRow .classAdminField").removeAttr("readonly");						//Umožnění editace
}
function adminNameChanged()
{
	changedIdentifier = "name";
	if ($("#editableClassAdminRow .classAdminField:eq(0)").val() === currentClassAdminValues[0])
	{
		//Umožnit změnu ID - jméno je stejné jako na začátku
		$("#editableClassAdminRow .classAdminField:eq(1)").removeAttr("readonly");
	}
	else
	{
		//Znemožnit změnu ID - jméno se změnilo
		$("#editableClassAdminRow .classAdminField:eq(1)").attr("readonly", "");
	}
}
function adminIdChanged()
{
	changedIdentifier = "id";
	if ($("#editableClassAdminRow .classAdminField:eq(1)").val() === currentClassAdminValues[1])
	{
		//Umožnit změnu ID - jméno je stejné jako na začátku
		$("#editableClassAdminRow .classAdminField:eq(0)").removeAttr("readonly");
	}
	else
	{
		//Znemožnit změnu ID - jméno se změnilo
		$("#editableClassAdminRow .classAdminField:eq(0)").attr("readonly", "");
	}
}
function cancelClassAdminEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale_temp_class").removeAttr("disabled");
	$(".grayscale_temp_class").removeClass("grayscale grayscale_temp_class");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editableClassAdminRow .classAdminTable .classAdminField:eq("+ i +")").val(currentClassAdminValues[i]);
	}
	
	$("#editableClassAdminRow .classAction").show();											//Znovuzobrazení ostatních tlačítek akcí
	$("#editableClassAdminRow .classEditAdminButtons").hide();									//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editableClassAdminRow .classAdminTable .classAdminField").removeClass("editableField");	//Odbarvení políček
	$("#editableClassAdminRow .classAdminField").attr("readonly", "");							//Znemožnit editaci (pro <input>)
	
	$("#editableClassAdminRow").removeAttr("id");
}
function confirmClassAdminEdit(classId)
{
	let newId = $("#editableClassAdminRow .classAdminTable .classAdminField:eq(0)").val();
	let newName = $("#editableClassAdminRow .classAdminTable .classAdminField:eq(1)").val();
	
	//Odeslat data na server
	$.post("administrate-action",
		{
			action: 'change class admin',
			classId: classId,
			changedIdentifier: changedIdentifier,
			adminId: $("#editableClassAdminRow .classAdminTable .classAdminField:eq(1)").val(),
			adminName: $("#editableClassAdminRow .classAdminTable .classAdminField:eq(0)").val()
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Aktualizace údajů o správci třídy v DOM
				let newName = response["newName"];
				let newId = response["newId"];
				let newEmail = response["newEmail"];
				let newKarma = response["newKarma"];
				let newStatus = response["newStatus"];
				
				currentClassAdminValues[0] = newName;
				currentClassAdminValues[1] = newId;
				$("#editableClassAdminRow .classAdminTable .classAdminData:eq(0)").text(newEmail);
				$("#editableClassAdminRow .classAdminTable .classAdminData:eq(1)").text(newKarma);
				$("#editableClassAdminRow .classAdminTable .classAdminData:eq(2)").text(newStatus);
				
				//Vypnutí nebo zapnutí tlačítka pro kontaktování správce třídy a změna adresáta předávaného jako parametr
				if (newEmail.length === 0)
				{
					//Nový správce nemá e-mail --> vypnout tlačítko
					$("#editableClassAdminRow .classAdminMailButton").attr("disabled", "");
					$("#editableClassAdminRow .classAdminMailButton").addClass("grayscale");
					$("#editableClassAdminRow .classAdminMailButton").removeClass("grayscale_temp_class");	//Aby nebyla třída "grayscale" odebrána při zavolání metody cancelClassAdminEdit() níže
					$("#editableClassAdminRow .classAdminMailButton").removeClass("activeBtn");
					$("#editableClassAdminRow .classAdminMailButton").removeAttr("onclick");
					$("#editableClassAdminRow .classAdminMailButton").removeAttr("title");
				}
				else
				{
					//Zapnutí tlačítka a aktualizace e-mailové adresy adresáta
					$("#editableClassAdminRow .classAdminMailButton").removeAttr("disabled");
					$("#editableClassAdminRow .classAdminMailButton").removeClass("grayscale");
					$("#editableClassAdminRow .classAdminMailButton").addClass("activeBtn");
					$("#editableClassAdminRow .classAdminMailButton").attr("onclick", "startMail(\""+ newEmail +"\")");
					$("#editableClassAdminRow .classAdminMailButton").attr("title", "Kontaktovat správce");
				}
				
				//Reset DOM
				cancelClassAdminEdit();
				
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
//Zahrnuto v souboru reports.js
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