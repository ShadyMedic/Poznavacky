function enterClassCode()
{
	$("#classCodeButton").hide();
	$("#classCode").show();
}
function closeClassCode()
{
	$("#classCode").hide();
	$("#classCodeButton").show();
}
/*-------------------------------------------------------*/
function showInvitations()
{
	$("#invitationsButton").hide();
	$("#invitations").show();
}
function hideInvitations()
{
	$("#invitations").hide();
	$("#invitationsButton").show();
}
/*-------------------------------------------------------*/
function acceptInvitation(event)
{
	event.preventDefault();
	$(event.target.parentNode.parentNode).attr("id", "activeInvitationForm");
	$("#activeInvitationForm > input[name='invitationAnswer']").val("accept");
	$("#activeInvitationForm").submit();
}
function rejectInvitation(event)
{
	event.preventDefault();
	$(event.target.parentNode.parentNode).attr("id", "activeInvitationForm");
	$("#activeInvitationForm > input[name='invitationAnswer']").val("reject");
	$("#activeInvitationForm").submit();
}