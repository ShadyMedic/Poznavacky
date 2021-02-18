//Nastavení URL pro AJAX požadavky
let ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/naturals', '/update-naturals'); //Nahraď neAJAX akci AJAX akcí

// vše, co se děje po načtení stránky
$(function() {

    //event listenery tlačítek
    $(".rename-natural").click(function(event) {rename(event)})
    $(".rename-confirm").click(function(event) {renameConfirm(event)})
    $(".rename-cancel").click(function(event) {renameCancel(event)})
    $(".remove-natural").click(function(event) {remove(event)})
})

/**
 * Funkce zobrazující vstupní pole pro přejmenování přírodniny
 */
function rename(event)
{
    console.log("rename");
    $(event.target).closest('tr').find('.normal-buttons').hide();
    $(event.target).closest('tr').find('.natural-name-box').hide();
    $(event.target).closest('tr').find('.rename-buttons').show();
    $(event.target).closest('tr').find('.natural-name-input-box').show();

    $(event.target).closest('tr').find('.natural-name-input').select();
}

/**
 * Funkce potvrzující přejmenování přírodniny, kontrolující údaje a odesílající AJAX požadavek na server
 */
function renameConfirm(event)
{
    //TODO
}

/**
 * Funkce skrývající vstupní pole pro přejmenování přírodniny a obnovující ho do původního stavu
 */
function renameCancel(event)
{
    $(event.target).closest('tr').find('.rename-buttons').hide();
    $(event.target).closest('tr').find('.natural-name-input-box').hide();
    $(event.target).closest('tr').find('.normal-buttons').show();
    $(event.target).closest('tr').find('.natural-name-box').show();

    $(event.target).closest('tr').find('.natural-name-input').val($(event.target).closest('tr').find('.natural-name').text());
}

/**
 * Funkce odebírající přírodninu a odesílající AJAX požadavek na její odstranění na server
 */
function remove(event)
{
    if (!confirm('Skutečně chcete odstranit přírodninu "'+$(event.target).closest('tr').find('.natural-name').text()+'" a všechny obrázky k ní přidané?\nTato akce je nevratná!')){ return }

    let deletedTableRow = $(event.target).closest('tr');
    $.post(ajaxUrl,
        {
            action: 'delete',
            naturalId: $(event.target).closest('tr').attr('data-natural-id')
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

