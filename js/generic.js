var smallTablet = 672;
var tablet = 768;
var prefersDarkTheme; //automatická preference prohlížeče

$(function()
{
    //event listenery tlačítek
    $("#messages").on("click", ".close-message-button", function() {closeMessage(this)})
    $(".custom-3-switch-wrapper").on("click", function(event) {toggleThreeSwitch(this, event)})

    //event listenery zmáčknutí klávesy
    $("body").on("keyup", ".form", function(event) {enterSubmit(event)})
    $("body").on("keyup", ".form", function(event) {escapeSubmit(event)})

    //event listener custom select boxů
    $(".custom-select-wrapper").each(function()
    {
        //automatické vybrání první položky v dropdownu při načtení stránky
        //netýká se následujících custom select boxů:
            //#add-natural-select - select box na výběr přírodniny při přidávání nového obrázku (pohled addPictures)
            //#class-status-select - select box na výběr statutu třídy (pohled manage)
            //#report-natural-select - select box na změnu přírodniny ve správě hlášení (pohled reportsTableManage)
        if (this.id != "add-natural-select" && this.id != "class-status-select" && !$(this).hasClass("report-natural-select")) 
        {
            $(this).find(".custom-option").first().addClass("selected");
        }

        $(this).click(function()
        {
            manageSelectBox($(this));
        })
    })

    //event listener kliknutí mimo select box
    $(window).click(function(event) {
        $(".custom-select").each(function()
        {
            if (!this.contains(event.target))
            {
                $(this).removeClass('open');
            }
        })
    });

    //event listener přidávající třídu podle toho, jestli uživatel používá myš, nebo tabulátor
    $(window).on("keydown", function(event)
    { 
        if (event.keyCode === 9) $("body").addClass("tab");
    })
    $(window).on("mousedown", function()
    {
        $("body").removeClass("tab");    
    })

    transitionAfterPageLoad();
})

/**
 * Funkce přepne trojitý toggle switch o jedno posunutí vpřed a spustí 'change' event
 */
function toggleThreeSwitch(wrapper, event)
{
    event.preventDefault();

    let $radios = $(wrapper).find("input[type=radio]");
    let index = $radios.index($radios.filter(":checked"));

    index = (index + 1) % $radios.length;
    $radios.eq(index).prop("checked", true);
    $(wrapper).trigger("change");
}

/**
 * Funkce povolující transitions později po načtení stránky
 */
function transitionAfterPageLoad()
{
    setTimeout(() =>  {
        $(".no-transition").removeClass("no-transition");
    }, 400);
}

/**
 * Funkce nastavující tmavý/světlý motiv
 */
function setTheme(newTheme)
{
    if (newTheme == "light")
    {   
        $("body").addClass("light-theme");
        $("body").removeClass("dark-theme");
        $("body").removeClass("system-theme");
    }
    else if (newTheme == "dark")
    {
        $("body").addClass("dark-theme");
        $("body").removeClass("light-theme");
        $("body").removeClass("system-theme");
    }
    else if (newTheme == "system")
    {
        $("body").addClass("system-theme");
        $("body").removeClass("dark-theme");
        $("body").removeClass("light-theme");
    }
    
}

/**
 * Funkce, která odešle formulář stiskem klávesy Enter
 * Formulář je libovolný prvek s třídou "form", tlačítko na jeho odeslání musí mít třídu "submit-button"
 * @param {event} event 
 * @returns 
 */
function enterSubmit(event)
{
    //byla stisknuta jiná klávesa
    if (event.code != "Enter")
    {
        return;
    }

    //byl stisknut Enter
    let $form = $(':focus').closest(".form");
    $form.find(".submit-button").click();
}

/**
 * Funkce, která zruší formulář stiskem klávesy Escape
 * Formulář je libovolný prvek s třídou "form", tlačítko na jeho odeslání musí mít třídu "cancel-button"
 * @param {event} event 
 * @returns 
 */
function escapeSubmit(event)
{
    //byla stisknuta jiná klávesa
    if (event.code != "Escape")
    {
        return;
    }
    
    //byl stisknut Escape
    let $form = $(':focus').closest(".form");
    $form.find(".cancel-button").click();
}

/**
 * Funkce zavírající zobrazenou hlášku
 * @param {jQuery objekt} $button Tlačítko na zavření, na které bylo kliknuto
 */
function closeMessage($button)
{
    $button.closest(".message-item").remove();
}

/**
 * Funkce pro získání hodnoty cookie
 * Zkopírována z https://www.w3schools.com/js/js_cookies.asp
 * @param {string} cname Název cookie
 * @returns {string} Obsah cookie
 */
function getCookie(cname)
{
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++)
    {
        var c = ca[i];
        while (c.charAt(0) == ' ')
        {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0)
        {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

/**
 * Funkce upravující manipulaci s custom select boxy
 * @param {jQuery objekt} $selectBox Custom select box
 */
function manageSelectBox($selectBox)
{
    $selectBox.find(".custom-select").toggleClass("open");

    //pokud je nějaký element zvolený, posune se dropdown tak, aby byl zvolený element vidět
    //netýká se #report-reason - select box na volbu důvodu nahlášení (pohled reportForm) - způsobovalo to divné poskočení
    if ($selectBox.find(".custom-options .selected").length != 0 && $selectBox[0] != $("#report-reason")[0])
    {
        
        $selectBox.find(".custom-options .selected")[0].scrollIntoView({ 
            block: 'nearest',
            inline: 'nearest',
            inline: 'start' 
        });
        
    }

    //změna zvolené položky
    $(".custom-option").each(function()
    {
        $(this).click(function()
        {
            if (!$(this).hasClass('selected')) {
                $(this).siblings().removeClass('selected');
                $(this).addClass('selected');
                $(this).closest('.custom-select').find(".custom-select-main span").text($(this).text());
            }
        })
    })
}

/**
 * Funkce vytvářející novou hlášku
 * @param {string} message Text hlášky
 * @param {string} type Typ hlášky (success / info / warning / error)
 * @param {string} data Další informace, pod data.origin je název akce, která vyvolala AJAX požadavek
 * @param {int} timeout Doba, po níž zpráva zmizí
 */
function newMessage(message, type, data, timeout)
{
    //smazání nejstarší zprávy, jsou-li již minimálně tři
    if ($("#messages").children().length >= 3 || $("#messages").outerHeight() >= $("main").outerHeight()/3)
    {
        $("#messages .message-item:last-child").remove();
    }

    $("#messages").prepend($("#message-item-template").html());
    let $message = $("#messages .message-item:first-child");
    $message.find(".message").text(message);
    $message.find(".data").text(data);
    $message.addClass(type + "-message");

    setTimeout(function() {
        $message.slideUp(400, function()
            {
                $(this).remove();
            }
        );
    }, (timeout != undefined) ? timeout : 3000)
}

/**
 * Funkce vytvářející nové potvrzovací okno
 * @param {string} message Zpráva v okně
 * @param {string} confirmButtonText Text tlačítka na potvrzení
 * @param {string} cancelButtonText Text tlačítka na zrušení
 * @param {funkce} callback Funkce volaná po kliknutí na tlačítko
 * Funkce callback vrací true při kliknutí na tlačítko na potvrzení a false při kliknutí na tlačítko na zrušení
 */
function newConfirm(message, confirmButtonText, cancelButtonText, callback)
{
    $("#overlay").addClass("show");
    $("body").css("overflow", "hidden");
    $("main").css("overflow", "hidden");
    $("#popups").append($("#confirm-item-template").html());
    $confirm = $("#popups .confirm-item:last-child");
    $confirm.find(".message").text(message);
    $confirm.find(".confirm-popup-button").text(confirmButtonText);
    $confirm.find(".cancel-popup-button").text(cancelButtonText);

    //aby fungovalo potvrzení enterem
    $confirm.focus();

    $confirm.on("click", ".confirm-popup-button", function()
    {
        $confirm.remove();
        $("#overlay").removeClass("show");
        $("body").css("overflow", "auto");
        $("main").css("overflow", "auto");
        callback(true);
    })
    $confirm.on("click", ".cancel-popup-button", function()
    {
        $confirm.remove();
        $("#overlay").removeClass("show");
        $("body").css("overflow", "auto");
        $("main").css("overflow", "auto");
        callback(false);
    })
}

