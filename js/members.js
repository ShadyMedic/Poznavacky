//nastavení URL pro AJAX požadavky
var ajaxUrl = window.location.href;
if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
ajaxUrl = ajaxUrl.replace('/manage/members', '/class-update'); //Nahraď neAJAX akci AJAX akcí

$(function()
{
    $(".show-info-button").show();

    //event listenery tlačítek
    $(".kick-user-button").click(function(event) {kickMember(event)})
    $(".kick-user-confirm-button").click(function(event) {kickMemberConfirm(event)})
    $(".kick-user-cancel-button").click(function(event) {kickMemberCancel(event)})
    $("#invite-user-button").click(function() {inviteFormShow()})
    $("#invite-user-confirm-button").click(function() {inviteUser()})
    $("#invite-user-cancel-button").click(function() {inviteFormHide()})
})

/**
 * Funkce zahajující odebrání člena třídy
 * @param {event} event 
 */
function kickMember(event)
{
    let $member = $(event.target).closest(".member.data-item");

    $member.find(".kick-user-button").hide();
    $member.find(".kick-user").show();
} 

/**
 * Funkce rušící odebrání člena třídy
 * @param {event} event 
 */
function kickMemberCancel(event)
{
    let $member = $(event.target).closest(".member.data-item");

    $member.find(".kick-user-button").show();
    $member.find(".kick-user").hide();
} 

/**
 * Funkce odesílající požadavek na odebrání člena třídy
 * @param {event} event 
 */
function kickMemberConfirm(event)
{
    let $member = $(event.target).closest(".member.data-item");
    let memberId = $member.attr("data-member-id");

    $.post(ajaxUrl,
        {
            action: 'kick member',
            memberId: memberId
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                    else if (messageType === "success")
                    {
                        //odebrání uživatele z DOM
                        $member.remove();
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce zobrazující formulář na pozvání uživatele do třídy
 */
function inviteFormShow()
{
    $("#invite-user-button").hide();
    $("#invite-user-form").show();
    $("#invite-user-form")[0].scrollIntoView({ 
        behavior: 'smooth',
        block: "start" 
    });
    $("#invite-user-name").focus();
}

/** 
 * Funkce skrývající formulář na pozvání uživatele do třídy
 */
function inviteFormHide()
{
    $("#invite-user-name").val("");
    $("#invite-user-button").show();
    $("#invite-user-form").hide();
}

/**
 * Funkce odesílající požadavek na pozvání uživatele do třídy
 */
function inviteUser()
{
    let userName = $("#invite-user-name").val();

    $.post(ajaxUrl,
        {
      action: 'invite user',
          userName: userName
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        inviteFormHide();
                        
                        newMessage(message,"success");
                    }
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                }
            );
        },
        "json"
    );
}