$(function() { $("#tab2-link").addClass("active-tab"); }); //Nabarvi zvolenou záložku

var currentClassValues = new Array(2);
function editClass(event)
{
    //Dočasné znemožnění ostatních akcí u všech tříd
    $(".class-action:not(.grayscale)").addClass("grayscale-temp-class");
    $(".class-action").addClass("grayscale");
    $(".class-action").attr("disabled", "");

    //Získat <tr> element upravované řádky
    let row = $(event.target.parentNode.parentNode.parentNode);
    row.attr("id", "editable-class-row");

    //Uložení současných hodnot
    for (let i = 0; i <= 1; i++)
    {
        currentClassValues[i] = $("#editable-class-row .class-field:eq("+ i +")").val();
    }

    $("#editable-class-row .class-action").hide();                        //Skrytí ostatních tlačítek akcí
    $("#editable-class-row .class-edit-buttons").show();                //Zobrazení tlačítek pro uložení nebo zrušení editace
    $("#editable-class-row .class-field").addClass("editable-field");    //Obarvení políček (//TODO)
    $("#editable-class-row .class-field").removeAttr("disabled");        //Umožnění editace (pro <select>)
    classStatusEdited();        //Umožnění nastavení kódu třídy, pokud je současný stav nastaven na "private" a kód tak má smysl
}
function classStatusEdited()
{
    let newStatus = $("#editable-class-row select.class-field").val();
    if (newStatus !== "private")
    {
        //Kód nemá smysl --> vymazat jej
        $("#editable-class-row input.class-field").val("");
        $("#editable-class-row input.class-field").attr("readonly", "");
    }
    else
    {
        //Je potřeba nastavit kód --> umožnit editaci
        if (currentClassValues[1] === "")
        {
            $("#editable-class-row input.class-field").val("0000");
        }
        else
        {
            $("#editable-class-row input.class-field").val(currentClassValues[1]);
        }

        $("#editable-class-row input.class-field").removeAttr("readonly");
    }
}
function cancelClassEdit()
{
    //Opětovné zapnutí ostatních tlačítek akcí
    $(".grayscale-temp-class").removeAttr("disabled");
    $(".grayscale-temp-class").removeClass("grayscale grayscale-temp-class");

    //Obnova hodnot vstupních polí
    for (let i = 0; i <= 1; i++)
    {
        $("#editable-class-row .class-field:eq("+ i +")").val(currentClassValues[i]);
    }

    $("#editable-class-row .class-action").show();                            //Znovuzobrazení ostatních tlačítek akcí
    $("#editable-class-row .class-edit-buttons").hide();                    //Skrytí tlačítek pro uložení nebo zrušení editace
    $("#editable-class-row .class-field").removeClass("editable-field");    //Odbarvení políček
    $("#editable-class-row input.class-field").attr("readonly", "");        //Znemožnit editaci (pro <input>)
    $("#editable-class-row select.class-field").attr("disabled", "");        //Znemožnit editaci (pro <select>)

    $("#editable-class-row").removeAttr("id");
}
function confirmClassEdit(classId)
{
    //Uložení nových hodnot
    for (let i = 0; i <= 1; i++)
    {
        currentClassValues[i] = $("#editable-class-row .class-field:eq("+ i +")").val();
    }

    //Odeslat data na server
    $.post("administrate-action",
        {
            action: 'update class',
            classId: classId,
            code: currentClassValues[1],
            status: currentClassValues[0]
        },
        function (response)
        {
            if (response["messageType"] === "success")
            {
                //Reset DOM
                cancelClassEdit();
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
var currentClassAdminValues = new Array(2);
var changedIdentifier;
function changeClassAdmin(event)
{
    //Dočasné znemožnění ostatních akcí u všech tříd
    $(".class-action:not(.grayscale)").addClass("grayscale-temp-class");
    $(".class-action").addClass("grayscale");
    $(".class-action").attr("disabled", "");

    //Získat <tr> element upravované řádky
    let row = $(event.target.parentNode.parentNode.parentNode);
    row.attr("id", "editable-class-admin-row");

    //Uložení současných hodnot
    for (let i = 0; i <= 1; i++)
    {
        currentClassAdminValues[i] = $("#editable-class-admin-row .class-admin-table .class-admin-field:eq("+ i +")").val();
    }

    $("#editable-class-admin-row .class-action").hide();                                            //Skrytí ostatních tlačítek akcí
    $("#editable-class-admin-row .class-edit-admin-buttons").show();                                    //Zobrazení tlačítek pro uložení nebo zrušení editace
    $("#editable-class-admin-row .class-admin-table .class-admin-field").addClass("editable-field");    //Obarvení políček (//TODO)
    $("#editable-class-admin-row .class-admin-field").removeAttr("readonly");                        //Umožnění editace
}
function adminNameChanged()
{
    changedIdentifier = "name";
    if ($("#editable-class-admin-row .class-admin-field:eq(0)").val() === currentClassAdminValues[0])
    {
        //Umožnit změnu ID - jméno je stejné jako na začátku
        $("#editable-class-admin-row .class-admin-field:eq(1)").removeAttr("readonly");
    }
    else
    {
        //Znemožnit změnu ID - jméno se změnilo
        $("#editable-class-admin-row .class-admin-field:eq(1)").attr("readonly", "");
    }
}
function adminIdChanged()
{
    changedIdentifier = "id";
    if ($("#editable-class-adminrow .class-admin-field:eq(1)").val() === currentClassAdminValues[1])
    {
        //Umožnit změnu ID - jméno je stejné jako na začátku
        $("#editable-class-admin-row .class-admin-field:eq(0)").removeAttr("readonly");
    }
    else
    {
        //Znemožnit změnu ID - jméno se změnilo
        $("#editable-class-admin-row .class-admin-field:eq(0)").attr("readonly", "");
    }
}
function cancelClassAdminEdit()
{
    //Opětovné zapnutí ostatních tlačítek akcí
    $(".grayscale-temp-class").removeAttr("disabled");
    $(".grayscale-temp-class").removeClass("grayscale grayscale-temp-class");

    //Obnova hodnot vstupních polí
    for (let i = 0; i <= 1; i++)
    {
        $("#editable-class-admin-row .class-admin-table .class-admin-field:eq("+ i +")").val(currentClassAdminValues[i]);
    }

    $("#editable-class-admin-row .class-action").show();                                            //Znovuzobrazení ostatních tlačítek akcí
    $("#editable-class-admin-row .class-edit-admin-buttons").hide();                                    //Skrytí tlačítek pro uložení nebo zrušení editace
    $("#editable-class-admin-row .class-admin-table .class-admin-field").removeClass("editable-field");    //Odbarvení políček
    $("#editable-class-admin-row .class-admin-field").attr("readonly", "");                            //Znemožnit editaci (pro <input>)

    $("#editable-class-admin-row").removeAttr("id");
}
function confirmClassAdminEdit(classId)
{
    let newId = $("#editable-class-admin-row .class-admin-table .class-admin-field:eq(0)").val();
    let newName = $("#editable-class-adminRow .class-admin-table .class-admin-field:eq(1)").val();

    //Odeslat data na server
    $.post("administrate-action",
        {
            action: 'change class admin',
            classId: classId,
            changedIdentifier: changedIdentifier,
            adminId: $("#editable-class-admin-row .class-admin-table .class-admin-field:eq(1)").val(),
            adminName: $("#editable-class-admin-row .class-admin-table .class-admin-field:eq(0)").val()
        },
        function (response)
        {
            if (response["messageType"] === "success")
            {
                //Aktualizace údajů o správci třídy v DOM
                let newName = response["newName"];
                let newId = response["newId"];
                let newEmail = response["newEmail"];
                let newKarma = response["newKarma"];
                let newStatus = response["newStatus"];

                currentClassAdminValues[0] = newName;
                currentClassAdminValues[1] = newId;
                $("#editable-class-admin-row .class-admin-table .class-admin-data:eq(0)").text(newEmail);
                $("#editable-class-admin-row .class-admin-table .class-admin-data:eq(1)").text(newKarma);
                $("#editable-class-admin-row .class-admin-table .class-admin-data:eq(2)").text(newStatus);

                //Vypnutí nebo zapnutí tlačítka pro kontaktování správce třídy a změna adresáta předávaného jako parametr
                if (newEmail === null)
                {
                    //Nový správce nemá e-mail --> vypnout tlačítko
                    $("#editable-class-admin-row .class-admin-mail-btn").attr("disabled", "");
                    $("#editable-class-admin-row .class-admin-mail-btn").addClass("grayscale");
                    $("#editable-class-admin-row .class-admin-mail-btn").removeClass("grayscale-temp-class");    //Aby nebyla třída "grayscale" odebrána při zavolání metody cancelClassAdminEdit() níže
                    $("#editable-class-admin-row .class-admin-mail-btn").removeClass("active-btn");
                    $("#editable-class-admin-row .class-admin-mail-btn").removeAttr("onclick");
                    $("#editable-class-admin-row .class-admin-mail-btn").removeAttr("title");
                }
                else
                {
                    //Zapnutí tlačítka a aktualizace e-mailové adresy adresáta
                    $("#editable-class-admin-row .class-admin-mail-btn").removeAttr("disabled");
                    $("#editable-class-admin-row .class-admin-mail-btn").removeClass("grayscale");
                    $("#editable-class-admin-row .class-admin-mail-btn").addClass("active-btn");
                    $("#editable-class-admin-row .class-admin-mail-btn").attr("onclick", "startMail(\""+ newEmail +"\")");
                    $("#editable-class-admin-row .class-admin-mail-btn").attr("title", "Kontaktovat správce");
                }

                //Reset DOM
                cancelClassAdminEdit();

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
function deleteClass(classId, event)
{
    if (!confirm("Opravdu chcete odstranit tuto třídu?\nTato akce je nevratná!"))
    {
        return;
    }
    $.post('administrate-action',
        {
            action: 'delete class',
            classId: classId
        },
        function(response)
        {
            //TODO - zobraz nějak chybovou nebo úspěchovou hlášku - ideálně ne jako alert() nebo jiný popup
            alert(response["message"]);

            if (response["messageType"] === "success")
            {
                //Odebrání třídy z DOM
                event.target.parentNode.parentNode.parentNode.remove();
            }
        }
    );
}