$(function()
{
    //event listenery tlačítek
    $(".accept-invitation-button").click(function (event) {answerInvitation(event, true)})
    $(".reject-invitation-button").click(function (event) {answerInvitation(event, false)})
})

/**
 * Funkce odesílající odpověď na pozvánku
 * @param {event} event
 * @param {string} answer Odpověď na pozvánku (accept/reject)
 */
function answerInvitation(event, answer)
{
    let $invitation = $(event.target).closest(".invitation-wrapper");

    let className = $invitation.find(".class.name").text();
    let classUrl = $invitation.attr('data-class-url');
    let classGroupsCount = $invitation.find(".class.tests-count").text();
    console.log(className);
    let ajaxUrl = "menu/" + classUrl + "/invitation/" + ((answer) ? "accept" : "reject");

    $.post(ajaxUrl, {},
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        //chyba při zpracování požadavku (zřejmě neplatný formát kódu)
                        newMessage(message, "error");
                    }
                    else if (messageType === "success")
                    {
                        if (answer)
                        {
                            //přidání nové třídy na konec seznamu neveřejných tříd
                            let $classTemplate = $('#class-template').html();
                            $classTemplate = $classTemplate.replace(/{name}/g, className);
                            $classTemplate = $classTemplate.replace(/{url}/g, classUrl);
                            $classTemplate = $classTemplate.replace(/{groups}/g, classGroupsCount);
                            $classTemplate = $classTemplate.replace(/btn class data-item/g, 'btn class closed-class data-item');
                            $($classTemplate).insertAfter('ul > .closed-class:last');

                            //nastavení event handlerů
                            $(".leave-link").click(function(event) {leaveClass(event)})
                            $(".class.data-item").click(function(event) {redirectToClass(event)})
                        }

                        $invitation.remove();

                        newMessage(message, "success");
                    }
                }
            );
        },
        "json"
    );
}
