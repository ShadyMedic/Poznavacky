function hideCookies()
{
	document.getElementById("cookiesAlert").style.visibility = "hidden"
}
function showLogin()
{
	document.getElementById("registrace").style.display = "none";
	document.getElementById("prihlaseni").style.display = "block";
}
function showRegister()
{
	document.getElementById("prihlaseni").style.display = "none";
	document.getElementById("registrace").style.display = "block";
}