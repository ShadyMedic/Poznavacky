var correct = undefined; //Správná odpověď k současnému obrázku
function isCorrect(answer)
{
	var a = removeDiacritic(answer.toLowerCase() + "××");
	var b = removeDiacritic(correct.toLowerCase() + "××");
	
  if (a === b)
  {
    //Odpověď bez překlepů
    return true;
  }
  
	var result = "typo";
	var errors = 0;
	
	for (var i = 0; i < b.length-2; i++)
	{
	    if (a[i] !== b[i])    //Neshodný znak
	    {
	        if (a[i] == b[i+1] && a[i+1] == b[i+2])    //Chybějící znak
	        {
	            a = a.slice(0, i) + b[i] + a.slice(i);    //Přidávání chybějícího znaku
	            errors++;
	        }
	        
	        else if (a[i+1] == b[i] && a[i+2] == b[i+1])    //Přebývající znak
	        {
	            a = a.slice(0, i) + a.slice(i+1);    //Odstraňování přebývajícího znaku
	            errors++;
	        }
	        
	        else    //Špatný znak
	        {
	            a = a.slice(0, i) + b[i] + a.slice(i+1);    //Oprava špatného znaku
	            errors++;
	        }
	    }
	}
	var ratio = errors / (a.length - 2)
	if (ratio > 0.334){result = false;}
	
	return result;
}
function removeDiacritic(str)
{
	str = str.replace("á","a");
	str = str.replace("ě","e");
	str = str.replace("é","e");
	str = str.replace("í","i");
	str = str.replace("ó","o");
	str = str.replace("ú","u");
	str = str.replace("ů","u");
	str = str.replace("ý","y");
	str = str.replace("č","c");
	str = str.replace("ď","d");
	str = str.replace("ň","n");
	str = str.replace("ř","r");
	str = str.replace("š","s");
	str = str.replace("ť","t");
	str = str.replace("ž","z");
	
	return str;
}
function answer(event)
{
	event.preventDefault();
	
	var ans = document.getElementById("textfield").value;
	
	document.getElementById("answerForm").style.display = "none";
	
  var correctionTest = isCorrect(ans);
	if (correctionTest === false)
	{
		document.getElementById("wrongAnswer").style.display = "block";
		document.getElementById("wrong3").innerHTML = correct;
	}
	else if (correctionTest === true)
	{
		document.getElementById("correctAnswer").style.display = "block";
		//Druhá kontrola správnosti odpovědi serverem a případné navýšení skóre uhodnutých obrázků
		postRequest("php/ajax/testAnswerCheck.php", responseFunc, responseFunc, ans);
	}
  else
  {
    document.getElementById("typoAnswer").style.display = "block";
    document.getElementById("typo2").innerHTML = correct;
		//Druhá kontrola správnosti odpovědi serverem a případné navýšení skóre uhodnutých obrázků
		postRequest("php/ajax/testAnswerCheck.php", responseFunc, responseFunc, ans);  
  }
	
	document.getElementById("nextButton").style.display = "block";
	document.getElementById("nextButton").focus();
}
function next()
{
	document.getElementById("image").src = "images/loading.gif";
	
	document.getElementById("nextButton").style.display = "none";
	document.getElementById("correctAnswer").style.display = "none";
  document.getElementById("typoAnswer").style.display = "none";
	document.getElementById("wrongAnswer").style.display = "none";
	
	document.getElementById("answerForm").style.display = "block";
	document.getElementById("textfield").value = "";
	document.getElementById("textfield").focus();
	
	
	getRequest("php/ajax/getRandomPic.php", showPic);
}
function showPic(response)
{
	if (response == "location.href = 'list.php';")
	{
		eval(response);
		return;
	}
	var arr = response.split("¶");
	
	document.getElementById("image").src = arr[0];
	correct = arr[1];
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
  case "Obrázek se načítá příliš dlouho":
    reason = 1;
    break;
	case "Obrázek zobrazuje nesprávnou přírodninu":
		reason = 2;
		break;
	case "Obrázek obsahuje název přírodniny":
		reason = 3;
		break;
	case "Obrázek má příliš špatné rozlišení":
		reason = 4;
		break;
	case "Obrázek porušuje autorská práva":
		reason = 5;
		break;
  case: "Jiný důvod":
    reason = 6;
    break;
	default:
		swal("Neplatný důvod!","","error");
		return;
	}
	
	//Kontrola obrázku
	switch (picUrl)
	{
	case "images/noImage.png":
	case "images/imagePreview.png":
	case "images/loading.gif":
		swal("Neplatný obrázek!","","error");
		return;
	}
	
	getRequest("php/ajax/newReport.php?pic=" + picUrl + "&reason=" + reason, responseFunc);
  
  //Skrýt formulář pro nahlašování
  cancelReport();
}
function cancelReport(event)
{
	document.getElementById("reportButton").style.display = "inline";
	document.getElementById("reportMenu").style.display = "none";
	document.getElementById("submitReport").style.display = "none";
	document.getElementById("cancelReport").style.display = "none";
  document.getElementById("reportMenu").selectedIndex = 0;
}
function responseFunc(response)
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
function postRequest(url, success = null, error = null, answer = null){
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
	req.open("POST", url, true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send("ans="+answer);
	return req;
}
