var smallTablet = 672;

$(function()
{
    //při zobrazení changelogu se zobrazí i overlay a skryje se scrollbar
    if ($("#changelog").length > 0)
    {
        $("#overlay").addClass("show");
        $("body").css("overflow", "hidden");
    }

    //event listenery tlačítek
    $(".logout-button").click(function() {logout()});
    $("#close-changelog-button").click(function() {closeChangelog()})
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
