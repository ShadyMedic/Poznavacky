//nastavení URL pro AJAX požadavky
var ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/naturals', '/update-naturals'); //Nahraď neAJAX akci AJAX akcí

//pole objektů položek přírodnin
var $naturals = [];
//pole, v němž má každá přírodnina svoje vlastní pole obsahující id, název přírodniny, počet výskytů a počet obrázků
var naturalParameters = [];

$(function()
{
    $(".natural-data-item").each(function() {
        $naturals.push($(this));
        naturalParameters.push(new Array($(this).attr("data-natural-id"), $(this).find(".natural-name").text(), $(this).find(".natural-uses-count").text(), $(this).find(".natural-pictures-count").text()));
    })

    //event listenery tlačítek
    $(".rename-natural-button").click(function(event) {rename(event)})
    $(".rename-confirm-button").click(function(event) {renameConfirm(event)})
    $(".rename-cancel-button").click(function(event) {renameCancel($(event.target))})
    $(".remove-natural-button").click(function(event) {remove(event)})
    $("#hide-naturals-info-button").click(function() {hideInfo()});
    $("#show-naturals-info-button").click(function() {showInfo()});
    $("#remove-filters-button").click(function() {removeFilters()})
    $(".sort-up-button").click(function(event) {sortNaturals(event, "up")})
    $(".sort-down-button").click(function(event) {sortNaturals(event, "down")})

    //event listener zmáčknutí klávesy
    $(".natural-name-input").keyup(function(event) { if (event.keyCode === 13) renameConfirm(event) })

    $("#filter-name").on("input", function() {filterByName($("#filter-name").val())})
})

function sortNaturals(event, direction)
{
    let classType = $(event.target).closest(".sort-buttons").siblings().first().attr("class");
    let sortBy;

    switch (classType) {
        case "natural-name":
            sortBy = 1;
            break;
        case "natural-uses-count":
            sortBy = 2;
            break;
        case "natural-pictures-count":
            sortBy = 3;
            break;
    }

    let unsorted = naturalParameters
    naturalParameters.sort((a,b) => {
        if (a[sortBy] === b[sortBy]) {
            return 0;
        }
        else {
            return a[sortBy].localeCompare(b[sortBy]);
        }
    });

    if (direction == "down")
    {
        naturalParameters.reverse();
    }

    naturalParameters.forEach(function(element) {
        let id = element[0];
        $natural = $(".natural-data-item[data-natural-id='" + id + "']");
        $("#naturals-data-section").append($natural.get(0).outerHTML);
        $natural.first().remove();   
    })
    
}

function removeFilters()
{
    //zrušení filtrování podle jména
    $("#filter-name").val("");
    filterByName("");

    $(".natural-data-item").remove();

    $naturals.forEach(function($element) {
        $("#naturals-data-section").append($element.get(0).outerHTML);
    })
}

function filterByName(name)
{
    $(".natural-data-item").each(function() {
        let naturalName = $(this).find(".natural-name").text().toLowerCase();
        if (naturalName.startsWith(name.toLowerCase()))
        {
            $(this).show();
        }
        else
        {
            $(this).hide();
        }
    })
}


/**
 * Funkce zobrazující sekci s nápovědou
 */
function showInfo()
{
    $("#naturals-info-section").show();
    $("#overlay").addClass("show");    
    $("body").css("overflow", "hidden");
}

/**
 * Funkce skrývající sekci s nápovědou
 */
function hideInfo()
{
    $("#naturals-info-section").hide();
    $("#overlay").removeClass("show");
    $("body").css("overflow", "auto");
}

/**
 * Funkce zobrazující vstupní pole pro přejmenování přírodniny
 * @param {event} event 
 */
function rename(event)
{
    let $natural = $(event.target).closest('.natural-data-item');

    $natural.find('.normal-buttons').hide();
    $natural.find('.natural-name-box').hide();
    $natural.find('.rename-buttons').show();
    $natural.find('.natural-name-input-box').show();
    $natural.find('.natural-name-input').select();
}

/**
 * Funkce potvrzující přejmenování přírodniny, kontrolující údaje a odesílající AJAX požadavek na server
 * @param {event} event 
 * @returns 
 */
function renameConfirm(event)
{
    let minChars = 1;
    let maxChars = 31;
    let allowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci"

    let $natural = $(event.target).closest('.natural-data-item');
    let newName;
    let oldName;

    //potvrzení tlačítkem
    if ($(event.target).prop("tagName") !== "INPUT")
    {
        newName = $natural.find(".natural-name-input").val();
        oldName = $natural.find(".natural-name").text();
    }
    //potvrzení Enterem
    else
    {
        newName = $(event.target).val();
        oldName = $natural.find(".natural-name").text();
    }

    //nový a starý název přírodniny se liší (odlišná velikost písma nevadí)
    if (newName.toUpperCase() !== oldName.toUpperCase())
    {
        //kontrola délky
        if (newName === undefined || !(newName.length >= minChars && newName.length <= maxChars))
        {
            newMessage("Název přírodniny musí mít 1 až 31 znaků", "error");
            return;
        }

        //kontrola znaků
        let re = new RegExp("[^" + allowedChars + "]", 'g');
        if (newName.match(re) !== null)
        {
            newMessage("Název přírodniny může obsahovat pouze písmena, číslice, mezeru a znaky _ . - + / * % ( ) \' \"", "error");
            return;
        }

        //kontrola unikátnosti

        //Získání seznamu přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
        let presentNaturals = $(".natural-data-item .natural-name").map(function() {return $(this).text().toUpperCase(); }).get();

        if (presentNaturals.includes(newName.toUpperCase()))
        {
            let confirmMessage = "Přírodnina s tímto názvem již existuje. Chcete tyto dvě přírodniny sloučit? Všechny obrázky zvolené přírodniny a hlášení k nim se vztahující budou přesunuty k existující přírodnině s tímto názvem a zvolená přírodnina bude odstraněna. Tato akce je nevratná.";
            newConfirm(confirmMessage, "Sloučit", "Zrušit", function(confirm) {
                if (confirm) {
                    let fromNaturalId = $natural.attr("data-natural-id");
                    let toNaturalId = $(".natural-data-item:eq(" + presentNaturals.indexOf(newName.toUpperCase()) + ")").attr("data-natural-id");
                    mergeNaturals(fromNaturalId, toNaturalId);
                }
                else return;
            })
            return;
        }
    }

    //kontrola informací OK

    $.post(ajaxUrl,
        {
            action: 'rename',
            naturalId: $natural.attr('data-natural-id'),
            newName: newName
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                    else if (messageType === "success")
                    {
                        //nastavení nového názvu přírodniny a reset DOM
                        $natural.find(".natural-name").text($natural.find(".natural-name-input").val())

                        renameCancel($(event.target))
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce skrývající vstupní pole pro přejmenování přírodniny a obnovující ho do původního stavu
 * @param $clickedButton Tlačítko, na které bylo kliknuto
 */
function renameCancel($clickedButton)
{
    let $natural = $clickedButton.closest(".natural-data-item");

    $natural.find('.rename-buttons').hide();
    $natural.find('.natural-name-input-box').hide();
    $natural.find('.normal-buttons').show();
    $natural.find('.natural-name-box').show();

    $natural.find('.natural-name-input').val($natural.find('.natural-name').text());
}

/**
 * Funkce odesílající požadavek na sloučení dvou přírodnin (odstraňuje vybranou a převádí její obrázky k nové)
 * @param fromNaturalId ID přírodniny, jejíž obrázky mají být převedeny a přírodnina odstraněna
 * @param toNaturalId ID přírodniny, ke které mají být obrázky převedeny
 */
function mergeNaturals(fromNaturalId, toNaturalId)
{
    let $deletedNatural = $("[data-natural-id=" + fromNaturalId + "]");
    let $mergedNatural = $("[data-natural-id=" + toNaturalId + "]");

    $.post(ajaxUrl,
        {
            action: 'merge',
            fromNaturalId: fromNaturalId,
            toNaturalId: toNaturalId
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                    else if (messageType === "success")
                    {
                        //odebrání sloučené přírodniny z DOM a přičtení jejích statistik k přírodnině, do které byla sloučena
                        $deletedNatural.remove();
                        $mergedNatural.find('.natural-uses-count').text(Number($mergedNatural.find('.natural-uses-count').text()) + data.newUsesCount);
                        $mergedNatural.find('.natural-pictures-count').text(Number($mergedNatural.find('.natural-pictures-count').text()) + data.newPicturesCount);
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce odstraňující přírodninu
 * @param {event} event 
 */
function remove(event)
{
    let $natural = $(event.target).closest('.natural-data-item');
    let confirmMessage = 'Skutečně chcete odstranit přírodninu "'+ $natural.find('.natural-name').text()+'" a všechny obrázky k ní přidané? Tato akce je nevratná!';
    newConfirm(confirmMessage, "Odebrat", "Zrušit", function(confirm) {
        if (confirm) removeFinal($natural)
        else return;
    })
}
/**
 * Funkce odesílající požadavek na odstranění přírodniny
 * @param {jQuery objekt} $natural 
 */
function removeFinal($natural)
{
    $.post(ajaxUrl,
        {
            action: 'delete',
            naturalId: $natural.attr('data-natural-id')
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                    else if (messageType === "success")
                    {
                        //odebrání přírodniny z DOM
                        $natural.remove();
                    }
                }
            );
        },
        "json"
    );
}

