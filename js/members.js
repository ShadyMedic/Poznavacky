var deletedTableRow;    //Ukládá řádek tabulky členů, který je odstraňován

function kickUser(memberId, memberName)
{
    if (!confirm("Opravdu chcete odebrat uživatele " + memberName + " ze třídy?"))
    {
        return;
    }
    deletedTableRow = event.target.parentNode.parentNode.parentNode;
    $.post("class-update",
		{
    		action: 'kick member',
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
			else if (response["messageType"] === "success")
			{
				//TODO - nějak odstraň řádek s uživatelem z DOM
				//Odebrání uživatele z DOM
				deletedTableRow.remove();
			}
			deletedTableRow = undefined;
		}
	);
}
function inviteFormShow()
{
    $("#inviteButton").hide();
    $("#inviteForm").show();
}
function inviteFormHide()
{
	$("#inviteUserInput").val("");
    $("#inviteForm").hide();
    $("#inviteButton").show();
}
function inviteUser()
{
    var userName = $("#inviteUserInput").val();
    $.post("class-update",
		{
    		action: 'invite user',
			userName: userName
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Vynuluj a skryj formulář
			    inviteFormHide();
			    
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
}