<!-- resources/views/vendor/survloop/reports/graph-trend-lines.blade.php -->
<canvas id="node{{ $nIDtxt }}graph" class="w100" style="height: {{ ((isset($height)) ? $height : 400) }}px;"></canvas>
<script>
var ctx = document.getElementById("node{{ $nIDtxt }}graph");
var myChart = new Chart(ctx, {
    height: {{ ((isset($height)) ? $height : 400) }}, 
    type: 'line',
    data: {
        labels: [ @foreach ($axisLabels as $i => $lab) "{{ $lab }}", @endforeach ],
        datasets: [ @forelse ($dataDays as $dLet => $dat)
            {
                label: "{{ $datMap[$dLet]['lab'] }}",
                fill: false,
                lineTension: 0.1,
                backgroundColor: "{{ $datMap[$dLet]['brdClr'] }}",
                borderColor: "{{ $datMap[$dLet]['brdClr'] }}",
                borderCapStyle: 'butt',
                borderDash: [],
                borderDashOffset: 0.0,
                borderJoinStyle: 'miter',
                pointBorderColor: "{{ $datMap[$dLet]['dotClr'] }}",
                pointBackgroundColor: "#fff",
                pointBorderWidth: 1,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "{{ $datMap[$dLet]['dotClr'] }}",
                pointHoverBorderColor: "{{ $datMap[$dLet]['brdClr'] }}",
                pointHoverBorderWidth: 2,
                pointRadius: 1,
                pointHitRadius: 10,
                data: [ @foreach ($dataDays[$dLet] as $d) {{ $d }}, @endforeach ],
            },
        @empty @endforelse ]
    }
});
</script>
