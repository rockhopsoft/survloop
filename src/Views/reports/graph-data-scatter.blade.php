<!-- resources/views/vendor/survloop/reports/graph-data-scatter.blade.php -->

<canvas id="popGraphPlot{{ $rand }}" height="{{ $height }}" width="100%"></canvas>

<script type="text/javascript">

var data = {
    labels: [ {!! $labelsX !!} ],
    datasets: [
    @foreach ($dataLines as $l => $line)
        {
            type: 'scatter',
            yAxisID: @if ($axisY2->label != '' && $axisY2->label == $line->unit) 'unitRight', @else 'unitLeft', @endif
            <?php /* labels: [ {!! $line->printDataLabels() !!} ], */ ?>
            data: [ {!! $line->printDataXY() !!} ],
            backgroundColor: {!! json_encode($line->color) !!},
            borderColor: {!! json_encode($line->color) !!},
            radius: 8
        } @if ($l < sizeof($dataLines)-1) , @endif
    @endforeach @if ($linRegLin !== null) ,
        {
            <?php /* label: {!! json_encode($linRegLin->title) !!}, */ ?>
            type: 'line',
            yAxisID: 'unitLeft',
            data: [ {!! $linRegLin->printDataXY() !!} ],
            backgroundColor: {!! json_encode($linRegLin->color) !!},
            borderColor: {!! json_encode($linRegLin->color) !!},
            borderWidth: 8,
            fill: false,
            lineTension: 0,
            radius: 0
        }
    @endif
    ]
};

{!! view(
    'vendor.survloop.reports.graph-data-options',
    [
        "title"      => $title,
        "axisX"      => $axisX,
        "axisY"      => $axisY,
        "axisY2"     => $axisY2,
        "colorTxt"   => $colorTxt,
        "printTitle" => $printTitle,
        "type"       => 'scatter'
    ]
)->render() !!}

var ctx = $("#popGraphPlot{{ $rand }}");

var myLineChart{{ $rand }} = new Chart(ctx, {
    type: 'scatter',
    data: data,
    options: options
});

</script>

<style>
#popGraphPlot{{ $rand }} {
    width: 100%;
    height: {{ $height }}px;
    max-height: {{ $height }}px;
}
</style>
