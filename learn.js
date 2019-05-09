var naturalList = [];
var selected
var imageNumber = 0;

function keyPressed(event)
{
    var charCode = event.code || event.which;
	console.log(charCode);
    switch (charCode)
	{
		case "KeyW":
			next();
			return;
			break;
		case "KeyS":
			prev();
			return;
			break;
		case "KeyD":
			nextImg();
			return;
			break;
		case "KeyA":
			prevImg();
			return;
			break;
	}
}
function sel()
{
	selected = document.getElementById("dropList").value;
	
	imageNumber = 0;
	getImage();
}
function prev(event)
{
	event.preventDefault();
	var index = naturalList.indexOf(selected);
	
	if(index <= 0){index = naturalList.length;}
	index--;
	selected = naturalList[index];
	document.getElementById("dropList").value = selected;
	
	imageNumber = 0;
	cancelReport();
	getImage();
}
function next(event)
{
	event.preventDefault();
	var index = naturalList.indexOf(selected);
	
	if(index >= naturalList.length - 1){index = -1;}
	index++;
	selected = naturalList[index];
	document.getElementById("dropList").value = selected;
	
	imageNumber = 0;
	cancelReport();
	getImage();
}
function prevImg()
{
	cancelReport();
	imageNumber--;
	getImage();
}
function nextImg()
{
	cancelReport();
	imageNumber++;
	getImage();
}
function getImage()
{
	getRequest("getPics.php?name=" + selected + "&number=" + imageNumber, showImg);
}
function showImg(response)
{
	if (response == "swal('Neplatný název!','','error');" || response == "location.href = 'list.php';")
	{
		eval(response);
		return;
	}
	else if (response != "noImage.png" && response != "imagePreview.png")
	{
		document.getElementById("reportButton").removeAttribute("disabled");
		document.getElementById("reportButton").removeAttribute("class");
		document.getElementById("reportButton").setAttribute("class","button");
	}
	else
	{
		document.getElementById("reportButton").setAttribute("disabled", true);
		document.getElementById("reportButton").setAttribute("class", "buttonDisabled");
	}
	document.getElementById("image").src = response;
}
function reportImg(event)
{
	//event.preventDefault();
	
	document.getElementById("reportButton").style.display = "none";
	document.getElementById("reportMenu").style.display = "inline";
	document.getElementById("submitReport").style.display = "inline";
	document.getElementById("cancelReport").style.display = "inline";
}
function submitReport(event)
{
	event.preventDefault();
	
	var reason = document.getElementById("reportMenu").value;
	var picUrl = document.getElementById("image").src;
	
	//Převedení důvodu na číslo
	switch (reason)
	{
	case "Obrázek se nezobrazuje správně":
		reason = 0;
		break;
	case "Obrázek zobrazuje nesprávnou přírodninu":
		reason = 1;
		break;
	case "Obrázek obsahuje název přírodniny":
		reason = 2;
		break;
	case "Obrázek má příliš špatné rozlišení":
		reason = 3;
		break;
	case "Obrázek porušuje autorská práva":
		reason = 4;
		break;
	default:
		swal("Neplatný důvod!","","error");
		return;
	}
	
	//Kontrola obrázku
	switch (picUrl)
	{
	case "noImage.png":
	case "imagePreview":
		swal("Neplatný obrázek!","","error");
		return;
	}
	
	getRequest("newReport.php?pic=" + picUrl + "&reason=" + reason, reportResponse);
}
function cancelReport(event)
{
	document.getElementById("reportButton").style.display = "inline";
	document.getElementById("reportMenu").style.display = "none";
	document.getElementById("submitReport").style.display = "none";
	document.getElementById("cancelReport").style.display = "none";
}
function reportResponse(response)
{
	eval(response);
}
function getRequest(url, success = null, error = null){
	var req = false;
	//Creating request
	try
	{
		//Most broswers
		req = new XMLHttpRequest();
	} catch (e)
	{
		//Interned Explorer
		try
		{
			req = new ActiveXObject("Msxml2.XMLHTTP");
		}catch(e)
		{
			//Older version of IE
			try
			{
				req = new ActiveXObject("Microsoft.XMLHTTP");
			}catch(e)
			{
				return false;
			}
		}
	}
	
	//Checking request
	if (!req) return false;
	
	//Checking function parameters and setting intial values in case they aren´t specified
	if (typeof success != 'function') success = function () {};
	if (typeof error!= 'function') error = function () {};
	
	//Waiting for server response
	req.onreadystatechange = function()
	{
		if(req.readyState == 4)
		{
			return req.status === 200 ? success(req.responseText) : error(req.status);
		}
	}
	req.open("GET", url, true);
	req.send();
	return req;
}