<!-- resources/views/admin/treeSessions.blade.php -->

<h1><i class="fa fa-snowflake-o"></i> {{ $GLOBALS['SL']->treeName }}: Users Session Stats</nobr></h1>
This report will be providing live data on how far people get through the entire experience, how long it takes, 
what routes were taken, etc...
<div class="p10"></div>

<h3 class="fL m0 slBlueDark">Number of Attempts Which Last Visited The Page</h3>
<h4 class="fR">(Most Recent 100 Submission Attempts)</h4>
<div class="fC"></div>
<canvas id="myChart" style="width: 100%; height: 300px;" ></canvas>
<script src="/survloop/Chart.bundle.min.js"></script>
<script>
var ctx = document.getElementById("myChart");
var myChart = new Chart(ctx, {
    height: 300, 
    type: 'line',
    data: {
        labels: [ @foreach ($axisLabels as $i => $l) "{{ $l }}", @endforeach ],
        datasets: [
            {!! $dataLines !!}
        ]
    }
});
</script>


<h2 class="slBlueDark">Attempts Page & Session History</h2>
<div class="row">
    <div class="col-md-6">
    @forelse ($printRawSessions as $i => $session)
        {!! $session !!}
        @if ($i == round(sizeof($printRawSessions)/2))
            </div><div class="col-md-6">
        @endif
    @empty
    @endforelse
        
    </div>
</div>

<div class="adminFootBuff"></div>
