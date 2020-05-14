//přidává třídu podle toho, jestli uživatel používá myš nebo tabulátor -> úprava pseudotřídy :focus
window.addEventListener("keydown", (event) => { 
	if (event.keyCode === 9)
		document.body.classList.add("tab");
})
window.addEventListener("mousedown", (event) => {
	document.body.classList.remove("tab");	
})

//Funkce pro získání hodnoty cookie
//Zkopírována z https://www.w3schools.com/js/js_cookies.asp
function getCookie(cname)
{
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++)
	{
		var c = ca[i];
		while (c.charAt(0) == ' ')
		{
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0)
		{
			return c.substring(name.length, c.length);
		}
	}
	return "";
}