$(function() {
    $("#recover-password-form").submit(function(event) {submitForm(event)})
  
    //event listenery inputů
    $("#new-pass").on("input", function() {checkNewPassword()})
    $("#new-repass").on("input", function() {checkNewRePassword()})
})

//odeslání formuláře pomocí AJAX
function submitForm(event)
{
    event.preventDefault();

    //Vypni tlačítko pro odeslání
    $("#submit-button").prop("disabled", true);

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
                        //Zapni tlačítko pro odeslání
                        $("#change-password-button").prop("disabled", false);

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

//funkce kontrolující správně zadané heslo při obnově hesla
function checkNewPassword() {
	var passwordAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\''";
	var newPasswordMessage;
	if ($("#new-pass").val().length == 0)
		newPasswordMessage = "Heslo musí být vyplněno."
	else if ($("#new-pass").val().length < 6)
		newPasswordMessage = "Heslo musí být alespoň 6 znaků dlouhé."
	else if ($("#new-pass").val().length > 31)
		newPasswordMessage = "Heslo může být nejvíce 31 znaků dlouhé."
	else newPasswordMessage = "";
	for (let i = 0; i < $("#new-pass").val().length; i++ ) {
		if (!passwordAllowedChars.includes($("#new-pass").val()[i]))
			newPasswordMessage = "Heslo obsahuje nepovolené znaky."
	}
	$("#new-pass-message").text(newPasswordMessage);

	if (newPasswordMessage == "")
		$("#new-pass").addClass("checked");
	else $("#new-pass").removeClass("checked");

	checkNewRePassword();
}

//funkce kontrolující správně zadané heslo znovu při obnově hesla
function checkNewRePassword() {
	var newRePasswordMessage;
	if ($("#new-repass").val().length == 0)
		newRePasswordMessage = "Heslo znovu musí být vyplněno."
	else if ($("#new-repass").val() != $("#new-pass").val())
		newRePasswordMessage = "Zadaná hesla se neshodují."
	else newRePasswordMessage = "";

	if (newRePasswordMessage == "")
		$("#new-repass").addClass("checked");
	else $("#new-repass").removeClass("checked");

	$("#new-repass-message").text(newRePasswordMessage);
}

