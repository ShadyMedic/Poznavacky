$(function()
{   
    //event listenery tlačítek
    $("#send-sql-query-button").click(function() {sendSqlQuery()})
})


/**
 * Funkce odesílající požadavek na databázi
 */
function sendSqlQuery()
{
    let query = $("#sql-query-input").val();
    
    $.post('administrate-action',
        {
            action:"execute sql query",
            query:query
        },
        function(response)
        {
            let result = response['dbResult'];
            $("#sql-result").html(result);
        }
    );
}