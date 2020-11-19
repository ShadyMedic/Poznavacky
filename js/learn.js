var smallTablet = 672;
var tablet = 768;

//vše, co se děje po načtení stránky
$(function() {
	//event listenery tlačítek na posun přírodnin a obrázků
	$("#natural-back-button").click(function(){updateNatural(-1)});
	$("#natural-forward-button").click(function(){updateNatural(1)});
	$("#picture-back-button").click(function(){updatePicture(-1)});
	$("#picture-forward-button").click(function(){updatePicture(1)});

	//event listener stisknutí klávesy
	$("#learn-wrapper").keypress(function(event){keyPressed(event)});

	//event listener změny select boxu přírodnin
	$("#natural-select span").on('DOMSubtreeModified',function(){sel()});

	resizeMainImg();
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
	resizeMainImg();
})

//funkce nastavující výšku #main-img tak, aby byla shodná s jeho šířkou
function resizeMainImg(){
	$("#learn-wrapper .picture").css("height", $("#learn-wrapper .picture").outerWidth());
}

// -------------------------------------------------------------------------------------------- */


//Objekt pro uchování přírodniny a jejích obrázků
function natural(name)
{
	this.name = name;
	this.pictures = new Array();
	this.lastPicture;
	this.status = 'initialized';
	
	/**
	 * Metoda pro získání adresy dalšího nebo předchozího obrázku této přírodniny a jejího nastavení do HTML
	 * Parametr picture: -1, pokud se má zobrazit předchozí obrázek, 0, pokud současný a 1, pokud následující
	 */
	this.getPicture = function(picture)
	{
		//Kontrola, zda jsou obrázky načteny nebo se právě načítají
		if (this.status !== "loaded" && this.status !== "loading")
		{
			//Po načtení obrázků je tato metoda znovu zavolána automaticky
			this.loadPictures(picture);
			return;
		}
		
		//Úprava čísla posledního zobrazeného obrázku
		this.lastPicture += picture;
		
		//Kontrola, zda index zobrazovaného obrázku spadá do hranic pole
		if (this.lastPicture > this.pictures.length - 1)
		{
			this.lastPicture %= this.pictures.length;
		}
		if (this.lastPicture < 0)
		{
			this.lastPicture += this.pictures.length;
		}
		
		$("#main-img").attr("src", this.pictures[this.lastPicture]);
	}
	
	/**
	 * Metoda načítající adresy všech obrázků vybrané přírodniny ze serveru
	 * Skript je pozastaven, dokud nepřijde odpověď ze serveru
	 */
	this.loadPictures = function(pictureOffset)
	{
		//Odeslání AJAX požadavku
		selectedNatural.status = "loading";
		$.get(document.location.href + "/learn-pictures?natural=" + encodeURIComponent(this.name),
			function (response, status)
			{
				ajaxCallback(response, status, function (messageType, message, data)
					{
					    if (messageType === "success")
					    {
							//Nastavení obrázků
							selectedNatural.pictures = data.pictures;
							selectedNatural.lastPicture = 0;
							selectedNatural.status = "loaded";
							
							//Zobrazení obrázku
							selectedNatural.getPicture(pictureOffset);
					    }
					    else
					    {
					    	//Nastala požadavek nebyl úspěšný
					    	alert(message);
					    }
				    }
				);
			},
			"json"
		);
	}
}

var naturals;	//Pole do kterého se ukládají objekty s přírodninami
var selectedNatural;	//Ukládá referenci na objekt s právě vybranou přírodninou

/**
 * Metoda načítající po připravení dokumentu seznam přírodnin a inicializující objekty pro jejich reprezentaci
 */
$(function()
{
	//Načti seznam přírodnin
	naturals = new Array();
	
	//Kód napsaný podle odpovědi na StackOverflow: https://stackoverflow.com/a/590219
	$("#natural-select .custom-options .custom-option").each(function()
	{
		naturals.push(new natural($(this).text()));
	});
	
	//Nastav první přírodninu
	sel();
	
	//Nastav focus na hlavní <div>, aby fungovaly klávesové zkratky
	$("main>div:eq(0)").focus();
})

/**
 * Funkce, která se spouští vždy, když je stisknuta nějaká klávesa, zatímco má focus jediný <div> v <main> obsahující vše důležité ze stránky
 * @param event
 */
function keyPressed(event)
{
	//Vypnutí klávesových zkratek, pokud uživatel zrovna píše důvod hlášení
	if ($(document.activeElement).is('INPUT') || $(document.activeElement).is('TEXTAREA'))
	{
		return;
	}
	
    var charCode = event.code || event.which;
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
 * @param offset Posun v poli dostupných obrázků před získáním adresy k zobrazení (-1 pro předchozí, 1 pro následující, 0 pro současný)
 */
function updatePicture(offset)
{
	selectedNatural.getPicture(offset);
}

/**
 * Funkce přenastavující odkaz na vybranou přírodninu a nastavující její obrázek
 */
function sel()
{
	//Tato funkce je zavolána dvakrát po každém výběru přírodniny:
	//  1. Když je název staré přírodniny vymazán
	//	2. Když je zobraze název nové přírodniny
	//V prvním případě by se změny nepovedly a zobrazovali by se do konzole chyby, proto je případ jedna ukončen následujícím řádkem
	if ($("#natural-select .custom-select-main span").text() === ""){ return; }
	let i;
	for (i = 0; i < naturals.length && naturals[i].name !== $("#natural-select .custom-select-main span").text(); i++){}
	selectedNatural = naturals[i];
	updatePicture(0);
}

/**
 * Funkce nastavující novou přírodninu relativně k nyní vybrané přírodnině (využívané tlačítky)
 * @param offset Posun v poli dostupných přírodnin (-1 pro předchozí, 1 pro následující)
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
	
		
	//Úprava currentNatural a obrázeku
	selectedNatural = naturals[currentNaturalIndex];
	updatePicture(0);
	
	
	//Aktualizace select boxu
	$("#natural-select span").text(selectedNatural.name);
	$("#natural-select .custom-options .custom-option").each(function()
	{
		if ($(this).text() === selectedNatural.name)
		{
			if (!$(this).hasClass('selected')) {
				$(this).siblings().removeClass('selected');
				$(this).addClass('selected');
				$(this).closest('.custom-select').find(".custom-select-main span").text($(this).text());
				return;
			}
		}
	});
}