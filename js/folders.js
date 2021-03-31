$(function()
{
	//event listenery tlačítek
	$("#change-folders-layout-button").click(function() {changeFoldersLayout()})
	$(".leave-link").click(function(event) {leaveClass(event)})
	$(".accept-invitation-button").click(function (event) {answerInvitation(event, true)})
	$(".reject-invitation-button").click(function (event) {answerInvitation(event, false)})
	$("#class-code-form").on("submit", function(event) {submitClassCode(event)})
	$("#request-class-button").click(function() {showNewClassForm()})
	$("#request-class-cancel-button").click(function(event) {hideNewClassForm(event)})
	$("#request-class-form").on("submit", function(event) {processNewClassForm(event)})
	$(".display-buttons-button:not(.disabled)").click(function(){displayButtons(this)})
	$(".class-item").click(function(event) {redirectToClass(event)})

	//event listener kliknutí myši
	$(document).mouseup(function(event) {hideButtons(event)});

});

/**
 * Funkce přesměrovávající do třídy
 * @param {event} event 
 */
function redirectToClass(event)
{
	let classLink = $(event.target).closest(".class-item").attr("data-class-url");
	
	//kontrola, jestli uživatel neklikl na link pro opuštění/správu třídy
	if (!$(event.target).is("a"))
	{
		window.location.href = classLink;
	}
}

/**
 * Funkce odesílající požadavek na opuštění třídy
 * @param {event} event 
 */
function leaveClass(event)
{
	let className = $(event.target).closest('.class-item').find("h4").text();
	let confirmMessage = "Opravdu chcete opustit třídu" + className + "?";
	newConfirm(confirmMessage, "Opustit", "Zrušit", function(confirm)
	{
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
								//chyba při zpracování požadavku (například protože je uživatel správce dané třídy)
								newMessage(message, "error");
							}
							else if (messageType === "success")
							{
								//odebrání opuštěné třídy z DOM
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

/**
 * Funkce odesílající odpověď na pozvánku
 * @param {event} event 
 * @param {string} answer Odpověď na pozvánku (accept/reject)
 */
function answerInvitation(event, answer)
{
	let $invitation = $(event.target).closest(".invitation");

	let className = $invitation.find(".col1").text();
	let classUrl = $invitation.attr('data-class-url');
	let classGroupsCount = $invitation.find(".col2").text();

	let ajaxUrl = "menu/" + classUrl + "/invitation/" + ((answer) ? "accept" : "reject");

	$.post(ajaxUrl, {},
		function (response, status)
		{
			ajaxCallback(response, status,
				function (messageType, message, data)
				{
					if (messageType === "error")
					{
						//chyba při zpracování požadavku (zřejmě neplatný formát kódu)
						newMessage(message, "error");
					}
					else if (messageType === "success")
					{
					    if (answer)
					    {
                            //přidání nové třídy na konec seznamu
                            let classDomItem = $('#class-item-template').html();
                            classDomItem = classDomItem.replace(/{name}/g, className);
                            classDomItem = classDomItem.replace(/{url}/g, classUrl);
                            classDomItem = classDomItem.replace(/{groups}/g, classGroupsCount);
                            $(classDomItem).insertAfter('ul > .btn:last');

                            //nastavení event handleru pro opuštění nových tříd
                            $(".leave-link").click(function(event) {leaveClass(event)})
                        }
						
                        $invitation.remove();

						newMessage(message, "success");
					}
				}
			);
		},
		"json"
	);
}

/**
 * Funkce odesílající zadaný kód třídy
 * @param {event} event 
 */
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
						//chyba při zpracování požadavku (zřejmě neplatný formát kódu)
						newMessage(message, "error"); //TODO zobrazit ve formuláři
					}
					else if (messageType === "warning")
					{
						//se zadaným kódem se nelze dostat do žádné třídy
						newMessage(message, "warning"); //TODO zobrazit ve formuláři
					}
					else if (messageType === "success")
					{
						//přidání nových tříd na konec seznamu
						let classes = data.accessedClassesInfo;
						for (let i = 0; i < classes.length; i++)
						{
							let classData = classes[i];
							let classDomItem = $('#class-item-template').html();
							classDomItem = classDomItem.replace(/{name}/g, classData.name);
							classDomItem = classDomItem.replace(/{url}/g, classData.url);
							classDomItem = classDomItem.replace(/{groups}/g, classData.groupsCount);
							$(classDomItem).insertAfter('ul > .btn:last');
						}

						//nastavení event handleru pro opuštění nových tříd
						$(".leave-link").click(function(event) {leaveClass(event)})

						newMessage(message, "success");
					}
				}
			);
		},
		"json"
	);
}

/**
 * Funkce zobrazující formulář na žádost o vytvoření nové třídy
 */
function showNewClassForm()
{
	$("#request-class-button").hide();
	$("#request-class-wrapper > span").hide();
	$("#request-class-form").show();
	$("#new-class-form-name").focus();
}

/**
 * Funkce skrývající formulář na žádost o vytvoření nové třídy
 * @param {event} event 
 */
function hideNewClassForm(event)
{
	event.preventDefault();

	$("#request-class-button").show();
	$("#request-class-wrapper > span").show();
	$("#request-class-form").hide();
	$("#request-class-form .text-field").val("");
}

/**
 * Funkce odesílající požadavek na založení nové třídy
 * @param {event} event 
 */
function processNewClassForm(event)
{
	event.preventDefault();

	let name = $("#new-class-form-name").val();
	let email = $("#new-class-form-email").val();	//pokud pole neexistuje, vrátí undefined
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
						newMessage(message, "error");

						//aktualizace ochrany proti robotům
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

/**
 * Funkce zobrazující tlačítka "Přidat obrázky", "Učit se" a "Vyzkoušet se"
 * @param {*jQuery objekt} $button Tlačítko části poznávačky
 */
function displayButtons($button)
{
	if (!$($button).hasClass("show"))
	{
		$($button).find(".col2, .col3").hide();
		$($button).find(".buttons, .part-info").addClass("show");
		$($button).find("li").addClass("show");
	}
}

/**
 * Funkce skrývající tlačítka "Přidat obrázky", "Učit se" a "Vyzkoušet se"
 * @param {event} event 
 */
function hideButtons(event)
{
	$(".display-buttons-button").each(function()
	{
		//kliknutí mimo tlačítko
		if (!$(this).is(event.target) && $(this).has(event.target).length === 0)
		{
			if ($(this).find("li").hasClass("show"))
			{
				$(this).find(".col2, .col3").show();
				$(this).find(".buttons, .part-info").removeClass("show");
				$(this).find("li").removeClass("show");
			}
		}
	})
}