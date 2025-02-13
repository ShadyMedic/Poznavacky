var smallTablet = 672;
var currentTheme = $('body').hasClass('dark') ? 'dark' : 'light';

$(function()
{
    if (currentTheme == 'dark') {
        $("#account-settings #dark-theme-checkbox").prop("checked", "true");
    }

    //při zobrazení changelogu se zobrazí i overlay a skryje se scrollbar
    if ($("#changelog").length > 0)
    {
        $("#overlay").addClass("show");
        $("body").css("overflow", "hidden");
    }

    //event listenery tlačítek
    $(".logout-button").click(function() {logout()});
    $("#close-changelog-button").click(function() {closeChangelog()})
    $(".hide-info-button").click(function() {hideInfo()});
    $(".show-info-button").click(function() {showInfo()});
});

/**
 * Funkce odesílající požadavek na odhlášení uživatele
 */
function logout()
{
    $.get('menu/logout', function (response, status) { ajaxCallback(response, status, function() {}); }, "json");
}

/**
 * Funkce zavírající changelog
 */
function closeChangelog()
{
    $("#changelog").remove();
    $("#overlay").removeClass("show");
    $("body").css("overflow", "auto");
}

/**
 * Funkce zobrazující sekci s nápovědou
 */
function showInfo()
{
    $(".info-section").slideDown();
    $(".show-info-button").hide();
    $(".hide-info-button").show();
}

/**
 * Funkce skrývající sekci s nápovědou
 */
function hideInfo()
{
    $(".info-section").slideUp();
    $(".hide-info-button").hide();
    $(".show-info-button").show();
}
