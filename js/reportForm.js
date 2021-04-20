//DO VŠECH STRÁNEK POUŽÍVAJÍCÍCH TENTO SKRIPT MUSÍ BÝT ZAHRNUT I SOUBOR ajaxMediator.js

// proměnné obsahující elementy položek select boxu s důvody nahlášení
var $reasonNotDisplaying;
var $reasonLongLoading;
var $reasonIncorrectNatural;
var $reasonContainsName;
var $reasonBadResolution;
var $reasonCopyright;
var $reasonOther;
var $reasonOtherAdmin;

$(function()
{
    //načtení proměnných skladujících důvody nahlášení
    $reasonNotDisplaying = $("#reason-not-displaying");
    $reasonLongLoading = $("#reason-long-loading");
    $reasonIncorrectNatural = $("#reason-incorrect-natural");
    $reasonContainsName = $("#reason-contains-name");
    $reasonBadResolution = $("#reason-bad-resolution");
    $reasonCopyright = $("#reason-copyright");
    $reasonOther = $("#reason-other");
    $reasonOtherAdmin = $("#reason-other-admin");

    displayImgPreview();

    //event listenery tlačítek
    $(".report-button").click(function(){reportImg()});
    $("#report-reason .custom-options").click(function(){updateReport()});
    $("#submit-report-button").click(function(e){submitReport(e)})
    $("#cancel-report-button").click(function(e){cancelReport(e)})

});

$(window).resize(function()
{
    displayImgPreview();
})

/**
 * Funkce zobrazující náhled nahlašovaného obrázku
 * Zobrazení závisí na velikosti okna - aby se náhled vešel
 */
function displayImgPreview() 
{
    if ($("#main-img").height() < 400)
    {
        $("#report-img-preview").hide();
    }
    else $("#report-img-preview").show();
}

/**
 * Funkce zahajující nahlášení obrázku
 */
function reportImg()
{
    $(".report-button").hide();
    $(".report-box").addClass("show");

    let url;
    if ($("#main-img").get(0).complete){ url = $("#main-img").attr("src"); }
    else { url = "images/loading.svg"; } //obrázek se stále načítá
    $("#report-img-preview > img").attr("src", url);
}

/**
 * Funkce rušící nahlášení obrázku
 */
function cancelReport()
{
    //skrytí report boxu a zobrazení tlačítka na jeho zobrazení
    $(".report-box").removeClass("show");
    $(".report-button").show();

    //obnovení hlavního select boxu
    $("#report-reason .selected").removeClass("selected");
    $("#report-reason .custom-option:first").addClass("selected");
    $("#report-reason .custom-select-main span").text($("#report-reason .selected").text());

    //obnovení dodatečných polí a select boxů
    $(".additional-report-info > *:not(#report-message)").hide();
    $("#long-loading-info .selected").removeClass("selected");
    $("#long-loading-info .custom-option:first").addClass("selected");
    $("#long-loading-info .custom-select-main span").text($("#long-loading-info .selected").text());
    $(".additional-report-info input").val("");
    $(".additional-report-info textarea").val("");
}

/**
 * Funkce aktualizující obsah report boxu podle zvoleného důvodu nahlášení
 */
function updateReport()
{
    $(".additional-report-info > *:not(#report-message)").hide();

    //obrázek se načítá příliš dlouho
    if ($reasonLongLoading.hasClass("selected")) 
    {
        $("#long-loading-info").show();
    }
    //obrázek zobrazuje nesprávnou přírodninu
    else if ($reasonIncorrectNatural.hasClass("selected"))
    {
        $(".incorrect-natural-info-wrapper").show();
    }
    //jiný důvod (pro správce třídy)
    else if ($reasonOther.hasClass("selected"))
    {
        $(".other-info-wrapper").show();
    }
    //jiný důvod (pro správce systému)
    else if ($reasonOtherAdmin.hasClass("selected"))
    {
        $(".other-admin-info-wrapper").show();
    }
}

/**
 * Funkce odesílající hlášení
 * @returns 
 */
function submitReport()
{
    let $reason = $("#report-reason").find(".selected");
    let isCompletlyLoaded = $("#main-img").get(0).complete;
    let picUrl = $("#main-img").attr("src");
    let reasonInfo = "";
    
    let $additionalInfoElement = $(".additional-report-info").find("*:visible:first");

    //je vidět nějaké pole pro zadání dalších informací
    if ($additionalInfoElement.length > 0)
    {
        if ($additionalInfoElement.hasClass("custom-select-wrapper"))
        {
            reasonInfo = $additionalInfoElement.find(".custom-options .selected").text();
        }
        else
        {
            reasonInfo = $additionalInfoElement.find(".text-field").val();
        }
    }
    
    //kontrola vyplnění informací pro obecná hlášení
    if (($reason[0] == $reasonOther[0] || $reason[0] == $reasonOtherAdmin[0]) && reasonInfo.length === 0)
    {
        $("#report-message").text("Musíte vyplnit důvod hlášení");
        return;
    }

    //kontrola obrázku
    if (!isCompletlyLoaded)
    {
        //obrázek se načítá a je zvolen jiný důvod než dlouhé načítání obrázku
        if ($reason[0] != $reasonLongLoading[0])
        {
            $("#report-message").text("Z tohoto důvodu nemůžete nahlásit zatím nenačtený obrázek");
            return;
        }
        //obrázek se nenačítá
        else if ($("#loading").is(":hidden"))
        {
            $("#report-message").text("Tento obrázek nemůžete nahlásit");
            return;
        }
        //TODO - při reportu obrázku blank.gif (důvodem pomalé načítání) vrátí server zprávu Neznámý obrázek
    }

    //nebyla zaznamenána žádná chyba
    $("#report-message").text("");

    let url = window.location.href;
    if (url.endsWith('/')) { url = url.slice(0, -1); } //odstranění trailing slashe (pokud je přítomen)
    url = url.substr(0, url.lastIndexOf("/")); //odstranění akce (/learn nebo /test)
    
    $.post(url + '/new-report',
        {
            picUrl: picUrl,
            reason: $reason.text(),
            info: reasonInfo
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    newMessage(message, messageType);
                    
                    //skrytí formuláře pro nahlašování
                    cancelReport();
                }
            );
        },
        "json"
    );
}