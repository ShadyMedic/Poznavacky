var deletedTableRow;    //ukládá řádek tabulky poznávaček, který je odstraňován
var ajaxUrl;

$(function()
{
    //nastavení URL pro AJAX požadavky
    ajaxUrl = window.location.href;
    if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //odstranění trailing slashe (pokud je přítomen)
    ajaxUrl = ajaxUrl.replace('/manage/tests', '/class-update'); //nahrazení neAJAX akci AJAX akcí
  
    //eventy listenery tlačítek
    $(".test.action .delete-group-button").click(function(event) {deleteTest(event)})
    $("#new-test-button").click(function() {newTest()})
    $("#new-test-confirm-button").click(function() {newTestConfirm()})
    $("#new-test-cancel-button").click(function() {newTestCancel()})
})

/**
 * Funkce zahajující přidání nové poznávačky
 */
function newTest()
{
    $("#new-test-button").hide();
    $("#new-test").show();
    $("#new-test-name").focus();
    $("#new-test")[0].scrollIntoView({ 
        behavior: 'smooth',
        block: "start" 
    });
}

/**
 * Funkce rušící přidání nové poznávačky
 */
function newTestCancel()
{
    $("#new-test-name").val("");
    $("#new-test").hide();
    $("#new-test-button").show();
}

/**
 * Funkce odesílající požadavek na vytvoření nové poznávačky
 */
function newTestConfirm()
{
    let testName = $("#new-test-name").val();

    $.post(ajaxUrl,
        {
            action: 'create test',
            testName: testName
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
                        //zaískání informací z data.newGroupData a jejich zobrazení v tabulce v DOM
                        let groupData = data.newGroupData;
                        let groupDomItem = $('#test-template').html();

                        groupDomItem = groupDomItem.replace(/{id}/g, groupData.id);
                        groupDomItem = groupDomItem.replace(/{name}/g, groupData.name);
                        groupDomItem = groupDomItem.replace(/{url}/g, groupData.url);
                        groupDomItem = groupDomItem.replace(/{parts}/g, groupData.parts);

                        $(".data-properties").show();
                        $('.tests-data-section').append(groupDomItem);

                        //doplň event listener na tlačítko pro odstranění poznávačky
                        $(".tests-data-section .test.action:last .delete-group-button").click(function(event) {deleteTest(event)})

                        newMessage(response["message"], "success");

                        newTestCancel();
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce zahajující odstranění poznávačky
 * @param {event} event 
 */
function deleteTest(event)
{
    let $test = $(event.target).closest(".test.data-item");
    let name = $test.attr('data-group-name');

    let confirmMessage = "Opravdu chceš trvale odstranit poznávačku " + name + "? Přírodniny, které tato poznávačka obsahuje, ani jejich obrázky nebudou odstraněny. Tato akce je nevratná!";
    newConfirm(confirmMessage, "Odstranit", "Zrušit", function(confirm) {
        if (confirm) {
            deleteTestFinal($test);
            $test = undefined;
        }
        else return;
    })
}

/**
 * Funkce odesílající požadavek na odstranění poznávačky
 * @param {event} $test 
 */
function deleteTestFinal($test)
{
    let testId = $test.attr('data-group-id');
    
    $.post(ajaxUrl,
        {
            action: 'delete test',
            testId: testId
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (response["messageType"] === "error")
                    {
                        newMessage(response["message"], "error");
                    }
                    else if (response["messageType"] === "success")
                    {
                        $test.remove();

                        if ($(".test.data-item").length -1 == 0) //nutné zahrnout i template
                        {
                            $(".data-properties").hide();
                        }
                        
                        newMessage(response["message"], "success");
                    }
                    $test = undefined;
                }
            );
        },
        "json"
    );
}