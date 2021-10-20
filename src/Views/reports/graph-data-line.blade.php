<!-- resources/views/vendor/survloop/reports/graph-data-line.blade.php -->

<div id="graphLineWrap{{ $nIDtxt }}" class="pT15 pL30 pR30">
    <canvas id="graphLine{{ $nIDtxt }}" class="w100" 
        style="height: {{ $height }}px; max-height: {{ $height }}px;"></canvas>
</div>

<script type="text/javascript">

var data = {
    labels: [ @foreach ($axisLabels as $i => $lab) @if ($i > 0) , @endif "{{ $lab }}" @endforeach ],
    datasets: [ @forelse ($datMap as $dLet => $dat) @if ($dLet != 'a') , @endif
        {
            label: "{{ $dat['lab'] }}",
            data: [ @forelse ($dataPts[$dLet] as $i => $val) @if ($i > 0) , @endif {{ $val }} @empty @endforelse ],

            backgroundColor: "{{ $GLOBALS['SL']->printHex2Rgba($dat['dotClr']) }}",
            borderColor: "{{ $GLOBALS['SL']->printHex2Rgba($dat['dotClr']) }}",
            fill: false,
            lineTension: 0,
            radius: 3
        }
    @empty @endforelse ]
};

var ctx = $("#graphLine{{ $nIDtxt }}");

var options = {
    responsive: true,
    @if (isset($title) && trim($title) != '') title: {
        display: true,
        position: "top",
        text: "{!! $title !!}",
        fontSize: 26,
        fontColor: "#111"
    }, @endif
    legend: {
        display: true,
        position: "bottom",
        labels: {
            fontColor: "#333",
            fontSize: 16
        }
    },
    scales: {
        yAxes: [{
        <?php /* @if ($increment !== null && $increment > 0)
            scaleOverride:true,
            scaleStartValue:0,
            scaleStepWidth:{{ $increment }}
        @endif */ ?>
        }]
    }
};

var myLineChart{{ $nIDtxt }} = new Chart(ctx, {
    type: 'line',
    data: data,
    options: options
});

</script>
