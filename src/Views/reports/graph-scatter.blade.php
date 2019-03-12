<!-- resources/views/vendor/survloop/reports/graph-scatter.blade.php -->
@if (isset($title)) {!! $title !!} @endif
<div class="w100" style="height: @if (isset($hgt)) {{ $hgt }} 
    @elseif (isset($currNode) && isset($currNode->extraOpts['hgt'])) {{ $currNode->extraOpts['hgt'] }} 
    @else auto @endif ; overflow: visible;">
@if ((isset($graphFail) && $graphFail) || !isset($data) || empty($data))
    <div class="jumbotron w100 h100 mB5"><i>No data found</i></div>
@else
    <canvas id="{{ $currGraphID }}myChart" style="width: 100%; height: 100%;" ></canvas>
    <script>
    new Chart(document.getElementById("{{ $currGraphID }}myChart"),{
        "type":"scatter",
        "data": {
            "datasets": [{
                @if (isset($title)) "label": "{!! strip_tags($title) !!}", @endif
                "backgroundColor":"{!! $GLOBALS['SL']->printHex2Rgba($dotColor, 0.15) !!}",
                "borderColor":"{!! $GLOBALS['SL']->printHex2Rgba($brdColor, 0) !!}",
                "pointRadius":15,
                "borderWidth":0,
                "showLine":false,
                "fill":false,
                "data":[{
                    @foreach ($data as $i => $d) @if ($i > 0) }, { @endif x: {{ $d[0] }}, y: {{ $d[1] }} @endforeach 
                }]
            }]
        },
        "options": {
            "legend": {"display":false},
            "scales": {
                @if (isset($xAxes))
                "xAxes":[{
                    "scaleLabel":{"display":true, "labelString":"{{ $xAxes }}",},
                    "ticks":{"beginAtZero":true}
                }],
                @endif
                "yAxes":[{
                    @if (isset($yAxes)) "scaleLabel":{"display":true, "labelString":"{{ $yAxes }}"}, @endif
                    "ticks":{"beginAtZero":true}
                }]
            }
        }
    });
    </script>
@endif
</div>