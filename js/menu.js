var smallTablet = 672;
var tablet = 768;

$(function() { //až po načtení stránky

	$(".rows").hide();
	checkHeader();
});

$(window).resize(function(){
	checkHeader();
})

//funkce nastavující padding mainu podle velikosti okna (různá zobrazení pro mobily a desktopy)
function checkHeader() {
	if ($(window).width() <= smallTablet)
		$("main").css("padding-top", $("header").outerHeight());
	else 
		$("main").css("padding-top", 0);
}

$(function(){
	/*
	checkTilesLayout();
	$(window).resize(function() {
		checkTilesLayout();
	});*/

	//changeMenuLayout();
	$("#change-menu-layout-button").click(function(){
		changeMenuLayout();
	})
});

//změna uspořádání dlaždic, pokud jich je příliš málo (neimplentována)
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

//přepnutí zobrazení menu
function changeMenuLayout() {
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

//převod rem na pixely
function remToPixels(rem) {    
    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
}