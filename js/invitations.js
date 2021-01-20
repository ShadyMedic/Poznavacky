$(function()
{
	//event listenery tlačítek
	$("#accept-invitation-button").click(function(event) {acceptInvitation(event)})
	$("#reject-invitation-button").click(function(event) {rejectInvitation(event)})
})

function acceptInvitation(event)
{
	event.preventDefault();
	let $activeInvitationForm = $(event.target).closest(".invitation").find("form");
	$activeInvitationForm.find("input[name='invitationAnswer']").val("accept");
	$activeInvitationForm.submit();
}
function rejectInvitation(event)
{
	event.preventDefault();
	let $activeInvitationForm = $(event.target).closest(".invitation").find("form");
	$activeInvitationForm.find("input[name='invitationAnswer']").val("reject");
	$activeInvitationForm.submit();
}
