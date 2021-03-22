var smallTablet = 672;
var tablet = 768;

//vše, co se děje po načtení stránky
$(function() {
	$("#messages").on("click", ".close-message-button", function() {closeMessage(this)})

	//event listener select boxů
	$(".custom-select-wrapper").each(function() {
		if (this.id != "add-natural-select" && this.id != "class-status-select" && !$(this).hasClass("report-natural-select")) 
		{
			$(this).find(".custom-option").first().addClass("selected");
		}
		$(this).click(function() {
			//$(this).find(".custom-option").first().addClass("selected");
			manageSelectBox($(this));
		})
	})

	//event listener kliknutí mimo select box
	$(window).click(function(e) {
		$(".custom-select").each(function() {
			if (!this.contains(e.target)) {
				$(this).removeClass('open');
			}
		})
	});

	//event listener přidávající třídu podle toho, jestli uživatel používá myš nebo tabulátor
	$(window).on("keydown", function(event) { 
		if (event.keyCode === 9)
			$("body").addClass("tab");
	})
	$(window).on("mousedown", function() {
		$("body").removeClass("tab");	
	})
})

function closeMessage($button) {
	$button.closest(".message-item").remove();
}

//Funkce pro získání hodnoty cookie
//Zkopírována z https://www.w3schools.com/js/js_cookies.asp
function getCookie(cname)
{
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++)
	{
		var c = ca[i];
		while (c.charAt(0) == ' ')
		{
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0)
		{
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

//funkce upravující manipulaci s custom select boxy
function manageSelectBox($selectBox)
{
	$selectBox.find(".custom-select").toggleClass("open");
	//pokud je nějaký element zvolený, posune se dropdown tak, aby byl zvolený element vidět
	//neplatí na select element v report boxu - způsobovalo to divné poskočení
	if ($selectBox.find(".custom-options .selected").length != 0 && $selectBox[0] != $("#report-reason")[0]) {
		$selectBox.find(".custom-options .selected")[0].scrollIntoView({ 
			block: 'start',
			inline: 'start' 
		});
	}
	$(".custom-option").each(function() {
		$(this).click(function() {
			if (!$(this).hasClass('selected')) {
				$(this).siblings().removeClass('selected');
				$(this).addClass('selected');
				$(this).closest('.custom-select').find(".custom-select-main span").text($(this).text());
			}
		})
	})
}

function newMessage(message, type, data) {
	$("#messages").prepend($("#message-item-template").html());
	$message = $("#messages .message-item:first-child");
	$message.find(".message").text(message);
	$message.find(".data").text(data);
	$message.addClass(type + "-message");
}

function newConfirm(message, confirmButtonText, cancelButtonText, callback)
{
	$("#popups").append($("#confirm-item-template").html());
	$confirm = $("#popups .confirm-item:last-child");
	$confirm.find(".message").text(message);
	$confirm.find(".confirm-popup-button").text(confirmButtonText);
	$confirm.find(".cancel-popup-button").text(cancelButtonText);
	$confirm.on("click", ".confirm-popup-button", function()
	{
		$confirm.remove();
		callback(true);
	})
	$confirm.on("click", ".cancel-popup-button", function()
	{
		$confirm.remove();
		callback(false);
	})
}

