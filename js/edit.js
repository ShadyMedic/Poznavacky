var groupUrls; //pole url poznávaček, které jsou v tétož třídě již obsaženy (včetně upravované)
var naturalNames; //pole názvů přírodnin, které patří do této třídy
var currentGroupUrl //nové URL poznávačky, pokud je přejmenována

var $nameBox; //všechny name boxy (obsahují název a tlačítko na přejmenování)
var $nameInputBox; //všechny name input boxy (obsahují textové pole a tlačítka na potvrzení a zrušení přejmenování)

$(function()
{
    $(".show-info-button").show();

    //načtení dočasných dat do proměnných a jejich odstranění z DOM
    groupUrls = JSON.parse($("#group-urls-json").text());
    naturalNames = JSON.parse($("#natural-names-json").text());
    $("#temp-data").remove();

    findNameBoxes();
    
    //event listenery tlačítek
    $("#help-button").click(function() {$("#help-text").toggle()})
    $(".rename-group").click(function(event) {rename(event, "group")})
    $(".rename-group-confirm").click(function(event) {renameConfirm(event, "group")})
    $("#edit-group-wrapper").on("click", ".remove-part", function(event) {removePart(event)})
    $("#edit-group-wrapper").on("click", ".rename-part", function(event) {rename(event, "part")})
    $("#edit-group-wrapper").on("click", ".rename-part-confirm", function(event) {renameConfirm(event, "part")})
    $("#edit-group-wrapper").on("click", ".new-natural-button", function(event) {addNatural(event)})
    $("#edit-group-wrapper").on("click", ".rename-natural", function(event) {rename(event, "natural")})
    $("#edit-group-wrapper").on("click", ".rename-natural-confirm", function(event) {renameConfirm(event, "natural")})
    $("#edit-group-wrapper").on("click", ".remove-natural", function(event) {removeNatural(event); })
    $("#edit-group-wrapper").on("click", ".rename-natural-cancel", function(event) {renameCancel(event)})
    $("#edit-group-wrapper").on("click", ".rename-part-cancel", function(event) {renameCancel(event)})
    $("#edit-group-wrapper").on("click", ".rename-group-cancel", function(event) {renameCancel(event)})
    $("#add-part-button").click(function(){addPart()});
    $("#submit-button").click(save);
    $(window).click(function(event) {renameCancelAll(event)})

    //event listenery stisknutí klávesy
    $("#edit-group-wrapper").on("keyup", ".part-name-input", function(event) {nameTyped(event, "part")})
    $("#edit-group-wrapper").on("keyup", ".natural-name-input", function(event) {nameTyped(event, "natural")})
    $("#edit-group-wrapper").on("keyup", ".new-natural-name-input", function(event) {nameTyped(event, "natural", true)})

    //event listener stisknutí klávesy
    $(".group-name-input").keyup(function(event) {nameTyped(event, "group")});

})

/**
 * Funkce zaplňující proměnné $nameBox a $nameInput box příslušnými elementy
 * Nutné volat opakovaně, protože při určitých akcích tyto elementy vznikají
 */
function findNameBoxes() 
{
    $nameBox = $(".natural-name-box, .part-name-box, .group-name-box");
    $nameInputBox = $(".natural-name-input-box, .part-name-input-box, .group-name-input-box");
}

/**
 * Objekt pro uchování dat poznávačky
 * @param {string} groupName Název poznávačky
 */
function groupData(groupName)
{
    this.name = groupName;
    this.parts = new Array();
    
    /**
     * Metoda přidávající do této poznávačky další prázdnou část
     */
    this.addPart = function(partName)
    {
        this.parts.push(new partData(partName));
    }
}

/**
 * Objekt pro uchování dat části
 * @param partName Název části
 */
function partData(partName)
{
    this.name = partName;
    this.naturals = new Array();

    /**
     * Metoda přidávající do této části další přírodninu
     * @param naturalName Název přírodniny
     */
    this.addNatural = function(naturalName)
    {
        this.naturals.push(naturalName);
    }
}

/**
 * Funkce přidávající do DOM nový element části
 */
function addPart()
{
    $("#parts-boxes-container").append($("#part-box-template").html());
    $(".part-box:last-child")[0].scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
    $(".part-box:last-child .part-name-input").focus();
}

/**
 * Funkce zahajující přejmenování položky
 * @param {event} event
 * @param {string} type Typ měněného názvu ("group", "part" nebo "natural")
 */
function rename(event, type)
{
    findNameBoxes();
    let $selectedNameBox = $(event.target).closest($nameBox);

    $selectedNameBox.hide();
    $selectedNameBox.siblings().filter($nameInputBox).show();
    $selectedNameBox.siblings().filter($nameInputBox).find(".text-field").focus().select();

}

/**
 * Funkce rušící všechna aktivní přejmenování
 * @param {event} event 
 */
function renameCancelAll(event) 
{
    findNameBoxes();
    let $addPartButton = $("#add-part-button");

    //pouze, pokud se neklikne do nameInputBoxu nebo na tlačítko přidávající novou část
    if (!$nameInputBox.is(event.target) && $nameInputBox.has(event.target).length === 0 && !$addPartButton.is(event.target))
    {
        //zobrazení všech nameBoxů kromě nameBoxu položky, kterou chceme přejmenovat
        //pokud je nameInputBox příslušného nameBoxu prázdný, k zobrazení nameBoxu nedojde

        //všechny nameBoxy, do nichž nebylo kliknuto
        let $otherNameBoxes = $nameBox.not($(event.target).closest($nameBox));

        $otherNameBoxes.each(function()
        {
            let isNamed = $(this).find("span").text() != "";
            if (isNamed)
            {
                $(this).show();
            }
        })

        //skrytí všech nameInputBoxů kromě nameInputBoxu položky, kterou chceme přejmenovat, a obnovení jejich textových polí
        //pokud je příslušný nameBox prázdný, ke skrytí nedojde

        //všechny nameInputBoxy, do jejichž příslušného nameBoxu nebylo kliknuto
        let $otherNameInputBoxes = $($nameInputBox.not($(event.target).closest($nameBox).siblings().filter($nameInputBox)));

        $otherNameInputBoxes.each(function()
        {
            let isNamed = $(this).closest($nameInputBox).siblings().filter($nameBox).find("span").text() != "";
            if (isNamed)
            {
                $(this).find(".text-field").val($(this).siblings().filter($nameBox).find("span").text());
                $(this).hide();
            }
        })
    }
}

/**
 * Funkce rušící přejmenování položky
 * @param {event} event 
 */
function renameCancel(event)
{
    findNameBoxes();
    let $selectedNameInputBox = $(event.target).closest($nameInputBox);

    $selectedNameInputBox.hide();
    $selectedNameInputBox.siblings().filter($nameBox).show();
    $(event.target).siblings().filter(".text-field").val("");
}

/**
 * Funkce ukládající změnu jména poznávačky nebo části
 * @param event
 * @param type Typ měněného názvu ("group", "part" nebo "natural")
 */
function renameConfirm(event, type)
{
    let className = type; // "group" / "part" / "natural"
    let errorString;
    let minChars;
    let maxChars;
    let allowedChars;
    let allowedSpecialChars;

    switch (type)
    {
        //při změně povolených znaků nezapomenout aktualizovat i znaky nahrazované "-" ve funkci generateUrl()

        case "group":
            errorString = "poznávačky";
            minChars = 3;
            maxChars = 31;
            allowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-";
            allowedSpecialChars = ". _ -";
            break;
        case "part":
            errorString = "části";
            minChars = 1;
            maxChars = 31;
            allowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-";
            allowedSpecialChars = ". _ -";
            break;
        case "natural":
            errorString = "přírodniny";
            minChars = 1;
            maxChars = 31;
            allowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-";
            allowedSpecialChars = "_ . - + / * % ( ) \' \"";
            break;
    }

    findNameBoxes();
    let $selectedNameInputBox = $(event.target).closest($nameInputBox);

    let newName = $selectedNameInputBox.find(".text-field").val();
    let oldName = $selectedNameInputBox.siblings().find("span").text(); //pro kontrolu unikátnosti u částí a poznávačky

    //kontrola délky
    if (newName === undefined || !(newName.length >= minChars && newName.length <= maxChars))
    {
        let message = "Název " + errorString + " musí mít 1 až 31 znaků";
        newMessage(message, "error");
        return;
    }
    
    //kontrola znaků
    let re = new RegExp("[^" + allowedChars + "]", 'g');
    if (newName.match(re) !== null)
    {
        let message = "Název " + errorString + " může obsahovat pouze písmena, číslice, mezeru a znaky " + allowedSpecialChars;
        newMessage(message, "error");
        return;
    }

    //kontrola unikátnosti
    let url = generateUrl(newName);
    let oldUrl = generateUrl(oldName);
    if (type === "group")
    {
        if (url !== oldUrl)
        {
            if (groupUrls.includes(url))
            {
                let message = "Poznávačka se stejným URL již ve vybrané třídě existuje";
                newMessage(message, "error");
                return;
            }
        }
        currentGroupUrl = url;
    }
    else if (type === "part")
    {
        //získání pole jmen všech částí
        let partUrls = $(".part-name").map(function () { return generateUrl($(this).text()); }).get();
        if (url !== oldUrl)
        {
            if (partUrls.includes(url))
            {
                let message = "Část se stejným URL již ve vybrané poznávačce existuje";
                newMessage(message, "error");
                return;
            }
        }
    }
    else
    {
        let presentNaturals;
        //získání seznamu přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077    
        presentNaturals = $(event.target).closest(".naturals-in-part").find(".natural-name").map(function() {return $(this).text().toUpperCase(); }).get();

        if (presentNaturals.includes(newName.toUpperCase()))
        {
            let message = "Tato přírodnina je již do této části přidána";
            newMessage(message, "error");
            return;
        }
    }
    

    //skrytí inputu, zobrazení textu
    $selectedNameInputBox.siblings().find("." + className + "-name").text(newName);
    $selectedNameInputBox.hide();
    $selectedNameInputBox.siblings().filter("." + className + "-name-box").show();
    
    //byly provedeny změny -> zamkni stránku
    lock();
}

/**
 * Funkce generující URL reprezentaci nového názvu a zobrazující jej
 * Je volaná při zadání znaku do textového pole
 * @param {event} event
 * @param {string} type Typ měněného názvu ("group", "part" nebo "natural")
 * @param {bool} addAsNew Týká se pouze přejmenovávání přírodniny, TRUE, pokud se jedná o novou přírodninu, FALSE, pokud se přejmenovává již přidaná přírodnina
 */
function nameTyped(event, type, addAsNew = false)
{
    //byl stisknut Enter -> potvrď změnu
    if (event.keyCode === 13)
    {
        if (type === "natural" && addAsNew) {addNatural(event); }
        else {renameConfirm(event, type);}
    }
    else
    {
        let className = type; //"group" / "part" / "natural"

        if (type !== "natural")
        {
            //vygenerování a zobrazení URL verze nového názvu
            let url = generateUrl($(event.target).val());
            let $selectedNameContainer = $(event.target).closest(".group-name-container, .part-info");
            $selectedNameContainer.find("." + className + "-name-url").text("V URL bude zobrazováno jako " + url);
        }
    }
}

/**
 * Funkce generující URL formu názvu poznávačky nebo části
 * @param {string} text Název k převedení na URL
 * @returns URL reprezentace řetězce poskytnutého jako argument
 */
function generateUrl(text)
{
    //vytvoření URL formy názvu stejným způsobem, jako to dělá backend
    let url;
    
    //převod na malá písmena
    url = text.toLowerCase();

    //odstranění diakritiky (napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/37511463/14011077)
    url = url.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

    //převedení mezer, podtržítek, teček a pomlček na "-"
    url = url.replace(/ /g, "-");
    url = url.replace(/\./g, "-");
    url = url.replace(/_/g, "-");

    //nahrazení násobných "-" za jedno
    url = url.replace(/--+/g, "-");

    //oříznutí "-" na začátku a na konci
    url = url.replace(/-/g, " ");
    url = url.trim();    //protože JavaScript má funkci jenom pro zkracování o mezery 
    url = url.replace(/ /g, "-");
    
    return url;
}

/**
 * Funkce volaná přidávající novou část
 * @param {event} event
 */
function addNatural(event)
{
    let $naturalInput;

    //potvrzení tlačítkem
    if ($(event.target).prop("tagName") === "BUTTON")
    {
        $naturalInput = $(event.target).siblings().filter(".new-natural-name-input");
    }
    //potvrzení Enterem
    else
    {
        $naturalInput = $(event.target);
    }

    let naturalName = $naturalInput.val();

    let naturalMinLength = 1;
    let naturalMaxLength = 31;
    let naturalAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-";
    
    //kontrola unikátnosti
    let presentNaturals = $naturalInput.closest(".part-box").find(".natural-name").map(function() {return $(this).text().toUpperCase(); }).get(); //Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
    if (presentNaturals.includes(naturalName.toUpperCase()))
    {
        let message = "Tato přírodnina je již do této části přidána";
        newMessage(message, "error");
        return;
    }
    
    //kontrola délky
    if (naturalName === undefined || !(naturalName.length >= naturalMinLength && naturalName.length <= naturalMaxLength))
    {
        let message = "Název přírodniny musí mít 1 až 31 znaků";
        newMessage(message, "error");
        return;
    }
    
    //kontrola znaků
    let re = new RegExp("[^" + naturalAllowedChars + "]", 'g');
    if (naturalName.match(re) !== null)
    {
        let message = "Název přírodniny může obsahovat pouze písmena, číslice, mezeru a znaky _ . - + / * % ( ) \' \"";
        newMessage(message, "error");
        return;
    }
    
    let $naturalList = $(event.target).closest(".part-box").find(".naturals-in-part");

    $naturalList.prepend($("#natural-item-template").html());
    $naturalList.children().first().find(".natural-name").text(naturalName);
    $naturalList.children().first().find(".natural-name-input").attr("value", naturalName);
    
    //vymazání vstupu
    $naturalInput.val("").focus();
    
    //byly provedeny změny -> zamkni stárnku
    lock();
}

/**
 * Funkce odebírající určitou přírodninu
 * @param {event} event
 */
function removeNatural(event)
{
    $(event.target).closest("li").remove();
    
    //byly provedeny změny -> zamkni stárnku
    lock();
}

/**
 * Funkce odebírající určitou část
 * @param event
 */
function removePart(event)
{
    let confirmMessage = "Opravdu si přeješ odebrat tuto část? Změny se neprojeví, dokud nebude úprava poznávačky uložena. Touto akcí nebudou odstraněny žádné existující přírodniny ani jejich obrázky.";

    newConfirm(confirmMessage, "Odebrat", "Zrušit", function(confirm) {
        if (confirm)
        {
            $(event.target).closest(".part-box").remove();
    
            //byly provedeny změny -> zamkni stárnku
            lock();
        }
        else return;
    })    
}

/**
 * Funkce volaná po kliknutí na tlačítko "Uložit", která poskládá JSON objekt obsahující všechna data poznávačky a odešle ho na backend
 */
function save()
{
    let data;

    //krok 1: získání nového názvu poznávačky
    let newGroupName = $(".group-name").text();
    data = new groupData(newGroupName);

    //krok 2: získání pole všech částí
    let partsArray = $("#edit-group-wrapper .part-box").get();

    //krok 3: získání názvu každé části
    for (let i = 0; i < partsArray.length; i++)
    {
        data.addPart($(partsArray[i]).find(".part-name").text());
    }

    //krok 4: získání seznamu přírodnin z každé části
    for (let i = 0; i < partsArray.length; i++)
    {
        let naturalsArray = $(partsArray[i]).find(".naturals-in-part").children().get();
        for (let j = 0; j < naturalsArray.length; j++)
        {
            data.parts[i].addNatural($(naturalsArray[j]).find(".natural-name").text());
        }
    }

    //odeslání dat na server
    let url = window.location.href.replace(/\/$/, "").replace(/edit$/, "")+"confirm-group-edit"; //adresa současné stránky (bez edit a lomena na konci)
    $.post(url,
        {
            data: JSON.stringify(data)
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        //zrušení tlačítek pro rychlé přejmenování
                        $(".rename-natural").hide();
                        
                        //odemčení stránky
                        unlock();
                        
                        let confirmMessage = "Změny byly úspěšně uloženy. Přeješ si aktualizovat stránku pro ověření změn?";
                        newConfirm(confirmMessage, "Aktualizovat", "Zrušit", function(confirm)
                        {
                            if (confirm) 
                            {
                                //jméno poznávačky bylo změněno
                                if (currentGroupUrl !== undefined) 
                                {
                                    let url = location.href;
                                    url = url.replace(/\/[a-z0-9-]+\/edit/, "/" + currentGroupUrl + "/edit");
                                    window.location.href = url;
                                }

                                else
                                {
                                    window.location.reload();
                                }
                            }
                            else return;
                        })
                    }
                    else if (messageType === "error")
                    {
                        //chyba vstupu
                        newMessage(message, "error");
                    }
                    else if (messageType = "warning")
                    {
                        //chyba ukládání
                        newMessage(message, "warning", data["json"], 1000000);
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce zamykající stránku
 * Při pokusu o její opuštění je zobrazen potvrzovací dialog pro zamezení ztráty neuložených změn v poznávačce
 */
function lock()
{
    $(window).on("beforeunload", function(event) {
        event.preventDefault();
        return "";
    })
}

/**
 * Funkce odemykající stránku
 * Při pokusu o její opuštění se již nebude zobrazovat potvrzovací dialog
 */
function unlock()
{
    $(window).off("beforeunload")
}