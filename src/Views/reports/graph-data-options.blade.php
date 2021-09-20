/* resources/views/vendor/survloop/reports/graph-data-options.blade.php */

var options = {
    responsive: true,
    maintainAspectRatio: false,
    @if ($printTitle) title: {
        display: true,
        position: "top",
        text: {!! json_encode(strip_tags($title)) !!},
        fontSize: @if ($GLOBALS["SL"]->isMobile()) 18, @else 26, @endif
        fontColor: "{{ $colorTxt }}"
    }, @endif
    legend: { @if ($type == 'line')
        display: true,
        position: "bottom",
        labels: {
            fontColor: "{{ $colorTxt }}",
            fontSize: @if ($GLOBALS["SL"]->isMobile()) 10 @else 14 @endif
        }
        @else display: false @endif
    },
    tooltips: {
        enabled: true,
        titleFontSize: @if ($GLOBALS["SL"]->isMobile()) 10 @else 14 @endif @if ($type == 'scatter') ,
        callbacks: {
            label: function(tooltipItem, data) {
               var label = data.labels[tooltipItem.index];
               return label + ': (' + tooltipItem.xLabel + ', ' + tooltipItem.yLabel + ')';
            }
        } @endif
    },
    scales: {
    @if ($axisX->label != '')
        xAxes: [{
            display: true,
            ticks: {
                @if (($axisX->min !== null && $axisX->min != 'NULL') || $axisX->min === 0) min: {!! $axisX->min !!} @if ($axisX->max !== null && $axisX->max != 'NULL') , @endif @endif
                @if ($axisX->max !== null && $axisX->max != 'NULL') max: {!! $axisX->max !!} @endif
            },
            scaleLabel: {
                display: true,
                labelString: {!! json_encode(strip_tags($axisX->label)) !!}
            }
        }],
    @endif
        yAxes: [
            {
                id: 'unitLeft',
                display: true,
            @if ($axisY->label != '')
                scaleLabel: {
                    display: true,
                    labelString: {!! json_encode(strip_tags($axisY->label)) !!}
                },
            @endif
                type: "{{ $axisY->scale }}",
                ticks: {
                    @if (($axisY->min !== null && $axisY->min != 'NULL') || $axisY->min === 0) min: {!! $axisY->min !!}, @endif
                    @if ($axisY->max !== null && $axisY->max != 'NULL') max: {!! $axisY->max !!}, @endif
                    callback: function(value, index, values) {
                        if (parseInt(value) >= 1000) {
                            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value;
                        }
                    }
                },
                position: 'left'
            } @if ($axisY2->label != '') ,
            {
                id: 'unitRight',
                display: true,
                scaleLabel: {
                    display: true,
                    labelString: {!! json_encode(strip_tags($axisY2->label)) !!}
                },
                type: "{{ $axisY2->scale }}",
                ticks: {
                    @if (($axisY2->min !== null && $axisY2->min != 'NULL') || $axisY2->min === 0) min: {!! $axisY2->min !!}, @endif
                    @if ($axisY2->max !== null && $axisY2->max != 'NULL') max: {!! $axisY2->max !!}, @endif
                    callback: function(value, index, values) {
                        if (parseInt(value) >= 1000) {
                            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value;
                        }
                    }
                },
                position: 'right'
            } @endif
        ]
    }
};
