var correct = undefined; //Správná odpověď k současnému obrázku
function isCorrect(answer)
{
	var a = removeDiacritic(answer.toLowerCase() + "××");
	var b = removeDiacritic(correct.toLowerCase() + "××");
	
	var result = true;
	var errors = 0;
	
	for (var i = 0; i < b.length-2; i++)
	{
	    if (a[i] !== b[i])    //Unmatching character
	    {
	        if (a[i] == b[i+1] && a[i+1] == b[i+2])    //Missing character
	        {
	            //console.log("Missing char: " + b[i]);
	            a = a.slice(0, i) + b[i] + a.slice(i);    //Adding the missing character
	            //console.log("Result: " + a);
	            errors++;
	        }
	        
	        else if (a[i+1] == b[i] && a[i+2] == b[i+1])    //Sulprusing character
	        {
	            //console.log("Sulprusing char: " + a[i]);
	            a = a.slice(0, i) + a.slice(i+1);    //Removing the sulprusing character
	            //console.log("Result: " + a);
	            errors++;
	        }
	        
	        else    //Wrong character
	        {
	            //console.log("Wrong char: " + a[i] + " (should be " + b[i] +")");
	            a = a.slice(0, i) + b[i] + a.slice(i+1);    //Replacing the wrong character
	            //console.log("Result: " + a);
	            errors++;
	        }
	    }
	}
	//console.log("Total spelling errors: " + errors);
	var ratio = errors / (a.length - 2)
	//console.log("Error ratio: " + ratio);
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
	
	if (isCorrect(ans))
	{
		document.getElementById("correctAnswer").style.display = "block";
		//Druhá kontrola správnosti odpovědi serverem a případné navýšení skóre uhodnutých obrázků
		postRequest("php/ajax/testAnswerCheck.php", responseFunc, responseFunc, correct);
	}
	else
	{
		document.getElementById("wrongAnswer").style.display = "block";
		document.getElementById("serverResponse").innerHTML = correct;
	}
	
	document.getElementById("nextButton").style.display = "block";
	document.getElementById("nextButton").focus();
}
function next()
{
	document.getElementById("image").src = "images/imagePreview.png";
	
	document.getElementById("nextButton").style.display = "none";
	document.getElementById("correctAnswer").style.display = "none";
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
	case "images/noImage.png":
	case "images/imagePreview.png":
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
