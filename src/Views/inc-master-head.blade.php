<!-- generated from resources/views/vendor/survloop/inc-master-head.blade.php -->

@if (!isset($GLOBALS["SL"]) || !$GLOBALS["SL"]->REQ->has("debug"))
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys1.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys2.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/css/fork-awesome.min.css" rel="stylesheet" type="text/css">
    <script id="sysJs" type="text/javascript" 
        src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys1.min.js"></script>
    <script id="sysJs2" type="text/javascript" 
        src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys2.min.js"></script>
@else
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys1.css?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] 
        }}" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/jquery-ui.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/css/fork-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys2.css?v={{
        $GLOBALS['SL']->sysOpts['log-css-reload'] }}" rel="stylesheet" type="text/css">
    <script src="/jquery.min.js" type="text/javascript"></script>
    <script src="/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/bootstrap.min.js" type="text/javascript"></script>
    <?php /* <script src="/survloop/parallax.min.js" 
        type="text/javascript"></script> */ ?>
    <script id="sysJs" src="/survloop/scripts-lib.js" 
        type="text/javascript"></script>
    {!! $GLOBALS['SL']->debugPrintExtraFilesCSS() !!}
    <script id="sysJs2" src="/sys2.min.js?v={{ 
        $GLOBALS['SL']->sysOpts['log-css-reload'] }}"></script>
@endif
@if ($isWsyiwyg)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" 
        crossorigin="anonymous" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4">
        </script>
@endif
@if ((isset($needsCharts) && $needsCharts) 
    || (isset($GLOBALS["SL"]->x["needsCharts"]) 
        && $GLOBALS["SL"]->x["needsCharts"]))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/Chart.bundle.min.js"></script>
@endif
@if ((isset($needsPlots) && $needsPlots) 
    || (isset($GLOBALS["SL"]->x["needsPlots"]) 
        && $GLOBALS["SL"]->x["needsPlots"]))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/plotly.min.js"></script>
@endif
<?php /* @if ($isWsyiwyg)
    <link rel="stylesheet" type="text/css" href="/content-tools.min.css">
@endif */ ?>
