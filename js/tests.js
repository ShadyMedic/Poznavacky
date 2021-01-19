var deletedTableRow;    //Ukládá řádek tabulky potnávaček, který je odstraňován

//Nastavení URL pro AJAX požadavky
let ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/manage/tests', '/class-update'); //Nahraď neAJAX akci AJAX akcí

function createTest()
{
	$("#createButton").hide();
	$("#createForm").show();
}
function createTestHide()
{
	$("#createInput").val("");
	$("#createForm").hide();
	$("#createButton").show();
}
function createTestSubmit()
{
	var testName = $("#createInput").val();
	$.post(ajaxUrl,
		{
    		action: 'create test',
			testName: testName
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data) 
				{
					if (messageType === "error")
					{
						//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
						alert(message);
					}
					else if (messageType === "success")
					{
						//Znovu načti stránku, ať se zobrazí nová poznávačka v DOM
						location.reload();
					}
				}
			);
		},
		"json"
	);
}
/*-------------------------------------------------------*/
function deleteTest(testId, name)
{
    if (!confirm("Opravdu chcete trvale odstranit poznávačku " + name + "? Přírodniny, které tato poznávačka obsahuje ani jejich obrázky nebudou odstraněny, ale zůstanou nepřiřazeny, dokud je nepřidáte do jiné poznávačky. Tato akce je nevratná!"))
    {
    	return;
	}
	deletedTableRow = event.target.parentNode.parentNode.parentNode;
	$.post(ajaxUrl,
		{
    		action: 'delete test',
			testId: testId
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (response["messageType"] === "error")
					{
						//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
						alert(response["message"]);
					}
					else if (response["messageType"] === "success")
					{
						deletedTableRow.remove();
					}
					deletedTableRow = undefined;
				}
			);
		},
		"json"
	);
}