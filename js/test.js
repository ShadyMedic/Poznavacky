$(function()
{
	resizeMainImg();
	next();

	//eventy listenery tlačítek
	$("#next-button").click(function() {next()});
	
	//event listener formuláře na odeslání odpovědi
	$("#answer-form").submit(function(event) {answer(event)});
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
	 * Po obdržení odpovědi je seznam obrázků naplněn (staré obrázky jsou přepsány)
	 * @param {bool} callNextUponResponse True, pokud se má po obdržení odpovědi zavolat funkce pro zobrazení dalšího obrázku
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

		let url = window.location.href;
		if (url.endsWith('/')) { url = url.slice(0, -1); } //odstranění trailing slashe (pokud je přítomen)
		url = url.substr(0, url.lastIndexOf("/")); //Odstranění akce (/test)

		$.get(url + "/test-pictures",
			function (response, status)
			{
				ajaxCallback(response, status,
					function(messageType, message, data)
					{
						if (messageType === "success")
						{
							//přepsání dvourozměrného pole do jednorozměrného s objekty
							for (let i = 0; i < data.pictures.length; i++) { data.pictures[i] = new picture(data.pictures[i]["num"], data.pictures[i]["url"]); }
							
							//z nějakého důvodu nejde odkazovat pomocí this
							pictureManager.pictures = data.pictures;
							
							//kontrola, zda se má zavolat funkce pro načtení dalšího obrázku (také nelze odkazovat pomocí this)
							if (pictureManager.callNext === true)
							{
								next();
							}
						}
						else
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

//jediná instance správce obrázků (statika není zatím moc spolehlivá)
var pictureManager = new pictureList();

/**
 * Funkce odesílající zadanou odpověď na server ke kontrole
 * @param {event} event
 */
function answer(event)
{
	event.preventDefault();
	
	$("#submit-answer-button").addClass("disabled");

	let ans = $("#answer").val();
	let num = $("#answer-hidden").val();
	$("#answer-hidden").val(-1);
	
	let url = window.location.href;
	if (url.endsWith('/')) { url = url.slice(0, -1); } //dstranění trailing slashe (pokud je přítomen)
	url = url.substr(0, url.lastIndexOf("/")); //odstranění akce (/test)

	if (num == -1) return;

	$.post(
		url + "/check-test-answer",
		{
			qNum: num,
			ans: ans
		},
		function (response, status) { ajaxCallback(response, status, displayResult); }
	);
}

/**
 * Funkce zobrazující, zda uživatel odpověděl správně
 * @param {string} messageType Typ hlášky
 * @param {string} message Text hlášky
 * @param {string} data Dodatečné informace
 */
function displayResult(messageType, message, data)
{
	let $correctAnswer = $("<p class='correct'></>").text("Správně!"); 
	let $correctTypoAnswer = $("<p class='correct-typo'></p>").text("Správně, ale s překlepem."); 
	let $incorrectAnswer = $("<p class='incorrect'></p>").text("Špatně."); 
	let $correction = $("<p class='correction'></p>").text("Správná odpověď je: ");
	
	$("#result-text").empty();

	//odpověď byla uznána
	if (message === "correct")
	{
		//odpověď bez překlepů
		if (softCheck($("#answer").val(), data.answer))
		{
			$("#result-text").append($correctAnswer);
		}
		//odpověď s překlepy
		else
		{
			$("#result-text").append($correctTypoAnswer);
			$("#result-text").append($correction);
			$correction.append("<span>" + data.answer + "</span>");
		}
	}
	//odpověď nebyla uznána
	else if (message === "wrong")
	{
		$("#result-text").append($incorrectAnswer);
		$("#result-text").append($correction);
		$correction.append("<span>" + data.answer + "</span>");
	}
	else
	{
		newMessage(message, "error");
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
	
	if (answer === correct) return true;
	else return false;
}

/**
 * Funkce zobrazující další obrázek uložený ve správci obrázků (pictureManager)
 */
function next()
{
	//pokud je dosavadní obrázek nahlašován, je nahlašování zrušeno
	if ($(".report-box").hasClass("show"))
	{
		cancelReport();
	}
		
	//nastavení načítání
	$("#main-img").attr("src","images/loading.svg");
	
	$("#result").hide();
	$("#answer").val("");
	$("#answer").focus();
	
	//získání dalšího obrázku
	if (!pictureManager.picturesAvailable())
	{
		pictureManager.loadPictures(true);	//argument true zajistí, že po obdržení odpovědi a načtení obrázků bude tato funkce zavolána znovu
		return;
	}
	
	let newPicture = pictureManager.getNextPicture();
	let newNum = newPicture["num"];
	let newUrl = newPicture["url"];

	$("#main-img").attr("src", newUrl);
	$("#answer-hidden").val(newNum);

	$("#submit-answer-button").removeClass("disabled");
}