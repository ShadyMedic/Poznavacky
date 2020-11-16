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
	
	processingFunction(msgType, msg, data);
}