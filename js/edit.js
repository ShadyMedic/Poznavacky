var groupUrls;    //Pole url poznávaček, které jsou v tétož třídě již obsaženy (včetně upravované)
var naturalNames; //Pole názvů přírodnin, které patří do této třídy
$(function()
{
	//Načtení dočasných dat do proměnných a jejich odstranění z DOM
	groupUrls = JSON.parse($("#group-urls-json").text());
	naturalNames = JSON.parse($("#natural-names-json").text());
	$("#temp-data").remove();
	
	//Nastavení event handlerů
	$("#help-button").click(function() { $("#help-text").toggle(); })
	$(".rename-group").click(function() { renameSomething(event, "group"); })
	$(".rename-group-confirm").click(function() { renameSomethingConfirm(event, "group"); })
	$(".group-name-input").keyup(function() { nameTyped(event, "group"); });
	$("#edit-interface").on("click", ".remove-part", function() { removePart(event); })
	$("#edit-interface").on("click", ".rename-part", function() { renameSomething(event, "part"); })
	$("#edit-interface").on("keyup", ".part-name-input", function() { nameTyped(event, "part"); })
	$("#edit-interface").on("click", ".rename-part-confirm", function() { renameSomethingConfirm(event, "part"); })
	$("#edit-interface").on("keyup", ".natural-name-input", function() { nameTyped(event, "natural") })
	$("#edit-interface").on("keyup", ".new-natural-name-input", function() { nameTyped(event, "natural", true) })
	$("#edit-interface").on("click", ".new-natural-button", function() { addNatural(event) })
	$("#edit-interface").on("click", ".rename-natural", function() { renameSomething(event, "natural"); })
	$("#edit-interface").on("click", ".rename-natural-confirm", function() { renameSomethingConfirm(event, "natural"); })
	$("#edit-interface").on("click", ".remove-natural", function() { removeNatural(event); })
	$("#add-part-button").click(addPart);
	$("#submit").click(save);
})

/* -------------------------------------------------------------------------------------------- */

/**
 * Objekt pro uchování dat poznávačky
 * @param groupName Název poznávačky
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

/* -------------------------------------------------------------------------------------------- */

/**
 * Funkce přidávající do DOM nový element části
 */
function addPart()
{
	$("#parts-boxes-container").append(`
	<div class="part-box" style="border:1px solid black">
		<button title="Odebrat část" class="remove-part actionButton">
			<img src='images/cross.svg'/>
		</button>
		<div class="part-name-box" style="display: none">
			<b class="part-name"></b>
			<button title="Přejmenovat část" class="rename-part actionButton">
				<img src='images/pencil.svg'/>
			</button>
		</div>
		<div class="part-name-input-box">
			<input type="text" maxlength="31" class="part-name-input" value=""/>
			<button class="rename-part-confirm actionButton">
				<img src='images/tick.svg'/>
			</button>
		</div>
		<div class="part-name-url">V URL bude zobrazováno jako </div>
		<label>Přírodnina k přidání</label>
		<input type="text" class="new-natural-name-input"/>
		<select class="new-natural-name-suggestions"></select><!-- TODO nahradit při stylování vhodnějším prvkem -->
		<button class="new-natural-button">↵</button>
		<ul class="naturals-in-part">
		</ul>
	</div>
	`);
	$(".part-box:last-child .part-name-input").focus(); //Uživatel by měl rovnou zadat jméno části
}

/**
 * Funkce umožňující změnu jména poznávačky nebo části
 * @param event
 * @param type Typ měněného názvu ("group", "part" nebo "natural")
 */
function renameSomething(event, type)
{
	//let className = (type === "group") ? "group" : (type === "part") ? "part" : "natural";
	let className = type; //V případě přejmenování tříd nebo změny argumentů odkomentovat předchozí řádku a tuto zakomentovat

	$(event.target).parent().parent().hide();
	$(event.target).parent().parent().siblings().filter("." + className + "-name-input-box").show();
}

/**
 * Funkce ukládající změnu jména poznávačky nebo části
 * @param event
 * @param type Typ měněného názvu ("group", "part" nebo "natural")
 */
function renameSomethingConfirm(event, type)
{
	//let className = (type === "group") ? "group" : (type === "part") ? "part" : "natural";
	let className = type; //V případě přejmenování tříd nebo změny argumentů odkomentovat předchozí řádku a tuto zakomentovat
	let errorString = (type === "group") ? "poznávačky" : (type === "part") ? "části" : "přírodniny";
	let minChars = (type === "group") ? 3 : (type === "part") ? 1 : 1;
	//let maxChars = (type === "group") ? 31 : (type === "part") ? 31 : 31;
	let maxChars = 31; //V případě, že by horní limit všech typů stringů neměl být stejný, odkomentovat předchozí řádku a tuto zakomentovat
	//Při změně povolených znaků nezapomenout aktualizovat i znaky nahrazované "-" ve funkci generateUrl()
	let allowedChars = (type === "group") ? "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-" : (type === "part") ? "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-" : "\"0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci"
	let allowedSpecialChars = (type === "group") ? ". _ -" : (type === "part") ? ". _ -" : "_ . - + / * % ( ) \' \"" //Pouze pro použití v chybových hláškách

	let newName;
	let oldName; //Pro kotnrolu unikátnosti u částí a poznávačky
	if ($(event.target).prop("tagName") === "IMG")
	{
		//Potvrzení tlačítkem (kliknuto na obrázek, který jej vyplňuje)
		newName = $(event.target).parent().siblings().filter("input").val()
		oldName = $(event.target).parent().parent().siblings().find("." + className + "-name").text();
	}
	else
	{
		//Potvrzení Enterem
		newName = $(event.target).val();
		oldName = $(event.target).parent().siblings().find("." + className + "-name").text();
	}

	//Kontrola délky
	if (newName === undefined || !(newName.length >= minChars && newName.length <= maxChars))
	{
		alert("Název " + errorString + " musí mít 1 až 31 znaků");
		return;
	}
	
	//Kontrola znaků
	let re = new RegExp("[^" + allowedChars + "]", 'g');
	if (newName.match(re) !== null)
	{
		alert("Název " + errorString + " může obsahovat pouze písmena, číslice, mezeru a znaky " + allowedSpecialChars);
		return;
	}

	//Kontrola unikátnosti
	let url = generateUrl(newName);
	let oldUrl = generateUrl(oldName);
	if (type === "group")
	{
		if (url !== oldUrl)
		{
			if (groupUrls.includes(url))
			{
				alert("Poznávačka se stejným URL již ve vybrané třídě existuje");
				return;
			}
		}
	}
	else if (type === "part")
	{
		//Získej pole jmen všech částí
		let partUrls = $(".part-name").map(function () { return generateUrl($(this).text()); }).get();
		if (url !== oldUrl)
		{
			if (partUrls.includes(url))
			{
				alert("Část se stejným URL již ve vybrané poznávačce existuje");
				return;
			}
		}
	}
	else
	{
		let presentNaturals;
		//Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
		if ($(event.target).prop("tagName") === "IMG")
		{
			//Potvrzení tlačítkem (kliknuto na obrázek, který jej vyplňuje)
			presentNaturals = $(event.target).parent().parent().parent().siblings().filter("li").children().find("span").map(function() {return $(this).text().toUpperCase(); }).get();
		}
		else
		{
			//Potvrzení Enterem
			presentNaturals = $(event.target).parent().parent().siblings().filter("li").children().find("span").map(function() {return $(this).text().toUpperCase(); }).get();
		}

		if (presentNaturals.includes(newName.toUpperCase()))
		{
			alert("Tato přírodnina je již do této části přidána");
			return;
		}
	}
	
	if ($(event.target).prop("tagName") === "IMG")
	{
		//Potvrzení tlačítkem (kliknuto na obrázek, který jej vyplňuje)
		$(event.target).parent().parent().parent().find("." + className + "-name").text(newName);
		
		$(event.target).parent().parent().hide();
		$(event.target).parent().parent().siblings().filter("." + className + "-name-box").show();
	}
	else
	{
		//Potvrzení Enterem
		$(event.target).parent().siblings().find("." + className + "-name").text(newName);
		
		$(event.target).parent().hide();
		$(event.target).parent().siblings().filter("." + className + "-name-box").show();
	}
}

/**
 * Funkce volaná při zadání dalšího znaku do pole pro přejmenování poznávačky nebo části a generující URL reprezentaci nového názvu a zobrazuje jej
 * @param event
 * @param type Typ měněného názvu ("group", "part" nebo "natural")
 * @param addAsNew Týká se pouze přejmenovávání přírodniny, TRUE, pokud se jedná o novou přírodninu, FALSE, pokud se přejmenovává již přidaná přírodnina
 */
function nameTyped(event, type, addAsNew = false)
{
	if (event.keyCode === 13)
	{
		//Byl stisknut Enter --> potvrď změnu
		if (type === "natural" && addAsNew) { addNatural(event); }
		else { renameSomethingConfirm(event, type); }
	}
	else
	{
		//let className = (type === "group") ? "group" : (type === "part") ? "part" : "natural";
		let className = type; //V případě přejmenování tříd nebo změny argumentů odkomentovat předchozí řádku a tuto zakomentovat

		if (type !== "natural")
		{
			//Vygeneruj a zobraz URL verzi nového názvu
			let url = generateUrl($(event.target).val());
			//Zobraz URL do příslušného elementu
			$(event.target).parent().siblings().filter("." + className + "-name-url").text("V URL bude zobrazováno jako " + url);
		}
		else
		{
			if (!addAsNew)
			{
				//Pokud přejmenováváme neuloženou přírodninu, není příliš potřeba napovídat
				//Kdyby chtěl uživatel přidat nějakou existující přírodninu, tak tu s překlepem prostě smaže
				return;
			}
			let inputElement = $(event.target).parent().children().filter(".new-natural-name-input");
			let suggestionsElement = $(event.target).parent().children().filter(".new-natural-name-suggestions");
			let currentTextLowercase = inputElement.val().toLowerCase();
			if (naturalNames.includes(currentTextLowercase))
			{
				inputElement.css("backgroundColor", "#77FF77");
			}
			else if (currentTextLowercase !== "")
			{
				inputElement.css("backgroundColor", "#ff7777");
				let suggestions = naturalNames.filter(word => word.startsWith(currentTextLowercase));
				console.log(suggestions);

				//TODO - zobraz nejbližší shodu (vázáno na element v edit.phtml)
				let suggestionsHTML = "";
				for (let i = 0; i < suggestions.length; i++)
				{
					suggestionsHTML += "<option>" + suggestions[i] + "</option>";
				}
				suggestionsElement.html(suggestionsHTML);
			}
			else
			{
				inputElement.css("backgroundColor", "");
			}
		}
	}
}

/**
 * Funkce generující URL formu názvu poznávačky nebo části
 * @param text Řetězec k převedení na URL
 * @returns URL reprezentace řetězce poskytnutého jako argument
 */
function generateUrl(text)
{
	//Vytvoř ze jména jeho URL formu, stejným způsobem, jako to dělá backend
	let url;
	
	//Převod na malá písmena
	url = text.toLowerCase();

	//Odstranění diakritiky (napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/37511463/14011077)
	url = url.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

	//Převedení mezer, podtržítek, teček a pomlček na "-"
	url = url.replace(/ /g, "-");
	url = url.replace(/\./g, "-");
	url = url.replace(/_/g, "-");

	//Nahrazení násobných "-" za jedno
	url = url.replace(/--+/g, "-");

	//Oříznutí "-" na začátku a na konci
	url = url.replace(/-/g, " ");
	url = url.trim();	//Protože JavaScript má funkci jenom pro zkracování o mezery :P
	url = url.replace(/ /g, "-");
	
	return url;
}

/**
 * Funkce volaná při stisknutí tlačítka pro přidání nové části
 * @param event
 */
function addNatural(event)
{
	let naturalName = $(event.target).parent().children().filter(".new-natural-name-input").val();
	let naturalMinLength = 1;
	let naturalMaxLength = 31;
	let naturalAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci
	
	//Proveď kontrolu unikátnosti
	let presentNaturals = $(event.target).siblings().filter(".naturals-in-part").children().filter("li").children().find("span").map(function() {return $(this).text().toUpperCase(); }).get(); //Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
	if (presentNaturals.includes(naturalName.toUpperCase()))
	{
		alert("Tato přírodnina je již do této části přidána");
		return;
	}
	
	//Kontrola délky
	if (naturalName === undefined || !(naturalName.length >= naturalMinLength && naturalName.length <= naturalMaxLength))
	{
		alert("Název přírodniny musí mít 1 až 31 znaků");
		return;
	}
	
	//Kontrola znaků
	let re = new RegExp("[^" + naturalAllowedChars + "]", 'g');
	if (naturalName.match(re) !== null)
	{
		alert("Název přírodniny může obsahovat pouze písmena, číslice, mezeru a znaky _ . - + / * % ( ) \' \"");
		return;
	}
	
	$(event.target).parent().children().filter(".naturals-in-part").prepend(`
		<li>
			<div class="natural-name-box">
				<span class="natural-name">` + naturalName + `</span>
				<button title="Přejmenovat" class="rename-natural actionButton" style="/*display:none;*/">
					<img src='images/pencil.svg'/>
				</button>
				<button title="Odebrat" class="remove-natural actionButton">
					<img src='images/cross.svg'/>
				</button>
			</div>
			<div class="natural-name-input-box" style="display:none;">
				<input type="text" maxlength="31" class="natural-name-input" value="` + naturalName + `"/>
				<button class="rename-natural-confirm actionButton">
					<img src='images/tick.svg'/>
				</button>
			</div>
		</li>
	`);
	
	//Vymaž vstup
	$(event.target).parent().children().filter(".new-natural-name-input").val("");
}

/**
 * Funkce odebírající určitou přírodninu
 * @param event
 */
function removeNatural(event)
{
	$(event.target).parent().parent().parent().remove();
}

/**
 * Funkce odebírající určitou část
 * @param event
 */
function removePart(event)
{
	if (!confirm("Opravdu si přejete odebrat tuto část?\nZměny se neprojeví, dokud nebude úprava poznávačky uložena.\nTouto akcí nebudou odstraněny žádné existující přírodniny, ani jejich obrázky."))
	{
		return;
	}
	
	$(event.target).parent().parent().remove();
}

/**
 * Funkce volaná po kliknutí na tlačítko "Uložit", která poskládá JSON objekt obsahující všechna data poznávačky a odesílající je na backend
 */
function save()
{
	let data;

	//Krok 1: Získej nový název poznávačky
	let newGroupName = $(".group-name").text();
	data = new groupData(newGroupName);

	//Krok 2: Získej pole všech částí
	let partsArray = $(".part-box").get();

	//Krok 3: Z každé části získej její název
	for (let i = 0; i < partsArray.length; i++)
	{
		data.addPart($(partsArray[i]).find(".part-name").text());
	}

	//Krok 4: Z každé části získej seznam přírodnin
	for (let i = 0; i < partsArray.length; i++)
	{
		let naturalsArray = $(partsArray[i]).find(".naturals-in-part").children().get();
		for (let j = 0; j < naturalsArray.length; j++)
		{
			data.parts[i].addNatural($(naturalsArray[j]).find(".natural-name").text());
		}
	}

	//Odešli data na server
	let url = window.location.href.replace(/\/$/, "").replace(/edit$/, "")+"confirm-group-edit"; //Adresa současné stránky (bez edit a lomena na konci)
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
						//Zruš tlačítka pro rychlé prejmenování
						$(".rename-natural").hide();

						if (confirm("Změny byly úspěšně uloženy\nPřejete si aktualizovat stránku pro ověření změn?"))
						{
							location.reload();
						}
					}
					else if (messageType === "error")
					{
						//Chyba vstupu
						alert(message);
					}
					else if (messageType = "warning")
					{
						//Chyba ukládání
						alert(message + data["json"]);
					}
				}
			);
		},
		"json"
	);
}