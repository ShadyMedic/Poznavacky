var smallTablet = 672;
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
	$("#add-natural-select .custom-options .custom-option").click(function() {setTimeout(function() {naturalSelected()}), 0}); //nastaven setTimeout s intervalem 0 na změnu pořadí volaných funkcí (tato se nyní správně volá později než funkce spravující custom select box ze souboru generic.js)

	//Chyba při načítání obrázku
	$("#preview-img-hidden").on("error", function()
	{
		$("#preview-img").attr("src", "images/imagePreview.png");
		$("#submit-fieldset").hide();
	});
	
	//Obrázek načten úspěšně
	$("#preview-img-hidden").on("load", function()
	{
		$("#preview-img").attr("src", $("#preview-img-hidden").attr("src"));
	});

	resizeMainImg();
})

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
	resizeMainImg();
})

//funkce nastavující výšku #main-img tak, aby byla shodná s jeho šířkou
function resizeMainImg(){
	$("#add-pictures-form-wrapper #preview-img").css("height", $("#add-pictures-form-wrapper #preview-img").outerWidth());
	if ($(window).width() >= smallTablet)
		$(".preview-buttons-fieldset").css("height", $("#add-pictures-form-wrapper #preview-img").height());
	else 
		$(".preview-buttons-fieldset").css("height", "auto");
}

/**
 * Funkce, která se spouští po výberu přírodniny a nastavující název té vybrané
 * Také zobrazuje druhý fieldset s <input>em pro URL obrázku
 */
function naturalSelected()
{
	let selectedNatural = "";
	var arr = $("#add-natural-select .custom-options .selected").text();
	for (var i = arr.length - 1; arr[i] != '('; i--){}
	for (var j = 0; j < i - 1; j++){selectedNatural += arr[j];}
	$("#duck-link").attr("href", "https://duckduckgo.com/?q=" + selectedNatural + "&iax=images&ia=images");
	$("#google-link").attr("href", "https://www.google.com/search?q=" + selectedNatural + "&tbm=isch");
	$("#natural-name-hidden").val(selectedNatural);
	$("#prewiew-buttons-fieldset").show();
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