var naturalList = [];
var selected
var imageNumber = 0;

function keyPressed(event)
{
	//Vypnutí klávesových zkratek, pokud uživatel zrovna píše důvod hlášení
	if (document.activeElement.tagName === "INPUT" || document.activeElement.tagName === "TEXTAREA")
	{
		return;
	}
	
    var charCode = event.code || event.which;
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
	document.getElementById("image").src = "images/loading.gif";
	
	getRequest("php/ajax/getPics.php?name=" + selected + "&number=" + imageNumber, showImg);
}
function showImg(response)
{
	if (response == "swal('Neplatný název!','','error');" || response == "location.href = 'list.php';")
	{
		eval(response);
		return;
	}
	else if (response != "images/noImage.png" && response != "images/imagePreview.png")
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
function updateReport()
{
  if (document.getElementById("reportMenu").selectedIndex === 1)  //Obrázek se načítá příliš dlouho
  {
    document.getElementById("additionalReportInfo").innerHTML = "<select><option>>2 s</option><option>>5 s</option><option>>10 s</option><option>>20 s</option></select>";
  }
  else if (document.getElementById("reportMenu").selectedIndex === 2) //Obrázek zobrazuje nesprávnou přírodninu
  {
    document.getElementById("additionalReportInfo").innerHTML = "<input type='text' placeholder='Přírodnina na obrázku' maxlength=31>";
  }
  else if (document.getElementById("reportMenu").selectedIndex === 6) //Jiný důvod
  {
    document.getElementById("additionalReportInfo").innerHTML = "<textarea placeholder='Specifikujte prosím důvod' maxlength=255></textarea>";
  }
  else
  {
	  document.getElementById("additionalReportInfo").innerHTML = "";
  }
}
function submitReport(event)
{
	event.preventDefault();
	
	var reason = document.getElementById("reportMenu").selectedIndex;
	var picUrl = document.getElementById("image").src;
	var reasonInfo = "";
	try{reasonInfo = document.getElementById("additionalReportInfo").childNodes[0].value;}catch(e){}
  
  //Kontrola důvodu
  if (reason > 6)
  {
    swal("Neplatný důvod!","","error");
    return;
  }
	
  //Získání a případná kontrola dalších informací
  switch (reason)
  {
    case 1:
      var info = document.getElementById("additionalReportInfo").childNodes[0].value;
      if (!(info === ">2 s" || info === ">5 s" || info === ">10 s" || info === ">20 s"))
      {
        swal("Neplatná volba!","","error");
        return;
      }
      break;
    case 2:
      var info = encodeURIComponent(document.getElementById("additionalReportInfo").childNodes[0].value);
      break;
    case 6:
      var info = encodeURIComponent(document.getElementById("additionalReportInfo").childNodes[0].value);
      if (info === "")
      {
        swal("Vyplňte prosím důvod hlášení.","","error");
        return;
      }
      break;
  }
  
	//Kontrola obrázku
	switch (picUrl)
	{
	case "images/noImage.png":
	case "images/imagePreview.png":
		swal("Neplatný obrázek!","","error");
		return;
	case "images/loading.gif":
		if (reason !== 1)
		{
			swal("Neplatný obrázek!","","error");
			return;
		}
	}
	
	getRequest("php/ajax/newReport.php?pic=" + picUrl + "&reason=" + reason + "&info=" + info, reportResponse);
  
  //Skrýt formulář pro nahlašování
  cancelReport();
}
function cancelReport(event)
{
    document.getElementById("reportButton").style.display = "inline";
    document.getElementById("reportMenu").style.display = "none";
    document.getElementById("additionalReportInfo").innerHTML = "";
    document.getElementById("submitReport").style.display = "none";
    document.getElementById("cancelReport").style.display = "none";
    document.getElementById("reportMenu").selectedIndex = 0;
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
