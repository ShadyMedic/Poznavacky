function reportImg()
{
	$("#reportButton").hide();
	$("#reportMenu").show();
}
function cancelReport()
{
	$("#reportButton").show();
	$("#reportMenu").hide();
	$("#reportMenu select")[0].selectedIndex = 0;
	$("#additionalReportInfo > *").hide();
	$("#additionalReportInfo input").val("");
	$("#additionalReportInfo textarea").val("");
	$("#additionalReportInfo select").selectedIndex = 0;
}

function updateReport()
{
	//Vše skrýt
	$("#additionalReportInfo > *").hide();
	if ($("#reportReason")[0].selectedIndex === 1)  //Obrázek se načítá příliš dlouho
	{
	    $("#longLoadingInfo").show();
	}
	else if ($("#reportReason")[0].selectedIndex === 2) //Obrázek zobrazuje nesprávnou přírodninu
	{
		$("#incorrectNaturalInfo").show();
	}
	else if ($("#reportReason")[0].selectedIndex === 6) //Jiný důvod (pro správce třídy)
	{
		$("#otherInfo").show();
	}
	else if ($("#reportReason")[0].selectedIndex === 7) //Jiný důvod (pro správce systému)
	{
		$("#otherAdminInfo").show();
	}
}

function submitReport()
{
	let reason = $("#reportReason").find(":selected").text();	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/10659117
	let picUrl = $("#mainImg").attr("src");
	let reasonInfo = "";
	
	let additionalInfoElement = $("#additionalReportInfo").find("*:visible:first");	//Napsáno podle odpovědi na StackOverflow: https://stackoverflow.com/a/18162730
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