//přidává třídu podle toho, jestli uživatel používá myš nebo tabulátor -> úprava pseudotřídy :focus
window.addEventListener("keydown", (event) => { 
	if (event.keyCode === 9)
		document.body.classList.add("tab");
})
window.addEventListener("mousedown", (event) => {
	document.body.classList.remove("tab");	
})