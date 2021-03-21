var parameter;
var navOffset;
var tosOffset;
var ppOffset;
var cookiesOffset;
var windowHeight;

//vše, co se děje po načtení stránky
$(function() {

	parameter = location.search.substring(1).split("&");

	navOffset = $('nav').offset().top;
	tosOffset = $("#tos").offset().top;
	ppOffset = $("#pp").offset().top;
	cookiesOffset = $("#cookies").offset().top;
	windowHeight = $(window).height();

	$('#tos-button').addClass('selected');

	//zonrazení části stránky podle parametru předaného v url adrese
	if (parameter == 'tos') showToS();
	else if (parameter == 'pp') showPP();
	else if (parameter == 'cookies') showCookies();

	//event listenery tlačítek
	$("#tos-button").click(function(){showToS()})
	$("#pp-button").click(function(){showPP()})
	$("#cookies-button").click(function(){showCookies()})

	//event listener scrollování
	$(window).scroll(function() 
	{  
		var top = $(window).scrollTop();
		if(top >= navOffset) 
		{
			$("nav").addClass("sticky");
			$("main .content").css("padding-top", navOffset);
		}
		else
		{
			$("nav").removeClass("sticky");
			$("main .content").css("padding-top", 0);
		}

		if ((cookiesOffset - top) < windowHeight/2) {
			$('#tos-button, #pp-button').removeClass('selected');
			$('#cookies-button').addClass('selected');
		}
		else if ((ppOffset - top) < windowHeight/2) {
			$('#tos-button, #cookies-button').removeClass('selected');
			$('#pp-button').addClass('selected');
		}
		else if ((tosOffset - top) < windowHeight/2) {
			$('#pp-button, #cookies-button').removeClass('selected');
			$('#tos-button').addClass('selected');
		}
	});
})

function showToS() {
	$(window).scrollTop(tosOffset - navOffset);
	$('#pp-button, #cookies-button').removeClass('selected');
	$('#tos-button').addClass('selected');
}

function showPP() {
	$(window).scrollTop(ppOffset - navOffset);
	$('#tos-button, #cookies-button').removeClass('selected');
	$('#pp-button').addClass('selected');
}

function showCookies() {
	$(window).scrollTop(cookiesOffset - navOffset);
	$('#tos-button, #pp-button').removeClass('selected');
	$('#cookies-button').addClass('selected');
}