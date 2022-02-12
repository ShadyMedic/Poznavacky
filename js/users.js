$(function() { $("#tab1-link").addClass("active-tab"); }); //Nabarvi zvolenou záložku

var currentUserValues = new Array(4);
function editUser(event)
{
    //Dočasné znemožnění ostatních akcí u všech uživatelů
    $(".user-action:not(.grayscale)").addClass("grayscale-temp-user");
    $(".user-action").addClass("grayscale");
    $(".user-action").attr("disabled", "");

    //Získat <tr> element upravované řádky
    let row = $(event.target.parentNode.parentNode.parentNode);
    row.attr("id", "editable-user-row");

    //Uložení současných hodnot
    for (let i = 0; i <= 3; i++)
    {
        currentUserValues[i] = $("#editable-user-row .user-field:eq("+ i +")").val();
    }

    $("#editable-user-row .user-action").hide();                    //Skrytí ostatních tlačítek akcí
    $("#editable-user-row .user-edit-buttons").show();                //Zobrazení tlačítek pro uložení nebo zrušení editace
    $("#editable-user-row .user-field").addClass("editable-field");    //Obarvení políček (//TODO)
    $("#editable-user-row .user-field").removeAttr("readonly");    //Umožnění editace (pro <input>)
    $("#editable-user-row .user-field").removeAttr("disabled");    //Umožnění editace (pro <select>)
}
function cancelUserEdit()
{
    //Opětovné zapnutí ostatních tlačítek akcí
    $(".grayscale-temp-user").removeAttr("disabled");
    $(".grayscale-temp-user").removeClass("grayscale grayscale-temp-user");

    //Obnova hodnot vstupních polí
    for (let i = 0; i <= 3; i++)
    {
        $("#editable-user-row .user-field:eq("+ i +")").val(currentUserValues[i]);
    }

    $("#editable-user-row .user-action").show();                        //Znovuzobrazení ostatních tlačítek akcí
    $("#editable-user-row .user-edit-buttons").hide();                    //Skrytí tlačítek pro uložení nebo zrušení editace
    $("#editable-user-row .user-field").removeClass("editable-field");    //Odbarvení políček
    $("#editable-user-row input.user-field").attr("readonly", "");        //Znemožnění editace (pro <input>)
    $("#editable-user-row select.user-field").attr("disabled", "");    //Znemožnění editace (pro <select>)

    $("#editable-user-row").removeAttr("id");
}
function confirmUserEdit(userId)
{
    //Uložení nových hodnot
    for (let i = 0; i <= 3; i++)
    {
        currentUserValues[i] = $("#editable-user-row .user-field:eq("+ i +")").val();
    }

    //Odeslat data na server
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
                //Reset DOM
                cancelUserEdit();
                //TODO - zobraz (možná) nějak úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
                //alert(response["message"]);
            }
            if (response["messageType"] === "error")
            {
                //TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
                alert(response["message"]);
            }
        }
    );
}
function deleteUser(userId, event)
{
    if (!confirm("Opravdu chcete odstranit tohoto uživatele?\nTato akce je nevratná!"))
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
            //TODO - zobraz nějak chybovou nebo úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
            alert(response["message"]);

            if (response["messageType"] === "success")
            {
                //Odebrání uživatele z DOM
                event.target.parentNode.parentNode.parentNode.remove();
            }
        }
    );
}