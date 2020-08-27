<!-- generated from resources/views/vendor/survloop/inc-master-foot-scripts.blade.php -->

@if (!$GLOBALS["SL"]->isPdfView())
    <div class="disNon">
        <iframe id="hidFrameID" name="hidFrame" src="" height=1 width=1 ></iframe>
    </div>
    <div id="imgPreloadID" class="imgPreload">
    @forelse ($GLOBALS["SL"]->listPreloadImgs() as $src)
        <img src="{{ $src }}" border=0 alt="" >
    @empty
    @endforelse
    </div>
@endif
@if (!$GLOBALS["SL"]->isPdfView())
    @if (isset($GLOBALS['SL']->pageSCRIPTS) && trim($GLOBALS['SL']->pageSCRIPTS) != '')
        {!! $GLOBALS['SL']->pageSCRIPTS !!}
    @endif
    @if ((isset($GLOBALS['SL']->pageJAVA) && trim($GLOBALS['SL']->pageJAVA) != '') 
        || ((isset($GLOBALS['SL']->pageAJAX) && trim($GLOBALS['SL']->pageAJAX) != '')))
        <script id="dynamicJS" type="text/javascript" defer >
        @if (isset($GLOBALS['SL']->pageJAVA) && trim($GLOBALS['SL']->pageJAVA) != '')
            {!! $GLOBALS['SL']->pageJAVA !!}
        @endif
        @if ((isset($GLOBALS['SL']->pageAJAX) && trim($GLOBALS['SL']->pageAJAX) != ''))
            $(document).ready(function(){ {!! $GLOBALS['SL']->pageAJAX !!} }); 
        @endif
        </script>
    @endif
@endif

@if (!$GLOBALS["SL"]->isPdfView())

    <?php /*
    @if (isset($GLOBALS["SL"]->pageView) 
        && in_array($GLOBALS["SL"]->pageView, ['pdf', 'full-pdf']))
        <script id="dynamicJS" type="text/javascript" defer >
        @if ($GLOBALS["SL"]->pageView != 'full-pdf')
            alert("Make sure you are logged in, so that the full complaint is visible here. Then use your browser's print tools to save this page as a PDF. For best results, use Chrome or Firefox.");
        @endif
        setTimeout("window.print()", 1000);
        </script>
    @endif
    */ ?>
    <?php /* @if ($isWsyiwyg)
        <script defer src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
            }}/survloop/ContentTools-master/build/content-tools.min.js"></script>
        <script defer src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
            }}/survloop/ContentTools-master/build/editor.js"></script>
    @endif */ ?>

    @if ($isWsyiwyg)
        <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-bs4.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-bs4.js"></script>
        <?php /* <link href="/summernote.css" rel="stylesheet"> <script defer src="/summernote.min.js"></script> */ ?>
    @endif
    @if (!isset($admMenu) || !$admMenu)
        {!! view('vendor.survloop.elements.inc-google-analytics')->render() !!}
    @endif
@endif