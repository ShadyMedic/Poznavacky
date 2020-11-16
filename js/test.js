/**
 * Objekt reprezentující obrázek
 * @param num Číslo, pod kterým je v $_SESSION na serveru uložena správná odpověď
 * @param url URL adresa obrázku k zobrazení
 */
function picture(num, url)
{
	this.num = num;
	this.url = url;
}

/**
 * Objekt reprezentující správce obrázků, který uchovává jejich data a v případě potřeby načítá další ze serveru
 */
function pictureList()
{
	this.pictures = new Array();
	
	/**
	 * Metoda získávající ze serveru náhodné obrázky z části/poznávačky pomocí AJAX get požadavku
	 * Po obdržení odpovědi jsou data z požadavku předána metodě importPictures()
	 * Parametr callNextUponResponse - TRUE, pokud se má po obdržení odpovědi zavolat funkce pro zobrazení dalšího obrázku
	 */
	this.loadPictures = function(callNextUponResponse)
	{
		if (callNextUponResponse)
		{
			this.callNext = true;
		}
		else
		{
			this.callNext = false;
		}
		$.get(document.location.href+"/test-pictures", this.importPictures);
	}
	
	/**
	 * Metoda nastavující nový seznam obrázků, přičemž staré obrázky jsou přepsány
	 */
	this.importPictures = function(response)
	{
		//Přepsání dvourozměrného pole do jednorozměrného s objekty
		for (let i = 0; i < response.length; i++) { response[i] = new picture(response[i]["num"], response[i]["url"]); }
		
		//Z nějakého důvodu nejde odkazovat pomocí this
		pictureManager.pictures = response;
		
		//Zkontrolovat, zda se má zavolat funkce pro načtení dalšího obrázku (také nelze odkazovat pomocí this)
		if (pictureManager.callNext === true)
		{
			next();
		}
	}
	
	/**
	 * Metoda získávající první dostupný objekt obrázku a odstraňující jej z pole dostupných obrázků
	 */
	this.getNextPicture = function()
	{
		return this.pictures.shift();
	}
	
	/**
	 * Metoda zjišťující, zda je k dispozici alespoň jeden objekt obrázku
	 */
	this.picturesAvailable = function()
	{
		return (this.pictures.length > 0) ? true : false;
	}
}

/*---------------------------------------------------------------------------------*/

//Jediná instance správce obrázků (statika není zatím moc spolehlivá)
var pictureManager = new pictureList();

/**
 * Funkce, která po načtení dokumentu načítá první obrázek
 */
$(function()
{
	next();
})

/*---------------------------------------------------------------------------------*/

/**
 * Funkce odesílající zadanou odpověď na server ke kontrole
 * @param event
 */
function answer(event)
{
	event.preventDefault();
	
	let ans = $("#textfield").val();
	let num = $("#hiddenInput").val();
	
	$("#answerForm").hide();
	
	$.post(
			"check-test-answer",
			{
				qNum: num,
				ans: ans
			},
			displayResult
		);
}

/**
 * Funkce volaná po obdržení odpovědi ze serveru (požadavek je vyvolán funkcí answer()), která zobrazuje výsledek vyhodnocení
 * @param response Odpověď se serveru obsahující objekt s vlastnostmi "result" (hodnoty "correct"/"wrong") a answer (správná odpověď)
 */
function displayResult(response)
{	
	if (response.result === "correct")
	{
		//Odpověď byla uznána
		if (softCheck($("#textfield").val(), response.answer))
		{
			//Odpověď bez překlepů
			//TODO - tohle asi budeš chtít nějak líp nastylovat
			$("#resultText").text("Správně!");
		}
		else
		{
			//Odpověď s překlepy
			//TODO - tohle asi budeš chtít nějak líp nastylovat
			$("#resultText").text("Správně, ale s překlepem. Správná odpověď: " + response.answer);
		}
	}
	else if (response.result === "wrong")
	{
		//Odpověď nebyla uznána
		//TODO - tohle asi budeš chtít nějak líp nastylovat
		$("#resultText").text("Špatně. Správná odpověď: " + response.answer);
	}
	else
	{
		//Vyskytla se chyba - v response.result je "error" nebo něco úplně jiného
		//V response.answer je cybová hláška
		//TODO - tohle asi budeš chtít udělat jinak, nebo to přesunout úplně jinam
		$("#resultText").text("Vyskytla se chyba: " + response.answer);
	}
	
	$("#result").show();
	$("#nextButton").focus();
}

/**
 * Funkce kontrolující správnost odpovědi
 * Chybějící diakritika a různě veliká písmena nejsou považovány za chybu
 * @param answer Zadaná odpověď
 * @param correct Správná odpověď
 * @returns TRUE, pokud je odpověď správná, FALSE, pokud ne
 */
function softCheck(answer, correct)
{
	//Kód napsaný podle odpovědi na StackOverflow: https://stackoverflow.com/a/37511463
	answer = answer.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
	correct = correct.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
	
	if (answer === correct){ return true; }
	return false;
}

/**
 * Funkce zobrazující další obrázek uložený ve správci obrázků (pictureManager)
 */
function next()
{
	//Nastavení načítání
	$("#main-img").attr("src","images/loading.gif");
	
	$("#result").hide();
	$("#answerForm").show();
	$("#textfield").val("");
	$("#textfield").focus();
	
	
	//Získání dalšího obrázku
	if (!pictureManager.picturesAvailable())
	{
		pictureManager.loadPictures(true);	//Argument true zajistí, že po obdržení odpovědi a načtení obrázků bude tato funkce zavolána znovu
		return;
	}
	
	let newPicture = pictureManager.getNextPicture();
	let newNum = newPicture["num"];
	let newUrl = newPicture["url"];
	
	$("#main-img").attr("src", newUrl);
	$("#hiddenInput").val(newNum);
}