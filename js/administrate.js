function hideAllTabs()
{
	$("#tab1").hide();
	$("#tab2").hide();
	$("#tab3").hide();
	$("#tab4").hide();
	$("#tab5").hide();
	$("#tab6").hide();
	
	$("#tab1").removeClass("activeTab");
	$("#tab2").removeClass("activeTab");
	$("#tab3").removeClass("activeTab");
	$("#tab4").removeClass("activeTab");
	$("#tab5").removeClass("activeTab");
	$("#tab6").removeClass("activeTab");
}
function firstTab()
{
	hideAllTabs();
	
	$("#tab1").show();
	$("#tab1").addClass("activeTab");
}
function secondTab()
{
	hideAllTabs();
	
	$("#tab2").show();
	$("#tab2").addClass("activeTab");
}
function thirdTab()
{
	hideAllTabs();
	
	$("#tab3").show();
	$("#tab3").addClass("activeTab");
}
function fourthTab()
{
	hideAllTabs();
	
	$("#tab4").show();
	$("#tab4").addClass("activeTab");
}
function fifthTab()
{
	hideAllTabs();
	
	$("#tab5").show();
	$("#tab5").addClass("activeTab");
}
function sixthTab()
{
	hideAllTabs();
	
	$("#tab6").show();
	$("#tab6").addClass("activeTab");
}

/*-------------------------------------------------------*/
/*-------------------------Tab 6-------------------------*/
function sendSqlQuery()
{
	let query = $("#sqlQueryInput").val();
	$.post('administrate-action',
		{
			action:"execute sql query",
			query:query
		},
		function(response)
		{
			result = JSON.parse(response)['dbResult'];
			$("#sqlResult").html(result);
		}
	);
}