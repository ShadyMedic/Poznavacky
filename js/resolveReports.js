function showPicture(url)
{
	$("#image-preview img").attr("src", url);
	$("#image-preview").show();
	$(".overlay").show();
}
var currentReportValues = new Array(2);
function editPicture(event)
{
	//Dočasné znemožnění ostatních akcí u všech hlášení
	$(".report-action").addClass("grayscale-temp-report");
	$(".report-action").addClass("grayscale");
	$(".report-action").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editable-report-row");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentReportValues[i] = $("#editable-report-row .report-field:eq("+ i +")").val();
	}
	
	/*
	Pokud nebyla změněna přírodnina, bude v currentReportValues[0] uloženo NULL
	V takovém případě nahradíme tuto hodnotu textem zobrazeném v <select> elementu
	Tento text je innerText prvního <option> elementu
	*/
	if (currentReportValues[0] === null){ currentReportValues[0] = $("#editable-report-row .report-field:eq(0)>option:eq(0)").text(); }
	
	$("#editable-report-row .report-action").hide();					//Skrytí ostatních tlačítek akcí
	$("#editable-report-row .report-edit-buttons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editable-report-row .report-field").addClass("editable-field");	//Obarvení políček (//TODO)
	$("#editable-report-row .report-field").removeAttr("readonly");	//Umožnění editace (pro <input>)
	$("#editable-report-row .report-field").removeAttr("disabled");	//Umožnění editace (pro <select>)
}
function cancelPictureEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale-temp-report").removeAttr("disabled");
	$(".grayscale-temp-report").removeClass("grayscale grayscale-temp-report");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editable-report-row .report-field:eq("+ i +")").val(currentReportValues[i]);
	}
	
	$("#editable-report-row .report-action").show();						//Znovuzobrazení ostatních tlačítek akcí
	$("#editable-report-row .report-edit-buttons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editable-report-row .report-field").removeClass("editable-field");	//Odbarvení políček
	$("#editable-report-row input.report-field").attr("readonly", "");		//Znemožnění editace (pro <input>)
	$("#editable-report-row select.report-field").attr("disabled", "");	//Znemožnění editace (pro <select>)

	$("#editable-report-row").removeAttr("id");
}
function confirmPictureEdit(picId, asAdmin = false)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentReportValues[i] = $("#editable-report-row .report-field:eq("+ i +")").val();
	}
	//Uložení názvu nové části, do které nová příronina patří
	let newPart = $("#editable-report-row select option:selected").attr("data-part-name");
	
	var ajaxUrl = (asAdmin) ? "administrate-action" : "report-action";
	
	//Odeslat data na server
	$.post(ajaxUrl,
		{
			action: 'update picture',
			pictureId: picId,
			natural: currentReportValues[0],
			url: currentReportValues[1]
		},
		function (response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "success")
			{
				//Reset DOM
				cancelPictureEdit();
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
	
	//Aktualizuj údaje u hlášení stejného obrázku v DOM
	let reportsToUpdateCount = $("#reports-table .picture-id" + picId).length;
	for (let i = 0; i < reportsToUpdateCount; i++)
	{
		//Změna názvu části v prvním sloupci
		if (asAdmin)
		{
			//Změna názvu části nahrazením části textu za posledním lomítkem
			let content = $("#reports-table .picture-id" + picId + ":eq(" + i + ") td:eq(0)").text();
			let stringParts = content.split(" / ");
			stringParts[2] = newPart;
			content = stringParts.join(" / ");
			$("#reports-table .picture-id" + picId + ":eq(" + i + ") td:eq(0)").text(content);
		}
		else
		{
		    //Změna názvu části nahrazením celého obsahu
			$("#reports-table .picture-id" + picId + ":eq(" + i + ") td:eq(0)").text(newPart);
		}
		for (let j = 0; j <= 1; j++)
		{
			$("#reports-table .picture-id" + picId + ":eq(" + i + ") .report-field:eq("+ j +")").val(currentReportValues[j]);
		}
	}
}
function disablePicture(event, picId, asAdmin = false)
{
	var ajaxUrl = (asAdmin) ? "administrate-action" : "report-action";
	
	$.post(ajaxUrl,
		{
			action: 'disable picture',
			pictureId: picId
		},
		function(response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
		
	//Odebrání všechna hlášení daného obrázku z DOM
	$("#reports-table .picture-id" + picId).remove();
}
function deletePicture(event, picId, asAdmin = false)
{
	var ajaxUrl = (asAdmin) ? "administrate-action" : "report-action";
	
	$.post(ajaxUrl,
			{
				action: 'delete picture',
				pictureId: picId
			},
			function(response)
			{
				response = JSON.parse(response);
				if (response["messageType"] === "error")
				{
					//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
					alert(response["message"]);
				}
			}
		);
		
	//Odebrání všechna hlášení daného obrázku z DOM
	$("#reports-table .picture-id" + picId).remove();
}
function deleteReport(event, reportId, asAdmin = false)
{
	var ajaxUrl = (asAdmin) ? "administrate-action" : "report-action";
	
	$.post(ajaxUrl,
		{
			action: 'delete report',
			reportId: reportId
		},
		function(response)
		{
			response = JSON.parse(response);
			if (response["messageType"] === "error")
			{
				//TODO - zobraz nějak chybovou hlášku - ideálně ne jako alert() nebo jiný popup
				alert(response["message"]);
			}
		}
	);
	//Odebrání hlášení z DOM
	event.target.parentNode.parentNode.parentNode.remove();
}