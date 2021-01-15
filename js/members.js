var deletedTableRow;    //Ukládá řádek tabulky členů, který je odstraňován

//vše, co se děje po načtení stránky
$(function() {

	//event listenery tlačítek
	$(".kick-user-button").click(function(event) {kickUser(event)})
	$(".kick-user-confirm-button").click(function(event) {kickUserConfirm(event)})
	$(".kick-user-cancel-button").click(function(event) {kickUserCancel(event)})
	$("#invite-user-button").click(function() {inviteFormShow()})
	$("#invite-user-confirm-button").click(function() {inviteUser()})
	$("#invite-user-cancel-button").click(function() {inviteFormHide()})
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
})


function kickUser(event) {
	$(event.target).closest(".members-data-item").find(".kick-user-button").hide();
	$(event.target).closest(".members-data-item").find(".kick-user").show();
} 

function kickUserCancel(event) {
	$(event.target).closest(".members-data-item").find(".kick-user-button").show();
	$(event.target).closest(".members-data-item").find(".kick-user").hide();
} 

function kickUserConfirm(event)
{
	let memberId = $(event.target).closest(".members-data-item").attr("data-member-id");

    deletedTableRow = $(event.target).parent().parent().remove();
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
	$("#invite-user-name").val("");
    $("#invite-user-button").show();
    $("#invite-user-form").hide();
}
function inviteUser()
{
    var userName = $("#invite-user-name").val();
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