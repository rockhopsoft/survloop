<!-- resources/views/vendor/survloop/reports/graph-bar-grouped.blade.php -->
<div id="{{ $nIDtxt }}graph" class="w100" style="height: {{ 
    ((strpos($height, 'px') > 0 || strpos($height, '%') > 0) 
        ? $height : $height . 'px') }};"></div>
<script type="text/javascript">
@forelse ($datMap as $dLet => $dat)
    var trace{{ $dLet }} = {
      x: [ @foreach ($axisLabels as $i => $lab) @if ($i > 0) , @endif "{{ $lab }}" @endforeach ],
      y: [ @forelse ($dataDays[$dLet] as $i => $val) @if ($i > 0) , @endif {{ $val }} @empty @endforelse ],
      name: '{{ $dat["lab"] }}',
      type: 'bar',
      marker: {
        color: '{{ $GLOBALS["SL"]->printHex2Rgba($dat["dotClr"]) }}'
      }
    };
@empty
@endforelse

var data = [ @forelse ($datMap as $dLet => $dat) @if ($dLet != 'a') , @endif trace{{ $dLet }} @empty @endforelse ];

var layout = {
    barmode: 'group'
};

Plotly.newPlot('{{ $nIDtxt }}graph', data, layout);

</script>