<!-- resources/views/vendor/survloop/print-header-legal.blade.php -->
<div class="row slPrint">
    <div id="logoPrint" class="col-4">
        {!! view(
            'vendor.survloop.elements.logo-print', 
            [ "sysOpts" => $GLOBALS["SL"]->sysOpts ]
        )->render() !!}
    </div>
    <div class="col-8 opac33 taR">
        {!! view(
            'vendor.survloop.elements.dbdesign-legal', 
            [
                "sysOpts"    => $GLOBALS["SL"]->sysOpts,
                "alignRight" => true
            ]
        )->render() !!}
    </div>
</div>
