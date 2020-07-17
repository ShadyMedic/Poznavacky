var classId;            //Ukládá ID spravované třídy (zda je přihlášený uživatel jejím správcem se kontroluje v PHP)
var initialStatus;      //Ukládá status třídy uložený v databázi
var initialCode;        //Ukládá vstupní kód třídy uložený v databázi
$(function()
{
	//Získání ID třídy z dokumentu
	classId = $("#id").text();
    initialStatus = $("#statusInput").val();
    initialCode = $("#statusCodeInputField").val();
    
    //Správně zobrazit tlačítko a vstupní pole pro kód
    statusChange();
});

function requestNameChange()
{
    $("#changeNameButton").hide();
    $("#changeNameInput").show();
}
function confirmNameChange()
{
    var newName = $("#changeNameInputField").val();
    newName = encodeURIComponent(newName);
    
    $.post("class-update",
		{
    		action: 'request name change',
			classId: classId,
			newName: newName
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Reset DOM
				cancelNameChange();
				//TODO - zobraz nějak úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
    
    //Reset HTML
    cancelNameChange();
}
function cancelNameChange()
{
    $("#changeNameInputField").val("");
    $("#changeNameInput").hide();
    $("#changeNameButton").show();
}
/*-------------------------------------------------------*/
function statusChange()
{
    if ($("#statusInput").val() === initialStatus)
    {
        //Status třídy se nezměnil
        if ($("#statusInput").val() !== "Soukromá")
        {
            //Třída není ani jako soukromá --> není možné změnit vstupní kód --> vše skrýt
            $("#statusSaveButton").hide();
            $("#statusCancelButton").hide();
            $("#statusCodeInput").hide();
            return;
        }
    }
    else
    {
        //Status třídy se změnil
    	$("#statusCancelButton").show();
        if ($("#statusInput").val() !== "Soukromá")
        {
            //Třída není nastavována jako soukromá --> zobraz tlačítko, ale skryj vstupní kód
            $("#statusSaveButton").show();
            $("#statusCodeInput").hide();
            return;
        }
    }
    //Sem se program dostane pouze pokud je třída nastavována jako soukromá --> zobraz vstupní kód
    $("#statusCodeInput").show();
    
    if ($("#statusCodeInputField").val() !== initialCode)
    {
    	//Kód se změnil
    	$("#statusCancelButton").show();
    }
    
    if ($("#statusCodeInputField").val().length !== 4 || parseInt($("#statusCodeInputField").val()) != $("#statusCodeInputField").val())
    {
        //Kód není platný --> skryj tlačítko pro uložení
        $("#statusSaveButton").hide();
    }
    else
    {
        //Kód je platný
    	if ($("#statusCodeInputField").val() !== initialCode)
    	{
    		 //Kód je platný a změnil se --> zobraz tlačítko pro uložení
            $("#statusSaveButton").show();
    	}
    }
}
function confirmStatusChange()
{
    var newStatus = $("#statusInput").val();
    var newCode = $("#statusCodeInputField").val();
    
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
    
    $.post("class-update",
		{
    		action: 'update access',
			classId: classId,
			newStatus: newStatus,
			newCode: newCode
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				initialStatus = newStatus;
				initialCode = newCode;
				//TODO - zobraz (možná) nějak úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
				//alert(response["message"]);
				
				//Skrytí nastavení členů, pokud byla třída změněna na veřejnou
				if (newStatus === "Veřejná")
				{
					$("#membersManagement").hide();
					$("#membersUnavailable").show();
				}
				else
				{
					$("#membersUnavailable").hide();
					$("#membersManagement").show();
				}
				
			    //Reset HTML
			    cancelStatusChange();
			}
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
}
function cancelStatusChange()
{	
	$("#statusSaveButton").hide();
    $("#statusCancelButton").hide();
    
	//Toto má význam pouze při zrušení změn
    $("#statusInput").val(initialStatus);
    $("#statusCodeInputField").val(initialCode);
    statusChange();
}
/*-------------------------------------------------------*/
function kickUser(memberId, memberName)
{
    if (!confirm("Opravdu chcete odebrat uživatele " + memberName + " ze třídy?"))
    {
        return;
    }
    $.post("class-update",
		{
    		action: 'kick member',
			classId: classId,
			memberId: memberId
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
}
/*-------------------------------------------------------*/
function inviteFormShow()
{
    $("#inviteButton").hide();
    $("#inviteForm").show();
}
function inviteFormHide()
{
    $("#inviteForm").hide();
    $("#inviteButton").show();
}
function inviteUser()
{
    var userName = $("#inviteUserInput").val();
    $.post("class-update",
		{
    		action: 'invite user',
			classId: classId,
			userName: userName
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
    
    //Reset HTML
    inviteFormHide();
}
/*-------------------------------------------------------*/
function createTest()
{
	$("#createButton").hide();
	$("#createForm").show();
}
function createTestHide()
{
	$("#createForm").hide();
	$("#createButton").show();
}
function createTestSubmit()
{
	var testName = $("#createInput").val();
	$.post("class-update",
		{
    		action: 'create test',
			classId: classId,
			name: testName
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
	
	//Reset HTML
	createTestHide();
}
/*-------------------------------------------------------*/
function deleteTest(id, name)
{
    if (confirm("Opravdu chcete trvale odstranit poznávačku " + name + "? Přírodniny, které tato poznávačka obsahuje ani jejich obrázky nebudou odstraněny, ale zůstanou nepřiřazeny, dokud je nepřidáte do jiné poznávačky. Tato akce je nevratná!"))
    {
    	$.post("class-update",
			{
	    		action: 'delete test',
				testId: testId
			},
			function (response)
			{
				response = JSON.parse(response);
				if (response["messageType"] === "error")
				{
					//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
					alert(response["message"]);
				}
			}
		);
    }
}
/*-------------------------------------------------------*/