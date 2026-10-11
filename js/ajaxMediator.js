/**
 * Funkce, jejímž úkolem je extrahovat z jednotného formátu odpovědí na AJAX požadavek
 * 1. Typ zprávy (úspěch, informace, varování, selhání, přesměrování)
 * 2. Zprávu (v případě přesměrování adresu)
 * 3. Pole dalších informací
 * Tyto informace se následně předají jako parametry fukci specifikované ve třetím argumentu
 * Pokud je typ zprávy nastaven na přesměrování, provádí tato funkce i okamžité přesměrovnání na adresu uvedenou ve zprávě
 * @param {string} response Odpověď ze serveru
 * @param {string} status Status odpovědi
 * @param {funkce} processingFunction Funkce, které se mají předat výše zmíněné tři informace
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

/**
 * Funkce, jejímž úkolem je odeslat POST požadavek na server pomocí fetch a zpracovat jednotný formát odpovědí na AJAX požadavek.
 * Z odpovědi extrahuje:
 * 1. Typ zprávy (úspěch, informace, varování, selhání, přesměrování)
 * 2. Zprávu (v případě přesměrování adresu)
 * 3. Pole dalších informací
 * Tyto informace následně vrátí jako objekt (na rozdíl od ajaxCallback je nepředává zpracovávající funkci, ale volající je získá pomocí await).
 * Pokud je typ zprávy nastaven na přesměrování, provádí tato funkce i okamžité přesměrování na adresu uvedenou ve zprávě a vrací null.
 * V případě chyby sítě nebo HTTP chyby (status mimo rozsah 200-299) vyhodí výjimku.
 * @param {string} url Cesta na požadavek
 * @param {Object} params Parametry odesílané na server jako formulářová data (klíč: hodnota)
 * @returns {Promise<{messageType: string, message: string, data: Object}|null>}
 *          Objekt s typem zprávy, zprávou a daty; null v případě přesměrování
 */
async function ajaxPost(url, params)
{
    const res = await fetch(url, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: new URLSearchParams(params),
    });
    //console.log(res);
    if (!res.ok) throw new Error("Response status: " + res.status);

    const { messageType, message, data } = await res.json();

    if (messageType === "redirect")
    {
        window.location = message;
        return null;
    }

    return { messageType, message, data };
}