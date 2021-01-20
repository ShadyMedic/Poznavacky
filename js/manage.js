var initialStatus;      //Ukládá status třídy uložený v databázi
var initialCode;        //Ukládá vstupní kód třídy uložený v databázi

//Nastavení URL pro AJAX požadavky
let ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/manage', '/class-update'); //Nahraď neAJAX akci AJAX akcí

//vše, co se děje po načtení stránky
$(function() {

	//získání původních přístupových informací třídy z dokumentu
	$initialStatus = $("#class-status-select .selected");
    initialStatus = $("#class-status-select .selected").text();
    initialCode = $("#change-class-status-code").val();

	//Správně zobrazit tlačítko a vstupní pole pro kód
    statusChange();

	//event listenery tlačítek
	$("#change-class-name-button").click(function() {changeClassName()})
	$("#change-class-name-confirm-button").click(function() {changeClassNameConfirm()})
	$("#change-class-name-cancel-button").click(function() {changeClassNameCancel()})
	$("#change-class-status-button").click(function() {changeClassStatus()})
	$("#change-class-status-confirm-button").click(function() {changeClassStatusConfirm()})
	$("#change-class-status-cancel-button").click(function() {changeClassStatusCancel()})
	$("#delete-class-button").click(function() {deleteClass()})
	$("#delete-class-confirm-button").click(function() {deleteClassVerify()})
	$("#delete-class-final-confirm-button").click(function() {deleteClassFinal()})
	$("#delete-class-cancel-button, #delete-class-final-cancel-button").click(function() {deleteClassCancel()})

	//event listener inputu
	$("#change-class-status-code").on("input", function() {statusChange()})

	//event listener změny select boxu stavu třídy
	$("#class-status-select span").on('DOMSubtreeModified',function(){statusChange()});
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
})

function changeClassName()
{
    $("#change-class-name-button").hide();
    $("#change-class-name").show();
	$("#change-class-name").closest(".class-data-item").find(".class-property-value").hide();
	$("#change-class-name-new").focus();

	changeClassStatusCancel();
	deleteClassCancel();
}

function changeClassNameConfirm()
{
    var newName = $("#change-class-name-new").val();

    $.post(ajaxUrl,
		{
    		action: 'request name change',
			newName: newName
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "success")
					{
						//Reset DOM
						changeClassNameCancel();

						newMessage(message, "success");
					}
					else if (messageType === "error")
					{
						//TODO - ideálně zobrazit přímo ve formuláři
						newMessage(message, "error");
					}
				}
			);
		},
		"json"
	);
}
function changeClassNameCancel()
{
    $("#change-class-name-new").val("");
	$("#change-class-name-button").show();
    $("#change-class-name").hide();
	$("#change-class-name").closest(".class-data-item").find(".class-property-value").show();
}
/*-------------------------------------------------------*/

function changeClassStatus()
{
    $("#change-class-status-button").hide();
    $("#change-class-status").show();
	$("#change-class-status").closest(".class-data-item").find(".class-property-value").hide();
	$("#change-class-status-confirm-button").addClass("disabled");

	changeClassNameCancel();
	deleteClassCancel();
}

function statusChange()
{
    if ($("#class-status-select .selected").text() === initialStatus)
    {
        //Status třídy se nezměnil
        if ($("#class-status-select .selected").text() !== "Soukromá")
        {
            //Třída není ani jako soukromá --> není možné změnit vstupní kód --> vše skrýt
            hideClassStatusCode();
            return;
        }
    }
    else
    {
        //Status třídy se změnil
    	$("#statusCancelButton").show();
        if ($("#class-status-select .selected").text() !== "Soukromá")
        {
            //Třída není nastavována jako soukromá --> zobraz tlačítko, ale skryj vstupní kód
            $("#change-class-status-confirm-button").removeClass("disabled");
            hideClassStatusCode();
            return;
        }
    }
    //Sem se program dostane pouze pokud je třída nastavována jako soukromá --> zobraz vstupní kód
    showClassStatusCode();
    
	/*
    if ($("#change-class-status-code").val() !== initialCode)
    {
    	//Kód se změnil
    	$("#statusCancelButton").show();
    }
	*/
    
    if ($("#change-class-status-code").val().length !== 4 || parseInt($("#change-class-status-code").val()) != $("#change-class-status-code").val())
    {
        //Kód není platný --> skryj tlačítko pro uložení
        $("#change-class-status-confirm-button").addClass("disabled");
    }
    else
    {
        //Kód je platný
    	if ($("#change-class-status-code").val() !== initialCode)
    	{
    		 //Kód je platný a změnil se --> zobraz tlačítko pro uložení
            $("#change-class-status-confirm-button").removeClass("disabled");
    	}
    }
}
function changeClassStatusConfirm()
{
    var newStatus = $("#class-status-select .selected").text();
    var newCode = $("#change-class-status-code").val();
    
    var confirmation;
    switch (newStatus)
    {
        case "Veřejná":
            confirmation = confirm("Třída bude nastavena jako veřejná a všichni přihlášení uživatelé do ní budou mít přístup. Pokračovat?");
            break;
        case "Soukromá":
            confirmation = confirm("Třída bude nastavena jako soukromá a všichni uživatelé, kteří nikdy nezadali platný vstupní kód třídy, ztratí do třídy přístup. Pokračovat?");
            break;
        case "Uzamčená":
            confirmation = confirm("Třída bude uzamčena a žádní uživatelé, kteří nyní nejsou jejími členy do ní nebudou moci vstupit (včetně těch, kteří zadají platný vstupní kód v budoucnosti). Pokračovat?");
            break;
        default:
            return;
    }
    
    if (!confirmation){return;}
    
    $.post(ajaxUrl,
		{
    		action: 'update access',
			newStatus: newStatus,
			newCode: newCode
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "success")
					{
						initialStatus = newStatus;
						initialCode = newCode;

						//aktualizace zobrazovaných údajů
						$("#status").text(newStatus);
						$("#class-status-select .custom-option").removeClass("selected");
						$("#class-status-select .custom-option:contains(" + newStatus + ")").addClass("selected");

						//newMessage(message, "success");
						
						//Skrytí nastavení členů, pokud byla třída změněna na veřejnou
						if (newStatus === "Veřejná")
						{
							$("#members-management-button").hide();
						}
						else
						{
							$("#members-management-button").show();
						}
						
					    //Reset HTML
					    $("#change-class-status-button").show();
						$("#change-class-status").hide();
						$("#change-class-status").closest(".class-data-item").find(".class-property-value").show();
					}
					if (messageType === "error")
					{
						newMessage(message, "error");
					}
				}
			);
		},
		"json"
	);
}
function changeClassStatusCancel()
{	
	$("#change-class-status-button").show();
    $("#change-class-status").hide();
	$("#change-class-status").closest(".class-data-item").find(".class-property-value").show();
    
	//Toto má význam pouze při zrušení změn
	$("#class-status-select .custom-option").removeClass("selected");
	$initialStatus.addClass("selected");
	$("#class-status-select .custom-select-main span").text(initialStatus);
    $("#change-class-status-code").val(initialCode);
    statusChange();
}

function hideClassStatusCode() {
	$("#change-class-status-code, label[for='change-class-status-code']").hide();
}

function showClassStatusCode() {
	$("#change-class-status-code, label[for='change-class-status-code']").show();
}
/*-------------------------------------------------------*/
function deleteClass()
{
	$("#delete-class-button").hide();
	$("#delete-class").show();
	$("#delete-class1").show();
	$("#delete-class-password").focus();
	document.querySelector("#delete-class").scrollIntoView({ 
		behavior: 'smooth' 
	});
	changeClassNameCancel();
	changeClassStatusCancel();
}
function deleteClassVerify()
{
	var password = $("#delete-class-password").val();
	$.post(ajaxUrl,
		{
    		action: 'verify password',
			password: password
		},
		function (response, status) { ajaxCallback(response, status, deleteClassConfirm); },
		"json"
	);
}
function deleteClassConfirm(messageType, message, data)
{
	if (data.verified === true)
	{
		$("#delete-class1").hide();
		$("#delete-class2").show();
	}
	else
	{
		//TODO - ideálně zobrazit přímo ve formuláři
		newMessage("Zadali jste špatné heslo", "error");
		$("#delete-class-password").val("");
	}
}
function deleteClassFinal()
{
	var password = $("#delete-class-password").val();

	$.post(ajaxUrl,
		{
    		action: 'delete class',
			password: password
		},
		function (response, status)
		{
			ajaxCallback(response, status, 
				function (response)
				{
					if (response["messageType"] === "error")
					{
						newMessage(response["message"], "error");
					}
					//V případě úspěchu je přesměrování zařízeno v js/ajaxMediator.js
				}
			);
		},
		"json"
	);
}
function deleteClassCancel()
{
	$("#delete-class-password").val("");
	$("#delete-class-button").show();
	$("#delete-class").hide();
	$("#delete-class2").hide();
}