function hideAllTabs()
{
	$("#tab1").hide();
	$("#tab2").hide();
	$("#tab3").hide();
	$("#tab4").hide();
	$("#tab5").hide();
	$("#tab6").hide();
	
	$("#tab1").removeClass("active-tab");
	$("#tab2").removeClass("active-tab");
	$("#tab3").removeClass("active-tab");
	$("#tab4").removeClass("active-tab");
	$("#tab5").removeClass("active-tab");
	$("#tab6").removeClass("active-tab");

	$("#tab1-link").removeClass("active-tab");
	$("#tab2-link").removeClass("active-tab");
	$("#tab3-link").removeClass("active-tab");
	$("#tab4-link").removeClass("active-tab");
	$("#tab5-link").removeClass("active-tab");
	$("#tab6-link").removeClass("active-tab");
}
function firstTab()
{
	hideAllTabs();
	
	$("#tab1").show();
	$("#tab1").addClass("active-tab");
	$("#tab1-link").addClass("active-tab");
}
function secondTab()
{
	hideAllTabs();
	
	$("#tab2").show();
	$("#tab2").addClass("active-tab");
	$("#tab2-link").addClass("active-tab");
}
function thirdTab()
{
	hideAllTabs();
	
	$("#tab3").show();
	$("#tab3").addClass("active-tab");
	$("#tab3-link").addClass("active-tab");
}
function fourthTab()
{
	hideAllTabs();
	
	$("#tab4").show();
	$("#tab4").addClass("active-tab");
	$("#tab4-link").addClass("active-tab");
}
function fifthTab()
{
	hideAllTabs();
	
	$("#tab5").show();
	$("#tab5").addClass("active-tab");
	$("#tab5-link").addClass("active-tab");
}
function sixthTab()
{
	hideAllTabs();
	
	$("#tab6").show();
	$("#tab6").addClass("active-tab");
	$("#tab6-link").addClass("active-tab");
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
	$(".user-action:not(.grayscale)").addClass("grayscale-temp-user");
	$(".user-action").addClass("grayscale");
	$(".user-action").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editable-user-row");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 3; i++)
	{
		currentUserValues[i] = $("#editable-user-row .user-field:eq("+ i +")").val();
	}
	
	$("#editable-user-row .user-action").hide();					//Skrytí ostatních tlačítek akcí
	$("#editable-user-row .user-edit-buttons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editable-user-row .user-field").addClass("editable-field");	//Obarvení políček (//TODO)
	$("#editable-user-row .user-field").removeAttr("readonly");	//Umožnění editace (pro <input>)
	$("#editable-user-row .user-field").removeAttr("disabled");	//Umožnění editace (pro <select>)
}
function cancelUserEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale-temp-user").removeAttr("disabled");
	$(".grayscale-temp-user").removeClass("grayscale grayscale-temp-user");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 3; i++)
	{
		$("#editable-user-row .user-field:eq("+ i +")").val(currentUserValues[i]);
	}
	
	$("#editable-user-row .user-action").show();						//Znovuzobrazení ostatních tlačítek akcí
	$("#editable-user-row .user-edit-buttons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editable-user-row .user-field").removeClass("editable-field");	//Odbarvení políček
	$("#editable-user-row input.user-field").attr("readonly", "");		//Znemožnění editace (pro <input>)
	$("#editable-user-row select.user-field").attr("disabled", "");	//Znemožnění editace (pro <select>)

	$("#editable-user-row").removeAttr("id");
}
function confirmUserEdit(userId)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 3; i++)
	{
		currentUserValues[i] = $("#editable-user-row .user-field:eq("+ i +")").val();
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
	$(".class-action:not(.grayscale)").addClass("grayscale-temp-class");
	$(".class-action").addClass("grayscale");
	$(".class-action").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editable-class-row");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentClassValues[i] = $("#editable-class-row .class-field:eq("+ i +")").val();
	}
	
	$("#editable-class-row .class-action").hide();						//Skrytí ostatních tlačítek akcí
	$("#editable-class-row .class-edit-buttons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editable-class-row .class-field").addClass("editable-field");	//Obarvení políček (//TODO)
	$("#editable-class-row .class-field").removeAttr("disabled");		//Umožnění editace (pro <select>)
	classStatusEdited();		//Umožnění nastavení kódu třídy, pokud je současný stav nastaven na "private" a kód tak má smysl
}
function classStatusEdited()
{
	let newStatus = $("#editable-class-row select.class-field").val();
	if (newStatus !== "private")
	{
		//Kód nemá smysl --> vymazat jej
		$("#editable-class-row input.class-field").val("");
		$("#editable-class-row input.class-field").attr("readonly", "");
	}
	else
	{
		//Je potřeba nastavit kód --> umožnit editaci
		if (currentClassValues[1] === "")
		{
			$("#editable-class-row input.class-field").val("0000");
		}
		else
		{
			$("#editable-class-row input.class-field").val(currentClassValues[1]);
		}
		
		$("#editable-class-row input.class-field").removeAttr("readonly");
	}
}
function cancelClassEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale-temp-class").removeAttr("disabled");
	$(".grayscale-temp-class").removeClass("grayscale grayscale-temp-class");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editable-class-row .class-field:eq("+ i +")").val(currentClassValues[i]);
	}
	
	$("#editable-class-row .class-action").show();							//Znovuzobrazení ostatních tlačítek akcí
	$("#editable-class-row .class-edit-buttons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editable-class-row .class-field").removeClass("editable-field");	//Odbarvení políček
	$("#editable-class-row input.class-field").attr("readonly", "");		//Znemožnit editaci (pro <input>)
	$("#editable-class-row select.class-field").attr("disabled", "");		//Znemožnit editaci (pro <select>)

	$("#editable-class-row").removeAttr("id");
}
function confirmClassEdit(classId)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentClassValues[i] = $("#editable-class-row .class-field:eq("+ i +")").val();
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
	$(".class-action:not(.grayscale)").addClass("grayscale-temp-class");
	$(".class-action").addClass("grayscale");
	$(".class-action").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editable-class-admin-row");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentClassAdminValues[i] = $("#editable-class-admin-row .class-admin-table .class-admin-field:eq("+ i +")").val();
	}
	
	$("#editable-class-admin-row .class-action").hide();											//Skrytí ostatních tlačítek akcí
	$("#editable-class-admin-row .class-edit-admin-buttons").show();									//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editable-class-admin-row .class-admin-table .class-admin-field").addClass("editable-field");	//Obarvení políček (//TODO)
	$("#editable-class-admin-row .class-admin-field").removeAttr("readonly");						//Umožnění editace
}
function adminNameChanged()
{
	changedIdentifier = "name";
	if ($("#editable-class-admin-row .class-admin-field:eq(0)").val() === currentClassAdminValues[0])
	{
		//Umožnit změnu ID - jméno je stejné jako na začátku
		$("#editable-class-admin-row .class-admin-field:eq(1)").removeAttr("readonly");
	}
	else
	{
		//Znemožnit změnu ID - jméno se změnilo
		$("#editable-class-admin-row .class-admin-field:eq(1)").attr("readonly", "");
	}
}
function adminIdChanged()
{
	changedIdentifier = "id";
	if ($("#editable-class-adminrow .class-admin-field:eq(1)").val() === currentClassAdminValues[1])
	{
		//Umožnit změnu ID - jméno je stejné jako na začátku
		$("#editable-class-admin-row .class-admin-field:eq(0)").removeAttr("readonly");
	}
	else
	{
		//Znemožnit změnu ID - jméno se změnilo
		$("#editable-class-admin-row .class-admin-field:eq(0)").attr("readonly", "");
	}
}
function cancelClassAdminEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale-temp-class").removeAttr("disabled");
	$(".grayscale-temp-class").removeClass("grayscale grayscale-temp-class");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editable-class-admin-row .class-admin-table .class-admin-field:eq("+ i +")").val(currentClassAdminValues[i]);
	}
	
	$("#editable-class-admin-row .class-action").show();											//Znovuzobrazení ostatních tlačítek akcí
	$("#editable-class-admin-row .class-edit-admin-buttons").hide();									//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editable-class-admin-row .class-admin-table .class-admin-field").removeClass("editable-field");	//Odbarvení políček
	$("#editable-class-admin-row .class-admin-field").attr("readonly", "");							//Znemožnit editaci (pro <input>)
	
	$("#editable-class-admin-row").removeAttr("id");
}
function confirmClassAdminEdit(classId)
{
	let newId = $("#editable-class-admin-row .class-admin-table .class-admin-field:eq(0)").val();
	let newName = $("#editable-class-adminRow .class-admin-table .class-admin-field:eq(1)").val();
	
	//Odeslat data na server
	$.post("administrate-action",
		{
			action: 'change class admin',
			classId: classId,
			changedIdentifier: changedIdentifier,
			adminId: $("#editable-class-admin-row .class-admin-table .class-admin-field:eq(1)").val(),
			adminName: $("#editable-class-admin-row .class-admin-table .class-admin-field:eq(0)").val()
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
				$("#editable-class-admin-row .class-admin-table .class-admin-data:eq(0)").text(newEmail);
				$("#editable-class-admin-row .class-admin-table .class-admin-data:eq(1)").text(newKarma);
				$("#editable-class-admin-row .class-admin-table .class-admin-data:eq(2)").text(newStatus);
				
				//Vypnutí nebo zapnutí tlačítka pro kontaktování správce třídy a změna adresáta předávaného jako parametr
				if (newEmail.length === 0)
				{
					//Nový správce nemá e-mail --> vypnout tlačítko
					$("#editable-class-admin-row .class-admin-mail-btn").attr("disabled", "");
					$("#editable-class-admin-row .class-admin-mail-btn").addClass("grayscale");
					$("#editable-class-admin-row .class-admin-mail-btn").removeClass("grayscale-temp-class");	//Aby nebyla třída "grayscale" odebrána při zavolání metody cancelClassAdminEdit() níže
					$("#editable-class-admin-row .class-admin-mail-btn").removeClass("active-btn");
					$("#editable-class-admin-row .class-admin-mail-btn").removeAttr("onclick");
					$("#editable-class-admin-row .class-admin-mail-btn").removeAttr("title");
				}
				else
				{
					//Zapnutí tlačítka a aktualizace e-mailové adresy adresáta
					$("#editable-class-admin-row .class-admin-mail-btn").removeAttr("disabled");
					$("#editable-class-admin-row .class-admin-mail-btn").removeClass("grayscale");
					$("#editable-class-admin-row .class-admin-mail-btn").addClass("active-btn");
					$("#editable-class-admin-row .class-admin-mail-btn").attr("onclick", "startMail(\""+ newEmail +"\")");
					$("#editable-class-admin-row .class-admin-mail-btn").attr("title", "Kontaktovat správce");
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
function showPicture(url)
{
	$("#previewImgElement").attr("src", url);
	$("#imagePreview").show();
}
var currentReportValues = new Array(2);
function editPicture(event)
{
	//Dočasné znemožnění ostatních akcí u všech hlášení
	$(".reportAction").addClass("grayscale_temp_report");
	$(".reportAction").addClass("grayscale");
	$(".reportAction").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editableReportRow");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentReportValues[i] = $("#editableReportRow .reportField:eq("+ i +")").val();
	}
	
	/*
	Pokud nebyla změněna přírodnina, bude v currentReportValues[0] uloženo NULL
	V takovém případě nahradíme tuto hodnotu textem zobrazeném v <select> elementu
	Tento text je innerText prvního <option> elementu
	*/
	if (currentReportValues[0] === null){ currentReportValues[0] = $("#editableReportRow .reportField:eq(0)>option:eq(0)").text(); }
	
	$("#editableReportRow .reportAction").hide();					//Skrytí ostatních tlačítek akcí
	$("#editableReportRow .reportEditButtons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editableReportRow .reportField").addClass("editableField");	//Obarvení políček (//TODO)
	$("#editableReportRow .reportField").removeAttr("readonly");	//Umožnění editace (pro <input>)
	$("#editableReportRow .reportField").removeAttr("disabled");	//Umožnění editace (pro <select>)
}
function cancelPictureEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale_temp_report").removeAttr("disabled");
	$(".grayscale_temp_report").removeClass("grayscale grayscale_temp_report");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editableReportRow .reportField:eq("+ i +")").val(currentReportValues[i]);
	}
	
	$("#editableReportRow .reportAction").show();						//Znovuzobrazení ostatních tlačítek akcí
	$("#editableReportRow .reportEditButtons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editableReportRow .reportField").removeClass("editableField");	//Odbarvení políček
	$("#editableReportRow input.reportField").attr("readonly", "");		//Znemožnění editace (pro <input>)
	$("#editableReportRow select.reportField").attr("disabled", "");	//Znemožnění editace (pro <select>)

	$("#editableReportRow").removeAttr("id");
}
function confirmPictureEdit(picId)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentReportValues[i] = $("#editableReportRow .reportField:eq("+ i +")").val();
	}
	
	//Odeslat data na server
	$.post("administrate-action",
		{
			action: 'update picture',
			pictureId: picId,
			natural: currentReportValues[0],
			url: currentReportValues[1]
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Reset DOM
				cancelPictureEdit();
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
function disablePicture(event, picId)
{
	$.post('administrate-action',
			{
				action: 'disable picture',
				pictureId: picId
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
		
		//Odebrání všechna hlášení daného obrázku z DOM
		$("#reportsTable .pictureId" + picId).remove();
}
function deletePicture(event, picId)
{
	$.post('administrate-action',
			{
				action: 'delete picture',
				pictureId: picId
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
		
		//Odebrání všechna hlášení daného obrázku z DOM
		$("#reportsTable .pictureId" + picId).remove();
}
function deleteReport(event, reportId)
{
	$.post('administrate-action',
		{
			action: 'delete report',
			reportId: reportId
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
	//Odebrání hlášení z DOM
	event.target.parentNode.parentNode.parentNode.remove();
}
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