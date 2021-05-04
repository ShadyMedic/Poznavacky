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
    let $invitation = $(event.target).closest(".invitation");

    let className = $invitation.find(".class-name").text();
    let classUrl = $invitation.attr('data-class-url');
    let classGroupsCount = $invitation.find(".class-tests-count").text();
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
                            console.log(className);
                            //přidání nové třídy na konec seznamu
                            let classDomItem = $('#class-item-template').html();
                            classDomItem = classDomItem.replace(/{name}/g, className);
                            classDomItem = classDomItem.replace(/{url}/g, classUrl);
                            classDomItem = classDomItem.replace(/{groups}/g, classGroupsCount);
                            $(classDomItem).insertAfter('ul > .btn:last');

                            //nastavení event handleru pro opuštění nových tříd
                            $(".leave-link").click(function(event) {leaveClass(event)})
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
