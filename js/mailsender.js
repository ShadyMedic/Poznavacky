$(function()
{    
    //nastavení adresy příjemce (pokud má být předvyplněna)
    const urlParams = new URLSearchParams(new URL(window.location.href).search);
    if (urlParams.has("to"))
    {
        $("#email-address").val(urlParams.get("to"));
    }

    //event listenery tlačítek
    $("#preview-email-button").click(function() {previewEmail()})
    $("#edit-email-button").click(function() {editEmail()})
    $("#send-email-button").click(function() {sendMail()})

    $("#email-info input").change(function() {emailModifiedCheck()})  
    $("#email-editor textarea").change(function() {emailModifiedCheck()})  
})

var emailModified = true;    //proměnná uchovávající informaci o tom, zda byl formulář pro odeslání e-mailu od posledního odeslání modifikován

function emailModifiedCheck()
{
    emailModified = true;
}

function previewEmail()
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
            $("#preview-email-button").hide();

            $("#email-preview").html(result);
            $("#email-preview").show();
            $("#edit-email-button").show();
        }
    );
}

function editEmail()
{
    $("#edit-email-button").hide();
    $("#email-preview").hide();

    $("#email-editor").show();
    $("#preview-email-button").show();
}

function sendMail()
{
    //ochrana před odesíláním duplicitních e-mailů
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
    $("#send-email-button").addClass("disabled");

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
            $("#send-email-button").removeClass("disabled");

            emailModified = false;

            if (response["messageType"] === "error" || response["messageType"] === "success")
            {
                alert(response["message"]);
            }
        }
    );
}