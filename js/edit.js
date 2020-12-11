$(function()
{
	$("#add-part-button").click(addPart);
	$(".rename-part").click(function() { renamePart(event); })
	$(".rename-part-confirm").click(function() { renamePartConfirm(event); })
})

/**
 * Funkce přidávající do DOM nový element části
 */
function addPart()
{
	console.log("addPart");
	$("#parts-boxes-container").append(`
		<div class="part-box">
        <button title="Odebrat část">
        	<img src='images/cross.svg'/>
        </button>
        <div class="part-name-box">
            <b class="part-name">Název části</b>
            <button title="Přejmenovat část" class="rename-part actionButton">
            	<img src='images/pencil.svg'/>
        	</button>
    	</div>
        <div class="part-name-input-box" style="display:none;">
        	<input type="text" maxlength="31" class="part-name-input"/>
        	<button class="rename-part-confirm actionButton"><img src='images/tick.svg'/></button>
        </div>
        <label>Přírodnina k přidání</label>
        <input type="text" />
        <ul class="naturals-in-part">
            <!-- Zde budou řádky s názvy přidaných přírodnin -->
            <li>
            	<span>Vrbovka úzkolistá</span>
                <button title="Odebrat">
                	<img src='images/pencil.svg'/>
                </button>
            </li>
        </ul>
    </div>
	`);
}

/**
 * Funkce umožňující změnu jména části
 */
function renamePart(event)
{
	console.log("renamePart");
	$(event.target).parent().parent().hide();
	$(event.target).parent().parent().siblings().filter(".part-name-input-box").show();
}

/**
 * Funkce ukládající změnu jména části
 */
function renamePartConfirm(event)
{
	console.log("renamePartConfirm");
	let newName = $(event.target).parent().siblings().filter(".part-name-input-box > input").val();
	
	//Kontrola délky
	if (newName === undefined || !(newName.length >= 1 && newName.length < 31))
	{
		alert("Jméno části musí mít 1 až 31 znaků");
		return;
	}
	
	//Kontrola znaků
	if (newName.match(/^[0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-]*$/) === null)
	{
		alert("Jméno části může obsahovat pouze písmena, číslice, mezeru a znaky . _ -");
		return;
	}
	
	$(event.target).parent().parent().parent().find(".part-name").text(newName);
	
	$(event.target).parent().parent().hide();
	$(event.target).parent().parent().siblings().filter(".part-name-box").show();
}