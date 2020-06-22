<!-- resources/views/vendor/survloop/reports/inc-glossary.blade.php -->
@if (sizeof($glossaryList) > 0)
    <h4 class="mT0 mB20 slBlueDark">Glossary of Terms</h4>
    <div class="glossaryList">
    @if (!$GLOBALS["SL"]->isPdfView())
        @foreach ($glossaryList as $i => $gloss)
            <div class="row pT15 pB15">
                <div class="col-md-3">{!! $gloss[0] !!}</div>
                <div class="col-md-9">{!! ((isset($gloss[1])) ? $gloss[1] : '') !!}</div>
            </div>
        @endforeach
    @else
        @foreach ($glossaryList as $i => $gloss)
            <div style="padding-bottom: 5px; font-weight: bold;">
                {!! $gloss[0] !!}
            </div>
            <div style="padding-bottom: 15px;">
                {!! ((isset($gloss[1])) ? $gloss[1] : '') !!}
            </div>
        @endforeach
    @endif
    </div>
@endif