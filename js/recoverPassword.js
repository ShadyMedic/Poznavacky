$(function() {
    $("#recover-password-form").submit(function(event) {submitForm(event)})
})

//odeslání formuláře pomocí AJAX
function submitForm(event)
{
    event.preventDefault();

    //Vypni tlačítko pro odeslání
    $("#submit-button").prop("disabled", true);

    let token = $("#token-input").val();
    let pass = $("#pass-input").val();
    let repass = $("#repass-input").val();

    let code = $("#class-code-input").val();

    $.post('token-password-change',
        {
            token: token,
            pass: pass,
            repass: repass
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        //Zapni tlačítko pro odeslání
                        $("#submit-button").prop("disabled", false);

                        //Chyba při zpracování požadavku (zřejmě neplatný formát kódu)
                        newMessage(message, "error"); //TODO zobrazit ve formuláři
                    }
                    else if (messageType === "success")
                    {
                        newMessage(message, "success");

                        //Přesměruj uživatele za tři vteřiny zpět na domovskou stránku
                        setInterval(function () { window.location = ''; }, 3000);
                    }
                }
            );
        },
        "json"
    );
}

