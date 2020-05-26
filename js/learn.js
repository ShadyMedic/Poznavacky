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
		//Kontrola, zda jsou obrázky načteny
		if (this.status !== "loaded")
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
		
		$("#mainImg").attr("src", this.pictures[this.lastPicture]);
	}
	
	/**
	 * Metoda načítající adresy všech obrázků vybrané přírodniny ze serveru
	 * Skript je pozastaven, dokud nepřijde odpověď ze serveru
	 */
	this.loadPictures = function(pictureOffset)
	{
		//Odeslání AJAX požadavku
		selectedNatural.status = "loading";
		$.post(document.location.href+"/learn-pictures", {
			name: this.name
		}, function(response)
		{
			//Nastavení obrázků
			selectedNatural.pictures = JSON.parse(response);
			selectedNatural.lastPicture = 0;
			selectedNatural.status = "loaded";
			
			//Zobrazení obrázku
			selectedNatural.getPicture(pictureOffset);
		});
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
	$("#selectBox option").each(function()
	{
		naturals.push(new natural($(this).val()));
	});
	
	//Nastav první přírodninu
	sel();
})

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
	let i;
	for (i = 0; i < naturals.length && naturals[i].name !== $("#selectBox").val(); i++){}
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
	
	$("#selectBox").prop("selectedIndex", currentNaturalIndex);
	
	//Úprava currentNatural a obrázeku
	selectedNatural = naturals[currentNaturalIndex];
	updatePicture(0);
}

function reportImg()
{
	//TODO
}