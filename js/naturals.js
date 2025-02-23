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
    $(".show-info-button").show();

    $(".natural.data-item").each(function() {
        $naturals.push($(this));
        naturalParameters.push(new Array($(this).attr("data-natural-id"), $(this).find("span.natural.name").text(), $(this).find(".natural.uses-count").text(), $(this).find(".natural.pictures-count").text()));
    })

    //event listenery tlačítek
    $("#remove-filters-button").click(function() {removeFilters()})
    $(".sort-up-button").click(function(event) {sortNaturals(event, "up")})
    $(".sort-down-button").click(function(event) {sortNaturals(event, "down")})
    $("#naturals-wrapper").on("click", ".rename-natural-button", function(event) {rename(event)})
    $("#naturals-wrapper").on("click", ".rename-confirm-button", function(event) {renameConfirm(event)})
    $("#naturals-wrapper").on("click", ".rename-cancel-button", function(event) {renameCancel($(event.target))})
    $("#naturals-wrapper").on("click", ".remove-natural-button", function(event) {remove(event)})
    $("#naturals-wrapper").on("click", ".display-pictures-button", function(event) {displayPictures(event)})
    $("#naturals-wrapper").on("click", ".hide-pictures-button", function(event) {hidePictures(event)})
    $("#naturals-wrapper").on("click", ".preview-picture-button", function(event) {previewPicture(event)})
    $("#naturals-wrapper").on("click", ".hide-picture-button", function(event) {hidePicture(event)})
    $("#naturals-wrapper").on("click", ".delete-picture-button", function(event) {deletePicture(event)})

    //event listener zmáčknutí klávesy
    $(".natural.name-input").keyup(function(event) { if (event.keyCode === 13) renameConfirm(event) })

    $("#filter-name").on("input", function() {filterByName($("#filter-name").val())})
})

/**
 * Funkce, která zruší grafické zvýraznění šipky pro řazení přírodnin
 */
function inactivateSortButton()
{
    let $buttonImgActiveOld = $(".sort-buttons .active").find("img");

    // existuje aktivní řazení (kdyby neexistovalo, tak $buttonImgActiveOld.length == 0)
    if ($buttonImgActiveOld.length != 0)
    {
        $buttonImgActiveOld.closest(".btn").removeClass("active")
        $buttonImgActiveOld.removeClass("selected");
        $buttonImgActiveOld.addClass("black")
    }
}

/**
 * Funkce, která seřadí vzestupně/sestupně přírodniny podle zvoleného parametru
 * @param {event} event 
 * @param {string} direction možnosti "up" a "down"
 */
function sortNaturals(event, direction)
{
    let classType = $(event.target).closest(".sort-buttons").siblings().first().attr("class");
    let sortBy;

    if (classType.includes("name"))
    {
        sortBy = 1;
    }
    else if (classType.includes("uses-count"))
    {
        sortBy = 2;
    }
    else if (classType.includes("pictures-count"))
    {
        sortBy = 3;
    }


    inactivateSortButton();

    // grafické zvýraznění šipky pro aktivní řazení
    let $buttonImg = $(event.target).closest(".btn").find("img");
    $buttonImg.closest(".btn").addClass("active");  
    $buttonImg.addClass("selected");
    $buttonImg.removeClass("black");

    
    naturalParameters.sort((a,b) => {
        if (a[sortBy] === b[sortBy]) {
            return 0;
        }

        // řazení stringů
        if (sortBy == 1)
        {
            if (direction == "up")
            {
                return a[sortBy].localeCompare(b[sortBy]); 
            }
            else if (direction == "down")
            {
                return (-1)*a[sortBy].localeCompare(b[sortBy]); 
            }
        }
        // řazení čísel
        else
        {
            if (direction == "up")
            {
                return a[sortBy] - b[sortBy]; 
            }
            else if (direction == "down")
            {
                return b[sortBy] - a[sortBy]; 
            }
        }
          
    });

    naturalParameters.forEach(function(element) {
        let id = element[0];
        $natural = $(".natural.data-item[data-natural-id='" + id + "']");
        $("#naturals-data-section").append($natural.get(0).outerHTML);
        $natural.first().remove();   
    })
    
}

/**
 * Funkce rušící všechny filtry
 */
function removeFilters()
{
    $(".natural.data-item").remove();

    $naturals.forEach(function($element) {
        $("#naturals-data-section").append($element.get(0).outerHTML);
    })

    //zrušení filtrování podle jména
    $("#filter-name").val("");
    $(".natural.data-item").show();

    inactivateSortButton();
}


/**
 * Funkce, která zobrazuje pouze přírodniny začínající na zadaný substring
 * @param {string} name substring, který má obsahovat hledaná přírodnina
 */
function filterByName(name)
{
    $(".natural.data-item").each(function() {
        let naturalName = $(this).find(".natural.name").text().trim().toLowerCase();
        if (naturalName.startsWith(name.trim().toLowerCase()))
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
 * Funkce zobrazující vstupní pole pro přejmenování přírodniny
 * @param {event} event 
 */
function rename(event)
{
    let $natural = $(event.target).closest('.natural.data-item');

    $natural.find('.normal-buttons').hide();
    $natural.find('.natural.name-box').hide();
    $natural.find('.rename-buttons').show();
    $natural.find('.natural.name-input-box').show();
    $natural.find('.natural.name-input').select();
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

    let $natural = $(event.target).closest('.natural.data-item');
    let newName;
    let oldName;

    //potvrzení tlačítkem
    if ($(event.target).prop("tagName") !== "INPUT")
    {
        newName = $natural.find(".natural.name-input").val();
        oldName = $natural.find(".natural.name").text();
    }
    //potvrzení Enterem
    else
    {
        newName = $(event.target).val();
        oldName = $natural.find(".natural.name").text();
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
        let presentNaturals = $(".natural.data-item .natural.name").map(function() {return $(this).text().toUpperCase(); }).get();

        if (presentNaturals.includes(newName.toUpperCase()))
        {
            let confirmMessage = "Přírodnina s tímto názvem již existuje. Chceš tyto dvě přírodniny sloučit? Všechny obrázky zvolené přírodniny a hlášení k nim se vztahující budou přesunuty k existující přírodnině s tímto názvem a zvolená přírodnina bude odstraněna. Tato akce je nevratná.";
            newConfirm(confirmMessage, "Sloučit", "Zrušit", function(confirm) {
                if (confirm) {
                    let fromNaturalId = $natural.attr("data-natural-id");
                    let toNaturalId = $(".natural.data-item:eq(" + presentNaturals.indexOf(newName.toUpperCase()) + ")").attr("data-natural-id");
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
                        $natural.find(".natural.name").text(newName);

                        renameCancel($natural.find(".rename-cancel-button"));
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
    let $natural = $clickedButton.closest(".natural.data-item");

    $natural.find('.rename-buttons').hide();
    $natural.find('.natural.name-input-box').hide();
    $natural.find('.normal-buttons').show();
    $natural.find('.natural.name-box').show();

    $natural.find('.natural.name-input').val($natural.find('.natural.name').text());
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
                        $mergedNatural.find('.natural.uses-count').text(Number($mergedNatural.find('.natural.uses-count').text()) + data.newUsesCount);
                        $mergedNatural.find('.natural.pictures-count').text(Number($mergedNatural.find('.natural.pictures-count').text()) + data.newPicturesCount);
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
    let $natural = $(event.target).closest('.natural.data-item');
    let confirmMessage = 'Skutečně chceš odstranit přírodninu "'+ $natural.find('.natural.name').text()+'" a všechny obrázky k ní přidané? Tato akce je nevratná!';
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

/**
 * Funkce zobrazující obrázky dané přírodniny"
 * @param {event} event
 */
function displayPictures(event)
{
    let $natural = $(event.target).closest(".data-item")
    let $naturalPictures = $natural.find(".pictures");
    if (!$naturalPictures.hasClass("show"))
    {
        $naturalPictures.slideDown(function() {
            $naturalPictures.addClass("show");
        });

        $natural.find(".display-pictures-button").hide();
        $natural.find(".hide-pictures-button").show();
    }
    
    $naturalPictures.find(".picture").on("error", function()
    {
        $(this).addClass("icon black");
        $(this).attr("src", '/images/file-error_o.svg');
        $(this).siblings(".img-buttons").find(".preview-picture-button").hide();
    })

    $(".data-item").each(function()
    {
        if (!$(this).is(event.target) && $(this).has(event.target).length === 0)
        {
            $(this).find(".pictures").slideUp(function() {
                $(this).removeClass("show");
            });
            $(this).find(".display-pictures-button").show();
            $(this).find(".hide-pictures-button").hide();
        }
    })

    //Načti obrázky
    $naturalPictures.find('.picture').each(function()
    {
        $(this).attr('src', $(this).attr('data-src'));
    })
}

/**
 * Funkce skrývající obrázky dané přírodniny"
 * @param {event} event
 */
function hidePictures(event)
{
    let $natural = $(event.target).closest(".data-item")
    let $naturalPictures = $natural.find(".pictures");
    if ($naturalPictures.hasClass("show"))
    {
        $naturalPictures.slideUp(function() {
            $(this).removeClass("show");
        });

        $natural.find(".display-pictures-button").show();
        $natural.find(".hide-pictures-button").hide();
    }
}

/**
 * Funkce zobrazující náhled konkrétního obrázku
 * @param {event} event 
 */
function previewPicture(event)
{
    let $naturalPictures = $(event.target).closest(".data-item").find(".pictures");
    url = $(event.target).closest(".img-wrapper").find("img.picture").attr("src");
    id = $(event.target).closest(".img-wrapper").find("img.picture").attr("data-id");

    $naturalPictures.find(".list").hide();

    $naturalPictures.find(".image > img").attr("src", url);
    $naturalPictures.find(".image > img").attr("data-id", id);
    $naturalPictures.find(".image").show();
}

/**
 * Funkce skrývající náhled konkrétního obrázku
 * @param {event} event 
 */
function hidePicture(event)
{
    $(event.target).closest(".data-item").find(".image").hide();
    $(event.target).closest(".data-item").find(".list").show();
}

/**
 * Funkce odstraňující obrázek
 * @param {event} event 
 */
function deletePicture(event)
{
    id = $(event.target).closest(".image, .img-wrapper").find(".picture").attr("data-id");
    $picture = $(event.target).closest(".pictures").find(".list .picture[data-id=" + id +"]");
    $picturesCount = $(event.target).closest(".data-item").find(".pictures-count")[0];
    $.post(ajaxUrl,
        {
            action:"delete picture",
            pictureId:$picture.attr('data-id')
        },
        function(response)
        {
            if (response["messageType"] === "success")
            {
                $picture.closest('.img-wrapper').remove();
                $picturesCount.innerText = $picturesCount.innerText - 1
                hidePicture(event);
            }
            if (response["messageType"] === "error")
            {
                alert(response["message"]);
            }
        }
    );
}

