var currentUserValues = new Array(4);

$(function()
{
    //event listenery tlačítek
    $(".edit-button").click(function(event) {editUser(event)})
    $(".edit-confirm-button").click(function(event) {editUserConfirm(event)})
    $(".edit-cancel-button").click(function(event) {editUserCancel(event)})
    $(".start-mail-button").click(function(event) {startMail(event)})
    $(".delete-user-button").click(function(event) {deleteUser(event)});
})

/**
 * Funkce zahajující změnu dat u uživatele
 * @param {event} event 
 */
function editUser(event)
{
    let $user = $(event.target).closest(".user-data-item");

    //dočasné znemožnění ostatních akcí u všech uživatelů
    $(".user-data-item").not($user).find(".user-action .btn").not(".disabled").addClass("temp");
    $(".user-data-item").not($user).find(".user-action .btn").addClass("disabled");

    //uložení současných hodnot
    for (let i = 0; i <= 3; i++)
    {
        currentUserValues[i] = $user.find(".user-field:eq("+ i +")").val();
    }

    $user.find(".user-action > div > .btn").hide();
    $user.find(".user-edit-buttons").show();            
    $user.find(".user-field").removeAttr("readonly");    //umožnění editace pro <input>
    $user.find(".user-field").removeAttr("disabled");    //umožnění editace pro <select>
}

/**
 * Funkce rušící změnu dat u uživatele
 * @param {event} event 
 */
function editUserCancel(event)
{
    let $user = $(event.target).closest(".user-data-item");

    //opětovné zapnutí ostatních tlačítek akcí
    $(".user-data-item").not($user).find(".user-action .btn.temp").removeClass("disabled");
    $(".user-data-item").not($user).find(".user-action .btn.temp").removeClass("temp");

    //obnova hodnot vstupních polí
    for (let i = 0; i <= 3; i++)
    {
        $user.find(".user-field:eq("+ i +")").val(currentUserValues[i]);
    }

    $user.find(".user-action > div > .btn").show();
    $user.find(".user-edit-buttons").hide();
    $user.find(".user-field").attr("readonly", "");    //znemožnění editace pro <input>
    $user.find(".user-field").attr("disabled", "");    //znemožnění editace pro <select>
}

/**
 * Funkce potvrzující změnu dat u uživatele a ukládající je
 * @param {event} event 
 */
function editUserConfirm(event)
{
    let $user = $(event.target).closest(".user-data-item");
    let userId = $user.attr("data-user-id")

    //uložení nových hodnot
    for (let i = 0; i <= 3; i++)
    {
        currentUserValues[i] = $user.find(".user-field:eq("+ i +")").val();
    }

    //odeslat data na server
    $.post("administrate-action",
        {
            action: 'update user',
            userId: userId,
            addedPics: currentUserValues[0],
            guessedPics: currentUserValues[1],
            karma: currentUserValues[2],
            status: currentUserValues[3],
        },
        function (response)
        {
            if (response["messageType"] === "success")
            {
                //reset DOM
                editUserCancel(event);
            }
            if (response["messageType"] === "error")
            {
                alert(response["message"]);
            }
        }
    );
}

/**
 * Funkce odstraňující uživatele
 * @param {event} event 
 * @returns
 */
function deleteUser(event)
{
    let $user = $(event.target).closest(".user-data-item");
    let userId = $user.attr("data-user-id")
    let userName = $user.attr("data-user-name")

    if (!confirm("Opravdu chceš odstranit uživatele " + userName + "?\nTato akce je nevratná!"))
    {
        return;
    }
    
    $.post('administrate-action',
        {
            action: 'delete user',
            userId: userId
        },
        function(response)
        {
            alert(response["message"]);

            if (response["messageType"] === "success")
            {
                //odebrání uživatele z DOM
                $user.remove();
            }
        }
    );
}