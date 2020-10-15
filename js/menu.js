var smallTablet = 672;
var tablet = 768;

//vše, co se děje po načtení stránky
$(function() {
	//skrytí částí postranního panelu, aby se při prvním otevření zobrazila jen jedna
	$("#aside-nav").hide();
	$("#aside-settings").hide();
	
	//event listenery tlačítek na manipulaci s postranním panelem
	$("#show-full-panel-button").click(function(){showFullPanel()});
	$("#show-aside-login-info-button").click(function(){showAdditionalPanel('aside-login-info')})
	$("#show-aside-nav-button").click(function(){showAdditionalPanel('aside-nav')})
	$("#show-aside-settings-button").click(function(){showAdditionalPanel('aside-settings')})
	$("#close-panel-button").click(function(){closePanel()});

	//skryje tlačítko na změnu zobrazení složek, pokud se uživatel nenachází na stránce se složkami
	if($("body").attr("id")!="menu")
		$("#change-folders-layout-button").hide();

	checkHeader();
});

//vše, co se děje při změně velikosti okna
$(window).resize(function(){
	checkHeader();
	resizeAsidePanel();
})

//funkce nastavující padding mainu podle velikosti okna (různá zobrazení pro mobily a desktopy)
function checkHeader() {
	if ($(window).width() <= smallTablet)
		$("main, aside").css("padding-top", $("header").outerHeight());
	else 
		$("main, aside").css("padding-top", 0);
}

//funkce měnící velikosti postranního panelu v závislosti na velikosti okna
function resizeAsidePanel() {
	if ($(window).width() < smallTablet) {
		$("main").css("margin-left", "0");
		$("#aside-additional-panel").removeClass("show");
	}
	if (($(window).width() >= smallTablet) && ($("aside").hasClass("show"))) {
		$("#aside-additional-panel").addClass("show");
	}
	if ($("#aside-additional-panel").hasClass("show"))
	{
		$("main.menu").css("margin-left", "304px");
	}
	else if ((!$("#aside-additional-panel").hasClass("show")) && ($(window).width() >= smallTablet)) {
		$("main.menu").css("margin-left", "64px");
	}
}

//funkce otevírající celý postranní panel (pro mobily)
function showFullPanel() {
	$("aside").addClass("show");
	$("body").css("overflow-y","hidden");
	$(".btn.cross").addClass("show");
} 

//funkce otevírající dodatečný postranní panel (pro desktop)
function showAdditionalPanel(spec) {
	if (!$("aside").hasClass("show")) {
		$("aside").addClass("show");
		$("#aside-additional-panel").addClass("show");
		$("main").css("margin-left", "304px");
		$(".btn.cross").addClass("show");
	}
	$("#aside-login-info").hide();
	$("#aside-nav").hide();
	$("#aside-settings").hide();
	$("#" + spec).show();
}

//funkce zavírající postranní panel
function closePanel() {
	if ($("#aside-additional-panel").hasClass("show")) {
		$("main").css("margin-left", "64px");
	}
	else {
		$("main").css("margin-left", "0");
		$("body").css("overflow-y","auto");
	}
	$("#aside-additional-panel").removeClass("show");
	$("aside").removeClass("show");
	$(".btn.cross").removeClass("show");
}



