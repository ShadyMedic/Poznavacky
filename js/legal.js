var navOffset;
var tosOffset;
var ppOffset;
var cookiesOffset;

//vše, co se děje po načtení stránky
$(function() {

	navOffset = $('nav').offset().top;
	var tosOffset = $("#tos").offset().top;
	var ppOffset = $("#pp").offset().top;
	var cookiesOffset = $("#cookies").offset().top;

	//event listenery tlačítek
	$("#tos-button").click(function()
	{
		$(window).scrollTop(tosOffset - navOffset);
	})

	$("#pp-button").click(function()
	{
		$(window).scrollTop(ppOffset - navOffset);
	})

	$("#cookies-button").click(function()
	{
		$(window).scrollTop(cookiesOffset - navOffset);
	})

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
	});
})