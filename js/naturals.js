//Nastavení URL pro AJAX požadavky
let ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/naturals', '/update-naturals'); //Nahraď neAJAX akci AJAX akcí

// vše, co se děje po načtení stránky
$(function() {

    //event listenery tlačítek
    $(".rename-natural").click(function(event) {rename(event)})
    $(".natural-name-input").keyup(function(event) { if (event.keyCode === 13) renameConfirm(event) })
    $(".rename-confirm").click(function(event) {renameConfirm(event)})
    $(".rename-cancel").click(function(event) {renameCancel(event.target)})
    $(".remove-natural").click(function(event) {remove(event)})
})

/**
 * Funkce zobrazující vstupní pole pro přejmenování přírodniny
 */
function rename(event)
{
    $(event.target).closest('tr[data-natural-id]').find('.normal-buttons').hide();
    $(event.target).closest('tr[data-natural-id]').find('.natural-name-box').hide();
    $(event.target).closest('tr[data-natural-id]').find('.rename-buttons').show();
    $(event.target).closest('tr[data-natural-id]').find('.natural-name-input-box').show();

    $(event.target).closest('tr[data-natural-id]').find('.natural-name-input').select();
}

/**
 * Funkce potvrzující přejmenování přírodniny, kontrolující údaje a odesílající AJAX požadavek na server
 */
function renameConfirm(event)
{
    let minChars = 1;
    let maxChars = 31;
    let allowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci"

    if ($(event.target).prop("tagName") !== "INPUT")
    {
        //Potvrzení tlačítkem (kliknuto na obrázek, který jej vyplňuje)
        newName = $(event.target).closest('tr[data-natural-id]').find(".natural-name-input").val();
        oldName = $(event.target).closest('tr[data-natural-id]').find(".natural-name").text();
    }
    else
    {
        //Potvrzení Enterem
        newName = $(event.target).val();
        oldName = $(event.target).closest('td').find(".natural-name").text();
    }

    if (newName.toUpperCase() !== oldName.toUpperCase()) //Prováděj kontroly pouze pokud se nejedná o změny ve velikosti písmen
    {
        //Kontrola délky
        if (newName === undefined || !(newName.length >= minChars && newName.length <= maxChars))
        {
            alert("Název přírodniny musí mít 1 až 31 znaků");
            return;
        }

        //Kontrola znaků
        let re = new RegExp("[^" + allowedChars + "]", 'g');
        if (newName.match(re) !== null)
        {
            alert("Název přírodniny může obsahovat pouze písmena, číslice, mezeru a znaky _ . - + / * % ( ) \' \"");
            return;
        }

        //Kontrola unikátnosti
        let presentNaturals;
        //Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
        presentNaturals = $("#naturals-wrapper").find(".natural-name").map(function() {return $(this).text().toUpperCase(); }).get();

        if (presentNaturals.includes(newName.toUpperCase()))
        {
            let merge = confirm("Přírodnina s tímto názvem již existuje\nChcete tyto dvě přírodniny sloučit? Všechny obrázky zvolené přírodniny a hlášení k nim se vztahující budou přesunuty k existující přírodnině s tímto názvem a zvolená přírodnina bude odstraněna.\nTato akce je nevratná.");
            if (merge)
            {
                let fromNaturalId = $(event.target).closest('tr[data-natural-id]').attr("data-natural-id");
                let toNaturalId = $("#naturals-wrapper").find("tr[data-natural-id]:eq(" + presentNaturals.indexOf(newName.toUpperCase()) + ")").attr("data-natural-id");
                mergeNaturals(fromNaturalId, toNaturalId);
            }
            return;
        }
    }

    //Kontrola informací OK

    let $targetRow = $(event.target).closest('tr[data-natural-id]');
    $.post(ajaxUrl,
        {
            action: 'rename',
            naturalId: $(event.target).closest('tr[data-natural-id]').attr('data-natural-id'),
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
                        //newMessage(message, "success")

                        //Nastavení nového názvu přírodniny a reset DOM
                        $targetRow.find(".natural-name").text($targetRow.find(".natural-name-input").val())

                        renameCancel(event.target)
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce skrývající vstupní pole pro přejmenování přírodniny a obnovující ho do původního stavu
 * @param clickedButton HTML element tlačítka, na které bylo kliknuto
 */
function renameCancel(clickedButton)
{
    $(clickedButton).closest('tr[data-natural-id]').find('.rename-buttons').hide();
    $(clickedButton).closest('tr[data-natural-id]').find('.natural-name-input-box').hide();
    $(clickedButton).closest('tr[data-natural-id]').find('.normal-buttons').show();
    $(clickedButton).closest('tr[data-natural-id]').find('.natural-name-box').show();

    $(clickedButton).closest('tr[data-natural-id]').find('.natural-name-input').val($(clickedButton).closest('tr').find('.natural-name').text());
}

/**
 * Funkce slučující dvě přírodniny (odstraňuje vybranou a převádí její obrázky k nové)
 * Toto je stvrzno odesláním AJAX požadavku na provedení změn v databázi
 * @param fromNaturalId ID přírodniny, jejíž obrázky mají být převedeny a přírodnina odstraněna
 * @param toNaturalId ID přírodniny, ke které mají být obrázky převedeny
 */
function mergeNaturals(fromNaturalId, toNaturalId)
{
    let deletedTableRow = $("[data-natural-id=" + fromNaturalId + "]");
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
                        //newMessage(message, "success")

                        //odebrání sloučené přírodniny z DOM
                        deletedTableRow.remove();
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce odebírající přírodninu a odesílající AJAX požadavek na její odstranění na server
 */
function remove(event)
{
    if (!confirm('Skutečně chcete odstranit přírodninu "'+$(event.target).closest('tr[data-natural-id]').find('.natural-name').text()+'" a všechny obrázky k ní přidané?\nTato akce je nevratná!')){ return }

    let deletedTableRow = $(event.target).closest('tr');
    $.post(ajaxUrl,
        {
            action: 'delete',
            naturalId: $(event.target).closest('tr[data-natural-id]').attr('data-natural-id')
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
                        //newMessage(message, "success")

                        //odebrání přírodniny z DOM
                        deletedTableRow.remove();
                    }
                }
            );
        },
        "json"
    );
}

