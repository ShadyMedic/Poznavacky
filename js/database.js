$(function() { $("#tab6-link").addClass("active-tab"); }); //Nabarvi zvolenou záložku

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