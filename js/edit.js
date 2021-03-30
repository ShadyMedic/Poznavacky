var groupUrls;    //Pole url poznávaček, které jsou v tétož třídě již obsaženy (včetně upravované)
var naturalNames; //Pole názvů přírodnin, které patří do této třídy
var currentGroupUrl //Nové URL poznávačky, pokud je přejmenována
$(function()
{
	//Načtení dočasných dat do proměnných a jejich odstranění z DOM
	groupUrls = JSON.parse($("#group-urls-json").text());
	naturalNames = JSON.parse($("#natural-names-json").text());
	$("#temp-data").remove();
	
	//event listenery tlačítek
	$("#help-button").click(function() { $("#help-text").toggle(); })
	$(".rename-group").click(function(event) { rename(event, "group"); })
	$(".rename-group-confirm").click(function(event) { renameConfirm(event, "group"); })
	$("#edit-group-wrapper").on("click", ".remove-part", function(event) { removePart(event); })
	$("#edit-group-wrapper").on("click", ".rename-part", function(event) { rename(event, "part"); })
	$("#edit-group-wrapper").on("keyup", ".part-name-input", function(event) { nameTyped(event, "part"); })
	$("#edit-group-wrapper").on("click", ".rename-part-confirm", function(event) { renameConfirm(event, "part"); })
	$("#edit-group-wrapper").on("keyup", ".natural-name-input", function(event) { nameTyped(event, "natural") })
	$("#edit-group-wrapper").on("keyup", ".new-natural-name-input", function(event) { nameTyped(event, "natural", true) })
	$("#edit-group-wrapper").on("click", ".new-natural-button", function(event) { addNatural(event) })
	$("#edit-group-wrapper").on("click", ".rename-natural", function(event) { rename(event, "natural"); })
	$("#edit-group-wrapper").on("click", ".rename-natural-confirm", function(event) { renameConfirm(event, "natural"); })
	$("#edit-group-wrapper").on("click", ".remove-natural", function(event) { removeNatural(event); })
	$("#edit-group-wrapper").on("click", ".rename-natural-cancel", function(event) { renameCancel(event); })
	$("#edit-group-wrapper").on("click", ".rename-part-cancel", function(event) { renameCancel(event); })
	$("#edit-group-wrapper").on("click", ".rename-group-cancel", function(event) { renameCancel(event); })
	$("#add-part-button").click(addPart);
	$("#submit-button").click(save);
	$(window).click(function(event) {renameCancelAll(event)})

	//event listener stisknutí klávesy
	$(".group-name-input").keyup(function(event) { nameTyped(event, "group"); });

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
	$("#parts-boxes-container").append($("#part-box-template").html());
	$(".part-box:last-child")[0].scrollIntoView({
		behavior: "smooth",
		block: "start"
	});
	$(".part-box:last-child .part-name-input").focus(); //Uživatel by měl rovnou zadat jméno části
}

/**
 * Funkce umožňující změnu jména poznávačky nebo části
 * @param event
 * @param type Typ měněného názvu ("group", "part" nebo "natural")
 */
function rename(event, type)
{
	let $nameBox = $(".natural-name-box, .part-name-box, .group-name-box");
	let $nameInputBox = $(".natural-name-input-box, .part-name-input-box, .group-name-input-box");

	$(event.target).closest($nameBox).hide();
	$(event.target).closest($nameBox).siblings().filter($nameInputBox).show();
	$(event.target).closest($nameBox).siblings().filter($nameInputBox).find(".text-field").focus().select();

}

//funkce rušící všechna aktivní přejmenovávání
function renameCancelAll(event) 
{
	let $nameBox = $(".natural-name-box, .part-name-box, .group-name-box");
	let $nameInputBox = $(".natural-name-input-box, .part-name-input-box, .group-name-input-box");

	//pouze, pokud se neklikne do nameInputBoxu
	if (!$nameInputBox.is(event.target) && $nameInputBox.has(event.target).length === 0)
	{
		//zobrazení všech nameBoxů kromě nameBoxu položky, kterou chceme přejmenovat
		$nameBox.not($(event.target).closest($nameBox)).show();

		//skrytí všech nameInputBoxů kromě nameInputBoxu položky, kterou chceme přejmenovat, a obnovení jejich textových polí
		let $otherNameInputBoxes = $($nameInputBox.not($(event.target).closest($nameBox).siblings().filter($nameInputBox)));
		$otherNameInputBoxes.hide();
		$otherNameInputBoxes.each(function() {
			$(this).find(".text-field").val($(this).siblings().filter($nameBox).find("span").text());
		})
	}
}

function renameCancel(event) {
	let $nameBox = $(".natural-name-box, .part-name-box, .group-name-box");
	let $nameInputBox = $(".natural-name-input-box, .part-name-input-box, .group-name-input-box");

	$(event.target).closest($nameInputBox).hide();
	$(event.target).closest($nameInputBox).siblings().filter($nameBox).show();
	$(event.target).siblings().filter(".text-field").val("");
}
/**
 * Funkce ukládající změnu jména poznávačky nebo části
 * @param event
 * @param type Typ měněného názvu ("group", "part" nebo "natural")
 */
function renameConfirm(event, type)
{
	//let className = (type === "group") ? "group" : (type === "part") ? "part" : "natural";
	let className = type; //V případě přejmenování tříd nebo změny argumentů odkomentovat předchozí řádku a tuto zakomentovat
	let errorString = (type === "group") ? "poznávačky" : (type === "part") ? "části" : "přírodniny";
	let minChars = (type === "group") ? 3 : (type === "part") ? 1 : 1;
	//let maxChars = (type === "group") ? 31 : (type === "part") ? 31 : 31;
	let maxChars = 31; //V případě, že by horní limit všech typů stringů neměl být stejný, odkomentovat předchozí řádku a tuto zakomentovat
	//Při změně povolených znaků nezapomenout aktualizovat i znaky nahrazované "-" ve funkci generateUrl()
	let allowedChars = (type === "group") ? "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-" : (type === "part") ? "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-" : "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci"
	let allowedSpecialChars = (type === "group") ? ". _ -" : (type === "part") ? ". _ -" : "_ . - + / * % ( ) \' \"" //Pouze pro použití v chybových hláškách

	let $nameInputBox = $(".natural-name-input-box, .part-name-input-box, .group-name-input-box");
	let newName;
	let oldName; //Pro kotnrolu unikátnosti u částí a poznávačky

	if ($(event.target).prop("tagName") !== "INPUT")
	{
		//Potvrzení tlačítkem (kliknuto na obrázek, který jej vyplňuje)
		newName = $(event.target).closest($nameInputBox).find("input").val();
		oldName = $(event.target).closest($nameInputBox).siblings().find("span").text();
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
		currentGroupUrl = url;
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
		presentNaturals = $(event.target).closest(".naturals-in-part").find(".natural-name").map(function() {return $(this).text().toUpperCase(); }).get();

		if (presentNaturals.includes(newName.toUpperCase()))
		{
			alert("Tato přírodnina je již do této části přidána");
			return;
		}
	}
	

	//skrytí inputu, zobrazení textu
	$(event.target).closest($nameInputBox).siblings().find("." + className + "-name").text(newName);
	$(event.target).closest($nameInputBox).hide();
	$(event.target).closest($nameInputBox).siblings().filter("." + className + "-name-box").show();
	
	//byly provedeny změny --> zamkni stránku
	lock();
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
		else { renameConfirm(event, type); }
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
			let inputElement = $(event.target);
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
	let $naturalInput;
	if ($(event.target).prop("tagName") === "BUTTON")
	{
		//Potvrzení tlačítekm
		$naturalInput = $(event.target).siblings().filter(".new-natural-name-input");
	}
	else
	{
		//Potvrzení enterem
		$naturalInput = $(event.target);
	}
	let naturalName = $naturalInput.val();

	let naturalMinLength = 1;
	let naturalMaxLength = 31;
	let naturalAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci
	
	//Proveď kontrolu unikátnosti
	let presentNaturals = $(event.target).closest(".part-box").find(".natural-name").map(function() {return $(this).text().toUpperCase(); }).get(); //Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
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
	
	let $naturalList;
	if ($(event.target).prop("tagName") === "BUTTON")
	{
		//Potvrzení tlačítekm
		$naturalList = $(event.target).closest(".part-box").find(".naturals-in-part");
	}
	else
	{
		//Potvrzení enterem
		$naturalList = $(event.target).closest(".part-box").find(".naturals-in-part");
	}


	$($naturalList).prepend($("#natural-item-template").html());
	$($naturalList.children().first()).find(".natural-name").text(naturalName);
	$($naturalList.children().first()).find(".natural-name-input").attr("value", naturalName);
	
	//Vymaž vstup
	$naturalInput.val("").focus();
	
	//byly provedeny změny --> zamkni stárnku
	lock();
}

/**
 * Funkce odebírající určitou přírodninu
 * @param event
 */
function removeNatural(event)
{
	$(event.target).closest("li").remove();
	
	//byly provedeny změny --> zamkni stárnku
	lock();
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
	
	$(event.target).closest(".part-box").remove();
	
	//byly provedeny změny --> zamkni stárnku
	lock();
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
	let partsArray = $("#edit-group-wrapper .part-box").get();

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
						
						//odemkni stránku
						unlock();
						
						if (confirm("Změny byly úspěšně uloženy\nPřejete si aktualizovat stránku pro ověření změn?"))
						{
							if (currentGroupUrl !== undefined)
							{
								//Jméno poznávačky bylo změněno
								let url = location.href;
								url = url.replace(/\/[a-z0-9-]+\/edit/, "/" + currentGroupUrl + "/edit");
								window.location.href = url;
							}
						}
					}
					else if (messageType === "error")
					{
						//Chyba vstupu
						newMessage(message, "error");

					}
					else if (messageType = "warning")
					{
						//Chyba ukládání
						//alert(message + data["json"]);
						newMessage(message, "warning", data["json"]);
					}
				}
			);
		},
		"json"
	);
}

/**
 * Funkce zamykající stránku, tzn. při pokusu o její opuštění je zobrazen potvrzovací dialog pro zamezení ztráty neuložených změn v poznávačce
 */
function lock()
{
	$(window).on("beforeunload", function() { return ""; })
}

/**
 * Funkce odemykající stránku, tzn. při pokusu o její opuštění se již nebude zobrazovat potvrzovací dialog
 */
function unlock()
{
	$(window).off("beforeunload")
}