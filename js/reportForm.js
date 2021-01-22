//DO VŠECH STRÁNEK POUŽÍVAJÍCÍCH TENTO SKRIPT MUSÍ BÝT ZAHRNUT I SOUBOR ajaxMediator.js

// proměnné obsahující elementy select boxu s důvody nahlášení
var $reasonNotDisplaying;
var $reasonLongLoading;
var $reasonIncorrectNatural;
var $reasonContainsName;
var $reasonBadResolution;
var $reasonCopyright;
var $reasonOther;
var $reasonOtherAdmin;

//vše, co se děje po načtení stránky
$(function() {

    //načtení proměnných skladujících důvody nahlášení
    $reasonNotDisplaying = $("#report-reason .custom-option:contains('Obrázek se nezobrazuje správně')");
    $reasonLongLoading = $("#report-reason .custom-option:contains('Obrázek se načítá příliš dlouho')");
    $reasonIncorrectNatural = $("#report-reason .custom-option:contains('Obrázek zobrazuje nesprávnou přírodninu')");
    $reasonContainsName = $("#report-reason .custom-option:contains('Obrázek obsahuje název přírodniny')");
    $reasonBadResolution = $("#report-reason .custom-option:contains('Obrázek má příliš špatné rozlišení')");
    $reasonCopyright = $("#report-reason .custom-option:contains('Obrázek porušuje autorská práva')");
    $reasonOther = $("#report-reason .custom-option:contains('Jiný důvod (řeší správce třídy)')");
    $reasonOtherAdmin = $("#report-reason .custom-option:contains('Jiný důvod (řeší správce služby)')");

	//event listener tlačítek
	$(".report-button").click(function(){reportImg()});
	$("#report-reason .custom-options").click(function(){updateReport()});
	$("#submit-report-button").click(function(e){submitReport(e)})
	$("#cancel-report-button").click(function(e){cancelReport(e)})

	
	displayImgPreview();
});

//vše, co se děje při změně velikosti okna
$(window).resize(function() {
	displayImgPreview();
})

//funkce zobrazující náhled nahlašovaného obrázku
//zobrazení závisí na velikosti okna - aby se náhled vešel
function displayImgPreview() 
{
	if ($("#main-img").height() < 400)
		$("#report-img-preview").hide();
	else $("#report-img-preview").show();
}

// funkce na nahlášení obrázku
function reportImg()
{
	$(".report-button").hide();
	$(".report-box").addClass("show");

	//nastavení url náhledu nahlašovaného obrázku
	url = $("#main-img").attr("src");
	$("#report-img-preview > img").attr("src", url);
}

// funkce na zrušení hlášení
function cancelReport()
{
	// skrytí report boxu a zobrazení tlačítka na jeho zobrazení
	$(".report-box").removeClass("show");
	$(".report-button").show();
    // obnovení hlavního select boxu
    $("#report-reason .selected").removeClass("selected");
    $("#report-reason .custom-option:first").addClass("selected");
    $("#report-reason .custom-select-main span").text($("#report-reason .selected").text());
    // obnovení dodatečných polí a select boxů
    $(".additional-report-info > *:not(#report-message)").hide();
    $("#long-loading-info .selected").removeClass("selected");
    $("#long-loading-info .custom-option:first").addClass("selected");
    $("#long-loading-info .custom-select-main span").text($("#long-loading-info .selected").text());
    $(".additional-report-info input").val("");
    $(".additional-report-info textarea").val("");
}

// funkce aktualizující obsah report boxu
function updateReport()
{
	$(".additional-report-info > *:not(#report-message)").hide();
    if ($reasonLongLoading.hasClass("selected"))  //Obrázek se načítá příliš dlouho
	{
        $("#long-loading-info").show();
    }
    else if ($reasonIncorrectNatural.hasClass("selected")) //Obrázek zobrazuje nesprávnou přírodninu
    {
        $(".incorrect-natural-info-wrapper").show();
    }
    else if ($reasonOther.hasClass("selected")) //Jiný důvod (pro správce třídy)
    {
        $(".other-info-wrapper").show();
    }
    else if ($reasonOtherAdmin.hasClass("selected")) //Jiný důvod (pro správce systému)
    {
        $(".other-admin-info-wrapper").show();
    }
}

// funkce odesílající hlášení
function submitReport()
{
    let $reason = $("#report-reason").find(".selected");	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/10659117
    let picUrl = $("#main-img").attr("src");
    let reasonInfo = "";
	
    let additionalInfoElement = $(".additional-report-info").find("*:visible:first");	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/18162730
    if (additionalInfoElement.length > 0) //Je-li vidět nějaké pole pro zadání dalších informací
    {
        //Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/5347371
        //Momentálně není nutné, jelikož nejsou využívány defaultní select boxy
        /*if (additionalInfoElement.prop("tagName") === "SELECT"){reasonInfo = additionalInfoElement.find(":selected").text();}*/

        if (additionalInfoElement.hasClass("custom-select-wrapper")) {
            reasonInfo = additionalInfoElement.find(".custom-options .selected").text();
		}
		else
		{
		    //Jiný důvod - hledání <textarea> nebo <input>
			reasonInfo = additionalInfoElement.find("textarea,input").val();
		}
	}
	
    //Kontrola vyplnění informací pro obecná hlášení
    if (($reason.get(0).isEqualNode($reasonOther.get(0)) || $reason.get(0).isEqualNode($reasonOtherAdmin.get(0))) && reasonInfo.length === 0)
	{
		$("#report-message").text("Musíte vyplnit důvod hlášení");
        return;
    }

	//Kontrola obrázku
	switch (picUrl)
	{
	case "images/noImage.png":
        case "images/imagePreview.png":
			$("#report-message").text("Tento obrázek nemůžete nahlásit");
            return;
        case "images/loading.gif":
            if (!$reason.get(0).isEqualNode($reasonLongLoading.get(0)))
		    {
				$("#report-message").text("Z tohoto důvodu nemůžete nahlásit zatím nenačtený obrázek");
                return;
            }
    }

	//nebyla zaznamenána žádná chyba
	$("#report-message").text("");

    let url = window.location.href;
    if (url.endsWith('/')) { url = url.slice(0, -1); } //Odstraň trailing slash (pokud je přítomen)
    url = url.substr(0, url.lastIndexOf("/")); //Odstraň akci (/learn nebo /test)
    $.post(url + '/new-report',
        {
            picUrl:picUrl,
            reason:$reason.text(),
			info:reasonInfo
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					newMessage(message, messageType);
					
                    //Skrýt formulář pro nahlašování
                    cancelReport();
                }
            );
        },
        "json"
    );
}