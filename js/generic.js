var smallTablet = 672;
var tablet = 768;


//přidává třídu podle toho, jestli uživatel používá myš nebo tabulátor -> úprava pseudotřídy :focus
$(window).on("keydown", function(event) { 
	if (event.keyCode === 9)
		$("body").addClass("tab");
})
$(window).on("mousedown", function() {
	$("body").removeClass("tab");	
})

$(window).resize(function() {
	resizeAsidePanel();
});

function resizeAsidePanel() {
	if ($(window).width() < smallTablet) {
		$("main").css("margin-left", "0");
		$("#aside-additional-panel").removeClass("show");
	}
	if (($(window).width() >= smallTablet) && ($("aside").hasClass("show"))) {
		$("#aside-additional-panel").addClass("show");
	}
	if ($("#aside-additional-panel").hasClass("show"))
	{
		$("main.menu").css("margin-left", "304px");
	}
	else if ((!$("#aside-additional-panel").hasClass("show")) && ($(window).width() >= smallTablet)) {
		$("main.menu").css("margin-left", "64px");
	}
}

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
	$("aside").addClass("show");
	$("#" + spec).show();
	$("main").css("margin-left", "304px");
}

function closePanel() {
	if ($("#aside-additional-panel").hasClass("show")) {
		$("main").css("margin-left", "64px");
	}
	else {
		$("main").css("margin-left", "0");
	}
	$("#aside-additional-panel").removeClass("show");
	$("aside").removeClass("show");
}

$(document).mouseup(function(e) 
{
    var container = $(".login-info");

    if (!container.is(e.target) && container.has(e.target).length === 0) 
    {
        $(".login-info").removeClass("show");
    }
});