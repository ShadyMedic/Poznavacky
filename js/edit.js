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
	$(".rename-group").click(function() { renameSomething(event, true); })
	$(".rename-group-confirm").click(function() { renameSomethingConfirm(event, true); })
	$(".group-name-input").keyup(function() { nameTyped(event, true); });
	$("#edit-interface").on("click", ".remove-part", function() { removePart(event); })
	$("#edit-interface").on("click", ".rename-part", function() { renameSomething(event, false); })
	$("#edit-interface").on("keyup", ".part-name-input", function() { nameTyped(event, false); })
	$("#edit-interface").on("click", ".rename-part-confirm", function() { renameSomethingConfirm(event, false); })
	$("#edit-interface").on("keyup", ".natural-input", function() { naturalTyped(event) })
	$("#edit-interface").on("click", ".natural-button", function() { addNatural(event) })
	$("#edit-interface").on("click", ".remove-natural", function() { removeNatural(event); })
	$("#add-part-button").click(addPart);
	$("#submit").click(save);
})

/* -------------------------------------------------------------------------------------------- */

/**
 * Objekt pro uchování dat poznávačky
 * @param {Název poznávačky} groupName 
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
 * @param {Název části} partName 
 */
function partData(partName)
{
	this.name = partName;
	this.naturals = new Array();

	/**
	 * Metoda přidávající do této části další přírodninu
	 * @param {Název přírodniny} naturalName 
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
        <div class="part-name-box" style="display:none;">
            <b class="part-name">Název části</b>
            <button title="Přejmenovat část" class="rename-part actionButton">
            	<img src='images/pencil.svg'/>
        	</button>
    	</div>
        <div class="part-name-input-box">
        	<input type="text" maxlength="31" class="part-name-input"/>
        	<button class="rename-part-confirm actionButton">
        		<img src='images/tick.svg'/>
    		</button>
        </div>
        <div class="part-name-url">V URL bude zobrazováno jako nazev-casti</div>
        <label>Přírodnina k přidání</label>
        <input type="text" class="natural-input" />
        <button class="natural-button">↵</button>
        <ul class="naturals-in-part">
            <!-- Zde budou řádky s názvy přidaných přírodnin -->
        </ul>
    </div>
	`);
	$(".part-box:last-child .part-name-input").focus(); //Uživatel by měl rovnou zadat jméno části
}

/**
 * Funkce umožňující změnu jména poznávačky nebo části
 */
function renameSomething(event, renamingGroup)
{
	let className = (renamingGroup) ? "group" : "part";
	
	$(event.target).parent().parent().hide();
	$(event.target).parent().parent().siblings().filter("." + className + "-name-input-box").show();
}

/**
 * Funkce ukládající změnu jména poznávačky nebo části
 */
function renameSomethingConfirm(event, renamingGroup)
{
	let className = (renamingGroup) ? "group" : "part";
	let errorString = (renamingGroup) ? "poznávačky" : "části";
	let minChars = (renamingGroup) ? 3 : 1;
	let maxChars = (renamingGroup) ? 31 : 31;
	//Při změně povolených znaků nezapomenout aktualizovat i znaky nahrazované "-" ve funkci generateUrl()
	let allowedChars = (renamingGroup) ? "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-" : "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-";
	
	let newName;
	if ($(event.target).prop("tagName") === "IMG")
	{
		//Potvrzení tlačítkem (kliknuto na obrázek, který jej vyplňuje)
		newName = $(event.target).parent().siblings().filter("." + className + "-name-input-box > input").val();
	}
	else
	{
		//Potvrzení Enterem
		newName = $(event.target).val();
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
		alert("Název " + errorString + " může obsahovat pouze písmena, číslice, mezeru a znaky . _ -");
		return;
	}

	//Kontrola unikátnosti
	if (renamingGroup)
	{
		let url = generateUrl(newName);
		
		if (groupUrls.includes(url))
		{
			alert("Poznávačka se stejným URL již ve vybrané třídě existuje");
			return;
		}
	}
	else
	{
		//Získej pole jmen všech částí
		let partNames = $(".part-name").map(function () { return generateUrl($(this).text()); }).get();
		if (partNames.includes(generateUrl(newName)))
		{
			alert("Část se stejným URL již ve vybrané poznávačce existuje");
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
		$(event.target).parent().parent().find("." + className + "-name").text(newName);
		
		$(event.target).parent().hide();
		$(event.target).parent().siblings().filter("." + className + "-name-box").show();
	}
}

/**
 * Funkce volaná při zadání dalšího znaku do pole pro přejmenování poznávačky nebo části a generující URL reprezentaci nového názvu a zobrazuje jej
 * @param renamingGroup TRUE, pokud je zadávání jméno přírodniny, FALSE, pokud části
 */
function nameTyped(event, renamingGroup)
{
	if (event.keyCode === 13)
	{
		//Byl stisknut Enter --> potvrď změnu
		renameSomethingConfirm(event, renamingGroup);
	}
	else
	{
		let className = (renamingGroup) ? "group" : "part";
		
		//Vygeneruj a zobraz URL verzi nového názvu
		let url = generateUrl($(event.target).val());
		
		//Zobraz URL do příslušného elementu
		$(event.target).parent().siblings().filter("." + className + "-name-url").text("V URL bude zobrazováno jako " + url);
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
 * Funkce volaná při zadání nějakého znaku do pole pro přidávání přírodniny a navrhující existující přírodniny či kontrolující potvrzení volby klávesou Enter
 */
function naturalTyped(event)
{
	if (event.keyCode === 13)
	{
		//Byl stisknut Enter --> přidej přírodninu do seznamu
		addNatural(event);
	}
	else
	{
		//TODO - proveď filtraci a zobraz návrhy
	}
}

/**
 * Funkce volaná při stisknutí tlačítka pro přidání nové části
 */
function addNatural(event)
{
	let naturalName = $(event.target).parent().children().filter(".natural-input").val();
	let naturalMinLength = 1;
	let naturalMaxLength = 31;
	let naturalAllowedChars = "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.+/*%()\'\"-"; //- musí být z nějakého důvodu až na konci
	
	//Proveď kontrolu unikátnosti
	let presentNaturals = $(event.target).siblings().filter(".naturals-in-part").children().filter("li").children().filter("span").map(function() {return $(this).text().toUpperCase(); }).get(); //Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
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
        	<span class="natural-name">` + naturalName + `</span>
            <button title="Odebrat" class="remove-natural actionButton">
            	<img src='images/cross.svg'/>
            </button>
        </li>
	`);
	
	//Vymaž vstup
	$(event.target).parent().children().filter(".natural-input").val("");
}

/**
 * Funkce odebírající určitou přírodninu
 */
function removeNatural(event)
{
	$(event.target).parent().parent().remove();
}

/**
 * Funkce odebírající určitou část
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
						if (confirm(`
						Změny byly úspěšně uloženy\n
						Přejete si aktualizovat stránku pro ověření změn?
						`)) { location.reload(); }
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