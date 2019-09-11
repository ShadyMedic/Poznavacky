window.onload = function ()
{
	//Umožnit odeslání formulářů stisktnutím enteru
	
	//Přihlašování
	var loginBtn = document.getElementById("login_submit");
	setEnterTrigger(document.getElementById("login_name"), loginBtn);
	setEnterTrigger(document.getElementById("login_pass"), loginBtn);
	setEnterTrigger(document.getElementById("login_keep"), loginBtn);
	
	//Registrace
	var registerBtn = document.getElementById("register_submit");
	setEnterTrigger(document.getElementById("register_name"), registerBtn);
	setEnterTrigger(document.getElementById("register_pass"), registerBtn);
	setEnterTrigger(document.getElementById("register_repass"), registerBtn);
	setEnterTrigger(document.getElementById("register_email"), registerBtn);
	
	//Obnova hesla
	var recoveryBtn = document.getElementById("passRecovery_submit");
	//TODO
}

function setEnterTrigger(input, button)
{
	input.addEventListener("keyup", function(event){
		//13 = enter
	  	if (event.keyCode === 13)
		{
	    	//Kliknout na tlačítko
	    	button.click();
		}
	});
}

/*-----------------------------------------------------------------------*/

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

/*-----------------------------------------------------------------------*/

function login()
{	
	var username = document.getElementById("login_name").value;
	var password = document.getElementById("login_pass").value;
	var keepLogged = document.getElementById("login_keep").checked;
	username = encodeURIComponent(username);
	password = encodeURIComponent(password);
	
	postRequest("login.php", responseFunc, null, username, password, keepLogged);
}

function register()
{
	var username = document.getElementById("register_name").value;
	var password = document.getElementById("register_pass").value;
	var rePassword = document.getElementById("register_repass").value;
	var email = document.getElementById("register_email").value;
	if (email.length == 0)
	{
		if (!confirm("Opravdu se chcete zaregistrovat bez zadání e-mailové adresy? Nebudete tak moci dostávat důležitá upozornění nebo obnovit zapomenuté heslo. E-mailovou adresu můžete kdykoliv změnit nebo odebrat.")){return;}
	}
	
	username = encodeURIComponent(username);
	password = encodeURIComponent(password);
	rePassword = encodeURIComponent(rePassword);
	email = encodeURIComponent(email);
	
	postRequest("register.php", responseFunc, null, username, password, rePassword, email);
}

function recoverPassword()
{
	var email = document.getElementById("passRecovery_input").value;
	
	email = encodeURIComponent(email);
	
	postRequest("recoverPassword.php", responseFunc, null, null, null, null, email);
}

function postRequest(url, success = null, error = null, username, password, rePassword = null, email = null){
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
	req.send("name="+username+"&pass="+password+"&rePass="+rePassword+"&email="+email);
	return req;
}

function responseFunc(response)
{
	if (response[0] == "l")	//Odpovědí je javascript (začíná slovem location)
	{
		eval(response);
	}
	else	//Odpovědí jsou <li> elementy se seznamem chyb
	{
		if (document.getElementById("registrace").style.display == "block")	//Je zobrazen registrační formulář
		{
			document.getElementById("registerErrors").innerHTML = response;
		}
		else if (document.getElementById("prihlaseni").style.display == "block")	//Je zobrazen přihlašovací formulář
		{
			document.getElementById("loginErrors").innerHTML = response;
		}
		else	//Je zobrazen formulář pro obnovu hesla
		{
			document.getElementById("passwordRecoveryErrors").innerHTML = response;
		}
	}
}
