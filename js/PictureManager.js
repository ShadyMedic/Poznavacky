/**
 * Objekt reprezentující obrázek
 * @param num Číslo, pod kterým je v $_SESSION na serveru uložena správná odpověď
 * @param url URL adresa obrázku k zobrazení
 */
class Picture
{
    constructor(num, url) {
        this.num = num;
        this.url = url;
    }
    
}

/**
* Objekt reprezentující správce obrázků, který uchovává jejich data
* a v případě potřeby načítá další ze serveru.
*/
class PictureManager {

    constructor(pictCount = -1) {
        this.pictures = [];
        this.callNext = false;
        this.pictCount = pictCount; 
    }

    /**
     * Načte ze serveru náhodné obrázky.
     * @param {boolean} callNextUponResponse
     * @param {int} numberOfPictures Počet obrázků, který se má načíst. Default null (řízeno backend konstantou)
     */
    loadPictures(callNextUponResponse, numberOfPictures = null, onLoaded = null) {
        this.callNext = callNextUponResponse;

        let url = window.location.href;
        if (url.endsWith('/')) {
            url = url.slice(0, -1); 
        } //odstranění trailing slashe (pokud je přítomen)
        url = url.substring(0, url.lastIndexOf("/")); //Odstranění akce (/test)

        const data = {};

        if (numberOfPictures !== null) {
            data.numberOfPictures = numberOfPictures;
        }

        $.get(
            url + "/test-pictures",
            (response, status) => {
                ajaxCallback(response, status, (messageType, message, data) => {
                    if (messageType === "success") {

                        // namapuje data ze serveru na uložené objekty
                        this.pictures = data.pictures.map(p => new Picture(p.num, p.url));

                        // starý JS callback
                        if (this.callNext === true) {
                            next();
                        }

                        // Preact callback
                        if (onLoaded) {
                            onLoaded();
                        }

                    } else {
                        newMessage(message, "error");
                    }
                });
            },
            "json"
        );
    }

    getNextPicture() {
        return this.pictures.shift();
    }

    picturesAvailable() {
        return this.pictures.length > 0;
    }
}

// k dispozici globálně (dočasné, než bude JS psáno přes moduly)
window.Picture = Picture;
window.PictureManager = PictureManager;
