# Zásady ochrany osobních údajů

Služba pro své fungování potřebuje **uchovávat některé informace** o
svých uživatelích. Tato data jsou uchovávána v databázi, nebo textovém
logovacím souboru.

Logovací soubor je po několika týdnech či měsících **obvykle**
vymazán, pokud v daném období nedošlo k žádným problémům vyžadujících
jeho uchování.

Databáze je **zálohována**, pokud je významně ovlivněna (je-li do ní
vloženo větší množství dat, nebo je-li jakýmkoliv způsobem
ovlivněna její struktura).

Obsah logovacího souboru ani databáze **není veřejně přístupný** a jsou
podnikána opatření za účelem udržení obsahů obou datových systémů v
tajnosti.

### V databázi jsou ukládány následující informace:

1.  Seznam všech uživatelských účtů obsahující pro každý účet
    uživatelské jméno, heslo v zašifrované formě, e-mailovou adresu
    (pokud byla poskytnuta), datum a čas posledního přihlášení,
    informaci o poslední navštívené složce (třídě nebo poznávačce),
    informaci o posledním zobrazeném seznamu změn, počet přidaných
    obrázků, počet uhodnutých obrázků na testovací stránce, karma (body
    získávané za chování ve službě) a status (host/člen/správce)

2.  Přihlašovací jména uživatelů, kteří zažádali o změnu jména a jejich
    žádost nebyla dosud vyřešena, uživatelské jméno, které požadují, a časový údaj
    podání žádosti

3.  ID tříd, jejichž správci zažádali o změnu názvu a jejichž žádost
    nebyla dosud vyřešena, název, která je pro třídu požadován, jeho URL
    reprezentace (generovaná podle požadovaného názvu) a časový údaj
    podání žádosti

4.  Seznam kódů sloužících k obnovení zapomenutých hesel v zašifrované
    formě, pro každý z nich ID uživatele, pro kterého bylo vytvořeno a
    časový údaj jeho expirace

5.  Seznam všech souborů cookie, které se vytvoří při zaškrtnutí políčka
    "Zůstat přihlášen" na přihlašovací stránce, který obsahuje pro každý
    soubor cookie jeho obsah v zašifrované formě, ID uživatele, kterým
    je používáno a časový údaj jeho expirace

6.  Seznam všech členství v neveřejných třídách, vždy ID třídy ve
    spojení s ID uživatele, který je členem dané třídy

7.  Seznam všech pozvánek do neveřejných tříd, pro každou z nich ID
    pozvaného uživatele, ID třídy, ze které byla pozvánka odeslána a
    časový údaj její expirace

8.  Seznam všech existujících tříd, pro každou z nich její název, URL
    reprezentace (generovaná podle názvu), počet poznávaček ve třídě,
    status třídy (veřejná/soukromá/uzamčená), vstupní kód pro získání
    členství do soukromé třídy (pokud je přítomen) a ID uživatele, který
    třídu spravuje

9.  Seznam všech poznávaček, pro každou z nich její název, URL
    reprezentace (generovaná podle názvu) počet částí, které obsahuje a
    ID třídy, do které daná poznávačka patří

10. Seznam všech částí poznávaček, pro každou z nich jeji název, URL
    reprezentace (generovaná podle názvu), počet přírodnin, které
    obsahuje, počet obrázků, které se v ní mohou zobrazit a ID
    poznávačky, jejíž je daná část součástí

11. Seznam přírodnin, pro každou z nich její název, počet obrázků k ní
    přidaných a ID třídy, do které patří
    
12. Informace o zařazení jednotlivých přírodnin do částí (pokud jsou
    alespoň do jedné části zařazeny), skládající se vždy z ID přírodniny
    a ID části, do které byla přírodnina přiřazena

13. Nahrané adresy k obrázkům, společně s údajem určující přírodninu,
    kterou mají zobrazovat a informace o tom, zda nebyl obrázek skryt
    administrátorem
    
14. Seznam hlášení obsahující pro každé z nich ID nahlášeného obrázku,
    důvod, případné další informace poskytnuté nahlašovatelem a počet
    hlášení stejného typu pro daný obrázek.

### V logovacím souboru jsou uchovávány následující informace:

Po téměř každém požadavku na zobrazení jakéhokoli obsahu nebo odeslání
jakýchkoli dat je do logovacího souboru zapsána nová zpráva označující
provedenou akci. Tato zpráva vždy obsahuje časový údaj (s přesností na
sekundy) a IP adresu, ze které byl požadavek odeslán. Pokud byl při
provádění akce přihlášen nějaký uživatel, zaznamenává se jeho ID.

Následně zpráva obsahuje informace specifické pro daný typ požadavku.
Například při přihlašování se zaznamenává jméno uživatele, který se
přihlásil, při požadavcích v sekci pro správu třídy se zase téměř vždy
zaznamenává ID spravované třídy. **Nikdy** se do tohoto souboru
nezaznamenávají zadaná hesla, a to ani správná, ani špatná. Za účelem
budoucího zlepšení služeb se zaznamenávají jiné neplatné údaje zadané
například při registraci. Příkladem je uživatelské jméno, které
obsahuje nepovolené znaky. Pokud zjistíme, že větší počet uživatelů se
pokouší získat uživatelské jméno s jistým nepovoleným znakem,
prověříme, zda jeho povolení nepředstavuje hrozbu a případně jej
povolíme.

Úplný seznam všech logovacích zpráv by byl příliš dlouhý, ale jelikož
je Služba open-source, je možné vše dohledat v online úložišti projektu
na GitHub, na adrese <https://github.com/ShadyMedic/Poznavacky>. Pro
vyhledávání míst, kde se do logovacího souboru zapisuje jakákoli zpráva
zadejte do vyhledávání fragment `(new Logger(true))`.

##### *Platnost k 20. dubnu 2021*
