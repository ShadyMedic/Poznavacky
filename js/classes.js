var currentClassValues = new Array(2);

$(function()
{   
    //event listenery tlačítek
    $(".edit-class-button").click(function(event) {editClass(event)})
    $(".edit-class-confirm-button").click(function(event) {editClassConfirm(event)})
    $(".edit-class-cancel-button").click(function(event) {editClassCancel(event)})
    $(".change-class-owner-button").click(function(event) {changeClassOwner(event)})
    $(".change-class-owner-confirm-button").click(function(event) {changeClassOwnerConfirm(event)})
    $(".change-class-owner-cancel-button").click(function(event) {changeClassOwnerCancel(event)})
    $(".class-redirect-button").click(function(event) {classRedirect(event)})
    $(".delete-class-button").click(function(event) {deleteClass(event)})

    $(".class-owner-name").change(function(event) {classOwnerChanged(event, 0)})
    $(".class-owner-id").change(function(event) {classOwnerChanged(event, 1)})
    $(".class-status").change(function(event) {classStatusChanged(event)})
  
})

/**
 * Funkce přesměrovávající admina do správy konkrétní třídy
 * @param {event} event 
 */
function classRedirect(event) 
{
    let classUrl = $(event.target).closest("class-data-item").attr("data-class-url") 

    window.location.href='menu/'+ classUrl + '/manage';
}


/**
 * Funkce zahajující úpravu třídy
 * @param {event} event 
 */
function editClass(event)
{
    let $class = $(event.target).closest(".class-data-item")

    //dočasné znemožnění ostatních akcí u všech tříd
    $(".class-data-item").not($class).find(".class-action .btn").not(".disabled").addClass("temp");
    $(".class-data-item").not($class).find(".class-action .btn").addClass("disabled");

    //uložení současných hodnot
    for (let i = 0; i <= 1; i++)
    {
        currentClassValues[i] = $class.find(".class-field:eq("+ i +")").val();
    }

    $class.find(".class-action > .btn").hide();
    $class.find(".class-edit-buttons").show();
    $class.find(".class-field").removeAttr("disabled");        //umožnění editace pro <select>
    classStatusChanged(event);        //umožnění nastavení kódu třídy, pokud je současný stav nastaven na "private" a kód tak má smysl
}

/**
 * Funkce ošeřtřující změnu statutu třídy
 * @param {event} event 
 */
function classStatusChanged(event)
{
    let $class = $(event.target).closest(".class-data-item")
    let newStatus = $class.find(".class-status").val();

    if (newStatus !== "private")
    {
        //kód nemá smysl --> vymazat jej
        $class.find(".class-code").val("");
        $class.find(".class-code").attr("readonly", "");
    }
    else
    {
        //je potřeba nastavit kód --> umožnit editaci
        if (currentClassValues[1] === "")
        {
            $class.find(".class-code").val("0000");
        }
        else
        {
            $class.find(".class-code").val(currentClassValues[1]);
        }

        $class.find(".class-code").removeAttr("readonly");
    }
}

/**
 * Funkce odesílající požadavek na změnu vlastností třídy
 * @param {event} event 
 */
function editClassConfirm(event)
{
    
    let $class = $(event.target).closest(".class-data-item");
    let classId = $class.attr("data-class-id");

    //uložení nových hodnot
    for (let i = 0; i <= 1; i++)
    {
        currentClassValues[i] = $class.find(".class-field:eq("+ i +")").val();
    }

    //odeslat data na server
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
                //reset DOM
                editClassCancel(event);
            }
            else
            {    
                alert(response["messageType"]);
            }
        }
    );
}

/**
 * Funkce rušící úpravu třídy
 * @param {event} event 
 */
function editClassCancel(event)
{
    let $class = $(event.target).closest(".class-data-item");

    //opětovné zapnutí ostatních tlačítek akcí
    $(".class-data-item").not($class).find(".class-action .btn.temp").removeClass("disabled");
    $(".class-data-item").not($class).find(".class-action .btn.temp").removeClass("temp");

    //obnova hodnot vstupních polí
    for (let i = 0; i <= 1; i++)
    {
        $class.find(".class-field:eq("+ i +")").val(currentClassValues[i]);
    }

    $class.find(".class-action > .btn").show();
    $class.find(".class-edit-buttons").hide();
    $class.find(".class-field").attr("readonly", "");       //znemožnění editace pro <input>
    $class.find(".class-field").attr("disabled", "");       //znemožnění editace pro <select>
}

var currentClassOwnerValues = new Array(2);
var changedIdentifier;

/**
 * Funkce zahajující změnu vlastníka třídy
 * @param {event} event 
 */
function changeClassOwner(event)
{
    let $class = $(event.target).closest(".class-data-item")

    //dočasné znemožnění ostatních akcí u všech tříd
    $(".class-data-item").not($class).find(".class-action .btn").not(".disabled").addClass("temp");
    $(".class-data-item").not($class).find(".class-action .btn").addClass("disabled");

    //uložení současných hodnot
    for (let i = 0; i <= 1; i++)
    {
        currentClassOwnerValues[i] = $class.find(".class-owner-table .class-owner-field:eq("+ i +")").val();
    }

    $class.find(".class-action > .btn").hide();
    $class.find(".class-change-class-owner-buttons").show();
    $class.find(".class-owner-field").removeAttr("readonly");        //umožnění editace pro <input>
}

/**
 * Funkce aktualizující pole při změně některého z identifikátorů vlastníka třídy
 * @param {event} event 
 * @param {int} identifier 0 při změně jména vlastníka třídy, 1 při změně id vlastníka třídy
 */
function classOwnerChanged(event, identifier)
{
    let $class = $(event.target).closest(".class-data-item")

    if (identifier == 0)
    {
        changedIdentifier = "name";
    }
    else if (identifier == 1)
    {
        changedIdentifier = "id";
    }

    //umožnit změnu ID - jméno je stejné jako na začátku
    if ($class.find(".class-owner-field:eq("+ identifier + ")").val() === currentClassOwnerValues[identifier])
    {
        $class.find(".class-owner-field:eq("+ identifier + ")").removeAttr("readonly");
    }
    //znemožnit změnu ID - jméno se změnilo
    else
    {
        $class.find(".class-owner-field:eq("+ identifier + ")").attr("readonly", "");
    }
}

/**
 * Funkce rušící změnu vlastníka třídy
 * @param {event} event 
 */
function changeClassOwnerCancel(event)
{
    let $class = $(event.target).closest(".class-data-item")

    //opětovné zapnutí ostatních tlačítek akcí
    $(".class-data-item").not($class).find(".class-action .btn.temp").removeClass("disabled");
    $(".class-data-item").not($class).find(".class-action .btn.temp").removeClass("temp");

    //Obnova hodnot vstupních polí
    for (let i = 0; i <= 1; i++)
    {
        $class.find(".class-owner-table .class-owner-field:eq("+ i +")").val(currentClassOwnerValues[i]);
    }

    $class.find(".class-action > .btn").show();
    $class.find(".class-change-class-owner-buttons").hide();
    $class.find(".class-field").attr("readonly", "");       //znemožnění editace pro <input>
}

/**
 * Funkce odesílající požadavek na změnu vlastníka třídy
 * @param {event} event 
 */
function changeClassOwnerConfirm(event)
{
    let $class = $(event.target).closest(".class-data-item");
    let $classOwnerTable = $class.find(".class-owner-table");
    let classId = $class.attr("data-class-id");
    let newName = $classOwnerTable.find(".class-owner-field:eq(0)").val();
    let newId = $classOwnerTable.find(".class-owner-field:eq(1)").val();

    //odeslat data na server
    $.post("administrate-action",
        {
            action: 'change class admin', //TODO předělat na change class admin, aby se nikde nic nerozbilo
            classId: classId,
            changedIdentifier: changedIdentifier,
            adminId: newId,
            adminName: newName
        },
        function (response)
        {
            if (response["messageType"] === "success")
            {
                //aktualizace údajů o správci třídy v DOM
                let newName = response["newName"];
                let newId = response["newId"];
                let newEmail = response["newEmail"];
                let newKarma = response["newKarma"];
                let newStatus = response["newStatus"];

                currentClassOwnerValues[0] = newName;
                currentClassOwnerValues[1] = newId;
                $classOwnerTable.find(".class-owner-data:eq(0)").text(newEmail);
                $classOwnerTable.find(".class-owner-data:eq(1)").text(newKarma);
                $classOwnerTable.find(".class-owner-data:eq(2)").text(newStatus);

                //vypnutí nebo zapnutí tlačítka pro kontaktování vlastníka třídy a změna adresáta předávaného jako parametr
                if (newEmail === null)
                {
                    //vypnutí tlačítka, pokud nový vlastník třídy nemá email
                    $class.find(".class-admin-mail-btn").addClass("disabled");
                    $class.find(".class-admin-mail-btn").removeClass("temp");    //aby nebyla třída "disabled" odebrána při zavolání metody editClassOwnerCancel() níže
                }
                else
                {
                    //zapnutí tlačítka, pokud nový vlastník třídy má email
                    $class.find(".class-admin-mail-btn").removeClass("disabled");
                    $class.find(".class-admin-mail-btn").attr("title", "Kontaktovat správce");
                }

                //reset DOM
                changeClassOwnerCancel(event);
            }
            if (response["messageType"] === "error")
            {
                alert(response["message"]);
            }
        }
    );
}

/**
 * Funkce odesílající požadavek na smazání třídy
 * @param {event} event 
 * @returns 
 */
function deleteClass(event)
{

    let $class = $(event.target).closest(".class-data-item");
    let classId = $class.attr("data-class-id");
    let className = $class.attr("data-class-name");

    if (!confirm("Opravdu chcete odstranit třídu " + className + "?\nTato akce je nevratná!"))
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
            alert(response["message"]);

            if (response["messageType"] === "success")
            {
                //odebrání třídy z DOM
                $class.remove();
            }
        }
    );
}