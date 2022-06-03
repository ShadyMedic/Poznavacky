$(function()
{   
    //event listenery tlačítek
    $(".accept-name-change-button").click(function(event) {acceptNameChange(event)})
    $(".decline-name-change-button").click(function(event) {declineNameChange(event)})
})

function acceptNameChange(event)
{
    let $request = $(event.target).closest(".name-change-request-data-item");

    let action = $request.attr("data-object-type") == "user" ? "accept user name change" : "accept class name change";
    let requestId = $request.attr("data-request-id");

    $.post('administrate-action',
        {
            action:action,
            reqId:requestId
        },
        function(response)
        {
            if (response["messageType"] === "error")
            {
                alert(response["message"]);
            }
            else
            {
                $request.remove();
            }
        }
    );
}
function declineNameChange(event)
{
    let reason = prompt("Zadejte prosím důvod zamítnutí žádosti (uživatel jej obdrží e-mailem, pokud jej zadal). Nevyplnění tohoto pole bude mít za následek zrušení zamítnutí.");
    if (reason === false || reason.length === 0)
    {
        return;
    }

    let $request = $(event.target).closest(".name-change-request-data-item");

    let action = $request.attr("data-object-type") == "user" ? "decline user name change" : "decline class name change";
    let requestId = $request.attr("data-request-id");

    $.post('administrate-action',
        {
            action: action,
            reqId: requestId,
            reason: reason
        },
        function(response)
        {
            if (response["messageType"] === "error")
            {
                alert(response["message"]);
            }
            else
            {
                $request.remove();
            }
        }
    );
}