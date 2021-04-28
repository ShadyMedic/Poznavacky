$(function()
{
    //zobrazení cookies alertu
    setTimeout(function() {
        $("#cookies-alert").addClass("show");
    }, 1000);

    //event listenery tlačítek
    $("#hide-login-section-button").click(function() {hideLoginSection()})
    $("#hide-cookies-alert-button").click(function() {hideCookiesAlert()})
    $(".show-login-section-login-button, .show-login-section-register-button, .show-login-section-password-recovery-button").click(function(event) {showLoginSection(event)});
    $("#demo-button").click(function() {demoLogin()})
    $("#learn-more-button").click(function() {learnMore()})
    $("#back-to-top-button").click(function() {backToTop()})

    //event listener kliknutí myši
    $(document).mouseup(function(e) {mouseUpChecker(e)})

    //event listener scrollování
    $(window).scroll(function(e) {showScrollButton(e)})

    //event listenery inputů
    $("#login-name").on("input", function() {checkLoginName()})
    $("#login-pass").on("input", function() {checkLoginPassword()})
    $("#register-name").on("input", function() {checkRegisterName()})
    $("#register-pass").on("input", function() {checkRegisterPassword()})
    $("#register-repass").on("input", function() {checkRegisterRePassword()})
    $("#register-email").on("input", function() {checkRegisterEmail()})
    $("#password-recovery-email").on("input", function() {checkRecoveryEmail()})
  
    //odeslání AJAX požadavku pro kontrolu neexistence uživatele při registraci
    $("#register-name").blur(function()
    {
        if (!($("#register-name").val() != "" && checkRegisterName())) return
        enqueueAjaxRequest
        (
        new ajaxRequest
        (
            'index-forms',
            {
            text: $("#register-name").val(),
            type: 'u'
            },
            function(messageType, message, data){ isStringUniqueCallback(messageType, message, data, true, $("#register-name")); }
        )
        );
    });

    //Odeslání AJAX poýadavku pro kontrolu neexistence e-mailu při registraci
    $("#register-email").blur(function()
    {
        if (!($("#register-email").val() != "" && checkRegisterEmail())) return
        enqueueAjaxRequest
        (
        new ajaxRequest
        (
            'index-forms',
            {
            text: $("#register-email").val(),
            type: 'e'
            },
            function(messageType, message, data){ isStringUniqueCallback(messageType, message, data, true, $("#register-email")); }
        )
        );
    });

    $("#register-form, #login-form, #pass-recovery-form").on("submit", function(e) {formSubmitted(e)})
})

/**
 * Funkce scrollující do info sekce
 */
function learnMore()
{
    $("#index-info-section")[0].scrollIntoView({ 
        behavior: 'smooth',
        block: "start" 
    });
}

/**
 * Funkce scrollující na začátek stránky
 */
function backToTop()
{
    $("#index")[0].scrollIntoView({ 
        behavior: 'smooth',
        block: "start" 
    });
}

/**
 * Funkce kontrolující správně zadané jméno při přihlašování
 */
function checkLoginName()
{
    let loginNameMessage;

    //přihlašovací jméno není vyplněno
    if($("#login-name").val().length == 0) 
    {
        loginNameMessage = "Jméno musí být vyplněno.";
    }
    else loginNameMessage = "";

    $("#login-name-message").text(loginNameMessage);
}

/**
 * Funkce kontrolující správně zadané heslo při přihlašování
 */
function checkLoginPassword()
{
    let loginPasswordMessage;

    //heslo není vyplněno
    if($("#login-pass").val().length == 0)
    {
        loginPasswordMessage = "Heslo musí být vyplněno.";
    }
    else loginPasswordMessage = "";

    $("#login-pass-message").text(loginPasswordMessage);
}

/**
 * Funkce kontrolující správně zadané jméno při registraci
 * @returns True, pokud je jméno zadáno správně, false, pokud je zadáno špatně (nutné pro požadavek na kontrolu unikátnosti)
 */
function checkRegisterName()
{
    let nameAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ";
    let registerNameMessage;

    //jméno není vyplněno
    if ($("#register-name").val().length == 0)
    {
        registerNameMessage = "Jméno musí být vyplněno.";
    }
    //jméno je kratší než 4 znaky
    else if ($("#register-name").val().length < 4)
    {
        registerNameMessage = "Jméno musí být alespoň 4 znaky dlouhé.";
    }
    //jméno je delší než 15 znaků
    else if ($("#register-name").val().length > 15)
    {
        registerNameMessage = "Jméno může být nejvíce 15 znaků dlouhé.";
    }

    else registerNameMessage = "";

    //některý ze znaků není povolený
    for (let i = 0; i < $("#register-name").val().length; i++ )
    {
        if (!nameAllowedChars.includes($("#register-name").val()[i]))
        {
            registerNameMessage = "Jméno obsahuje nepovolené znaky.";
        }
    }

    $("#register-name-message").text(registerNameMessage);

    if (registerNameMessage == "") return true;
    else
    {
        $("#register-name").removeClass("checked");
        return false;
    }
}

/**
 * Funkce kontrolující správně zadané heslo při registraci
 */
function checkRegisterPassword()
{
    let passwordAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\''";
    let registerPasswordMessage;

    //heslo není vyplněno
    if ($("#register-pass").val().length == 0)
    {
        registerPasswordMessage = "Heslo musí být vyplněno.";
    }
    //heslo je kratší než 6 znaků
    else if ($("#register-pass").val().length < 6)
    {
        registerPasswordMessage = "Heslo musí být alespoň 6 znaků dlouhé.";
    }
    //heslo je delší než 31 znaků
    else if ($("#register-pass").val().length > 31)
    {
        registerPasswordMessage = "Heslo může být nejvíce 31 znaků dlouhé.";
    }

    else registerPasswordMessage = "";

    //některý ze znaků není povolený
    for (let i = 0; i < $("#register-pass").val().length; i++ )
    {
        if (!passwordAllowedChars.includes($("#register-pass").val()[i]))
        {
            registerPasswordMessage = "Heslo obsahuje nepovolené znaky.";
        }
    }

    $("#register-pass-message").text(registerPasswordMessage);

    if (registerPasswordMessage == "")
    {
        $("#register-pass").addClass("checked");
    }
    else $("#register-pass").removeClass("checked");

    checkRegisterRePassword();
}

/**
 * Funkce kontrolující správně zadané heslo znovu při registraci
 */
function checkRegisterRePassword()
{
    let registerRePasswordMessage;

    //heslo znovu není vyplněno
    if ($("#register-repass").val().length == 0)
    {
        registerRePasswordMessage = "Heslo znovu musí být vyplněno.";
    }
    //heslo znovu je jiné než heslo
    else if ($("#register-repass").val() != $("#register-pass").val())
    {
        registerRePasswordMessage = "Zadaná hesla se neshodují.";
    }

    else registerRePasswordMessage = "";

    if (registerRePasswordMessage == "")
    {
        $("#register-repass").addClass("checked");
    }
    else $("#register-repass").removeClass("checked");

    $("#register-repass-message").text(registerRePasswordMessage);
}

/**
 * Funkce kontrolující správně zadaný email při registraci
 * @returns True, pokud je email zadán správně, false, pokud je zadán špatně (nutné pro požadavek na kontrolu unikátnosti)
 */
function checkRegisterEmail()
{
    let registerEmailMessage;
      let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    //tvar emailu se neshoduje s tvarem udaným regex výrazem
    if ($("#register-email").val() != "" && !regex.test($("#register-email").val()))
    {
        registerEmailMessage = "Zadaný email má nesprávný tvar.";
    }

    else registerEmailMessage= "";

    $("#register-email-message").text(registerEmailMessage);

    if (registerEmailMessage == "") return true;
    else
    {
        $("#register-email").removeClass("checked");
        return false;
    }
}

/**
 * Funkce kontrolující správně zadaný email při obnově hesla
 */
function checkRecoveryEmail()
{
    let recoveryEmailMessage;
      let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    
    //email není vyplněn
    if ($("#password-recovery-email").val().length == 0)
    {
        recoveryEmailMessage = "Email musí být vyplněn.";
    }
    //tvar emailu se neshoduje s tvarem udaným regex výrazem
    else if ($("#password-recovery-email").val() != "" && !regex.test($("#password-recovery-email").val()))
    {
        recoveryEmailMessage = "Zadaný email má nesprávný tvar.";
    }

    else recoveryEmailMessage= "";

    $("#password-recovery-email-message").text(recoveryEmailMessage);
}

/**
 * Funkce zasouvající cookies alert
 */
function hideCookiesAlert()
{
    $("#cookies-alert").removeClass("show");
}

var documentHeight = $(window).height();
var scrollOffset = 50;
/**
 * Funkce zobrazující/skrývající back to top tlačítko podle toho, kolik je odscrollováno
 */
function showScrollButton()
{
    let scrolled = $(window).scrollTop();

    if (scrolled > (documentHeight + scrollOffset))
    {
        $("#back-to-top-button").addClass("show");
    }
    else if (scrolled <= (documentHeight + scrollOffset))
    {
        $("#back-to-top-button").removeClass("show");
    }
}

/**
 * Funkce zobrazující login sekci
 * @param {event} event 
 */
function showLoginSection(event)
{
    if (!$("#index-login-section").hasClass("show"))
    {
        $("#index-login-section").addClass("show");
        $("#overlay").addClass("show");
        $("body").css("overflowY", "hidden");
    }

    if ($(event.target).hasClass("show-login-section-login-button"))
    {
        showLoginDiv($("#login"));
    }
    else if ($(event.target).hasClass("show-login-section-register-button"))
    {
        showLoginDiv($("#register"));
    }
    else if ($(event.target).hasClass("show-login-section-password-recovery-button"))
    {
        showLoginDiv($("#password-recovery"));
    }
}

/**
 * Funkce zobrazující požadovanou část login sekce
 * @param {jQuery objekt} $loginSectionDiv Část, kterou chce uživatel zobrazit
 */
function showLoginDiv($loginSectionDiv)
{
    $("#register").hide();
    $("#login").hide();
    $("#password-recovery").hide();
    $loginSectionDiv.show();
    $loginSectionDiv.find(".text-field").first().focus();

    emptyForms($(".user-data .text-field, .user-data .message"));
}

/**
 * Funkce skrývající login sekci
 */
function hideLoginSection()
{
    $("#index-login-section").removeClass("show");
    $("#overlay").removeClass("show");
    $("body").css("overflowY", "auto");

    emptyForms($(".user-data .text-field, .user-data .message"));
}

/**
 * Funkce přihlašující uživatele pod demo účtem
 */
function demoLogin()
{
    $("#login-name").val("Demo");
    $("#login-pass").val("6F{1NPL#/p[O-y25JkKeOp2N7MLN@p}"); 
    $("#login-persist").prop("checked", false);
    $("#login-form").submit();
}

/**
 * Funkce mazající obsah všech textových polí ve formuláři
 * @param {jQuery objekt} $fields 
 */
function emptyForms($fields)
{
    $fields.val('');
    $fields.text('');
}

/**
 * Funkce skrývající login sekci, pokud bylo kliknuto mimo
 * @param {event} event 
 */
function mouseUpChecker(event)
{
    let $container = $("#index-login-section");
    let $cookiesAlert = $("#cookies-alert");

    //nebylo kliknuto na login sekci nebo na cookies alert
    if (!$container.is(event.target) && !$cookiesAlert.is(event.target) && $container.has(event.target).length === 0 && $cookiesAlert.has(event.target).length === 0)
    {
        hideLoginSection();
    }
}

/*--------------------------------------------------------------------------*/
/* Odesílání dat z formulářů na server */

var ajaxRequestsQueue = [];

//Objekt obsahující data o AJAX požadavku k odeslání
function ajaxRequest(url, data, callback)
{
    this.url = url;
    this.data = data;
    this.callback = callback;
}

/**
 * Funkce zařazující nový AJAX požadavek do fronty k odeslání / vyřízení
 * Pokud zatím ve frontě není žádný požadavek, je tento zařazený okamžitě odeslán
*/
function enqueueAjaxRequest(request)
{
    ajaxRequestsQueue.push(request);
    if (ajaxRequestsQueue.length === 1) //Ve frontě je pouze aktuální požadavek --> okamžitě jej odešli
    {
        sendAjaxRequest();
    }
}

/**
 * Funkce odesílající první AJAX požadavek ve frontě, tato funkce by se neměla volat přímo
 */
function sendAjaxRequest()
{
    let request = ajaxRequestsQueue[0]; //Načti údaje o požadavku
    $.post(
        request.url,
        request.data,
        function (response, status)
        {
            ajaxRequestsQueue.shift(); //Odstraň vyřešený požadavek z fronty
            if (ajaxRequestsQueue.length > 0) { sendAjaxRequest(); } //Mezitím byl zařazen další požadavek

            ajaxCallback(response, status, request.callback);
        }
    );
}

/**
 * Funkce zpracovávající odpověď na AJAX požadavek pro zjištění, zda je dané jméno nebo e-mail unikátní
 * @param {string} messageType Typ hlášky
 * @param {string} message Text hlášky
 * @param {string} data Dodatečná data
 * @param {bool} shouldBeUnique True, pokud má být obsah daného textového pole v celé databázi unikátní
 * @param {jQuery objekt} $inputElement Posuzované textové pole
 */
function isStringUniqueCallback(messageType, message, data, shouldBeUnique, $inputElement)
{
    if (messageType === "success")
    {
        if (!data.unique)
        {
            //zobrazení chybové hlášky
            $inputElement.removeClass("checked");

            if ($inputElement[0] === $("#register-name")[0])
            {
                $("#register-name-message").text("Toto jméno už používá jiný uživatel.");
            }
            else if ($inputElement[0] === $("#register-email")[0])
            {
                $("#register-email-message").text("Tento email už používá jiný uživatel.")
            }
        }
        else
        {
            //zobrazení potvrzovací ikony - jedinečný input splňující všechny podmínky (zkontrolováno předtím)
            $inputElement.addClass("checked");
        }
    }
}

/**
 * Funkce volaná při odeslání jakéhokoli formuláře, která z něj načte data a zařadí AJAX požadavek, který je odešle
 * @param {event} event
 */
function formSubmitted(event)
{
    event.preventDefault();

    let formId = event.target.id;
    let type = $("#"+formId).find('*').filter(':input:first').val();    //Hodnota prvního <input> prvku (identifikátor formuláře)
    let name = "";
    let pass = "";
    let repass = "";
    let email = "";
    let stayLogged = "";

    switch (type)
    {
        //přihlašovací formulář
        case 'l':
            name = $("#login-name").val();
            pass = $("#login-pass").val();
            stayLogged = $("#login-persist").is(":checked");
            break;
        //registrační formulář
        case 'r':
            name = $("#register-name").val();
            pass = $("#register-pass").val();
            repass = $("#register-repass").val();
            email = $("#register-email").val();
            break;
        //formulář pro obnovu hesla
        case 'p':
            email = $("#password-recovery-email").val();
            break;
        default:
            return;
    }

    enqueueAjaxRequest(
        new ajaxRequest
        (
            'index-forms',
            {
                type: type,
                name: name,
                pass: pass,
                repass: repass,
                email: email,
                stayLogged: stayLogged
            },
            serverResponse
        )
    );
}

/**
 * Funkce zpracovávající odpověď na AJAX požadavek odesílající data z odeslaného formuláře
 * @param {string} messageType Typ hlášky
 * @param {string} message Text hlášky
 * @param {string} data Dodatečná data
 */
function serverResponse(messageType, message, data)
{
    let errors = message.replaceAll("|", ". ");
    if (!errors.endsWith("."))
    {
        errors = errors.concat(".");
    }

    switch(data.origin)
    {
        case "login":
            $("#login-server-message").text(errors);
            break;
        case "register":
            $("#register-server-message").text(errors);
            break;
        case "passRecovery":
            $("#password-recovery-server-message").text(errors);
            break;
    }
}

