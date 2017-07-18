<!-- resources/views/admin/tree-sessions-one.blade.php -->
<div class="panel panel-info">
    <div class="panel-heading">
        <span class="pull-right m0">{{ date("m/d/y g:ia", $session["date"]) }}</span>
        <h3 class="panel-title">{{ $GLOBALS["SL"]->coreTbl }} #{{ $recID }}</h3>
    </div>
    <div class="panel-body">
        <table class="table table-striped"><tbody>
        @forelse ($session["pages"] as $k => $page)
            <tr>
            <td><span class="slBlueLight">{{ $page["node"] }}</span> /{{ $nodeUrls[$page["node"]] }}</td>
            <td> @if ($k == 0) {{ $page["date"] }} @else {{ round($page["time"], 2) }} min @endif </td>
            </tr>
        @empty
        @endforelse
        </tbody></table>
    </div>
</div>