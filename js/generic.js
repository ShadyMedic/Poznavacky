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

function showPanel() {
	$("aside").addClass("show");
	$("#aside-nav").hide();
	$("#aside-settings").hide();
} 

function showAdditionalPanel(spec) {
	$("#aside-additional-panel").addClass("show");
	$("#aside-login-info").hide();
	$("#aside-nav").hide();
	$("#aside-settings").hide();
	$("#" + spec).show();
	$("main").css("margin-left", "304px");
}

function closePanel() {
	if ($("#aside-additional-panel").hasClass("show"))
	{
		$("#aside-additional-panel").removeClass("show");
		$("main").css("margin-left", "64px");
	}
	else if ($("aside").hasClass("show"))
	{
		$("aside").removeClass("show");
	}
}

$(document).mouseup(function(e) 
{
    var container = $(".login-info");

    if (!container.is(e.target) && container.has(e.target).length === 0) 
    {
        $(".login-info").removeClass("show");
    }
});