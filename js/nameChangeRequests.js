$(function() { $("#tab4-link").addClass("active-tab"); }); //Nabarvi zvolenou záložku

function acceptNameChange(event, objectType, requestId)
{
    let action = (objectType === "user") ? "accept user name change" : "accept class name change";
    $.post('administrate-action',
        {
            action:action,
            reqId:requestId
        },
        function(response)
        {
            if (response["messageType"] === "error")
            {
                //TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
                alert(response["message"]);
            }
            else
            {
                //Odebrání žádosti z DOM
                event.target.parentNode.parentNode.parentNode.remove();
            }
        }
    );
}
function declineNameChange(event, objectType, requestId)
{
    let reason = prompt("Zadejte prosím důvod zamítnutí žádosti (uživatel jej obdrží e-mailem, pokud jej zadal). Nevyplnění tohoto pole bude mít za následek zrušení zamítnutí.");
    if (reason === false || reason.length === 0){ return; }
    let action = (objectType === "user") ? "decline user name change" : "decline class name change";
    $.post('administrate-action',
        {
            action:action,
            reqId:requestId,
            reason: reason
        },
        function(response)
        {
            if (response["messageType"] === "error")
            {
                //TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
                alert(response["message"]);
            }
            else
            {
                //Odebrání žádosti z DOM
                event.target.parentNode.parentNode.parentNode.remove();
            }
        }
    );
}