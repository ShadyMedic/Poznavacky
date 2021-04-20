$(function()
{
    //event listener submitu
    $("#recover-password-form").submit(function(event) {submitForm(event)})
  
    //event listenery inputů
    $("#new-pass").on("input", function() {checkNewPassword()})
    $("#new-repass").on("input", function() {checkNewRePassword()})
})

/**
 * Funkce odesílající požadavek na obnovu hesla
 * @param {event} event 
 */
function submitForm(event)
{
    event.preventDefault();

    //vypnutí tlačítka pro odeslání
    $("#change-password-button").prop("disabled", true);

    let token = $("#token").val();
    let pass = $("#new-pass").val();
    let repass = $("#new-repass").val();
  
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
                        //zapnutí tlačítka pro odeslání
                        $("#change-password-button").prop("disabled", false);

                        $("#new-server-message").text(message);
                    }
                    else if (messageType === "success")
                    {
                        $("#new-server-message").text("");
                        newMessage(message, "success");

                        //přesměrování uživatele za tři vteřiny zpět na domovskou stránku
                        setInterval(function () { window.location = ''; }, 3000);
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce kontrolující správně zadané heslo při obnově hesla
 */
function checkNewPassword()
{
    let passwordAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\''";
    let newPasswordMessage;

    //heslo není vyplněno
    if ($("#new-pass").val().length == 0)
    {
        newPasswordMessage = "Heslo musí být vyplněno.";
    }
    //heslo je kratší než 6 znaků
    else if ($("#new-pass").val().length < 6)
    {
        newPasswordMessage = "Heslo musí být alespoň 6 znaků dlouhé.";
    }
    //heslo je delší než 31 znaků
    else if ($("#new-pass").val().length > 31)
    {
        newPasswordMessage = "Heslo může být nejvíce 31 znaků dlouhé.";
    }
    else newPasswordMessage = "";

    //některý ze znaků není povolený
    for (let i = 0; i < $("#new-pass").val().length; i++ )
    {
        if (!passwordAllowedChars.includes($("#new-pass").val()[i]))
        {
            newPasswordMessage = "Heslo obsahuje nepovolené znaky.";
        }
    }

    $("#new-pass-message").text(newPasswordMessage);

    if (newPasswordMessage == "")
    {
        $("#new-pass").addClass("checked");
    }
    else $("#new-pass").removeClass("checked");

    checkNewRePassword();
}

/**
 * Funkce kontrolující správně zadané heslo znovu při obnově heslo
 */
function checkNewRePassword()
{
    let newRePasswordMessage;

    //heslo znovu není vyplněno
    if ($("#new-repass").val().length == 0)
    {
        newRePasswordMessage = "Heslo znovu musí být vyplněno.";
    }
    //heslo znovu je jiné než heslo
    else if ($("#new-repass").val() != $("#new-pass").val())
    {
        newRePasswordMessage = "Zadaná hesla se neshodují.";
    }
    else newRePasswordMessage = "";

    if (newRePasswordMessage == "")
    {
        $("#new-repass").addClass("checked");
    }
    else $("#new-repass").removeClass("checked");

    $("#new-repass-message").text(newRePasswordMessage);
}

