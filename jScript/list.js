var selectedPartTR;	//Skladuje innerHTML řádku tabulky, který obsahuje právě vybranou část

function closeChangelog()
{
    document.getElementById("changelogContainer").style.display = "none";
}
function choose(depth, option = undefined, type = undefined)
{
    switch (depth)
    {
        //Vypsání všech tříd
        case 0:
            getRequest("php/getClasses.php", replaceTable, errorResponse);
            break;
        //Vybrání třídy
        case 1:
            getRequest("php/getGroups.php?classId=" + option, replaceTable, errorResponse);
            break;
        //Vybrání skupiny
        case 2:
            getRequest("php/getParts.php?groupId=" + option, replaceTable, errorResponse);
            break;
        //Vybrání části
        case 3:
            document.cookie="current=" + option;
            switch (type)
            {
                case 0:
                	location.href = 'addPics.php';
                    break;
                case 1:
                	location.href = 'learn.php';
                    break;
                case 2:
                	location.href = 'test.php';
                    break;
            }
            break;
    }
}
function showOptions(event, option)
{
    var row = event.target.parentNode;
    if (!Number.isInteger(Number(row.childNodes[1].innerHTML)))
    {
    	return; //Tlačítka jsou již zobrazená
    }
    else
    {
    	//Obnovení dříve vybraného řádku
    	for (var i = 0; i < row.parentNode.childNodes.length; i++)
    	{
    		if (row.parentNode.childNodes[i].childNodes.length === 1)
    		{
    			row.parentNode.childNodes[i].innerHTML = selectedPartTR;
    			break;
    		} 
    	}
    	
    	//Uložení současného řádku
    	selectedPartTR = row.innerHTML;
    }
    
    row.removeChild(row.childNodes[2]);
    row.removeChild(row.childNodes[1]);
    row.childNodes[0].setAttribute("colspan",3);
    row.childNodes[0].innerHTML = "<button class='button' onclick='choose(3,\""+option+"\""+",0)'>Přidat obrázky</button><button class='button' onclick='choose(3,"+"\""+option+"\""+",1)'>Učit se</button><button class='button' onclick='choose(3,"+"\""+option+"\""+",2)'>Vyzkoušet se</button>";
}
function replaceTable(response)
{
    document.getElementById("table").innerHTML = response;
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
function errorResponse(response)
{
    alert(response);
}