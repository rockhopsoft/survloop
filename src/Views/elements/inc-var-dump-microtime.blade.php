<!-- resources/views/vendor/survloop/elements/inc-var-dump-microtime.blade.php -->
@if (isset($log) && is_array($log) && sizeof($log) > 0)
    <div class="pT20">
        <table border=0 class="table table-striped" >
            <tr>
                <th><h3>Page MicroLog</h3></th>
                <th><h3>Time Elapsed</h3></th>
            </tr>
        @foreach ($log as $l)
            <tr>
                <td>{{ $l[0] }}</td>
                <td>{{ number_format($l[1]) }}</td>
            </tr>
        @endforeach
        </table>
    </div>
@endif