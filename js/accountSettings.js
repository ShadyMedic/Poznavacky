$(function()
{
    //event listenery tlačítek
    $("#change-name-button").click(function() {changeName()})
    $("#change-name-confirm-button").click(function() {changeNameConfirm()})
    $("#change-name-cancel-button").click(function() {changeNameCancel()})
    $("#change-password-button").click(function() {changePassword()})
    $("#change-password-confirm-button").click(function() {changePasswordConfirm()})
    $("#change-password-cancel-button").click(function() {changePasswordCancel()})
    $("#change-email-button").click(function() {changeEmail()})
    $("#change-email-confirm-button").click(function() {changeEmailConfirm()})
    $("#change-email-cancel-button").click(function() {changeEmailCancel()})
    $("#delete-account-button").click(function() {deleteAccount()})
    $("#delete-account-confirm-button").click(function() {deleteAccountVerify()})
    $("#delete-account-final-confirm-button").click(function() {deleteAccountFinal()})
    $("#delete-account-cancel-button, #delete-account-final-cancel-button").click(function() {deleteAccountCancel()})
})

/**
 * Funkce rušící změnu jména
 */
function changeNameCancel()
{
    $("#change-name-button").show()
    $("#change-name").closest(".user-data-item").find(".user-property-value").show();
    $("#change-name").hide();
    $("#change-name .text-field").val("");
}

/**
 * Funkce rušící změnu hesla
 */
function changePasswordCancel()
{
    $("#change-password-button").show()
    $("#change-password").closest(".user-data-item").find(".user-property-value").show();
    $("#change-password").hide();
    $("#change-password .text-field").val("");
}

/**
 * Funkce rušící změnu emailu
 */
function changeEmailCancel()
{
    $("#change-email-button").show()
    $("#change-email").closest(".user-data-item").find(".user-property-value").show();
    $("#change-email").hide();
    $("#change-email .text-field").val("");
}

/**
 * Funkce zahajující žádost o změnu jména
 */
function changeName()
{
    $("#change-name-button").hide()
    $("#change-name").closest(".user-data-item").find(".user-property-value").hide();
    $("#change-name").show();
    $("#change-name-new").focus();

    changePasswordCancel();
    changeEmailCancel();
    deleteAccountCancel();
}

/**
 * Funkce potvrzující žádost o změnu jména
 */
function changeNameConfirm()
{
    let newName = $("#change-name-new").val();
    newName = encodeURIComponent(newName);
    
    $.post("menu/account-update",
        {
            action: "request name change",
            name: newName
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        //Reset HTML
                        changeNameCancel();
                    }
                    newMessage(message, messageType);
                }
            );
        },
        "json"
    );
}

/**
 * Funkce zahajující změnu hesla
 */
function changePassword()
{
    $("#change-password-button").hide()
    $("#change-password").closest(".user-data-item").find(".user-property-value").hide();
    $("#change-password").show();
    $("#change-password-old").focus();

    changeNameCancel();
    changeEmailCancel();
    deleteAccountCancel();
}

/**
 * Funkce potvrzující změnu hesla
 */
function changePasswordConfirm()
{
    let oldPass = $("#change-password-old").val();
    let newPass = $("#change-password-new").val();
    let rePass = $("#change-password-re-new").val();
    
    oldPass = encodeURIComponent(oldPass);
    newPass = encodeURIComponent(newPass);
    rePass = encodeURIComponent(rePass);
    
    $.post("menu/account-update",
        {
            action: "change password",
            oldPassword: oldPass,
            newPassword: newPass,
            rePassword: rePass
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        //Reset HTML
                        changePasswordCancel();
                    }
                    else if (messageType === "error")
                    {
                        //Výmaz nového hesla a zobrazení pole pro nové heslo poprvé
                        $("#change-password-new").val("");
                        $("#change-password-re-new").val("");
                    }                
                    newMessage(message, messageType);
                }
            );
        },
    "json"
    );
}

/**
 * Funkce zahajující změnu emailu
 */
function changeEmail()
{
    $("#change-email-button").hide()
    $("#change-email").closest(".user-data-item").find(".user-property-value").hide();
    $("#change-email").show();
    $("#change-email-password").focus();

    changeNameCancel();
    changePasswordCancel();
    deleteAccountCancel();
}

/**
 * Funkce potvrzující změnu emailu
 */
function changeEmailConfirm()
{
    let password = $("#change-email-password").val();
    let newEmail = $("#change-email-new").val();
    
    if (newEmail.length == 0)
    {
        let confirmMessage = "Opravdu chcete ze svého účtu odebrat e-mailovou adresu? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo.";
        newConfirm(confirmMessage, "Odebrat", "Zrušit", function(confirm)
        {
            if (confirm) changeEmailFinal(password, newEmail);
            else return;
        });    
    }
    else changeEmailFinal(password, newEmail);
}

/**
 * Funkce odesílající požadavek na změnu emailu, pokud byla změna potvrzena
 * @param {string} password Heslo uživatele
 * @param {string} newEmail Nový email uživatele
 */
function changeEmailFinal(password, newEmail)
{
    $.post("menu/account-update",
        {
            action: "change email",
            password: password,
            newEmail: newEmail
        },
        function (response, code){
            ajaxCallback(response, code,
                function (messageType, message, data)
                {
                    //Funkce zajišťující změnu e-mailu v DOM v případě úspěšné změny
                    if (messageType === 'success')
                    {
                        $("#email-address").text(decodeURIComponent(newEmail));
                        
                        //Reset HTML
                        changeEmailCancel();
                    }
                    newMessage(message, messageType);
                }
            );
        },
        "json"
    );
}

/**
 * Funkce zahajující odstranění účtu
 */
function deleteAccount()
{
    $("#delete-account-button").hide();
    $("#delete-account").show();
    $("#delete-account1").show();
    $("#delete-account-password").focus();
    $("#delete-account")[0].scrollIntoView({ 
        behavior: 'smooth',
        block: "start" 
    });

    changeNameCancel();
    changePasswordCancel();
    changeEmailCancel();
}

/**
 * Funkce ověřující heslo uživatele při odstraňování účtu
 */
function deleteAccountVerify()
{
    let password = $("#delete-account-password").val();
    
    $.post("menu/account-update",
        {
            action: "verify password",
            password: password
        },
        function (response, status) { ajaxCallback(response, status, deleteAccountConfirm); },
        "json"
    );
}

/**
 * Funkce zobrazující druhou fázi odstranění účtu v případě ověřeného hesla, v opačném případě zobrazující chybovou hlášku
 * @param {string} messageType Typ hlášky
 * @param {string} message Text hlášky
 * @param {string} data Dodatečná data
 */
function deleteAccountConfirm(messageType, message, data)
{
    if (data.verified === true)
    {
        $("#delete-account2").show();
        $("#delete-account1").hide();
    }
    else
    {
        $("#delete-account-message").text(message);
        $("#delete-account-password").val("");
    }
}

/**
 * Funkce odesílající požadavek na odstranění účtu
 */
function deleteAccountFinal()
{
    let password = $("#delete-account-password").val();
    
    $.post("menu/account-update",
        {
            action: "delete account",
            password: password
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        //Uvedení HTML do původního stavu (má smysl pouze v případě selhání)
                        deleteAccountCancel();
                    }
                    newMessage(message, messageType);
                }
            )
        },
        "json"
    );
}

/**
 * Funkce rušící odstranění účtu
 */
function deleteAccountCancel()
{
    $("#delete-account-password").val("");
    $("#delete-account-button").show();
    $("#delete-account").hide();
    $("#delete-account2").hide();
    $("#delete-account-message").text("");
}