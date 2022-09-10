$(function()
{
    //event listenery tlačítek
    $(".import-alerts-button").click(function() {importAlerts()})
    $(".resolve-alert-button").click(function(event) {resolveAlerts(event)})
})

function importAlerts()
{
    $.post('administrate-action',
        {
            action:"import alerts"
        },
        function(response)
        {
            alert(response["message"]);
            if (response["messageType"] === "success")
            {
                location.reload() //cuz lazy ¯\_(ツ)_/¯
            }
        }
    );
}

function resolveAlerts(event) {
    if (!confirm('Opravdu odebrat toto chybové hlášení?')) { return; }

    let alertId = $(event.target).closest('tr').attr('data-alert-id');
    let $alert = $(event.target).closest(".alert-data-item");

    $.post('administrate-action',
        {
            action:"resolve alert",
            alertId:alertId
        },
        function(response)
        {
            if (response["messageType"] === "success")
            {
                $alert.remove();
            }
            if (response["messageType"] === "error")
            {
                alert(response["message"]);
            }
        }
    );
}