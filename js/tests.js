var deletedTableRow;    //Ukládá řádek tabulky potnávaček, který je odstraňován
var ajaxUrl;

//vše, co se děje po načtení stránky
$(function() {
  
  //Nastavení URL pro AJAX požadavky
  ajaxUrl = window.location.href;
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
	$("#new-test")[0].scrollIntoView({ 
		behavior: 'smooth',
		block: "start" 
	});
	$("#new-test-name").focus();
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
						//Získej z data.newGroupData informace a zobraz je v tabulce v DOM
						let groupData = data.newGroupData;
						let groupDomItem = $('#test-data-item-template').html();
						groupDomItem = groupDomItem.replace(/{id}/g, groupData.id);
						groupDomItem = groupDomItem.replace(/{name}/g, groupData.name);
						groupDomItem = groupDomItem.replace(/{url}/g, groupData.url);
						groupDomItem = groupDomItem.replace(/{parts}/g, groupData.parts);
						$('.tests-data-section').append(groupDomItem);
						newMessage(response["message"], "success");

						//Schování formuláře
						newTestCancel();
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

    if (!confirm("Opravdu chcete trvale odstranit poznávačku " + name + "? Přírodniny, které tato poznávačka obsahuje ani jejich obrázky nebudou odstraněny. Tato akce je nevratná!"))
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
						newMessage(response["message"], "success");
					}
					deletedTableRow = undefined;
				}
			);
		},
		"json"
	);
}