//vše, co se děje po načtení stránky
$(function() {
	//event listener tlačítka na zobrazení report menu
	$(".report-button").click(function(){reportImg()});
});

function reportImg()
{
	console.log("click");
	$(".report-button").hide();
	$(".report-box").addClass("show");
}
function cancelReport()
{
	$("#report-button").show();
	$("#report-menu").hide();
	$("#report-menu select")[0].selectedIndex = 0;
	$("#additional-report-info > *").hide();
	$("#additional-report-info input").val("");
	$("#additional-report-info textarea").val("");
	$("#additional-report-info select").selectedIndex = 0;
}

function updateReport()
{
	//Vše skrýt
	$("#additional-report-info > *").hide();
	if ($("#report-reason")[0].selectedIndex === 1)  //Obrázek se načítá příliš dlouho
	{
	    $("#long-loading-info").show();
	}
	else if ($("#report-reason")[0].selectedIndex === 2) //Obrázek zobrazuje nesprávnou přírodninu
	{
		$("#incorrect-natural-info").show();
	}
	else if ($("#report-reason")[0].selectedIndex === 6) //Jiný důvod (pro správce třídy)
	{
		$("#other-info").show();
	}
	else if ($("#report-reason")[0].selectedIndex === 7) //Jiný důvod (pro správce systému)
	{
		$("#other-admin-info").show();
	}
}

function submitReport()
{
	let reason = $("#report-reason").find(":selected").text();	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/10659117
	let picUrl = $("#main-img").attr("src");
	let reasonInfo = "";
	
	let additionalInfoElement = $("#additional-reportInfo").find("*:visible:first");	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/18162730
	if (additionalInfoElement.length > 0)
	{
		if (additionalInfoElement.prop("tagName") === "SELECT")		//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/5347371
		{
			reasonInfo = additionalInfoElement.find(":selected").text();
		}
		else
		{
			reasonInfo = additionalInfoElement.val();	
		}
	}
	
	//Kontrola vyplnění informací pro obecná hlášení
	if ((reason === "Jiný důvod (řeší správce třídy)" || reason === "Jiný důvod (řeší správce služby)") && reasonInfo.length === 0)
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
		if (reason !== "Obrázek se načítá příliš dlouho")
		{
			//TODO - nějak šikovně zobrazit chybovou hlášku
			alert("Z tohoto důvodu nemůžete nahlásit zatím nenačtený obrázek");
			return;
		}
	}
	
	$.post('new-report',
	{
		picUrl:picUrl,
		reason:reason,
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