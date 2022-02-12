$(function() {
    $("#tab5-link").addClass("active-tab"); //Nabarvi zvolenou záložku

    const urlParams = new URLSearchParams(new URL(window.location.href).search);
    if (urlParams.has("to")) { $("#email-address").val(urlParams.get("to")); }  //Nastav adresu příjemce (pokud má být předvyplněna)
});

var emailModified = true;    //Proměnná uchovávající informaci o tom, zda byl formulář pro odeslání e-mailu od posledního odeslání modifikován
function emailModification()
{
    emailModified = true;
}
function previewEmailMessage()
{
    let rawHTMLbody = $("#email-message").val();
    let rawHTMLfooter = $("#email-footer").val();
    $.post('administrate-action',
        {
            action:"preview email",
            htmlMessage:rawHTMLbody,
            htmlFooter:rawHTMLfooter
        },
        function(response)
        {
            let result = response['content'];
            $("#email-editor").hide();
            $("#email-preview-btn").hide();

            $("#email-preview").html(result);
            $("#email-preview").show();
            $("#email-edit-btn").show();
        }
    );
}
function editEmailMessage()
{
    $("#email-edit-btn").hide();
    $("#email-preview").hide();

    $("#email-editor").show();
    $("#email-preview-btn").show();
}
function sendMail()
{
    //Ochrana před odesíláním duplicitních e-mailů
    if (!emailModified)
    {
        if (!confirm("Opravdu chcete odeslat ten samý e-mail znovu?"))
        {
            return;
        }
    }

    let sender = $("#email-sender").val();
    let fromAddress = $("#email-sender-address").val();
    let addressee = $("#email-address").val();
    let subject = $("#email-subject").val();
    let rawHTMLbody = $("#email-message").val();
    let rawHTMLfooter = $("#email-footer").val();

    $("#status-info").show();
    $("#email-send-btn").attr("disabled", true);

    $.post('administrate-action',
        {
            action:"send email",
            addressee:addressee,
            subject:subject,
            htmlMessage:rawHTMLbody,
            htmlFooter:rawHTMLfooter,
            sender:sender,
            fromAddress:fromAddress
        },
        function(response)
        {
            $("#status-info").hide();
            $("#email-send-btn").removeAttr("disabled");

            emailModified = false;

            if (response["messageType"] === "error" || response["messageType"] === "success")
            {
                //TODO - zobraz nějak chybovou nebo úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
                alert(response["message"]);
            }
        }
    );
}