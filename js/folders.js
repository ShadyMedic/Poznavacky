//vše, co se děje po načtení stránky
$(function() {
	//skrytí řádkového zobrazení (v budoucnu bude řešeno v návaznosti na to, co má uživatel trvale nastaveno)
	$(".rows").hide();

	//event listener tlačítka na změnu zobrazení složek
	$("#change-folders-layout-button").click(function(){changeFoldersLayout()})

	changeFoldersLayout();

	//případná implementace
	/*checkTilesLayout();*/
});

//vše, co se děje při změně velikosti okna
$(window).resize(function(){
	//případná implementace
	/*checkTilesLayout();*/
})

//funkce přepínající zobrazení složek
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

//funkce měnící uspořádání dlaždic, pokud jich je příliš málo (neimplentováno)
//možná by stačilo jenom nemít margin dlaždic nastavený na auto
function checkTilesLayout() {
	let gapSize = ($(window).width() >= 576)? remToPixels(2) : remToPixels(1);
	let numberOfTiles = [];
	numberOfTiles = $(".folders > ul > button").length;
	let allTilesWidth = numberOfTiles*remToPixels(16) + (numberOfTiles-1)*gapSize;
	let containerWidth = $(".menu > .wrapper").width();
	if (allTilesWidth < containerWidth)
		$(".folders > ul").addClass("not-enough-tiles");
	else
		$(".folders > ul").removeClass("not-enough-tiles");
}

//funkce převádějící rem na pixely
function remToPixels(rem) {    
    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
}