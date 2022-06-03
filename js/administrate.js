$(function()
{   
    setActiveTab();

    //event listenery tlačítek
    $(".start-mail-button:not(.disabled)").click(function(event) {startMail(event)})
})

function setActiveTab()
{
    let activeTab = $("body").attr("id");
    $("nav #" + activeTab + "-link").addClass("active-tab");
}

function startMail(event)
{
    if ($(event.target).closest("body").attr("id")=="name-change-requests")
    {
        mail = $(event.target).closest(".name-change-request-data-item").attr("data-request-email");
    }
    else if ($(event.target).closest("body").attr("id")=="classes")
    {
        mail = $(event.target).closest(".class-data-item").attr("data-class-owner-mail");
    }
    window.location.href = "/administrate/mailsender?to=" + mail;
}
