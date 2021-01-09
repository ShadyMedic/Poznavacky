//vše, co se děje po načtení stránky
$(function() {

	//event listenery tlačítek
	$("#change-folders-layout-button").click(function(){changeFoldersLayout()})
	$("#request-class-button").click(function() {showNewClassForm()})
	$("#request-class-cancel-button").click(function() {hideNewClassForm()})
	$(".display-buttons-button").click(function(){displayButtons(this)})

	//event listener kliknutí myši
	$(document).mouseup(function(e){hideButtons(e)});

});

//vše, co se děje při změně velikosti okna
$(window).resize(function(){
})

//zobrazí formulář na žádost o vytvoření nové třídy
function showNewClassForm() {
	$("#request-class-button").hide();
	$("#request-class-wrapper > span").hide();
	$("#request-class-form").show();
	$("#new-class-form-name").focus();
}
//skryje formulář na žádost o vytvoření nové třídy
function hideNewClassForm() {
	$("#request-class-button").show();
	$("#request-class-wrapper > span").show();
	$("#request-class-form").hide();
	$("#request-class-form .text-field").val("");
}

//zobrazí tlačítka "Přidat obrázky", "Učit se" a "Vyzkoušet se"
function displayButtons(button) {
	if (!$(button).hasClass("show")) {
		$(button).find(".col2, .col3").hide();
		$(button).find(".buttons").addClass("show");
		$(button).find("li").addClass("show");
	}
}

//skryje tlačítka "Přidat obrázky", "Učit se" a "Vyzkoušet se"
function hideButtons(e) {
	$(".display-buttons-button").each(function(){
		if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
			if ($(this).find("li").hasClass("show")) {
				$(this).find(".col2, .col3").show();
				$(this).find(".buttons").removeClass("show");
				$(this).find("li").removeClass("show");
			}
		}
	})
}

//funkce přepínající zobrazení složek
//neimplementováno
function changeFoldersLayout() {
	if ($(".folders ul").hasClass("tiles")) {
		$(".folders ul").removeClass("tiles");
		$(".folders ul").addClass("rows");
		$(".rows").show();
		$(".tiles").hide();
	}
	else if ($(".folders ul").hasClass("rows")) {
		$(".folders ul").removeClass("rows");
		$(".folders ul").addClass("tiles");
		$(".tiles").show();
		$(".rows").hide();
	}
}

//funkce převádějící rem na pixely
function remToPixels(rem) {    
    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
}