<!-- resources/views/vendor/survloop/admin/db/network-map.blade.php -->

<div><b>All {{ sizeof($GLOBALS["DB"]->tbls) }} Tables with Primary/Foreign Key Linkages:</b></div>
<canvas id="myCanvas" width="{{ $canvasDimensions[0] }}" height="{{ $canvasDimensions[1] }}"></canvas>
<script>
var canvas = document.getElementById("myCanvas");

@foreach ($tables as $i => $tbl)
    var table{{ $i }} = canvas.getContext("2d");
    table{{ $i }}.beginPath();
    table{{ $i }}.arc({{ $tbl[2] }}, {{ $tbl[3] }}, {{ $tbl[1] }}, 0, 2 * Math.PI, false);
    table{{ $i }}.fillStyle = "#fae333";
    table{{ $i }}.fill();
    table{{ $i }}.lineWidth = 1;
    table{{ $i }}.strokeStyle = "#f6c82e";
    table{{ $i }}.stroke();
@endforeach

@foreach ($keyLines as $i => $line)
    var line{{ $i }} = canvas.getContext("2d");
    line{{ $i }}.beginPath();
    line{{ $i }}.moveTo({{ $tables[$line[0]][2] }}, {{ $tables[$line[0]][3] }});
    line{{ $i }}.lineTo({{ $tables[$line[1]][2] }}, {{ $tables[$line[1]][3] }});
    line{{ $i }}.lineWidth = 1;
    @if ($tables[$line[0]][4] != '')
        line{{ $i }}.strokeStyle = "{{ $tables[$line[0]][4] }}";
    @elseif ($tables[$line[1]][4] != '')
        line{{ $i }}.strokeStyle = "{{ $tables[$line[1]][4] }}";
    @else
        line{{ $i }}.strokeStyle = "#AAAAAA";
    @endif
    line{{ $i }}.stroke();
    <?php /* if (!isset($tables[$line[0]])) { $errors .= '<br />missing table point tables['.$line[0].'][0] = '. $tables[$line[0]][0]; } */ ?>
@endforeach

@foreach ($tables as $i => $tbl)
    var table{{ $i }}txtShade2 = canvas.getContext("2d");
    table{{ $i }}txtShade2.font = "bold 10pt Helvetica";
    table{{ $i }}txtShade2.textAlign = "center";
    table{{ $i }}txtShade2.fillStyle = "#EEEEEE";
    table{{ $i }}txtShade2.fillText("{{ $tbl[0] }}", {{ ($tbl[2]-1) }}, {{ ($tbl[3]+4) }});
    
    var table{{ $i }}txtShade = canvas.getContext("2d");
    table{{ $i }}txtShade.font = "bold 10pt Helvetica";
    table{{ $i }}txtShade.textAlign = "center";
    table{{ $i }}txtShade.fillStyle = "#FFFFFF";
    table{{ $i }}txtShade.fillText("{{ $tbl[0] }}", {{ ($tbl[2]+2) }}, {{ ($tbl[3]+7) }});
@endforeach

@foreach ($tables as $i => $tbl)
    var table{{ $i }}txt = canvas.getContext("2d");
    table{{ $i }}txt.font = "bold 10pt Helvetica";
    table{{ $i }}txt.textAlign = "center";
    table{{ $i }}txt.fillStyle = "#000000";
    table{{ $i }}txt.fillText("{{ $tbl[0] }}", {{ $tbl[2] }}, {{ ($tbl[3]+5) }});
@endforeach

</script></center>

<span style="color: #FF0000;">{!! $errors !!}</span>
