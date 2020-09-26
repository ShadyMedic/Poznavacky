function showPicture(url)
{
	$("#previewImgElement").attr("src", url);
	$("#imagePreview").show();
}
var currentReportValues = new Array(2);
function editPicture(event)
{
	//Dočasné znemožnění ostatních akcí u všech hlášení
	$(".reportAction").addClass("grayscale_temp_report");
	$(".reportAction").addClass("grayscale");
	$(".reportAction").attr("disabled", "");
	
	//Získat <tr> element upravované řádky
	let row = $(event.target.parentNode.parentNode.parentNode);
	row.attr("id", "editableReportRow");
	
	//Uložení současných hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentReportValues[i] = $("#editableReportRow .reportField:eq("+ i +")").val();
	}
	
	/*
	Pokud nebyla změněna přírodnina, bude v currentReportValues[0] uloženo NULL
	V takovém případě nahradíme tuto hodnotu textem zobrazeném v <select> elementu
	Tento text je innerText prvního <option> elementu
	*/
	if (currentReportValues[0] === null){ currentReportValues[0] = $("#editableReportRow .reportField:eq(0)>option:eq(0)").text(); }
	
	$("#editableReportRow .reportAction").hide();					//Skrytí ostatních tlačítek akcí
	$("#editableReportRow .reportEditButtons").show();				//Zobrazení tlačítek pro uložení nebo zrušení editace
	$("#editableReportRow .reportField").addClass("editableField");	//Obarvení políček (//TODO)
	$("#editableReportRow .reportField").removeAttr("readonly");	//Umožnění editace (pro <input>)
	$("#editableReportRow .reportField").removeAttr("disabled");	//Umožnění editace (pro <select>)
}
function cancelPictureEdit()
{
	//Opětovné zapnutí ostatních tlačítek akcí
	$(".grayscale_temp_report").removeAttr("disabled");
	$(".grayscale_temp_report").removeClass("grayscale grayscale_temp_report");
	
	//Obnova hodnot vstupních polí
	for (let i = 0; i <= 1; i++)
	{
		$("#editableReportRow .reportField:eq("+ i +")").val(currentReportValues[i]);
	}
	
	$("#editableReportRow .reportAction").show();						//Znovuzobrazení ostatních tlačítek akcí
	$("#editableReportRow .reportEditButtons").hide();					//Skrytí tlačítek pro uložení nebo zrušení editace
	$("#editableReportRow .reportField").removeClass("editableField");	//Odbarvení políček
	$("#editableReportRow input.reportField").attr("readonly", "");		//Znemožnění editace (pro <input>)
	$("#editableReportRow select.reportField").attr("disabled", "");	//Znemožnění editace (pro <select>)

	$("#editableReportRow").removeAttr("id");
}
function confirmPictureEdit(picId)
{
	//Uložení nových hodnot
	for (let i = 0; i <= 1; i++)
	{
		currentReportValues[i] = $("#editableReportRow .reportField:eq("+ i +")").val();
	}
	
	//Odeslat data na server
	$.post("administrate-action",
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
}
function disablePicture(event, picId)
{
	$.post('administrate-action',
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
		$("#reportsTable .pictureId" + picId).remove();
}
function deletePicture(event, picId)
{
	$.post('administrate-action',
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
		$("#reportsTable .pictureId" + picId).remove();
}
function deleteReport(event, reportId)
{
	$.post('administrate-action',
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