const pictureManager = new PictureManager();

$(function()
{
    next();

    //eventy listenery tlačítek
    $("#next-button").click(function() {next()});
    
    //event listener formuláře na odeslání odpovědi
    $("#answer-form").submit(function(event) {answer(event)});

    //event listener chyby načítání obrázku
    $("#main-img").on("error", function() {imgErrorHandle()});
})

/**
 * Funkce odesílající zadanou odpověď na server ke kontrole
 * @param {event} event
 */
function answer(event)
{
    event.preventDefault();
    
    $("#submit-answer-button").addClass("disabled");

    let ans = $("#answer").val();
    let num = $("#answer-hidden").val();
    $("#answer-hidden").val(-1);
    
    let url = window.location.href;
    if (url.endsWith('/')) { url = url.slice(0, -1); } //dstranění trailing slashe (pokud je přítomen)
    url = url.substr(0, url.lastIndexOf("/")); //odstranění akce (/test)

    if (num == -1) return;

    $.post(
        url + "/check-test-answer",
        {
            qNum: num,
            ans: ans
        },
        function (response, status) { ajaxCallback(response, status, displayResult); }
    );
}

/**
 * Funkce zobrazující, zda uživatel odpověděl správně
 * @param {string} messageType Typ hlášky
 * @param {string} message Text hlášky
 * @param {string} data Dodatečné informace
 */
function displayResult(messageType, message, data)
{
    let $correctAnswer = $("<p class='correct'></>").text("Správně!"); 
    let $correctTypoAnswer = $("<p class='correct-typo'></p>").text("Správně, ale s překlepem."); 
    let $incorrectAnswer = $("<p class='incorrect'></p>").text("Špatně."); 
    let $correction = $("<p class='correction'></p>").text("Správná odpověď je: ");
    
    $("#result-text").empty();

    //odpověď byla uznána
    if (message === "correct")
    {
        //odpověď bez překlepů
        if (softCheck($("#answer").val(), data.answer))
        {
            $("#result-text").append($correctAnswer);
        }
        //odpověď s překlepy
        else
        {
            $("#result-text").append($correctTypoAnswer);
            $("#result-text").append($correction);
            $correction.append("<span>" + data.answer + "</span>");
        }
    }
    //odpověď nebyla uznána
    else if (message === "wrong")
    {
        $("#result-text").append($incorrectAnswer);
        $("#result-text").append($correction);
        $correction.append("<span>" + data.answer + "</span>");
    }
    else
    {
        newMessage(message, "error");
    }
    
    $("#result").show();
    $("#next-button").focus();
}

/**
 * Funkce kontrolující správnost odpovědi
 * Chybějící diakritika a různě veliká písmena nejsou považovány za chybu
 * @param answer Zadaná odpověď
 * @param correct Správná odpověď
 * @returns TRUE, pokud je odpověď správná, FALSE, pokud ne
 */
function softCheck(answer, correct)
{
    //Kód napsaný podle odpovědi na StackOverflow: https://stackoverflow.com/a/37511463
    answer = answer.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    correct = correct.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    
    if (answer === correct) return true;
    else return false;
}

/**
 * Funkce zobrazující další obrázek uložený ve správci obrázků (pictureManager)
 */
function next()
{
    //pokud je dosavadní obrázek nahlašován, je nahlašování zrušeno
    if ($(".report-box").hasClass("show"))
    {
        cancelReport();
    }
        
    //nastavení načítání
    $("#main-img").attr("src","images/loading.svg");
    
    $("#result").hide();
    $("#answer").val("");
    $("#answer").focus();
    
    //získání dalšího obrázku
    if (!pictureManager.picturesAvailable())
    {
        pictureManager.loadPictures(true);    //argument true zajistí, že po obdržení odpovědi a načtení obrázků bude tato funkce zavolána znovu
        return;
    }
    
    let newPicture = pictureManager.getNextPicture();
    let newNum = newPicture["num"];
    let newUrl = newPicture["url"];

    $("#main-img").attr("src", newUrl);
    $("#answer-hidden").val(newNum);

    $("#submit-answer-button").removeClass("disabled");
}

/**
 * Funkce nahrazující špatně načtené obrázky
 */
function imgErrorHandle()
{
    $("#main-img").attr("src", '/images/file-error_o.svg');
}