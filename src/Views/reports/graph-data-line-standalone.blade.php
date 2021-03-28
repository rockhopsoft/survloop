<!-- resources/views/vendor/survloop/reports/graph-data-line-standalone.blade.php -->

<canvas id="popGraphPlot{{ $rand }}" height="{{ $height }}" width="100%"></canvas>

@if (isset($axisListY) && sizeof($axisListY) > 2)
    <div class="alert alert-danger fade in alert-dismissible show" style="padding: 10px 15px;">
        * It looks like you selected variables with {{ sizeof($axisListY) }}
        different Y axis units ({{ implode(', ', $axisListY) }}).
        Unfortunately, I haven't figured out how to best handle
        more than two units at a time yet. For best results, please
        remove enough data lines to get back down to two units.
    </div>
@endif


<script type="text/javascript">

var data = {
    labels: [ {!! $labelsX !!} ],
    datasets: [
    @foreach ($dataLines as $l => $line)
        {
            label: {!! json_encode($line->title) !!},
            yAxisID: @if ($axisY2->label != '' && $axisY2->label == $line->unit) 'unitRight', @else 'unitLeft', @endif
            data: [ {!! $line->printData() !!} ],
            backgroundColor: {!! json_encode($line->color) !!},
            borderColor: {!! json_encode($line->color) !!},
            fill: false,
            lineTension: 0,
            radius: 1
        } @if ($l < sizeof($dataLines)-1) , @endif
    @endforeach
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
        "type"       => 'line'
    ]
)->render() !!}

var ctx = $("#popGraphPlot{{ $rand }}");

var myLineChart{{ $rand }} = new Chart(ctx, {
    type: 'line',
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
