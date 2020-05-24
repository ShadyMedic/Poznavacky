//přidává třídu podle toho, jestli uživatel používá myš nebo tabulátor -> úprava pseudotřídy :focus
$(window).on("keydown", function(event) { 
	if (event.keyCode === 9)
		$("body").addClass("tab");
})
$(window).on("mousedown", function() {
	$("body").removeClass("tab");	
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