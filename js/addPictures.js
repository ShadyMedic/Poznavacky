/**
 * Funkce, která po načtení stránky nastavuje even handlery na skrytý náhled obrázku.
 * src atributa skrytého náhledu se nastavuje po potvrzení zadané URL adresy
 * Pokud se tento obrázek v pořádku načte, je adresa nastavena jako src atributa viditelného náhledu a je zobrazeno tlačítko pro odeslání fomruláře
 */
//vše, co se děje po načtení stránky
$(function()
{
	//event listenery tlačítek
	$("#url-confirm-button").click(function(event) {pictureSelected(event)});
	$("#add-natural-select .custom-options .custom-option").click(function() {naturalSelected()});

	//Chyba při načítání obrázku
	$("#preview-img-hidden").on("error", function()
	{
		$("#preview-img").attr("src", "images/imagePreview.png");
		$("#submit-section").hide();
	});
	
	//Obrázek načten úspěšně
	$("#preview-img-hidden").on("load", function()
	{
		$("#preview-img").attr("src", $("#preview-img-hidden").attr("src"));
	});
});

/**
 * Funkce, která se spouští po výberu přírodniny a nastavující název té vybrané
 * Také zobrazuje druhý fieldset s <input>em pro URL obrázku
 */
function naturalSelected()
{
	setTimeout(function(){
		let selectedNatural = "";
		var arr = $("#add-natural-select .custom-options .selected").text();
		for (var i = arr.length - 1; arr[i] != '('; i--){}
		for (var j = 0; j < i - 1; j++){selectedNatural += arr[j];}
		$("#duck-link").attr("href", "https://duckduckgo.com/?q=" + selectedNatural + "&iax=images&ia=images");
		$("#natural-name-hidden").val(selectedNatural);
		$("#prewiew-section").show();
	},0);
}

/**
 * Funkce, která se spouští po potvrzení URL adresy
 * Nastavuje adresu pro skrytý náhled obrázku
 */
function pictureSelected(event)
{
	event.preventDefault();
	$("#preview-img-hidden").attr("src", $("#url-input").val());
	//Třetí fieldset je zobrazen, pokud je načten obrázek úspěšně (viz funkce $(function(){}))
}