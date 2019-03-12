<!-- resources/views/vendor/survloop/admin/db/network-map.blade.php -->
<div>
    <i>All {{ sizeof($GLOBALS['SL']->tbls) }} Tables with Primary/Foreign Key Linkages:</i>
</div>
<canvas id="myCanvas" width="{{ $canvasDimensions[0] }}" height="{{ $canvasDimensions[1] }}"></canvas>
<script>
var canvas = document.getElementById("myCanvas");

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
        line{{ $i }}.strokeStyle = "{!! $css['color-success-on'] !!}";
    @endif
    line{{ $i }}.stroke();
    <?php /* if (!isset($tables[$line[0]])) {
        $errors .= '<br />missing table point tables['.$line[0].'][0] = '. $tables[$line[0]][0]; } */ ?>
@endforeach

@foreach ($tables as $i => $tbl)
    var table{{ $i }} = canvas.getContext("2d");
    table{{ $i }}.beginPath();
    table{{ $i }}.arc({{ $tbl[2] }}, {{ $tbl[3] }}, {{ $tbl[1] }}, 0, 2 * Math.PI, false);
    table{{ $i }}.fillStyle = "{!! $css['color-main-faint'] !!}";
    table{{ $i }}.fill();
    table{{ $i }}.lineWidth = 1;
    table{{ $i }}.strokeStyle = "{!! $css['color-success-on'] !!}";
    table{{ $i }}.stroke();
@endforeach

@foreach ($tables as $i => $tbl)
    var table{{ $i }}txtShade = canvas.getContext("2d");
    table{{ $i }}txtShade.font = "bold 10pt Helvetica";
    table{{ $i }}txtShade.textAlign = "center";
    table{{ $i }}txtShade.fillStyle = "{!! $css['color-main-faint'] !!}";
    table{{ $i }}txtShade.fillText("{{ $tbl[0] }}", {{ ($tbl[2]-1) }}, {{ ($tbl[3]+4) }});
@endforeach
@foreach ($tables as $i => $tbl)
    var table{{ $i }}txtShade = canvas.getContext("2d");
    table{{ $i }}txtShade.font = "bold 10pt Helvetica";
    table{{ $i }}txtShade.textAlign = "center";
    table{{ $i }}txtShade.fillStyle = "{!! $css['color-main-faint'] !!}";
    table{{ $i }}txtShade.fillText("{{ $tbl[0] }}", {{ ($tbl[2]+1) }}, {{ ($tbl[3]+6) }});
@endforeach

@foreach ($tables as $i => $tbl)
    var table{{ $i }}txt = canvas.getContext("2d");
    table{{ $i }}txt.font = "bold 10pt Helvetica";
    table{{ $i }}txt.textAlign = "center";
    table{{ $i }}txt.fillStyle = "{!! $css['color-main-text'] !!}";
    table{{ $i }}txt.fillText("{{ $tbl[0] }}", {{ $tbl[2] }}, {{ ($tbl[3]+5) }});
@endforeach

</script></center>

<span style="color: #FF0000;">{!! $errors !!}</span>
