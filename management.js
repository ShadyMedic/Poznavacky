function firstTab()
{
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab1").style.display = "block";
}
function secondTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab2").style.display = "block";
}
function thirdTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab3").style.display = "block";
}
function fourthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab5").style.display = "none";
	
	document.getElementById("tab4").style.display = "block";
}
function fifthTab()
{
	document.getElementById("tab1").style.display = "none";
	document.getElementById("tab2").style.display = "none";
	document.getElementById("tab3").style.display = "none";
	document.getElementById("tab4").style.display = "none";
	
	document.getElementById("tab5").style.display = "block";
}
/*------------------------------------------------------------*/
function acceptNameChange(event)
{
	//TODO implementovat funkci pro přijmutí změny jména
	console.log("Accepted.");
}
function declineNameChange(event)
{
	//TODO implementovat funkci pro odmítnutí změny jména
	console.log("Declined.");
}
function sendMailNameChange(event)
{
	//TODO implementovat funkci pro odeslání e-mailu žadateli o změnu jména
	console.log("Sent.");
}