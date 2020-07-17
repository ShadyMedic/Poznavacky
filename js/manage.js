var classId;            //Ukládá ID spravované třídy (zda je přihlášený uživatel jejím správcem se kontroluje v PHP)
$(function()
{
	//Získání ID třídy z dokumentu
	classId = $("#id").text();
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