/*-Funkce využívány více stránkami v administrační sekci-*/
function startMail(addressee)
{
    window.location.href = "/administrate/mailsender?to=" + addressee;
}
