var smallTablet = 672;
var tablet = 768;

$(function()
{
    loadNaturals();
    resizeMainImg();

    //event listenery tlačítek na posun přírodnin a obrázků
    $("#natural-back-button").click(function() {updateNatural(-1)});
    $("#natural-forward-button").click(function() {updateNatural(1)});
    $("#picture-back-button").click(function() {updatePicture(-1)});
    $("#picture-forward-button").click(function() {updatePicture(1)});

    //event listener stisknutí klávesy
    $("#learn-wrapper").keypress(function(event) {keyPressed(event)});

    //event listener změny select boxu přírodnin
    $("#natural-select span").on('DOMSubtreeModified', function() {sel()});
})

$(window).resize(function()
{
    resizeMainImg();
})

/**
 * Funkce nastavující výšku #main-img tak, aby byla shodná s jeho šířkou
 */
function resizeMainImg()
{
    let pictureWidth = $("#learn-wrapper .picture").outerWidth();

    $("#learn-wrapper .picture").css("height", pictureWidth);
}

/**
 * Objekt pro uchování přírodniny a jejích obrázků
 * @param {string} name Název přírodniny
 */
function natural(name)
{
    this.name = name;
    this.pictures = new Array();
    this.lastPicture;
    this.status = 'initialized';
    
    /**
     * Metoda pro získání adresy dalšího nebo předchozího obrázku této přírodniny a jejího nastavení do HTML
     * @param {int} picture -1, pokud se má zobrazit předchozí obrázek, 0, pokud současný, a 1, pokud následující
     */
    this.getPicture = function(picture)
    {
        //kontrola, zda jsou obrázky načteny nebo se právě načítají
        if (this.status !== "loaded" && this.status !== "loading")
        {
            //po načtení obrázků je tato metoda znovu zavolána automaticky
            this.loadPictures(picture);
            return;
        }
        
        //úprava čísla posledního zobrazeného obrázku
        this.lastPicture += picture;
        
        //kontrola, zda index zobrazovaného obrázku spadá do hranic pole
        if (this.lastPicture > this.pictures.length - 1)
        {
            this.lastPicture %= this.pictures.length;
        }
        if (this.lastPicture < 0)
        {
            this.lastPicture += this.pictures.length;
        }

        let nextUrl = this.pictures[this.lastPicture];
        $("#main-img").attr("src", "images/loading.svg"); //načítací gif bude zobrazován, dokud se plně nenačte obrázek
        $("#main-img").on("load", function()
        {
            $("#main-img").off("load");
            $("#main-img").attr("src", nextUrl);
        })
    }
    
    /**
     * Metoda načítající adresy všech obrázků vybrané přírodniny ze serveru
     * Skript je pozastaven, dokud nepřijde odpověď ze serveru
     * @param {int} pictureOffset Pořadí obrázku dané přírodniny k zobrazení
     */
    this.loadPictures = function(pictureOffset)
    {
        //odeslání AJAX požadavku
        selectedNatural.status = "loading";

        //zobrazení ikony načítání
        $("#main-img").attr("src","images/loading.svg");

        let url = window.location.href;
        if (url.endsWith('/')) { url = url.slice(0, -1); } //odstranění trailing slashe (pokud je přítomen)
        url = url.substr(0, url.lastIndexOf("/")); //odstranění akce (/learn)

        $.get(url + "/learn-pictures?natural=" + encodeURIComponent(this.name),
            function (response, status)
            {
                ajaxCallback(response, status, function (messageType, message, data)
                    {
                        if (messageType === "success")
                        {
                            //nastavení obrázků
                            selectedNatural.pictures = data.pictures;
                            selectedNatural.lastPicture = 0;
                            selectedNatural.status = "loaded";
                            
                            //zobrazení obrázku
                            selectedNatural.getPicture(pictureOffset);
                        }
                        else
                        {
                            //požadavek nebyl úspěšný
                            newMessage(message, "error");
                        }
                    }
                );
            },
            "json"
        );
    }
}

var naturals;    //pole, do kterého se ukládají objekty s přírodninami
var selectedNatural;    //ukládá referenci na objekt s právě vybranou přírodninou
/**
 * Metoda načítající po připravení dokumentu seznam přírodnin a inicializující objekty pro jejich reprezentaci
 */
function loadNaturals()
{
    //načtení seznamu přírodnin
    naturals = new Array();
    //kód napsaný podle odpovědi na StackOverflow: https://stackoverflow.com/a/590219
    $("#natural-select .custom-options .custom-option").each(function()
    {
        naturals.push(new natural($(this).text()));
    });
    
    //nastavení první přírodniny
    sel();
    
    //nastavení focusu na hlavní <div>, aby fungovaly klávesové zkratky
    $("main>div:eq(0)").focus();
}

/**
 * Funkce, která se spouští vždy, když je stisknuta nějaká klávesa, zatímco má focus jediný <div> v <main> obsahující vše důležité ze stránky
 * @param {event} event
 */
function keyPressed(event)
{
    //Vypnutí klávesových zkratek, pokud uživatel zrovna píše důvod hlášení
    if ($(document.activeElement).is('INPUT') || $(document.activeElement).is('TEXTAREA'))
    {
        return;
    }
    
    let charCode = event.code || event.which;
    switch (charCode)
    {
        case "KeyW":
            updateNatural(1);
            return;
        case "KeyS":
            updateNatural(-1);
            return;
        case "KeyD":
            updatePicture(1);
            return;
        case "KeyA":
            updatePicture(-1);
            return;
    }
}

/**
 * Funkce nastavující nový obrázek
 * @param {int} offset Posun v poli dostupných obrázků před získáním adresy k zobrazení (-1 pro předchozí, 1 pro následující, 0 pro současný)
 */
function updatePicture(offset)
{
    if ($(".report-box").hasClass("show"))
    {
        cancelReport();
    }
    selectedNatural.getPicture(offset);
}

/**
 * Funkce přenastavující odkaz na vybranou přírodninu a nastavující její obrázek
 * Tato funkce je zavolána dvakrát po každém výběru přírodniny:
 *    1. Když je název staré přírodniny vymazán
 *     2. Když je zobrazen název nové přírodniny
 */
function sel()
{
    //v prvním případě by se změny nepovedly a zobrazovaly by se do konzole chyby, proto je případ jedna ukončen následujícím řádkem
    if ($("#natural-select .custom-select-main span").text() === ""){ return; }

    let i;
    for (i = 0; i < naturals.length && naturals[i].name !== $("#natural-select .custom-select-main span").text(); i++){}
    selectedNatural = naturals[i];

    updatePicture(0);
}

/**
 * Funkce nastavující novou přírodninu relativně k nyní vybrané přírodnině (využívané tlačítky)
 * @param {int} offset Posun v poli dostupných přírodnin (-1 pro předchozí, 1 pro následující)
 */
function updateNatural(offset)
{
    let currentNaturalIndex = naturals.indexOf(selectedNatural);
    currentNaturalIndex += offset;
    
    if (currentNaturalIndex > naturals.length - 1)
    {
        currentNaturalIndex %= naturals.length;
    }
    if (currentNaturalIndex < 0)
    {
        currentNaturalIndex += naturals.length;
    }
    
    selectedNatural = naturals[currentNaturalIndex];

    updatePicture(0);
    
    //aktualizace select boxu
    $("#natural-select span").text(selectedNatural.name);
    $("#natural-select .custom-options .custom-option").each(function()
    {
        if ($(this).text() === selectedNatural.name)
        {
            if (!$(this).hasClass('selected')) 
            {
                $(this).siblings().removeClass('selected');
                $(this).addClass('selected');
                $(this).closest('.custom-select').find(".custom-select-main span").text($(this).text());
                return;
            }
        }
    });
}