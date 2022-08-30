var ajaxUrl;

$(function()
{
    ajaxUrl = window.location.href;
    if (ajaxUrl.endsWith('/')) { ajaxUrl = ajaxUrl.slice(0, -1); } //odstranění trailing slashe (pokud je přítomen)

    //správa hlášení administrátorem
    if (ajaxUrl.endsWith("/admin-reports"))
    {
        ajaxUrl = ajaxUrl.replace("admin-reports", "report-action");
    }

    //správa hlášení správcem třídy
    else
    {
        ajaxUrl = ajaxUrl.replace("reports", "report-action");
    }
    
    //event listenery tlačítek
    $(".show-picture-button").click(function(event) {showPicture(event)})
    $(".hide-picture-button").click(function(event) {hidePicture(event)})
    $(".edit-picture-button").click(function(event) {editPicture(event)})
    $(".edit-picture-confirm-button").click(function(event) {editPictureConfirm(event)})
    $(".edit-picture-cancel-button").click(function(event) {editPictureCancel(event)})
    $(".delete-picture-button").click(function(event) {deletePicture(event)})
    $(".delete-report-button").click(function(event) {deleteReport(event)})
  
})

/**
 * Funkce zobrazující náhled nahlášeného obrázku
 * @param {event} event 
 */
function showPicture(event)
{
    let $report = $(event.target).closest(".report-data-item");
    let url = $report.attr("data-report-url");
    
    //class owner
    if ($('body').attr("id") == "resolve-reports")
    {
        //skrytí ostatních zobrazených obrázků
        $(".report-image").not($report.find(".report-image")).hide();

        //doplnění url a zobrazení obrázku
        $report.find(".report-image > img").attr("src", url);
        $report.find(".report-image").show();
    }

    //admin
    else {
        $("#report-image > img").attr("src", url);
        $("#report-image").show();
        $("#overlay").show();
    }
}

/**
 * Funkce skrývající náhled nahlášeného obrázku
 * @param {event} event 
 */
function hidePicture(event)
{
    //class owner
    if ($('body').attr("id") == "resolve-reports")
    {
        let $report = $(event.target).closest(".report-data-item");

        $report.find(".report-image").hide();
    }

    //admin
    else {
        $("#report-image").hide();
        $("#overlay").hide();
        $("#report-image > img").attr("src", "");
    }
}

var currentName;
var currentUrl;
/**
 * Funkce zahajující úpravu informací o nahlášeném obrázku
 * @param {event} event 
 */
function editPicture(event)
{
    let $report = $(event.target).closest(".report-data-item");

    //skrytí ostatních zobrazených obrázků
    $(".report-image").not($report.find(".report-image")).hide();

    //dočasné znemožnění ostatních akcí u všech hlášení
    $(".report-action > .btn").addClass("disabled");
        
    //uložení současných hodnot
    currentName = $report.find(".report-name").text();
    currentUrl = $report.find(".report-url").text();
    
    //zobrazení příslušných tlačítek a polí
    $report.find(".report-action > .btn").hide();
    $report.find(".report-action > .report-edit-buttons").show();
    $report.find(".report-name").hide();
    $report.find(".report-name-edit").show();
    $report.find(".report-url").hide();
    $report.find(".report-url-edit").show();
}

/**
 * Funkce rušící úpravu informací o nahlášeném obrázku
 * @param {event} event 
 */
function editPictureCancel(event)
{
    let $report = $(event.target).closest(".report-data-item");
    let $reportNaturalSelect = $report.find(".report-name-edit .report-natural-select");

    //reset custom select boxu
    $reportNaturalSelect.find(".custom-option").removeClass("selected");
    $reportNaturalSelect.find(".custom-select-main span").text(currentName);
    $reportNaturalSelect.find(".custom-option:contains(" + currentName + ")").addClass("selected");

    //reset url pole
    $report.find(".report-url-edit .text-field").val(currentUrl);

    //zobrazení příslušných tlačítek, skrytí polí
    $report.find(".report-action > .btn").show();
    $report.find(".report-action > .report-edit-buttons").hide();
    $report.find(".report-name").show();
    $report.find(".report-name-edit").hide();
    $report.find(".report-url").show();
    $report.find(".report-url-edit").hide();    

    //odblokování ostatních akcí u všech hlášení
    $(".report-action > .btn").removeClass("disabled");    
}

/**
 * Funkce odesílající požadavek na úpravu informací o nahlášeném obrázku
 * @param {event} event 
 */
function editPictureConfirm(event)
{
    let $report = $(event.target).closest(".report-data-item");
    let pictureId = $report.attr("data-picture-id");

    //uložení nových hodnot
    currentName = $report.find(".report-name-edit .report-natural-select .selected").text().trim();
    currentUrl = $report.find(".report-url-edit .text-field").val().trim();
    
    $.post(ajaxUrl,
        {
            action: 'update picture',
            pictureId: pictureId,
            natural: currentName,
            url: currentUrl
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function (messageType, message, data)
                {
                    if (messageType === "success")
                    {
                        //reset DOM (simulace kliknutí na tlačítko cancel kvůli eventu jako parametru funkce)
                        $report.find(".edit-picture-cancel-button").trigger("click");

                        $report.find(".report-name").text(currentName);
                        $report.find(".report-url").text(currentUrl);
                    }
                    if (messageType === "error")
                    {
                        newMessage(message, "error");
                    }
                    else
                    {
                        //aktualizuj údaje u hlášení stejného obrázku v DOM
                        let $reportsToUpdate = $(".report-data-item[data-picture-id='" + pictureId + "']");
                        let reportsToUpdateCount = $reportsToUpdate.length;

                        for (let i = 0; i < reportsToUpdateCount; i++)
                        {
                            $reportsToUpdate.each(function()
                            {
                                let $reportNaturalSelect = $(this).find(".report-name-edit .report-natural-select");
                                //aktualizace spanů
                                $(this).find(".report-name").text(currentName);
                                $(this).find(".report-url").text(currentUrl);

                                //aktualizace custom select boxu
                                $reportNaturalSelect.find(".custom-option").removeClass("selected");
                                $reportNaturalSelect.find(".custom-select-main span").text(currentName);
                                $reportNaturalSelect.find(".custom-option:contains(" + currentName + ")").addClass("selected");

                                //aktualizace url textarey
                                $(this).find(".report-url-edit .text-field").val(currentUrl);
                            })
                        }
                    }
                }
            );
        },
        "json"
    );
}

/**
 * Funkce odesílající požadavek na odstranění obrázku
 * @param {event} event 
 */
function deletePicture(event)
{
    let pictureId = $(event.target).closest(".report-data-item").attr("data-picture-id");

    $.post(ajaxUrl,
            {
                action: 'delete picture',
                pictureId: pictureId
            },
            function (response, status)
            {
                ajaxCallback(response, status,
                    function (messageType, message, data)
                    {
                        if (messageType === "error")
                        {
                            //class owner
                            if ($('body').attr("id") == "resolve-reports")
                            {
                                newMessage(message, "error");
                            }

                            //admin
                            else {
                                alert(message);
                            }
                        }
                        else
                        {
                            //odebrání všech hlášení daného obrázku z DOM
                            $(".report-data-item[data-picture-id='" + pictureId + "']").remove();
                        }
                    }
                );
            },
            "json"
        );
}

/**
 * Funkce odesílající požadavek na odstranění hlášení
 * @param {event} event 
 */
function deleteReport(event)
{
    let reportId = $(event.target).closest(".report-data-item").attr("data-report-id");
    
    $.post(ajaxUrl,
        {
            action: 'delete report',
            reportId: reportId
        },
        function (response, status)
        {
            ajaxCallback(response, status,
                function(messageType, message, data)
                {
                    if (messageType === "error")
                    {
                        //class owner
                        if ($('body').attr("id") == "resolve-reports")
                        {
                            newMessage(message, "error");
                        }

                        //admin
                        else {
                            alert(message);
                        }
                    }
                    else
                    {
                        //odebrání hlášení z DOM
                        $(event.target).closest(".report-data-item").remove();
                    }
                }
            );
        },
        "json"
    );
}