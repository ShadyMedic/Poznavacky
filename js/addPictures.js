/**
 * Funkce, která po načtení stránky nastavuje even handlery na skrytý náhled obrázku.
 * src atributa skrytého náhledu se nastavuje po potvrzení zadané URL adresy
 * Pokud se tento obrázek v pořádku načte, je adresa nastavena jako src atributa viditelného náhledu a je zobrazeno tlačítko pro odeslání fomruláře
 */
$(function()
{
	//Chyba při načítání obrázku
	$("#previewImgHidden").on("error", function()
	{
		$("#previewImg").attr("src", "images/imagePreview.png");
		$("#field3").hide();
	});
	
	//Obrázek načten úspěšně
	$("#previewImgHidden").on("load", function()
	{
		$("#previewImg").attr("src", $("#previewImgHidden").attr("src"));
		$("#field3").show();
	});
});

/**
 * Funkce, která se spouští po výberu přírodniny a nastavující název té vybrané
 * Také zobrazuje druhý fieldset s <input>em pro URL obrázku
 */
function naturalSelected()
{
	$("#field2").show();
	
	let selectedNatural = "";
	var arr = $("#dropList").val();
	for (var i = arr.length - 1; arr[i] != '('; i--){}
	for (var j = 0; j < i - 1; j++){selectedNatural += arr[j];}
	$("#duckLink").attr("href", "https://duckduckgo.com/?q=" + selectedNatural + "&iax=images&ia=images");
	$("#hiddenInput").val(selectedNatural);
}

/**
 * Funkce, která se spouští po potvrzení URL adresy
 * Nastavuje adresu pro skrytý náhled obrázku
 */
function pictureSelected(event)
{
	event.preventDefault();
	$("#previewImgHidden").attr("src", $("#urlInput").val());
	//Třetí fieldset je zobrazen, pokud je načten obrázek úspěšně (viz funkce $(function(){}))
}