/**
 * Funkce, jejímž úkolem je extrahovat z jednotného formátu odpovědí na AJAX požadavek
 * 1. Typ zprávy (úspěch, informace, varování, selhání, přesměrování)
 * 2. Zprávu (v případě přesměrování adresu)
 * 3. Pole dalších informací
 * Tyto informace se následně předají jako parametry fukci specifikované ve třetím argumentu
 * Pokud je typ zprávy nastaven na přesměrování, provádí tato funkce i okamžité přesměrovnání na adresu uvedenou ve zprávě
 * @param response Odpověď ze serveru
 * @param status Status odpovědi
 * @param processingFunction Funkce, které se mají předat výše zmíněné tři informace
 */
function ajaxCallback(response, status, processingFunction)
{
	let msgType = response["messageType"];
	let msg = response["message"];
	let data = response["data"];

	//Přesměrování
	if (msgType === "redirect")
	{
		window.location = response["message"];
	}
	else
	{
		processingFunction(msgType, msg, data);
	}
}