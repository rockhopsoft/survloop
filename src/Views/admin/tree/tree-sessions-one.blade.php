<!-- resources/views/admin/tree-sessions-one.blade.php -->
<div class="card">
    <div class="card-header">
        <span class="float-right m0">{{ date("m/d/y g:ia", $session["date"]) }}</span>
        <h3>{{ $GLOBALS["SL"]->coreTbl }} #{{ $recID }}</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped"><tbody>
        @forelse ($session["pages"] as $k => $page)
            <tr>
            <td><span class="slBlueDark">{{ $page["node"] }}</span> /{{ $nodeUrls[$page["node"]] }}</td>
            <td> @if ($k == 0) {{ $page["date"] }} @else {{ round($page["time"], 2) }} min @endif </td>
            </tr>
        @empty
        @endforelse
        </tbody></table>
    </div>
</div>