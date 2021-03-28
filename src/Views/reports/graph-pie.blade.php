<!-- resources/views/vendor/survloop/reports/graph-pie.blade.php -->

@if (isset($title)) 
    {!! $title !!}
@elseif (isset($yAxisLab)) 
    {!! $yAxisLab !!}
@elseif (isset($currNode) && isset($currNode->extraOpts["y-axis-lab"])) 
    {!! $currNode->extraOpts['y-axis-lab'] !!}
@endif
<div id="{{ $graphID }}myChartWrap" class="w100 mB30" style="overflow: visible;
    @if (isset($hgt) && $hgt !== null) height: {{ $hgt }}px;
    @elseif (isset($currNode) && isset($currNode->extraOpts['hgt'])) height: {{ $currNode->extraOpts['hgt'] }};
    @else height: 50%; 
    @endif ">
@if (isset($graphFail) && $graphFail)
    <div class="jumbotron w100 h100 mB5"><i>No data found</i></div>
@elseif (isset($data) && is_array($data) && sizeof($data) > 0)
    <div class="w100 pR5">
        <canvas id="{{ $graphID }}myChart" style="width: 100%; 
            @if (isset($hgt) && $hgt !== null) height: {{ $hgt }}px;
            @elseif (isset($currNode) && isset($currNode->extraOpts['hgt'])) height: {{ $currNode->extraOpts['hgt'] }};
            @else height: 100%; 
            @endif " ></canvas>
    </div>
    <script>
    new Chart(document.getElementById("{{ $graphID }}myChart").getContext('2d'), {
        type: 'pie',
        data: {
            datasets: [{
                data: [ @foreach ($data as $i => $dat) {{ $dat->value }}, @endforeach ],
                backgroundColor: [ @foreach ($data as $i => $dat) "{!! $dat->color !!}", @endforeach ],
                @if (isset($title)) label: '{!! $title !!}' @else label: '' @endif
            }],
            labels: [
                @foreach ($data as $i => $dat)
                    {!! json_encode($dat->label) !!},
                @endforeach
            ]
        },
        options: {
            responsive: true
        }
    });
    </script>
@endif
</div>