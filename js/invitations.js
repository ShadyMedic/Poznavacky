$(function()
{
    //event listenery tlačítek
    $("#accept-invitation-button").click(function(event) {acceptInvitation(event)})
    $("#reject-invitation-button").click(function(event) {rejectInvitation(event)})
})

/**
 * Funkce potvrzující pozvánku do třídy
 * @param {event} event 
 */
function acceptInvitation(event)
{
    event.preventDefault();
    
    let $activeInvitationForm = $(event.target).closest(".invitation").find("form");

    $activeInvitationForm.find("input[name='invitationAnswer']").val("accept");
    $activeInvitationForm.submit();
}

/**
 * Funkce odmítající pozvánku do třídy
 * @param {event} event 
 */
function rejectInvitation(event)
{
    event.preventDefault();

    let $activeInvitationForm = $(event.target).closest(".invitation").find("form");

    $activeInvitationForm.find("input[name='invitationAnswer']").val("reject");
    $activeInvitationForm.submit();
}
