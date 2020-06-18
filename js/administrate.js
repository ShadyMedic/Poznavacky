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
/*-------------------------Tab 2-------------------------*/
/*-------------------------Tab 3-------------------------*/
/*-------------------------Tab 4-------------------------*/
function acceptNameChange(event, objectType, requestId)
{
	let action = (objectType === "user") ? "accept user name change" : "accept class name change";
	$.post('administrate-action',
		{
			action:action,
			reqId:requestId,
		},
		function(response)
		{
			//TODO - reaguj na odpověď
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
			//TODO - reaguj na odpověď
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
			
			let result = JSON.parse(response)['message'];
			//TODO - nějak šikovněji zobrazit chybovou nebo úspěchovou hlášku
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