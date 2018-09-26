/* 
// once site is in production, rather use:

function simpleLog(severity, item) { ; }

*/

// inline logger
function simpleLog(severity,item) {
    var logTable = document.getElementById('brad-log');
    if (logTable == null) return ;

    // we have a table, do the log.
    newRow = "<tr>" +
        "<td>" + severity + "</td>" +
        "<td>" + new Date().toString() + "</td>" +
        "<td>" + item + "</td>" +
        "</tr>";
    logTable.getElementsByTagName('tbody')[0].innerHTML += newRow;
}
