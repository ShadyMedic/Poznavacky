function hideCookies()
{
	document.getElementById("cookiesAlert").style.visibility = "hidden"
}

function showLogin()
{
	document.getElementById("obnoveniHesla").style.display = "none";
	document.getElementById("registrace").style.display = "none";
	document.getElementById("prihlaseni").style.display = "block";
}

function showRegister()
{
	document.getElementById("obnoveniHesla").style.display = "none";
	document.getElementById("prihlaseni").style.display = "none";
	document.getElementById("registrace").style.display = "block";
}

function showPasswordRecovery()
{
	document.getElementById("prihlaseni").style.display = "none";
	document.getElementById("registrace").style.display = "none";
	document.getElementById("obnoveniHesla").style.display = "block";
}