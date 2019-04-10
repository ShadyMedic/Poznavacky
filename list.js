function choose(option)
{
	document.cookie="current=" + option;
	location.href = 'menu.php';
}