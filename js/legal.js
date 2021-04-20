var parameter; //část url za '?'
var navOffset; //vzdálenost navigace od začátku stránky
var tosOffset; //vzdálenost terms of service sekce od začátku stránky
var ppOffset; //vzdálenost privacy policy sekce od začátku stránky
var cookiesOffset; //vzdálenost cookies sekce od začátku stránky
var windowHeight; //výška okna

$(function()
{
    parameter = location.search.substring(1).split("&");

    navOffset = $('nav').offset().top;
    tosOffset = $("#tos").offset().top;
    ppOffset = $("#pp").offset().top;
    cookiesOffset = $("#cookies").offset().top;
    windowHeight = $(window).height();

    $('#tos-button').addClass('selected');

    //zobrazení části stránky podle parametru předaného v url adrese
    if (parameter == 'tos') showToS();
    else if (parameter == 'pp') showPP();
    else if (parameter == 'cookies') showCookies();

    //event listenery tlačítek
    $("#tos-button").click(function(){showToS()})
    $("#pp-button").click(function(){showPP()})
    $("#cookies-button").click(function(){showCookies()})

    //event listener scrollování
    $(window).scroll(function() {scrollCheck()})
})

/**
 * Funkce kontrolující, kolik bylo odscrollováno, a nastavující příslušné třídy
 */
function scrollCheck()
{
    //odscrollovaná vzdálenost od začátku stránky
    let top = $(window).scrollTop();

    if (top >= navOffset) 
    {
        $("nav").addClass("sticky");
        $("main .content").css("padding-top", navOffset);
    }
    else
    {
        $("nav").removeClass("sticky");
        $("main .content").css("padding-top", 0);
    }

    //většinu stránky zabírá cookies sekce
    if ((cookiesOffset - top) < windowHeight/2)
    {
        $('#tos-button, #pp-button').removeClass('selected');
        $('#cookies-button').addClass('selected');
    }
    //většinu stránky zabírá privacy policy sekce
    else if ((ppOffset - top) < windowHeight/2)
    {
        $('#tos-button, #cookies-button').removeClass('selected');
        $('#pp-button').addClass('selected');
    }
    //většinu stránky zabírá terms of service sekce
    else if ((tosOffset - top) < windowHeight/2)
    {
        $('#pp-button, #cookies-button').removeClass('selected');
        $('#tos-button').addClass('selected');
    }
}

/**
 * Funkce zobrazující terms of service sekci
 */
function showToS()
{
    $(window).scrollTop(tosOffset - navOffset);
    $('#pp-button, #cookies-button').removeClass('selected');
    $('#tos-button').addClass('selected');
}

/**
 * Funkce zobrazující privacy policy sekci
 */
function showPP()
{
    $(window).scrollTop(ppOffset - navOffset);
    $('#tos-button, #cookies-button').removeClass('selected');
    $('#pp-button').addClass('selected');
}

/**
 * Funkce zobrazující cookies sekci
 */
function showCookies()
{
    $(window).scrollTop(cookiesOffset - navOffset);
    $('#tos-button, #pp-button').removeClass('selected');
    $('#cookies-button').addClass('selected');
}