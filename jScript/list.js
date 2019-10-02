function choose(option)
{
	document.cookie="current=" + option;
	location.href = '../menu.php';
}
function closeChangelog()
{
	document.getElementById("changelogContainer").style.display = "none";
}
