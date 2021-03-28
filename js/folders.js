//vše, co se děje po načtení stránky
$(function() {

	//event listenery tlačítek
	$("#change-folders-layout-button").click(function() {changeFoldersLayout()})
	$(".leave-link").click(function(event) {leaveClass(event)})
	$(".accept-invitation-button").click(function (event) {answerInvitation(event, true)})
	$(".reject-invitation-button").click(function (event) {answerInvitation(event, false)})
	$("#class-code-form").on("submit", function(event) {submitClassCode(event)})
	$("#request-class-button").click(function() {showNewClassForm()})
	$("#request-class-cancel-button").click(function() {hideNewClassForm(event)})
	$("#request-class-form").on("submit", function(event) {processNewClassForm(event)})
	$(".display-buttons-button:not(.disabled)").click(function(){displayButtons(this)})
	$(".class-item").click(function(event) {redirectToClass(event)})

	//event listener kliknutí myši
	$(document).mouseup(function(e){hideButtons(e)});

});

//vše, co se děje při změně velikosti okna
$(window).resize(function(){})

//funkce přesměrovávající do třídy
function redirectToClass(event)
{
	let classLink = $(event.target).closest(".class-item").attr("data-class-url");
	
	//kontrola, jestli uživatel neklikl na link pro opuštění/správu třídy
	if (!$(event.target).is("a"))
		window.location.href = classLink;
}

//odešle AJAX požadavek na opuštění dané třídy
function leaveClass(event)
{
	let className = $(event.target).closest('.class-item').find("h4").text();
	let confirmMessage = "Opravdu chcete opustit třídu" + className + "?";
	newConfirm(confirmMessage, "Opustit", "Zrušit", function(confirm){
		if (confirm) 
		{
			let url = $(event.target).attr("data-leave-url");
			let $leftClass = $(event.target).closest('.class-item');

			event.stopPropagation();

			$.post(url, {},
				function (response, status)
				{
					ajaxCallback(response, status,
						function (messageType, message, data)
						{
							if (messageType === "error")
							{
								//Chyba při zpracování požadavku (například protože je uživatel správce dané třídy)
								newMessage(message, "error");
							}
							else if (messageType === "success")
							{
								//Odebrání opuštěné třídy z DOM
								$leftClass.remove();

								newMessage(message, "success");
							}
						}
					);
				},
				"json"
			);
		}
	})
}

//odešle odpověď na pozvánku (pozitivní i negativní)
function answerInvitation(event, answer)
{
	let className = $(event.target).closest(".invitation").find(".col1").text();
	let classUrl = $(event.target).closest("div").attr('data-class-url');
	let classGroupsCount = $(event.target).closest(".invitation").find(".col2").text();

	let ajaxUrl = "menu/" + classUrl + "/invitation/" + ((answer) ? "accept" : "reject");
    let $answeredInvitation = $(event.target).closest(".invitation");

	$.post(ajaxUrl, {},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "error")
					{
						//Chyba při zpracování požadavku (zřejmě neplatný formát kódu)
						newMessage(message, "error");
					}
					else if (messageType === "success")
					{
					    if (answer)
					    {
                            //Přidání nové třídy na konec seznamu
                            let classDomItem = $('#class-item-template').html();
                            classDomItem = classDomItem.replace(/{name}/g, className);
                            classDomItem = classDomItem.replace(/{url}/g, classUrl);
                            classDomItem = classDomItem.replace(/{groups}/g, classGroupsCount);
                            $(classDomItem).insertAfter('.rows > button:last');

                            //Nastavení event handleru pro opuštění nových tříd
                            $(".leave-link").click(function(event) {leaveClass(event)})
                        }

                        //Odstranění pozvánky
                        $answeredInvitation.remove();

						newMessage(message, "success");
					}
				}
			);
		},
		"json"
	);
}

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

						//Nastavení event handleru pro opuštění nových tříd
						$(".leave-link").click(function(event) {leaveClass(event)})

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