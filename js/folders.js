//vše, co se děje po načtení stránky
$(function() {

	//event listenery tlačítek
	$("#change-folders-layout-button").click(function(){changeFoldersLayout()})
	$("#class-code-form").on("submit", function(event) {submitClassCode(event)})
	$("#request-class-button").click(function() {showNewClassForm()})
	$("#request-class-cancel-button").click(function() {hideNewClassForm(event)})
	$("#request-class-form").on("submit", function(event) {processNewClassForm(event)})
	$(".display-buttons-button").click(function(){displayButtons(this)})

	//event listener kliknutí myši
	$(document).mouseup(function(e){hideButtons(e)});

});

//vše, co se děje při změně velikosti okna
$(window).resize(function(){})

//odešle zadaný kód třídy
function submitClassCode(event)
{
	event.preventDefault();

	let code = $("#class-code-input").val();

	$.post('menu/enter-class-code',
		{
			code: code
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "error")
					{
						//Chyba při zpracování požadavku (zřejmě neplatný formát kódu)
						newMessage(message, "error"); //TODO zobrazit ve formuláři
					}
					else if (messageType === "warning")
					{
						//Se zadaným kódem se nelze dostat do žádné třídy
						newMessage(message, "warning"); //TODO zobrazit ve formuláři
					}
					else if (messageType === "success")
					{
						//Přidání nových tříd na konec seznamu
						let classes = data.accessedClassesInfo;
						for (let i = 0; i < classes.length; i++)
						{
							let classData = classes[i];
							let classDomItem = $('#class-item-template').html();
							classDomItem = classDomItem.replace(/{name}/g, classData.name);
							classDomItem = classDomItem.replace(/{url}/g, classData.url);
							classDomItem = classDomItem.replace(/{groups}/g, classData.groupsCount);
							$(classDomItem).insertAfter('.rows > button:last');
						}

						newMessage(message, "success");
					}
				}
			);
		},
		"json"
	);
}

//zobrazí formulář na žádost o vytvoření nové třídy
function showNewClassForm() {
	$("#request-class-button").hide();
	$("#request-class-wrapper > span").hide();
	$("#request-class-form").show();
	$("#new-class-form-name").focus();
}
//skryje formulář na žádost o vytvoření nové třídy
function hideNewClassForm(event) {
	event.preventDefault();
	$("#request-class-button").show();
	$("#request-class-wrapper > span").show();
	$("#request-class-form").hide();
	$("#request-class-form .text-field").val("");
}
//funkce odesílající AJAX požadavek s informacemi vyplněnými do formuláře pro založení nové třídy
function processNewClassForm(event)
{
	event.preventDefault();

	let name = $("#new-class-form-name").val();
	let email = $("#new-class-form-email").val();	//Pokud pole neexistuje, vrátí undefined
	let info = $("#new-class-form-info").val();
	let antispam = $("#new-class-form-antispam").val();

	$.post('menu/request-new-class',
		{
			className: name,
			email: email,
			text: info,
			antispam: antispam
		},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "error")
					{
						newMessage(message, "error"); //TODO zobrazit ve formuláři
						//Aktualizuj ochranu proti robotům
						$("#antispam-question").text(data.newCaptcha);
						$("#new-class-form-antispam").val("");
					}
					else if (messageType === "success")
					{
						newMessage(message, "success");
						hideNewClassForm();
					}
				}
			);
		},
		"json"
	);
}

//zobrazí tlačítka "Přidat obrázky", "Učit se" a "Vyzkoušet se"
function displayButtons(button) {
	if (!$(button).hasClass("show")) {
		$(button).find(".col2, .col3").hide();
		$(button).find(".buttons").addClass("show");
		$(button).find("li").addClass("show");
	}
}

//skryje tlačítka "Přidat obrázky", "Učit se" a "Vyzkoušet se"
function hideButtons(e) {
	$(".display-buttons-button").each(function(){
		if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
			if ($(this).find("li").hasClass("show")) {
				$(this).find(".col2, .col3").show();
				$(this).find(".buttons").removeClass("show");
				$(this).find("li").removeClass("show");
			}
		}
	})
}

//funkce přepínající zobrazení složek
//neimplementováno
function changeFoldersLayout() {
	if ($(".folders ul").hasClass("tiles")) {
		$(".folders ul").removeClass("tiles");
		$(".folders ul").addClass("rows");
		$(".rows").show();
		$(".tiles").hide();
	}
	else if ($(".folders ul").hasClass("rows")) {
		$(".folders ul").removeClass("rows");
		$(".folders ul").addClass("tiles");
		$(".tiles").show();
		$(".rows").hide();
	}
}

//funkce převádějící rem na pixely
function remToPixels(rem) {    
    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
}