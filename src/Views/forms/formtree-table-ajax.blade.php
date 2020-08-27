/* resources/views/survloop/forms/formtree-table-ajax.blade.php */

@if (isset($tableDat['month']) && intVal($tableDat['month']) > 0)

var monthFld{{ $nIDtxt }} = "n{{ $tableDat['month'] }}FldID";
var monthFld{{ $nIDtxt }}StartMonth = {{ (date("n")+1) }};
var monthFld{{ $nIDtxt }}StartYear = {{ (date("y")-1) }};

var cols{{ $nIDtxt }} = new Array();
var rowIDs{{ $nIDtxt }} = new Array();
var rowLabs{{ $nIDtxt }} = new Array();
var rowData{{ $nIDtxt }} = new Array();
var idToRow{{ $nIDtxt }} = new Array();

@forelse ($tableDat["cols"] as $k => $col)
    cols{{ $nIDtxt }}[{{ $k }}] = @if (isset($col->nID)) {{ $col->nID }} @elseif (isset($col->node_id)) {{ $col->node_id }} @else 0 @endif ;
@empty
@endforelse

@forelse ($tableDat["rows"] as $j => $row)
    /* Row lookup by month */
    idToRow{{ $nIDtxt }}[{{ $row['id'] }}] = {{ $j }};

    /* Row label element IDs */
    rowLabs{{ $nIDtxt }}[{{ $j }}] = "n{{ $nIDtxt }}tbl{{ $j }}rowLab";

    /* Form field element IDs */
    rowIDs{{ $nIDtxt }}[{{ $j }}] = new Array();
    rowIDs{{ $nIDtxt }}[{{ $j }}][0] = "n{{ $nIDtxt }}tbl{{ $j }}fldRowID";

    /* Month table record primary key */
    rowData{{ $nIDtxt }}[{{ $j }}] = new Array();
    rowData{{ $nIDtxt }}[{{ $j }}][0] = {{ $row['id'] }};

    @forelse ($row['cols'] as $k => $col)
        rowIDs{{ $nIDtxt }}[{{ $j }}][{{ ($k+1) }}] = "n"+cols{{ $nIDtxt }}[{{ $k }}]+"tbl{{ $j }}FldID";
        rowData{{ $nIDtxt }}[{{ $j }}][{{ ($k+1) }}] = "{{ $tableDat["rows"][$j]["data"][$k] }}";
    @empty
    @endforelse
@empty
@endforelse

function loadMonthFlds{{ $nIDtxt }}(first) {
    if (document.getElementById(monthFld{{ $nIDtxt }}) && (first || (monthFld{{ $nIDtxt }}StartMonth != document.getElementById(monthFld{{ $nIDtxt }}).value))) {
        if (!first) pullMonthUpdates{{ $nIDtxt }}();
        chkNewMonthForLayout{{ $nIDtxt }}();
        pushNewMonthLayout{{ $nIDtxt }}();
    }
    /* setTimeout(function() { loadMonthFlds{{ $nIDtxt }}(false); }, 2000); */
}
setTimeout(function() { loadMonthFlds{{ $nIDtxt }}(true); }, 50);

$(document).on("change", "#"+monthFld{{ $nIDtxt }}+"", function() {
//console.log("loadMonthFlds{{ $nIDtxt }}");
    loadMonthFlds{{ $nIDtxt }}(false);
    return true;
});


function chkNewMonthForLayout{{ $nIDtxt }}() {
    monthFld{{ $nIDtxt }}StartMonth = parseInt(document.getElementById(monthFld{{ $nIDtxt }}).value)+1;
    monthFld{{ $nIDtxt }}StartYear = {{ (date("y")-1) }};
    if (monthFld{{ $nIDtxt }}StartMonth > {{ date("n") }}) {
        monthFld{{ $nIDtxt }}StartYear--;
    }
}

function pushNewMonthLayout{{ $nIDtxt }}() {
    var currMonth = monthFld{{ $nIDtxt }}StartMonth;
    var currYear = monthFld{{ $nIDtxt }}StartYear;
    if (currMonth == 13) {
        currMonth = 1;
        currYear++;
    }
    for (var i = 0; i < 12; i++) {
        var currRow = currMonth-1;
        if (document.getElementById(rowLabs{{ $nIDtxt }}[i])) {
            document.getElementById(rowLabs{{ $nIDtxt }}[i]).innerHTML="<div class=\"monthAbbr\">"+monthAbbr[currMonth]+"</div><div class=\"monthAbbr\">'"+currYear+"</div><div class=\"fC\"></div>";
        }
//console.log("i: "+i+", currMonth: "+currMonth+", row: "+currRow+" --- "+monthAbbr[currMonth]+" '"+currYear+" into "+rowLabs{{ $nIDtxt }}[i]+" pushing [0] "+rowData{{ $nIDtxt }}[currRow][0]+" into "+rowIDs{{ $nIDtxt }}[i][0]+", [1] "+rowIDs{{ $nIDtxt }}[i][1]+" will be "+rowData{{ $nIDtxt }}[currRow][1]);
        for (var k = 0; k < (1+cols{{ $nIDtxt }}.length); k++) {
            if (document.getElementById(rowIDs{{ $nIDtxt }}[i][k])) {
                document.getElementById(rowIDs{{ $nIDtxt }}[i][k]).value=rowData{{ $nIDtxt }}[currRow][k];
            } else {
                document.getElementById(rowIDs{{ $nIDtxt }}[i][k]).value="";
            }
        }
        currMonth++;
        if (currMonth == 13) {
            currMonth = 1;
            currYear++;
        }
    }
}

function pullMonthUpdates{{ $nIDtxt }}() {
    var currMonth = monthFld{{ $nIDtxt }}StartMonth;
    var currYear = monthFld{{ $nIDtxt }}StartYear;
    if (currMonth == 13) {
        currMonth = 1;
        currYear++;
    }
    for (var i = 0; i < 12; i++) {
        var currRow = currMonth-1;
//console.log("i: "+i+", currMonth: "+currMonth+", row: "+currRow+" --- "+monthAbbr[currMonth]+" '"+currYear+" ("+rowLabs{{ $nIDtxt }}[i]+") ... pulling [0] "+rowIDs{{ $nIDtxt }}[i][0]+" which is "+document.getElementById(rowIDs{{ $nIDtxt }}[i][0]).value+", [1] "+rowIDs{{ $nIDtxt }}[i][1]+" which is "+document.getElementById(rowIDs{{ $nIDtxt }}[i][1]).value+"");
        for (var k = 0; k < (1+cols{{ $nIDtxt }}.length); k++) {
            if (document.getElementById(rowIDs{{ $nIDtxt }}[currRow][k]).value != rowData{{ $nIDtxt }}[currRow][k]) {
                rowData{{ $nIDtxt }}[currRow][k]=document.getElementById(rowIDs{{ $nIDtxt }}[i][k]).value;
            } else {
                rowData{{ $nIDtxt }}[currRow][k]="";
            }
        }
        currMonth++;
        if (currMonth == 13) {
            currMonth = 1;
            currYear++;
        }
    }
}


@endif
