$(function()
{
	$("#help-button").click(function() { $("#help-text").toggle(); })
	$(".rename-group").click(function() { renameSomething(event, true); })
	$(".rename-group-confirm").click(function() { renameSomethingConfirm(event, true); })
	$("#edit-interface").on("click", ".remove-part", function() { removePart(event); })
	$("#edit-interface").on("click", ".rename-part", function() { renameSomething(event, false); })
	$("#edit-interface").on("click", ".rename-part-confirm", function() { renameSomethingConfirm(event, false); })
	$("#edit-interface").on("keyup", ".natural-input", function() { naturalTyped(event) })
	$("#edit-interface").on("click", ".natural-button", function() { addNatural(event) })
	$("#edit-interface").on("click", ".remove-natural", function() { removeNatural(event); })
	$("#add-part-button").click(addPart);
})

/**
 * Funkce přidávající do DOM nový element části
 */
function addPart()
{
	console.log("addPart");
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
        <label>Přírodnina k přidání</label>
        <input type="text" />
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
	console.log("renameSomething");
	let className = (renamingGroup) ? "group" : "part";
	
	$(event.target).parent().parent().hide();
	$(event.target).parent().parent().siblings().filter("." + className + "-name-input-box").show();
}

/**
 * Funkce ukládající změnu jména poznávačky nebo části
 */
function renameSomethingConfirm(event, renamingGroup)
{
	console.log("renameSomethingConfirm");
	let className = (renamingGroup) ? "group" : "part";
	let errorString = (renamingGroup) ? "poznávačky" : "části";
	let minChars = (renamingGroup) ? 3 : 1;
	let maxChars = (renamingGroup) ? 31 : 31;
	let allowedChars = (renamingGroup) ? "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-" : "0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-";
	
	let newName = $(event.target).parent().siblings().filter("." + className + "-name-input-box > input").val();
	
	//Kontrola délky
	if (newName === undefined || !(newName.length >= minChars && newName.length < maxChars))
	{
		alert("Jméno " + errorString + " musí mít 1 až 31 znaků");
		return;
	}
	
	//Kontrola znaků
	let re = new RegExp("[^" + allowedChars + "]", 'g');
	if (newName.match(re) !== null)
	{
		alert("Jméno " + errorString + " může obsahovat pouze písmena, číslice, mezeru a znaky . _ -");
		return;
	}
	
	$(event.target).parent().parent().parent().find("." + className + "-name").text(newName);
	
	$(event.target).parent().parent().hide();
	$(event.target).parent().parent().siblings().filter("." + className + "-name-box").show();
}

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

function addNatural(event)
{
	let naturalName = $(event.target).parent().children().filter(".natural-input").val();
	
	//Proveď kontrolu unikátnosti
	let presentNaturals = $(event.target).siblings().filter(".naturals-in-part").children().filter("li").children().filter("span").map(function() {return $(this).text(); }).get(); //Získej seznam přidaných přírodnin - kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/3496338/14011077
	if (presentNaturals.includes(naturalName))
	{
		alert("Tato přírodnina je již do této části přidána");
		return;
	}
	
	$(event.target).parent().children().filter(".naturals-in-part").prepend(`
		<li>
        	<span>` + naturalName + `</span>
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
	console.log("removePart");
	if (!confirm("Opravdu si přejete odebrat tuto část?\nZměny se neprojeví, dokud nebude úprava poznávačky uložena.\nTouto akcí nebudou odstraněny žádné existující přírodniny, ani jejich obrázky."))
	{
		return;
	}
	
	$(event.target).parent().parent().remove();
}