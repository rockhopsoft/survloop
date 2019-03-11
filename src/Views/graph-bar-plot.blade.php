/* resources/views/vendor/survloop/graph-bar-plot.blade.php */
var data = [{
  type: 'bar',
  x: [ {!! $graph["values"] !!} ],
  y: [ {!! $graph["labels"] !!} ],
  orientation: 'h'
}];
var layout = {
  yaxis: {
    automargin: true
  }
};
Plotly.newPlot('{{ $graph["divName"] }}', data, layout);