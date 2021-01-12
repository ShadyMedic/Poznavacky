var deletedTableRow;    //Ukládá řádek tabulky členů, který je odstraňován

//vše, co se děje po načtení stránky
$(function() {

	//event listenery tlačítek
	$("#invite-user-button").click(function() {inviteFormShow()})
	$("#invite-user-confirm-button").click(function() {inviteUser()})
	$("#invite-user-cancel-button").click(function() {inviteFormHide()})
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
})

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
						//TODO - nějak odstraň řádek s uživatelem z DOM
						//Odebrání uživatele z DOM
						deletedTableRow.remove();
					}
					deletedTableRow = undefined;
				}
			);
		},
		"json"
	);
}
function inviteFormShow()
{
    $("#invite-user-button").hide();
    $("#invite-user-form").show();
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
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "success")
					{
						//Vynuluj a skryj formulář
					    inviteFormHide();
					    
					    //TODO - zobraz nějak úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
						alert(message);
					}
					if (messageType === "error")
					{
						//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
						alert(message);
					}
				}
			);
		},
		"json"
	);
}