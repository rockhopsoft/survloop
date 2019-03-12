/* resources/views/vendor/survloop/reports/graph-bar-plot.blade.php */
var data = [{
  type: 'bar',
  x: [ {!! $graph["values"] !!} ],
  y: [ {!! $graph["labels"] !!} ],
  orientation: 'h'
}];
var layout = {
  @if (isset($height)) height: {{ $height }}, @endif
  yaxis: {
    automargin: true
  }
};
Plotly.newPlot('{{ $graph["divName"] }}', data, layout);