function closeChangelog()
{
    document.getElementById("changelogContainer").style.display = "none";
}
function choose(depth, option = undefined)
{
    switch (depth)
    {
        //Vypsání všech tříd
        case -1:
            getRequest("php/ajax/getClasses.php", replaceTable, errorResponse);
            break;
        //Vybrání třídy
        case 0:
            getRequest("php/ajax/getGroups.php?classId=" + option, replaceTable, errorResponse);
            break;
        //Vybrání skupiny
        case 1:
            getRequest("php/ajax/getParts.php?groupId=" + option, replaceTable, errorResponse);
            break;
        //Vybrání části
        case 2:
            document.cookie="current=" + option;
            location.href = 'menu.php';
            break;
    }
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