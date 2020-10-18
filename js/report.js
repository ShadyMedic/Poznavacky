// proměnné obsahující elementy select boxu s důvody nahlášení
var reasonNotDisplaying;
var reasonLongLoading;
var reasonIncorrectNatural;
var reasonContainsName;
var reasonBadResolution;
var reasonCopyright;
var reasonOther;
var reasonOtherAdmin;

//vše, co se děje po načtení stránky
$(function() {

	//načtení proměnných skladujících důvody nahlášení
	reasonNotDisplaying = $("#report-reason .custom-option:contains('Obrázek se nezobrazuje správně')");
	reasonLongLoading = $("#report-reason .custom-option:contains('Obrázek se načítá příliš dlouho')");
	reasonIncorrectNatural = $("#report-reason .custom-option:contains('Obrázek zobrazuje nesprávnou přírodninu')");
	reasonContainsName = $("#report-reason .custom-option:contains('Obrázek obsahuje název přírodniny')");
	reasonBadResolution = $("#report-reason .custom-option:contains('Obrázek má příliš špatné rozlišení')");
	reasonCopyright = $("#report-reason .custom-option:contains('Obrázek porušuje autorská práva')");
	reasonOther = $("#report-reason .custom-option:contains('Jiný důvod (řeší správce třídy)')");
	reasonOtherAdmin = $("#report-reason .custom-option:contains('Jiný důvod (řeší správce služby)')");

	//event listener tlačítek
	$(".report-button").click(function(){reportImg()});
	$("#report-reason .custom-options").click(function(){updateReport()});
	$("#submit-report-button").click(function(e){submitReport(e)})
	$("#cancel-report-button").click(function(e){cancelReport(e)})
});

// funkce na nahlášení obrázku
function reportImg()
{
	$(".report-button").hide();
	$(".report-box").addClass("show");
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
	$(".additional-report-info > *").hide();
	$("#long-loading-info .selected").removeClass("selected");
	$("#long-loading-info .custom-option:first").addClass("selected");
	$("#long-loading-info .custom-select-main span").text($("#long-loading-info .selected").text());
	$(".additional-report-info input").val("");
	$(".additional-report-info textarea").val("");
}

// funkce aktualizující obsah report boxu
function updateReport()
{
	$(".additional-report-info > *").hide();
	if (reasonLongLoading.hasClass("selected"))  //Obrázek se načítá příliš dlouho
	{
	    $("#long-loading-info").show();
	}
	else if (reasonIncorrectNatural.hasClass("selected")) //Obrázek zobrazuje nesprávnou přírodninu
	{
		$(".incorrect-natural-info-wrapper").show();
	}
	else if (reasonOther.hasClass("selected")) //Jiný důvod (pro správce třídy)
	{
		$(".other-info-wrapper").show();
	}
	else if (reasonOtherAdmin.hasClass("selected")) //Jiný důvod (pro správce systému)
	{
		$(".other-admin-info-wrapper").show();
	}
}

// funkce odesílající hlášení
function submitReport()
{
	let reason = $("#report-reason").find(".selected");	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/10659117
	let picUrl = $("#main-img").attr("src");
	let reasonInfo = "";
	
	let additionalInfoElement = $(".additional-report-info").find("*:visible:first");	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/18162730
	if (additionalInfoElement.length > 0)
	{
		//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/5347371

		// momentálně ení nutné, jelikož nejsou využívány defaultní select boxy
		/*if (additionalInfoElement.prop("tagName") === "SELECT"){reasonInfo = additionalInfoElement.find(":selected").text();}*/

		if (additionalInfoElement.hasClass("custom-select-wrapper")) {
			reasonInfo = additionalInfoElement.find(".custom-options .selected").text();
			console.log(reasonInfo);
		}
		else
		{
			reasonInfo = additionalInfoElement.val();	
		}
	}
	
	//Kontrola vyplnění informací pro obecná hlášení
	if ((reason === reasonOther || reason === reasonOtherAdmin) && reasonInfo.length === 0)
	{
		//TODO - nějak šikovně zobrazit chybovou hlášku
		alert("Musíte vyplnit důvod hlášení");
		return;
	}
	
	//Kontrola obrázku
	switch (picUrl)
	{
	case "images/noImage.png":
	case "images/imagePreview.png":
		//TODO - nějak šikovně zobrazit chybovou hlášku
		alert("Tento obrázek nemůžete nahlásit");
		return;
	case "images/loading.gif":
		if (reason !== reasonLongLoading)
		{
			//TODO - nějak šikovně zobrazit chybovou hlášku
			alert("Z tohoto důvodu nemůžete nahlásit zatím nenačtený obrázek");
			return;
		}
	}
	
	$.post('new-report',
	{
		picUrl:picUrl,
		reason:reason.text(),
		info:reasonInfo
	}, function(response)
	{
		$msg = JSON.parse(response)['msg'];
		//TODO - nějak šikovně zobrazit hlášku ze serveru
		alert($msg);
	});
  
	//Skrýt formulář pro nahlašování
	cancelReport();
}