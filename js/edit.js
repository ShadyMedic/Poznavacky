$(function()
{
	$(".rename-group").click(function() { renameSomething(event, true); })
	$(".rename-group-confirm").click(function() { renameSomethingConfirm(event, true); })
	$(".rename-part").click(function() { renameSomething(event, false); })
	$(".rename-part-confirm").click(function() { renameSomethingConfirm(event, false); })
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
        <button title="Odebrat část" class="actionButton">
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
            <li>
            	<span>Vrbovka úzkolistá</span>
                <button title="Odebrat" class="actionButton">
                	<img src='images/cross.svg'/>
                </button>
            </li>
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
