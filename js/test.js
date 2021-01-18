//vše, co se děje po načtení stránky
$(function() {
	//event listener formuláře na odeslání odpovědi
	$("#answer-form").submit(function(event){answer(event)});

	//eventy listenery tlačítek
	$("#next-button").click(function(){next()});

	resizeMainImg();
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
	resizeMainImg();
})

//funkce nastavující výšku #main-img tak, aby byla shodná s jeho šířkou
function resizeMainImg(){
	$("#test-wrapper .picture").css("height", $("#test-wrapper .picture").outerWidth());
}

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
	 * Po obdržení odpovědi je sezma, obrázků naplněn (staré obrázky jsou přepsány)
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
		$.get(document.location.href+"/test-pictures",
			function (response, status)
			{
				ajaxCallback(response, status,
					function(messageType, message, data)
					{
						if (messageType === "success")
						{
							//Přepsání dvourozměrného pole do jednorozměrného s objekty
							for (let i = 0; i < data.pictures.length; i++) { data.pictures[i] = new picture(data.pictures[i]["num"], data.pictures[i]["url"]); }
							
							//Z nějakého důvodu nejde odkazovat pomocí this
							pictureManager.pictures = data.pictures;
							
							//Zkontrolovat, zda se má zavolat funkce pro načtení dalšího obrázku (také nelze odkazovat pomocí this)
							if (pictureManager.callNext === true)
							{
								next();
							}
						}
						else
						{
							//Požadavek nebyl úspěšný
							alert(message);
						}
					}
				);
			},
			"json"
		);
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
	
	let ans = $("#answer").val();
	let num = $("#answer-hidden").val();
	
	$("#answerForm").hide();
	
	$.post(
			"check-test-answer",
			{
				qNum: num,
				ans: ans
			},
			function (response, status) { ajaxCallback(response, status, displayResult); }
		);
}

/**
 * Funkce volaná po obdržení odpovědi ze serveru (požadavek je vyvolán funkcí answer()), která zobrazuje výsledek vyhodnocení
 * @param response Odpověď se serveru obsahující objekt s vlastnostmi "result" (hodnoty "correct"/"wrong") a answer (správná odpověď)
 */
function displayResult(messageType, message, data)
{
	var correctAnswer = $("<p class='correct'></>").text("Správně!"); 
	var correctTypoAnswer = $("<p class='correct-typo'></p>").text("Správně, ale s překlepem."); 
	var incorrectAnswer = $("<p class='incorrect'></p>").text("Špatně."); 
	var correction = $("<p class='correction'></p>").text("Správná odpověď je: ");
	var error = $("<p class='error'></p>").text("Vyskytla se chyba: ");
	
	$("#result-text").empty();

	if (message === "correct")
	{
		//Odpověď byla uznána
		if (softCheck($("#answer").val(), data.answer))
		{
			//Odpověď bez překlepů
			//TODO - tohle asi budeš chtít nějak líp nastylovat
			$("#result-text").append(correctAnswer);
		}
		else
		{
			//Odpověď s překlepy
			//TODO - tohle asi budeš chtít nějak líp nastylovat
			$("#result-text").append(correctTypoAnswer);
			$("#result-text").append(correction);
			correction.append("<span>" + data.answer + "</span>");
		}
	}
	else if (message === "wrong")
	{
		//Odpověď nebyla uznána
		//TODO - tohle asi budeš chtít nějak líp nastylovat
		$("#result-text").append(incorrectAnswer);
		$("#result-text").append(correction);
		correction.append("<span>" + data.answer + "</span>");
	}
	else
	{
		//Vyskytla se chyba - v response.result je "error" nebo něco úplně jiného
		//V data.answer je chybová hláška
		//TODO - tohle asi budeš chtít udělat jinak, nebo to přesunout úplně jinam
		$("#result-text").append(error);
		error.append("<span>" + data.answer + "</span>");
	}
	
	$("#result").show();
	$("#next-button").focus();
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
	if ($(".report-box").hasClass("show"))
		cancelReport();
		
	//Nastavení načítání
	$("#main-img").attr("src","images/loading.gif");
	
	$("#result").hide();
	$("#answerForm").show();
	$("#answer").val("");
	$("#answer").focus();
	
	
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
	$("#answer-hidden").val(newNum);
}