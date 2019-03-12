<!-- resources/views/vendor/survloop/reports/graph-bar.blade.php -->
@if (isset($title)) {!! $title !!}
@elseif (isset($yAxisLab)) {!! $yAxisLab !!}
@elseif (isset($currNode) && isset($currNode->extraOpts["y-axis-lab"])) {!! $currNode->extraOpts['y-axis-lab'] !!}
@endif
<div class="w100" style="height: @if (isset($hgt)) {{ $hgt }} 
    @elseif (isset($currNode) && isset($currNode->extraOpts['hgt'])) {{ $currNode->extraOpts['hgt'] }} 
    @else auto @endif ; overflow: visible;">
@if (isset($graphFail) && $graphFail)
    <div class="jumbotron w100 h100 mB5"><i>No data found</i></div>
@else
    <canvas id="{{ $currGraphID }}myChart" style="width: 100%; height: 100%;" ></canvas>
    <script>
    new Chart(document.getElementById("{{ $currGraphID }}myChart"),{
        "type":"bar",
        "data": {
            "labels": [ {!! $graph["lab"] !!} ],
            "datasets": [{
                <?php /* @if (isset($yAxisLab))
                    "label":"{!! $yAxisLab !!}",
                @elseif (isset($currNode) && isset($currNode->extraOpts["y-axis-lab"]))
                    "label":"{!! $currNode->extraOpts['y-axis-lab'] !!}",
                @endif */ ?>
                "data":[ {!! $graph["dat"] !!} ],
                "fill":false,
                "backgroundColor":[ {!! $graph["bg"] !!} ],
                "borderColor":[ {!! $graph["brd"] !!} ],
                "borderWidth":1
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