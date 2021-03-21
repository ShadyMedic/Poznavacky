var deletedTableRow;    //Ukládá řádek tabulky potnávaček, který je odstraňován

//vše, co se děje po načtení stránky
$(function() {
  
  //Nastavení URL pro AJAX požadavky
  let ajaxUrl = window.location.href;
  if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
  ajaxUrl = ajaxUrl.replace('/manage/tests', '/class-update'); //Nahraď neAJAX akci AJAX akcí
  
	//eventy listenery tlačítek
	$(".test-action .delete-group-button").click(function(event) {deleteTest(event)})
	$("#new-test-button").click(function() {newTest()})
	$("#new-test-confirm-button").click(function() {newTestConfirm()})
	$("#new-test-cancel-button").click(function() {newTestCancel()})
  
})

function newTest()
{
	$("#new-test-button").hide();
	$("#new-test").show();
	$("#new-test-name").focus();
	$("#new-test")[0].scrollIntoView({ 
		behavior: 'smooth',
		block: "start" 
	});
}
function newTestCancel()
{
	$("#new-test-name").val("");
	$("#new-test").hide();
	$("#new-test-button").show();
}
function newTestConfirm()
{
	var testName = $("#new-test-name").val();
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
						newMessage(message, "error");
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
function deleteTest(event)
{
	let testId = $(event.target).attr('data-group-id');
	let name = $(event.target).attr('data-group-name');

    if (!confirm("Opravdu chcete trvale odstranit poznávačku " + name + "? Přírodniny, které tato poznávačka obsahuje ani jejich obrázky nebudou odstraněny, ale zůstanou nepřiřazeny, dokud je nepřidáte do jiné poznávačky. Tato akce je nevratná!"))
    {
    	return;
	}
  
	deletedTableRow = $(event.target).closest(".tests-data-item");
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
						newMessage(response["message"], "error");
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