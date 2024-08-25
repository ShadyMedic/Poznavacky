var $initialStatus;        //ukládá zvolenou položku v custom select elementu statutu třídy uloženého v databázi
var initialStatus;      //ukládá status třídy uložený v databázi
var initialCode;        //ukládá vstupní kód třídy uložený v databázi
var initialReadonly;     //ukládá, zda je třída nastavena jako jenom pro čtení

//nastavení URL pro AJAX požadavky
var ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/manage', '/class-update'); //Nahraď neAJAX akci AJAX akcí

$(function()
{
    //získání původních přístupových informací třídy z dokumentu
    $initialStatus = $("#class-status-select .selected");
    initialStatus = $("#class-status-select .selected").text();
    initialCode = $("#change-class-status-code").val();
    initialReadonly = $("#readonly").is(':checked');

    statusChange();

    //event listenery tlačítek
    $("#change-class-name-button").click(function() {changeClassName()})
    $("#change-class-name-confirm-button").click(function() {changeClassNameConfirm()})
    $("#change-class-name-cancel-button").click(function() {changeClassNameCancel()})
    $("#change-class-status-button").click(function() {changeClassStatus()})
    $("#change-class-status-confirm-button").click(function() {changeClassStatusConfirm()})
    $("#change-class-status-cancel-button").click(function() {changeClassStatusCancel()})
    $("#delete-class-button").click(function() {deleteClass()})
    $("#delete-class-confirm-button").click(function() {deleteClassVerify()})
    $("#delete-class-final-confirm-button").click(function() {deleteClassFinal()})
    $("#delete-class-cancel-button, #delete-class-final-cancel-button").click(function() {deleteClassCancel()})

    //event listener inputu
    $("#change-class-status-code").on("input", function() {statusChange()})

    $("#readonly").on('change',function() {statusChange()});

    //observery
    let options = { childList: true };
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function() {
            statusChange();
        });    
    });
    observer.observe($("#class-status-select span")[0], options);
})

/**
 * Funkce zahajující změnu názvu třídy
 */
function changeClassName()
{
    $("#change-class-name-button").hide();
    $("#change-class-name").show();
    $("#change-class-name").closest(".class-property.data-item").find(".value").hide();
    $("#change-class-name-new").focus();

    changeClassStatusCancel();
    deleteClassCancel();
}

/**
 * Funkce potvrzující změnu názvu třídy
 */
function changeClassNameConfirm()
{
    let newName = $("#change-class-name-new").val();

    $.post(ajaxUrl,
        {
            action: 'request name change',
            newName: newName
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        //Reset DOM
                        changeClassNameCancel();

                        newMessage(message, "success");
                    }
                    else if (messageType === "error")
                    {
                        $("#change-class-name-message").text(message);
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce rušící změnu názvu třídy
 */
function changeClassNameCancel()
{
    $("#change-class-name-new").val("");
    $("#change-class-name-button").show();
    $("#change-class-name").hide();
    $("#change-class-name").closest(".class-property.data-item").find(".value").show();
    $("#change-class-name-message").text("");
}

/**
 * Funkce zahajující změnu statutu třídy
 */
function changeClassStatus()
{
    statusChange();

    $("#change-class-status-button").hide();
    $("#change-class-status").show();
    $("#change-class-status").closest(".class-property.data-item").find(".value").hide();
    $("#change-class-status-confirm-button").addClass("disabled");

    changeClassNameCancel();
    deleteClassCancel();
}

/**
 * Funkce nastavující zobrazení elementů podle zvoleného statutu třídy
 * @returns 
 */
function statusChange()
{
    if (!changeWasMade())
    {
        $("#change-class-status-confirm-button").addClass("disabled");
    }
    else 
    {
        $("#change-class-status-confirm-button").removeClass("disabled");
    }

    //třída není jako soukromá
    if ($("#class-status-select .selected").text() !== "Soukromá")
    {
        //není možné změnit vstupní kód -> skrytí
        hideClassStatusCode();
    }
    //třída je jako soukromá
    else
    {
        showClassStatusCode();

        //kód není dlouhý 4 znaky nebo obsahuje písmena    
        if ($("#change-class-status-code").val().length !== 4 || parseInt($("#change-class-status-code").val()) != $("#change-class-status-code").val())
        {
            //kód není platný -> skrytí tlačítka pro uložení
            $("#change-class-status-confirm-button").addClass("disabled");
        }
    }
}

/**
 * Funkce potvrzující změnu statutu třídy
 * @returns 
 */
function changeClassStatusConfirm()
{
    let newStatus = $("#class-status-select .selected").text();
    let newCode = $("#change-class-status-code").val();
    let newReadonly = $("#readonly").is(':checked');

    let confirmMessage;
    switch (newStatus)
    {
        case "Veřejná":
            confirmMessage = "Třída bude nastavena jako veřejná a všichni přihlášení uživatelé do ní budou mít přístup. Pokračovat?";
            newCode = "";
            break;
        case "Soukromá":
            confirmMessage = "Třída bude nastavena jako soukromá a všichni uživatelé, kteří nikdy nezadali platný vstupní kód třídy, ztratí do třídy přístup. Pokračovat?";
            break;
        case "Uzamčená":
            newCode = "";
            confirmMessage = "Třída bude uzamčena a žádní uživatelé, kteří nyní nejsou jejími členy, do ní nebudou moci vstupit (včetně těch, kteří zadají platný vstupní kód v budoucnosti). Pokračovat?";
            break;
        default:
            return;
    }
    
    newConfirm(confirmMessage, "Potvrdit", "Zrušit", function(confirm) {
        if (confirm) changeClassStatusFinal(newStatus, newCode, newReadonly);
        else return;
    })
}

/**
 * Funkce odesílající požadavek na změnu statutu třídy
 * @param {string} newStatus Nový status třídy (veřejná/soukromá/uzamčená)
 * @param {int} newCode Nový kód třídy
 * @param {bool} newReadonly Nové nastavení readonly (zda mohou obrázky přidávat i nečlenové třídy)
 */
function changeClassStatusFinal(newStatus, newCode, newReadonly) //TODO newReadonly
{
    $.post(ajaxUrl,
        {
            action: 'update access',
            newStatus: newStatus,
            newCode: newCode,
            newReadonly: newReadonly
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        $initialStatus = $("#class-status-select").find("li:contains(" + newStatus +")");
                        initialStatus = newStatus;
                        initialCode = newCode;
                        initialReadonly = newReadonly;

                        //aktualizace zobrazovaných údajů
                        $("#status").text(newStatus);
                        if (newStatus == "Soukromá")
                        {
                            $("#status").append(" (kód třídy: " + newCode + ")");
                        }
                        if (newReadonly)
                        {
                            $("#status").append(" | Pouze ty můžeš přidávat obrázky.");
                        }
                        $("#class-status-select .custom-option").removeClass("selected");
                        $("#class-status-select .custom-option:contains(" + newStatus + ")").addClass("selected");

                        //reset HTML
                        $("#change-class-status-button").show();
                        $("#change-class-status").hide();
                        $("#change-class-status").closest(".class-property.data-item").find(".value").show();
                    }
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce rušící změnu statutu třídy
 */
function changeClassStatusCancel()
{    
    $("#change-class-status-button").show();
    $("#change-class-status").hide();
    $("#change-class-status").closest(".class-property.data-item").find(".value").show();
    
    //zrušení změn
    $("#class-status-select .custom-option").removeClass("selected");
    $initialStatus.addClass("selected");
    $("#class-status-select .custom-select-main span").text(initialStatus);
    $("#change-class-status-code").val(initialCode);
    $("#readonly").prop('checked', initialReadonly);

    statusChange();
}

/**
 * Funkce zjišťující, jestli se změnilo něco ohledně statutu třídy
 * @returns TRUE, pokud se změnilo něco ohledně statutu třídy
 */
function changeWasMade()
{
    let changeWasMade = false;

    if ($("#class-status-select .selected").text() != initialStatus ||
        $("#change-class-status-code").val() != initialCode ||
        $("#readonly").is(":checked") != initialReadonly)
    {
        changeWasMade = true
    }

    return changeWasMade;
}

/**
 * Funkce skrývající pole pro zadání kódu třídy
 */
function hideClassStatusCode()
{
    $("#change-class-status-code, label[for='change-class-status-code']").hide();
}

/**
 * Funkce zobrazující pole pro zadání kódu třídy
 */
function showClassStatusCode()
{
    $("#change-class-status-code, label[for='change-class-status-code']").show();
}

/**
 * Funkce zahajující odstranění třídy
 */
function deleteClass()
{
    $("#delete-class-button").hide();
    $("#delete-class").show();
    $("#delete-class1").show();
    $("#delete-class-password").focus();
    $("#delete-class")[0].scrollIntoView({ 
        behavior: 'smooth',
        block: "start" 
    });

    changeClassNameCancel();
    changeClassStatusCancel();
}

/**
 * Funkce ověřuující heslo správce třídy při odstraňování třídy
 */
function deleteClassVerify()
{
    let password = $("#delete-class-password").val();

    $.post(ajaxUrl,
        {
            action: 'verify password',
            password: password
        },
        function (response, status) { ajaxCallback(response, status, deleteClassConfirm); },
        "json"
    );
}

/**
 * Funkce zobrazující druhou fázi odstranění třídy v případě ověřeného hesla, v opačném případě zobrazující chybovou hlášku
 * @param {string} messageType Typ hlášky
 * @param {string} message Text hlášky
 * @param {string} data Dodatečné informace
 */
function deleteClassConfirm(messageType, message, data)
{
    if (data.verified === true)
    {
        $("#delete-class1").hide();
        $("#delete-class2").show();
    }
    else
    {
        $("#delete-class-message").text(message);

        $("#delete-class-password").val("");
    }
}

/**
 * Funkce odesílající požadavek na odstranění třídy
 */
function deleteClassFinal()
{
    let password = $("#delete-class-password").val();

    $.post(ajaxUrl,
        {
            action: 'delete class',
            password: password
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
                        newMessage(message, "success");
                        //přesměruj uživatele za tři vteřiny zpět na seznam tříd
                        setInterval(function () { window.location = 'menu'; }, 3000);
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce rušící odstranění třídy
 */
function deleteClassCancel()
{
    $("#delete-class-password").val("");
    $("#delete-class-button").show();
    $("#delete-class").hide();
    $("#delete-class2").hide();
    $("#delete-class-message").text("");
}