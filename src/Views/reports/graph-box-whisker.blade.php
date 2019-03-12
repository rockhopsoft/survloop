<!-- resources/views/vendor/survloop/reports/graph-box-whisker.blade.php -->
<?php if (!isset($currGraphID)) $currGraphID = 'rand' . rand(0, 100000); ?>
@if (isset($title)) {!! $title !!}
@elseif (isset($yAxisLab)) {!! $yAxisLab !!}
@elseif (isset($currNode) && isset($currNode->extraOpts["y-axis-lab"])) {!! $currNode->extraOpts['y-axis-lab'] !!}
@endif
<?php /*
<div id="{{ $currGraphID }}myChartWrap" class="w100" style="height: @if (isset($hgt)) {{ $hgt }} 
    @elseif (isset($currNode) && isset($currNode->extraOpts['hgt'])) {{ $currNode->extraOpts['hgt'] }} 
    @else 50% @endif ; overflow: visible;">
@if (isset($graphFail) && $graphFail)
    <div class="jumbotron w100 h100 mB5"><i>No data found</i></div>
@elseif (isset($data) && is_array($data) && sizeof($data) > 0)
    <div class="w100 pR5"><canvas id="{{ $currGraphID }}myChart" style="width: 100%; height: 100%;" ></canvas></div>
    <script>
    new Chart(document.getElementById("{{ $currGraphID }}myChart").getContext('2d'), {
        type: 'pie',
        data: {
            datasets: [{
                data: [
                @foreach ($data as $i => $dat)
                    {{ $dat[0] }},
                @endforeach
                ],
                backgroundColor: [
                @foreach ($data as $i => $dat)
                    {!! $dat[2] !!},
                @endforeach
                ],
                @if (isset($title)) label: '{!! $title !!}' @else label: '' @endif
            }],
            labels: [
                @foreach ($data as $i => $dat)
                    {!! json_encode($dat[1]) !!},
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
*/ ?>