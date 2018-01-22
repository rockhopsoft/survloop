<!-- resources/views/vendor/survloop/graph-bar.blade.php -->
<div class="w100" style="height: {{ $currNode->extraOpts['hgt'] }}; overflow: visible;">
@if (isset($graphFail) && $graphFail)
    <div class="jumbotron w100 h100 mB5"><i>No data found</i></div>
@else
    <div class="w100 pR5"><canvas id="myChart" style="width: 100%; height: 100%;" ></canvas></div>
    <script src="/survloop/Chart.bundle.min.js"></script>
    <script>
    new Chart(document.getElementById("myChart"),{
        "type":"bar",
        "data": {
            "labels": [ {!! $graph["lab"] !!} ],
            "datasets": [{
                @if (isset($currNode) && isset($currNode->extraOpts["y-axis-lab"]))
                    "label":"{!! $currNode->extraOpts['y-axis-lab'] !!}",
                @endif
                "data":[ {!! $graph["dat"] !!} ],                    
                "fill":false,
                "backgroundColor":[ {!! $graph["bg"] !!} ],
                "borderColor":[ {!! $graph["brd"] !!} ],
                "borderWidth":1
            }]
        },
        "options": {
            "scales": {
                "yAxes":[{
                    "ticks":{"beginAtZero":true}
                }]
            }
        }
    });
    </script>
@endif
</div>